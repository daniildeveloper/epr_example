<?php

namespace App\Http\Controllers\Api\Proposal;

use Action;
use ActionType;
use App\Http\Controllers\Controller;
use App\Models\DeclinedRests;
use App\Models\FoundPurse;
use App\Models\MoneyTransaction;
use App\Models\Packaging;
use App\Models\PackagingManipulation;
use App\Models\ProfitCoordinator;
use App\Models\Proposal;
use App\Models\ProposalNotAllowedArgument as Argument;
use App\Models\ProposalStatus;
use App\Models\ProposalWare;
use App\Models\Purse;
use App\Models\RestFramework;
use App\Models\RestFrameworkManipulation;
use App\Models\Sticker;
use App\Models\StickerManipulation;
use App\Models\Ware;
use App\Models\WareDataChange;
use App\Models\WarrantyCase;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use JWTAuth;
use Log;
use Pusher\Pusher;
use Validator;

class ProposalController extends Controller
{
    /**
     * @var mixed
     */
    private $pusher;

    public function __construct()
    {
        $options = array(
            'cluster'   => env('PUSHER_APP_CLUSTER'),
            'encrypted' => true,
        );
        $this->pusher = new Pusher(
            env('PUSHER_APP_KEY'),
            env('PUSHER_APP_SECRET'),
            env('PUSHER_APP_ID'),
            $options
        );
    }

    /**
     * @param Request $reqeust
     */
    public function index(Request $request)
    {
        $user   = JWTAuth::toUser($request->header('Authorization'));
        $userID = $user->id;
        // if user has limited access => return only limited
        if ($user->hasRole('saler_limited_ops')) {
            $proposals = Proposal::where(function ($q) use ($userID) {
                $q->where('status_id', '<=', 7);
            })->get();
        } else {
            $proposals = Proposal::where(function ($q) {
                $q->where('status_id', '<=', 7);
            })->get();
        }

        foreach ($proposals as $proposal) {
            $proposal->wares  = ProposalWare::where('proposal_id', $proposal->id)->get();
            $proposal->client = $proposal->client;
            if ($proposal->object_id != null) {
                $proposal->object = $proposal->object;
            }
            if ($proposal->status_id === 2) {
                $proposal->argument = Argument::where('proposal_id', $proposal->id)->orderBy('id', 'desc')->first();
            }
            foreach ($proposal->wares as $ware) {
                $ware = $ware->ware;
            }
        }

        return response()->json($proposals, 200);
    }

    /**
     * @param Request $request
     */
    public function store(Request $request)
    {

        Log::info($request);
        $user = JWTAuth::toUser($request->header('Authorization'));

        // create validation rules
        $input = $request->all();
        $rules = [];

        foreach ($request->wares as $key => $val) {
            $rules['wares.' . $key . '.ware_id']         = 'required|integer|min:1';
            $rules['wares.' . $key . '.price_per_count'] = 'required|integer|min:1';
            $rules['wares.' . $key . '.count']           = 'required|integer|min:1';

            if ($request->wares[$key]['color_doesnt_exist'] !== true) {
                $rules['wares.' . $key . '.color_price'] = 'required|integer|min:1';
                $rules['wares.' . $key . '.color']       = 'required';
            }
        }

        $rules['workers_deadline'] = 'required';
        $rules['client_deadline']  = 'required';

        // tax isnt required by warranty case
        if ($request->warranty_case != true && $request->is_with_docs) {
            $rules['tax'] = 'required|integer';
        }

        // if proposal is warranty case
        if ($request->warranty_case === true) {
            $rules['object'] = 'required';
        }

        $rules['client']       = 'required';
        $rules['client_phone'] = 'required';

        // check if partner payyment exist
        if ($request->partner != false) {
            $rules['partner_payment'] = 'required|integer|min:1';
        }

        // validate data
        $validator = Validator::make($input, $rules);

        if ($validator->fails()) {
            Log::info('Proposal hasnt validated');
            Log::info($validator->errors()->all());
            return response()->json(['message' => 'Заполните все поля', 'errors' => $validator->errors(), 'status' => 'validator_errors'], 200);
        }

        // create proposal
        $proposal               = new Proposal();
        $proposal->code         = $this->generateCode();
        $proposal->client       = $request->client;
        $proposal->client_phone = $request->client_phone;
        $proposal->creator_id   = $user->id;

        if ($request->object != 0) {
            $proposal->object = $request->object;
        }

        if ($request->is_hot) {
            $proposal->is_hot = $request->is_hot;
            // TODO: notifiction about hot notification
        }
        $proposal->tax              = $request->tax;
        $proposal->is_with_docs     = $request->is_with_docs;
        $proposal->client_deadline  = $request->client_deadline;
        $proposal->workers_deadline = $request->workers_deadline;
        $proposal->status_id        = 1;
        $proposal->warranty_case    = $request->warranty_case;
        $proposal->notes            = $request->notes;

        if ($request->is_with_tax) {
            $proposal->tax_type = $request->tax_type;
        }

        // partner payments
        $proposal->partner_payment = $request->partner_payment;
        $proposal->partner_notes   = $request->partner_notes;

        try {
            $proposal->save();
        } catch (Exception $e) {
            $proposal->code = $this->generateCode();
            $proposal->save();
        }

        $proposalWaresPrice = 0; // total incomes from wares
        // set tax and partners amount
        $waresSelfCost = 0; // wares self cost
        $partnerAmount = $request->partner_payment ? (int) $request->partner_payment : 0; // partner payment

        foreach ($request->wares as $ware) {
            $proposalWare                  = new ProposalWare();
            $proposalWare->ware_id         = $ware['ware_id'];
            $proposalWare->proposal_id     = $proposal->id;
            $proposalWare->price_per_count = $ware['price_per_count'];
            $proposalWare->count           = $ware['count'];
            $proposalWare->color           = $ware['color'];
            $proposalWare->color_price     = $ware['color_price'];
            $proposalWare->save();

            // total for this ware
            $totalForThisWare = $ware['price_per_count'] * $ware['count'];

            $proposalWaresPrice += $totalForThisWare; // calculate proposals total

            // color
            $color = [];

            if ($request->warranty_case === true) {
                Log::info('Warranty case proposal ' . $proposal->id . 'Количество товара' . $ware['count']);

                // calculate warranty purse id. TO send here money in all cases now
                $warrantyPurseID = Purse::where('slug', 'warranty_purse')->first()->id;

                $wareW            = Ware::find($ware['ware_id']);
                $wareW->packaging = $wareW->packaging;
                $wareW->framework = $wareW->framework;
                $wareW->sticker   = $wareW->sticker;

                $chemieTransaction                = new MoneyTransaction();
                $chemieTransaction->purse_from_id = $warrantyPurseID;
                $chemieTransaction->sum           = $wareW->framework->price * $ware['count'];
                $chemieTransaction->argument      = "Гарантийный случай по заявке $proposal->code по товару $wareW->name";
                $chemieTransaction->proposal_id   = $proposal->id;
                $chemieTransaction->save();

                $stickerTransaction                = new MoneyTransaction();
                $stickerTransaction->purse_from_id = $warrantyPurseID;
                $stickerTransaction->sum           = $wareW->sticker->price * $ware['count'];
                $stickerTransaction->argument      = "Гарантийный случай по заявке $proposal->code по наклейке $wareW->name";
                $stickerTransaction->proposal_id   = $proposal->id;
                $stickerTransaction->save();

                $packagingTransaction                = new MoneyTransaction();
                $packagingTransaction->purse_from_id = $warrantyPurseID;
                $packagingTransaction->sum           = $wareW->packaging->price * $ware['count'];
                $packagingTransaction->argument      = "Гарантийный случай по заявке $proposal->code по упаковке $wareW->name";
                $packagingTransaction->proposal_id   = $proposal->id;
                $packagingTransaction->save();

                $colorSum = $ware['color_price'] * $ware['count'];

                $colorTransaction                = new MoneyTransaction();
                $colorTransaction->purse_from_id = $warrantyPurseID;
                $colorTransaction->sum           = $colorSum;
                $colorTransaction->argument      = "Гарантийный случай по заявке $proposal->code по цвету " . $ware['color'];
                $colorTransaction->proposal_id   = $proposal->id;
                $colorTransaction->save();

                // store all warranty cases
                $chemieWarrantyCase                 = new WarrantyCase();
                $chemieWarrantyCase->proposal_id    = $proposal->id;
                $chemieWarrantyCase->transaction_id = $chemieTransaction->id;
                $chemieWarrantyCase->save();

                $stickerWarrantyCase                 = new WarrantyCase();
                $stickerWarrantyCase->proposal_id    = $proposal->id;
                $stickerWarrantyCase->transaction_id = $stickerTransaction->id;
                $stickerWarrantyCase->save();

                $packagingWarrantyCase                 = new WarrantyCase();
                $packagingWarrantyCase->proposal_id    = $proposal->id;
                $packagingWarrantyCase->transaction_id = $packagingTransaction->id;
                $packagingWarrantyCase->save();

                if ($colorSum > 0) {
                    $colorWarrantyCase                 = new WarrantyCase();
                    $colorWarrantyCase->proposal_id    = $proposal->id;
                    $colorWarrantyCase->transaction_id = $colorTransaction->id;
                    $colorWarrantyCase->save();
                }
            } else {
                // rest framework only by normal case
                $manipulation               = new RestFrameworkManipulation();
                $manipulation->framework_id = Ware::find($ware['ware_id'])->framework_id;
                $manipulation->count        = $ware['count'];
                $manipulation->proposal_id  = $proposal->id;
                $manipulation->refill       = false;
                $manipulation->save();

                $this->wareAccept($ware['ware_id'], $proposal->id, $totalForThisWare, $ware['count'], $request->tax, $ware['price_per_count'], $color);
            }
        }

        $warrantyAction = $request->warranty_case ? ' гарантийная ' : '';

        Action::do(1, 'Создана новая ' . $warrantyAction . ' заявка ' . $proposal->code, $user->id);
        $this->pusher->trigger('proposal-data-change', 'proposal.created', ['message' => 'new proposal created']);
        return response()->json($proposal, 200);
    }

    /**
     * @param $wareID
     * @param $proposalID
     * @param $total
     * @param $count
     * @param $taxProcent
     * @param array $partner
     * @param array $color
     */
    private function wareAccept(
        $wareID,
        $proposalID,
        $total,
        $count,
        $taxProcent,
        $price,
        $color = []
    ) {
        // Сразу выбираем из бд товар
        $ware = Ware::find($wareID);

        // calculate chemiePrice

        $ware->packaging = $ware->packaging;
        $ware->framework = $ware->framework;
        $ware->sticker   = $ware->sticker;

        $chemieCost = $ware->framework->price * $count;

        // sticker rests manipulation
        $stickerM              = new StickerManipulation();
        $stickerM->sticker_id  = $ware->sticker->id;
        $stickerM->count       = $count;
        $stickerM->refill      = false;
        $stickerM->proposal_id = $proposalID;
        $stickerM->save();

        // packaging rest manipulation
        $packagingM               = new PackagingManipulation();
        $packagingM->packaging_id = $ware->packaging->id;
        $packagingM->count        = $count;
        $packagingM->refill       = false;
        $packagingM->proposal_id  = $proposalID;
        $packagingM->save();

        // ware data changes for proposals
        $this->wareDataChangesTableUpdate($ware->id, $count, $total, $price);

        return 0;
    }

    /**
     * @param $taxProcent
     * @param $totalAmount
     * @param $proposalID
     * @param $proposalCode
     * @return mixed
     */
    private function proposalTaxManipulate(
        $taxProcent,
        $totalAmount,
        $proposalID,
        $proposalCode
    ) {
        // calculate tax
        $sum = ($totalAmount * $taxProcent) / 100;
        Log::info("Tax: total amount = $totalAmount. taxProcent = $taxProcent. Tax total = $sum. proposalID $proposalID");

        $taxTransaction              = new MoneyTransaction();
        $taxTransaction->purse_to_id = Purse::where('slug', 'system_tax')->first()->id;
        $taxTransaction->proposal_id = $proposalID;
        $taxTransaction->sum         = $sum;
        $taxTransaction->argument    = "НДС($taxProcent) по заявке $proposalCode";
        $taxTransaction->save();

        return $sum;
    }

    /**
     * Coordinate profit.
     * @param  [type] $profit [description]
     * @return [type]         [description]
     */
    private function profitCoordinate(
        $profit,
        $proposalID,
        $taxType = 1
    ) {
        $proposal = Proposal::findOrFail($proposalID)->first()->id;

        // if tax_type is for "ТОО" => get fromn tax procents
        if ($taxType === 2) {
            $profitTax = ($profit * 5) / 100;

            $profit -= $profitTax;

            $mt              = new MoneyTransaction();
            $mt->sum         = $profitTax;
            $mt->argument    = "Налог (5%)";
            $mt->purse_to_id = Purse::where('slug', 'system_tax')->first()->id;
            $mt->proposal_id = $proposalID;
            $mt->save();
        }

        // получаем распределение прибыли
        $pc = ProfitCoordinator::first();

        // Вычисляем прибыль для распределения
        $workersProfit = (($profit * $pc->workers_profit) / 100);
        Log::info('Workers profit ' . $workersProfit);
        $salersProfit = $profit - $workersProfit;
        Log::info('Salers profit ' . $salersProfit);

        // send to workers profits
        $workersProfitTransaction              = new MoneyTransaction();
        $workersProfitTransaction->purse_to_id = Purse::where('slug', 'workers_profit_purse')->first()->id;
        $workersProfitTransaction->sum         = $workersProfit;
        $workersProfitTransaction->proposal_id = $proposalID;
        $workersProfitTransaction->argument    = "Прибыль цеха с заявки #$proposalID";
        $workersProfitTransaction->save();

        // send to salers Profit
        $salersProfitTransaction              = new MoneyTransaction();
        $salersProfitTransaction->purse_to_id = Purse::where('slug', 'salers_profit_purse')->first()->id;
        $salersProfitTransaction->sum         = $salersProfit;
        $salersProfitTransaction->proposal_id = $proposalID;
        $salersProfitTransaction->argument    = "Прибыль с заявки #$proposalID";
        $salersProfitTransaction->save();

        // get all open funds
        $funds = Purse::where(function ($query) {
            $query->where('category_id', 3)
                ->where('open', true);
        })->get();

        // if funds opens exists, then send money
        if (count($funds) > 0) {
            Log::info('Отправка денег на кошелек-fund');
            foreach ($funds as $fund) {
                $fundInfo = FoundPurse::where('purse_id', $fund->id)->first(); // purse fund where to send money

                // calculate procents to send
                $workersFundProcent = ($workersProfit * $fundInfo->profit_procent) / 100;
                $salersFundProcent  = ($salersProfit * $fundInfo->profit_procent) / 100;

                // trnsaction to send procents to funds
                $workersFundProcentTransaction                = new MoneyTransaction();
                $workersFundProcentTransaction->purse_from_id = Purse::where('slug', 'workers_profit_purse')->first()->id;
                $workersFundProcentTransaction->sum           = $workersFundProcent;
                $workersFundProcentTransaction->argument      = 'Перечисление денег в фонд ' . $fund->name;
                $workersFundProcentTransaction->purse_to_id   = $fund->id;
                $workersFundProcentTransaction->proposal_id   = $proposalID;
                $workersFundProcentTransaction->save();

                $salersFundProcentTransaction                = new MoneyTransaction();
                $salersFundProcentTransaction->purse_from_id = Purse::where('slug', 'salers_profit_purse')->first()->id;
                $salersFundProcentTransaction->sum           = $salersFundProcent;
                $salersFundProcentTransaction->argument      = 'Перечисление денег в фонд ' . $fund->name;
                $salersFundProcentTransaction->purse_to_id   = $fund->id;
                $salersFundProcentTransaction->proposal_id   = $proposalID;
                $salersFundProcentTransaction->save();
            }
        }
    }

    /**
     * @return mixed
     */
    private function generateCode()
    {
        $dateNow = Carbon::now();
        $month   = $dateNow->month;
        $m       = $this->getCurrentMothInTwoDigits($month);

        $currentPeriod = $dateNow->year - (env('BEGIN_YEAR') - 1);

        if ($currentPeriod < 10) {
            $currentPeriod = '0' . $currentPeriod;
        }

        $latestProposal = DB::table('proposals')
            ->orderBy('id', 'desc')
            ->get();

        if (count($latestProposal) > 0) {
            $proposalArray  = explode('/', $latestProposal[0]->code);
            $latestProposal = (int) $proposalArray[2];
            $latestProposal += 1;

            if ($latestProposal < 10) {
                $latestProposal = '00' . $latestProposal;
            } else if ($latestProposal < 100) {
                $latestProposal = '0' . $latestProposal;
            }
        } else {
            $latestProposal = '001';
        }

        $proposalCode = "$m/$currentPeriod/$latestProposal";

        return $proposalCode;
    }

    /**
     * @param $month
     */
    private function getCurrentMothInTwoDigits($month)
    {
        switch ($month) {
            case 1:
                return 'JN';
                break;
            case 2:
                return 'FB';
                break;
            case 3:
                return 'MR';
                break;
            case 4:
                return 'AP';
                break;
            case 5:
                return 'MY';
            case 6:
                return 'JN';
                break;
            case 7:
                return 'JL';
                break;
            case 8:
                return 'AU';
                break;
            case 9;
                return 'SE';
                break;
            case 10:
                return 'OC';
                break;
            case 11:
                return 'NV';
                break;
            case 12:
                return 'DC';
                break;

            default:
                return 'MM';
                break;
        }
    }

    /**
     * @param Request $request
     */
    public function getProposals(Request $request)
    {
        $proposals = Proposal::where(function ($q) {
            $q->where('status', '<', 7);
        })->get();

        foreach ($proposals as $proposal) {
            $proposal->wares = ProposalWare::where('proposal_id', $proposal->id)->get();

            if ($proposal->status_id === 2) {
                $proposal->argument = Argument::where('proposal_id', $proposal->id)->orderBy('id', 'desc')->first();
            }
        }
    }

    /**
     * @param Request $request
     */
    public function changeProposalStatus(Request $request)
    {
        $user                   = JWTAuth::toUser($request->header('Authorization'));
        $proposalStatusToChange = ProposalStatus::find($request->status_id);
        $proposal               = Proposal::find($request->proposal_id);

        // check, if isnt proposal accepted, can not be in process or ready
        if ($proposalStatusToChange->id === 5 && $proposal->status_id != 3) {
            return response()->json(['message' => 'Невозможно обновить заявку']);
        }

        // if ($proposalStatusToChange->id > 5) {
        //     $proposalStatusID = $proposal->status_id;
        //     $mt               = MoneyTransaction::where(function ($query) use ($proposalStatusID) {
        //         $query
        //             ->where('proposal_id', $proposalStatusID)
        //             ->where('purse_to_id', Purse::where('slug', 'sale_oborot')->first());
        //     })->get();

        //     // check if mt is null
        //     if (count($mt) === 0) {
        //         return response()->json(['message' => 'Невозможно обновить заявку']);
        //     }
        // }

        // проверяем нужный статус.
        if ($proposalStatusToChange->slug === 'decline_candidat') {
            //  todo -1 (general) +0: send notification
            $proposal->status_id = $request->status_id;
            $proposal->save();

            Action::do(3, 'Статус заявки ' . $proposal->code . ' изменился на ' . $proposalStatusToChange->name, $user->id);
            return response()->json(['message' => 'updated', 'proposal' => $proposal], 200);
        } else if ($proposalStatusToChange->slug === 'declined') {
            Log::info('Proposal to decline');
            $proposal            = Proposal::find($request->proposal_id);
            $proposalID          = $request->proposal_id;
            $proposal->status_id = $request->status_id;
            $proposal->save();

            // получаем все транзакции
            $proposalsMoneyTransactions = MoneyTransaction::where(function ($query) use ($proposalID) {
                $query->where('proposal_id', $proposalID);
            })->get();

            // возвращаем транзакции
            foreach ($proposalsMoneyTransactions as $proposalMoneyTransaction) {
                $transaction                = new MoneyTransaction();
                $transaction->purse_from_id = $proposalMoneyTransaction->purse_to_id;
                $transaction->sum           = $proposalMoneyTransaction->sum;
                $transaction->argument      = "Возврат денежных средств по заявке $proposal->id";
                $transaction->proposal_id   = $proposal->id;
                $transaction->save();
            }
            // proposals wares
            $proposal->wares = $proposal->wares;
            Log::info("Proposals wares count " . count($proposal->wares));

            foreach ($proposal->wares as $ware) {
                $ware->sticker              = $ware->ware->sticker;
                $ware->packaging            = $ware->ware->packaging;
                $manipulation               = new RestFrameworkManipulation();
                $manipulation->framework_id = Ware::find($ware['ware_id'])->framework_id;
                $manipulation->count        = $ware['count'];
                $manipulation->proposal_id  = $proposal->id;
                $manipulation->refill       = true;
                $manipulation->save();

                $stickerM              = new StickerManipulation();
                $stickerM->sticker_id  = $ware->sticker->id;
                $stickerM->count       = $ware['count'];
                $stickerM->refill      = true;
                $stickerM->proposal_id = $proposal->id;
                $stickerM->save();

                // packaging rest manipulation
                $packagingM               = new PackagingManipulation();
                $packagingM->packaging_id = $ware->packaging->id;
                $packagingM->count        = $ware['count'];
                $packagingM->refill       = true;
                $packagingM->proposal_id  = $proposal->id;
                $packagingM->save();
            }

            // if (count($request->declinedRests) > 0) {
            //     $this->declineProposalRestsSave($proposal->id, $request->decinedRests);
            //     Log::info('Return declined proposals');
            // }

            Action::do(3, 'Статус заявки ' . $proposal->code . ' изменился на ' . $proposalStatusToChange->name, $user->id);

        } else if ($proposalStatusToChange->slug === 'accepted_workers' && $proposal->accepted === 0) {
            // allow proposal
            $proposal->status_id = $request->status_id;
            $proposal->accepted  = true;
            $proposal->save();
            $this->allowProposal($proposal->id);

            Action::do(3, 'Статус заявки ' . $proposal->code . ' изменился на ' . $proposalStatusToChange->name, $user->id);

            return response()->json(['message' => 'updated', 'proposal' => $proposal], 200);
        } else {
            $proposal->status_id = $request->status_id;
            $proposal->save();

            Action::do(3, 'Статус заявки ' . $proposal->code . ' изменился на ' . $proposalStatusToChange->name, $user->id);

            return response()->json(['message' => 'updated', 'proposal' => $proposal], 200);
        }
    }

    /**
     * @param $id
     */
    public function show($id)
    {
        $proposal        = Proposal::findOrFail($id);
        $proposal->wares = ProposalWare::where('proposal_id', $proposal->id)->get();

        $proposal->object  = $proposal->object;
        $proposal->client  = $proposal->client;
        $proposal->creator = $proposal->creator;

        foreach ($proposal->wares as $ware) {
            $ware->ware = $ware->ware;
        }

        return response()->json($proposal, 200);
    }

    /**
     * @param $proposalID
     */
    public function unallowProposal($proposalID)
    {
        $proposal = Proposal::find($proposalID);

        // получаем все транзакции
        $proposalsMoneyTransactions = MoneyTransaction::where(function ($query) use ($proposalID) {
            $query->where('proposal_id', $proposalID);
        })->get();

        $declinedProposalsPurse = Purse::where('slug', 'declined_proposals_purse')->first();

        // возвращаем транзакции
        foreach ($proposalsMoneyTransactions as $proposalMoneyTransaction) {
            $transaction                = new MoneyTransaction();
            $transaction->purse_from_id = $proposalMoneyTransaction->purse_to_id;
            $transaction->sum           = $proposalMoneyTransaction->sum;
            $transaction->argument      = "Возврат денежных средств по заявке $proposal->id";
            $transaction->save();

            $manipulation               = new RestFrameworkManipulation();
            $manipulation->framework_id = Ware::find($ware['ware_id'])->framework_id;
            $manipulation->count        = $proposal->ware['count'];
            $manipulation->proposal_id  = $proposal->id;
            $manipulation->refill       = true;
            $manipulation->save();

            $stickerM              = new StickerManipulation();
            $stickerM->sticker_id  = $proposal->ware->sticker->id;
            $stickerM->count       = $count;
            $stickerM->refill      = true;
            $stickerM->proposal_id = $proposalID;
            $stickerM->save();

            // packaging rest manipulation
            $packagingM               = new PackagingManipulation();
            $packagingM->packaging_id = $proposal->ware->packaging->id;
            $packagingM->count        = $count;
            $packagingM->refill       = true;
            $packagingM->proposal_id  = $proposalID;
            $packagingM->save();
        }
    }

    /**
     * @param Request $request
     * @api {POST} /api/proposal/change-deadline ChangeProposalDeadline
     * @apiGroup Proposal
     * @apiVersion 0.0.1
     * @apiParamExample JSON:
     *     {
     *         "proposal_id": 1,
     *         "client_deadline": "date",
     *         "workers_deadline": "date"
     *     }
     */
    public function changeProposalDeadline(Request $request)
    {
        // all needed variables from request
        $proposalID       = $request->proposal_id;
        $client_deadline  = $request->client_deadline;
        $workers_deadline = $request->workers_deadline;

        // get proposal
        $proposal = Proposal::find($proposalID);

        // check for proposal status
        if ($proposal->status_id > 2) {
            return response()->json(['message' => ''], 401);
        }

        // ckeck if deadline isnt null, then change it
        if ($client_deadline != null) {
            $proposal->client_deadline = $client_deadline;
        }

        if ($workers_deadline != null) {
            $proposal->workers_deadline = $workers_deadline;
        }

        $proposal->save();

        return response()->json($proposal, 200);
    }

    /**
     * Proposals list with pagination
     * @param  Request $request [description]
     * @return [type]           [description]
     * @api {GET} /api/proposal/proposal/paginate Paginate proposals
     * @apiGroup Proposal
     * @apiVersion 0.0.1
     * @apiDescription Return paginated proposals
     */
    public function proposalPaginate(Request $request)
    {
        $proposals = Proposal::paginate(30);

        foreach ($proposals as $proposal) {
            $proposal->wares  = ProposalWare::where('proposal_id', $proposal->id)->get();
            $proposal->client = $proposal->client;
            if ($proposal->object_id != null) {
                $proposal->object = $proposal->object;
            }
            if ($proposal->status_id === 2) {
                $proposal->argument = Argument::where('proposal_id', $proposal->id)->orderBy('id', 'desc')->first();
            }
            foreach ($proposal->wares as $ware) {
                $ware = $ware->ware;
            }

            $proposal->creator = $proposal->creator;
            $proposal->status  = $proposal->status;
        }

        return response()->json($proposals, 200);
    }

    /**
     * Save declined rests results
     * @param  [type] $proposalID [description]
     * @param  array  $rests      [description]
     * @return [type]             [description]
     */
    private function declineProposalRestsSave(
        $proposalID,
        $rests = []
    ) {
        // 1. Create new instance of declined proposal model for each rests item to save
        foreach ($rests as $rest) {
            $declinedRest                 = new DeclinedRests();
            $declinedRest->proposal_id    = $proposalID;
            $declinedRest->component_type = $rest['component_type'];
            $declinedRest->component_id   = $rest['component_id'];
            $declinedRest->save();
        }
    }

    /**
     * Check for warranty proposal cases exists
     *
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function unclosedWarrantyCaseExist(Request $request)
    {
        //
        $warrantyProposals = Proposal::where(function ($query) {
            $query->where('closed', 0);
        })->get();

        if (count($warrantyProposals) > 0) {
            return response()->json($warrantyProposals, 200);
        } else {
            return response()->json([], 200);
        }
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @api {POST} /api/proposal/proposal/notes-update/:id ProposalNotesUpdate
     * @apiVersion 0.0.1
     * @apiGroup Proposal
     * @apiDescription Update proposal notes
     * @apiParam ID: int to change
     */
    public function changeProposalComment(
        Request $request,
        $id
    ) {
        $user     = JWTAuth::toUser($request->header('Authorization'));
        $proposal = Proposal::findOrFail($id);

        // pick old comment to save to log
        $oldComment = $proposal->notes;

        // update comment
        $proposal->notes = $request->notes;
        $proposal->save();

        // save action to log
        Action::do(ActionType::where('slug', 'proposal_notes_change')->first()->id, 'Новый комментарий к заявке ' . $proposal->code . ' Старый комментарий: ' . $oldComment, $user->id);
        $oldComment = '';
        // return new proposal object
        return response()->json($proposal, 200);
    }

    /**
     * @param $wareID
     * @param $saledUnits
     * @param $incomes
     * @param $price
     */
    private function wareDataChangesTableUpdate(
        $wareID,
        $saledUnits,
        $incomes,
        $price
    ) {
        // array of old data wares changes
        $oldWareDataChangesArray = WareDataChange::where(function ($query) use ($wareID, $price) {
            $query->where('ware_id', $wareID)
                ->where('price', $price)
                ->where('closed', false);
        })
            ->orderBy('id', 'desc')
            ->paginate(5);

        if (count($oldWareDataChangesArray) > 0) {
            if ($oldWareDataChangesArray[0]->price === $price) {
                $wareDataChange                = $oldWareDataChangesArray[0];
                $wareDataChange->total_saled   = $wareDataChange->total_saled + $saledUnits;
                $wareDataChange->total_incomes = $wareDataChange->total_incomes + $incomes;
                $wareDataChange->save();
            } else {
                $wareDataChange                = new WareDataChange();
                $wareDataChange->total_saled   = $saledUnits;
                $wareDataChange->total_incomes = $incomes;
                $wareDataChange->price         = $price;
                $wareDataChange->ware_id       = $wareID;
                $wareDataChange->save();
            }

        } else {
            $wareDataChange                = new WareDataChange();
            $wareDataChange->total_saled   = $saledUnits;
            $wareDataChange->total_incomes = $incomes;
            $wareDataChange->price         = $price;
            $wareDataChange->ware_id       = $wareID;
            $wareDataChange->save();
        }
    }

    /**
     * @param $proposalID
     */
    private function allowProposal($proposalID)
    {
        // find proposal by id
        $proposal = Proposal::findOrFail($proposalID);

        // get proposal wares
        $proposal->wares = $proposal->wares;

        $proposalWaresPrice = 0; // total incomes from wares
        // set tax and partners amount
        $waresSelfCost      = 0; // wares self cost
        $partnerAmount      = $proposal->partner_payment ? (int) $proposal->partner_payment : 0; // partner payment
        $workersColorProfit = 0;

        if ($proposal->warranty_case != 1) {
            // send to purses to
            foreach ($proposal->wares as $proposal_ware) {
                $proposalWareSelfCost = 0;
                // calculte total incomes for this wares
                $ware = Ware::find($proposal_ware->id);

                $ware->packaging = $ware->packaging;
                $ware->framework = $ware->framework;
                $ware->sticker   = $ware->sticker;

                $chemieCost = $ware->framework->price * $proposal_ware->count;
                // send money to chemie price
                $chemieCostTransaction              = new MoneyTransaction();
                $chemieCostTransaction->purse_to_id = Purse::where('slug', 'sale_oborot')->first()->id;
                $chemieCostTransaction->sum         = $chemieCost;
                $chemieCostTransaction->proposal_id = $proposalID;
                $chemieCostTransaction->argument    = "Возвращаем в оборот деньги за химию " . RestFramework::find($ware->framework_id)->name;
                $chemieCostTransaction->save();

                // packaging cost transaction
                $packagingCost                         = $ware->packaging->price * $proposal_ware->count;
                $packagingCostTransaction              = new MoneyTransaction();
                $packagingCostTransaction->purse_to_id = Purse::where('slug', 'sale_packagings')->first()->id;
                $packagingCostTransaction->sum         = $packagingCost;
                $packagingCostTransaction->proposal_id = $proposalID;
                $packagingCostTransaction->argument    = 'Стоимость упаковки ' . $ware->packaging->name;
                $packagingCostTransaction->save();

                // sticker cost
                $stickerCost                         = $ware->sticker->price * $proposal_ware->count;
                $stickerCostTransaction              = new MoneyTransaction();
                $stickerCostTransaction->purse_to_id = Purse::where('slug', 'sale_packagings')->first()->id;
                $stickerCostTransaction->sum         = $stickerCost;
                $stickerCostTransaction->proposal_id = $proposalID;
                $stickerCostTransaction->argument    = 'Стоимость наклейки ' . $ware->sticker->name;
                $stickerCostTransaction->save();

                $proposalWareSelfCost += $packagingCost + $stickerCost + $chemieCost;
                $proposalWaresPrice += $proposal_ware->price_per_count * $proposal_ware->count;
                $waresSelfCost += $proposalWareSelfCost;

                if ($proposal_ware->color != null) {
                    // total color incomes
                    $colorTotal = $proposal_ware->color_price * $proposal_ware->count;

                    // increase workers color profit
                    $workersColorProfit += $colorTotal;

                    // send to workers purse
                    $colorProfitTransaction              = new MoneyTransaction();
                    $colorProfitTransaction->purse_to_id = Purse::where('slug', 'workers_purse')->first()->id;
                    $colorProfitTransaction->sum         = $colorTotal;
                    $colorProfitTransaction->proposal_id = $proposalID;
                    $colorProfitTransaction->argument    = "Отчисление за прибыль по цвету $proposal_ware->color";
                    $colorProfitTransaction->save();
                }
            }
            // define tax
            $tax = 0;

            if ($proposal->is_with_docs === 1) {
                $tax = $this->proposalTaxManipulate($proposal->tax, $proposalWaresPrice, $proposal->id, $proposal->code);
            }

            $this->partnersMoneyTransaction($partnerAmount, $proposal->id, $proposal->partner_notes);

            // send to total incomes purse
            $totalTransaction              = new MoneyTransaction();
            $totalTransaction->purse_to_id = Purse::where('slug', 'salers_money')->first()->id;
            $totalTransaction->sum         = $proposalWaresPrice + (($proposalWaresPrice * $proposal->tax) / 100) + $workersColorProfit;
            $totalTransaction->proposal_id = $proposal->id;
            $totalTransaction->argument    = "Общий доход за заявку $proposal->code";
            $totalTransaction->save();

            // calculate profit
            $profitTotal = $proposalWaresPrice - $waresSelfCost - $partnerAmount;
            Log::info('Profit from ' . $proposal->code . ' ' . $profitTotal);
            $this->profitCoordinate($profitTotal, $proposal->id, $proposal->tax_type);
        }
    }

    /**
     * @param $partnerAmount
     * @param $proposalID
     * @param $partner_notes
     */
    private function partnersMoneyTransaction(
        $partnerAmount,
        $proposalID,
        $partner_notes
    ) {
        // partner money transaction
        $partnerMoneyTransaction              = new MoneyTransaction();
        $partnerMoneyTransaction->sum         = $partnerAmount;
        $partnerMoneyTransaction->purse_to_id = Purse::where('slug', 'affialate')->first()->id;
        $partnerMoneyTransaction->argument    = "Отчисление по партнерской программе. Заметки: " . $partner_notes;
        $partnerMoneyTransaction->proposal_id = $proposalID;
        $partnerMoneyTransaction->save();
    }

    /**
     * @api {GET} /api/proposal/proposal/sort SortProposals
     * @apiGroup Proposal
     * @apiVersion 0.0.1
     * @apiDescription Available sort types:
     *     * newest
     *     * unallowed
     *     * hot
     *     * warranty cases
     */
    public function sort(Request $request, $sort_type)
    {
        $user      = JWTAuth::toUser($request->header('Authorization'));
        $userID    = $user->id;
        $proposals = [];
        switch ($sort_type) {
            case 'newest':
                // if user has limited access => return only limited
                if ($user->hasRole('saler_limited_ops')) {
                    $proposals = Proposal::where(function ($q) use ($userID) {
                        $q->where('status_id', '<=', 7);
                    })->orderBy('created_at', 'desc')->get();
                } else {
                    $proposals = Proposal::where(function ($q) {
                        $q->where('status_id', '<=', 7);
                    })->orderBy('created_at', 'desc')->get();
                }

                foreach ($proposals as $proposal) {
                    $proposal->wares  = ProposalWare::where('proposal_id', $proposal->id)->get();
                    $proposal->client = $proposal->client;
                    if ($proposal->object_id != null) {
                        $proposal->object = $proposal->object;
                    }
                    if ($proposal->status_id === 2) {
                        $proposal->argument = Argument::where('proposal_id', $proposal->id)->orderBy('id', 'desc')->first();
                    }
                    foreach ($proposal->wares as $ware) {
                        $ware = $ware->ware;
                    }
                }
                break;
            case 'unallowed':
                // if user has limited access => return only limited
                if ($user->hasRole('saler_limited_ops')) {
                    $proposals = Proposal::where(function ($q) use ($userID) {
                        $q->where('status_id', '<', 3);
                    })->get();
                } else {
                    $proposals = Proposal::where(function ($q) {
                        $q->where('status_id', '<', 3);
                    })->get();
                }

                foreach ($proposals as $proposal) {
                    $proposal->wares  = ProposalWare::where('proposal_id', $proposal->id)->get();
                    $proposal->client = $proposal->client;
                    if ($proposal->object_id != null) {
                        $proposal->object = $proposal->object;
                    }
                    if ($proposal->status_id === 2) {
                        $proposal->argument = Argument::where('proposal_id', $proposal->id)->orderBy('id', 'desc')->first();
                    }
                    foreach ($proposal->wares as $ware) {
                        $ware = $ware->ware;
                    }
                }
                break;
            case 'hot':
                // if user has limited access => return only limited
                if ($user->hasRole('saler_limited_ops')) {
                    $proposals = Proposal::where(function ($q) use ($userID) {
                        $q->where('status_id', '<=', 7)->where('is_hot', true);
                    })->get();
                } else {
                    $proposals = Proposal::where(function ($q) {
                        $q->where('status_id', '<=', 7)->where('is_hot', true);
                    })->get();
                }

                foreach ($proposals as $proposal) {
                    $proposal->wares  = ProposalWare::where('proposal_id', $proposal->id)->get();
                    $proposal->client = $proposal->client;
                    if ($proposal->object_id != null) {
                        $proposal->object = $proposal->object;
                    }
                    if ($proposal->status_id === 2) {
                        $proposal->argument = Argument::where('proposal_id', $proposal->id)->orderBy('id', 'desc')->first();
                    }
                    foreach ($proposal->wares as $ware) {
                        $ware = $ware->ware;
                    }
                }

            case 'warranty_case':
                // if user has limited access => return only limited
                if ($user->hasRole('saler_limited_ops')) {
                    $proposals = Proposal::where(function ($q) use ($userID) {
                        $q->where('status_id', '<=', 7)->where('warranty_case', true);
                    })->get();
                } else {
                    $proposals = Proposal::where(function ($q) {
                        $q->where('status_id', '<=', 7)->where('warranty_case', true);
                    })->get();
                }

                foreach ($proposals as $proposal) {
                    $proposal->wares  = ProposalWare::where('proposal_id', $proposal->id)->get();
                    $proposal->client = $proposal->client;
                    if ($proposal->object_id != null) {
                        $proposal->object = $proposal->object;
                    }
                    if ($proposal->status_id === 2) {
                        $proposal->argument = Argument::where('proposal_id', $proposal->id)->orderBy('id', 'desc')->first();
                    }
                    foreach ($proposal->wares as $ware) {
                        $ware = $ware->ware;
                    }
                }
            default:
                // if user has limited access => return only limited
                if ($user->hasRole('saler_limited_ops')) {
                    $proposals = Proposal::where(function ($q) use ($userID) {
                        $q->where('status_id', '<=', 7);
                    })->get();
                } else {
                    $proposals = Proposal::where(function ($q) {
                        $q->where('status_id', '<=', 7);
                    })->get();
                }

                foreach ($proposals as $proposal) {
                    $proposal->wares  = ProposalWare::where('proposal_id', $proposal->id)->get();
                    $proposal->client = $proposal->client;
                    if ($proposal->object_id != null) {
                        $proposal->object = $proposal->object;
                    }
                    if ($proposal->status_id === 2) {
                        $proposal->argument = Argument::where('proposal_id', $proposal->id)->orderBy('id', 'desc')->first();
                    }
                    foreach ($proposal->wares as $ware) {
                        $ware = $ware->ware;
                    }
                }
                break;
        }

        return response()->json($proposals, 200);
    }
}
