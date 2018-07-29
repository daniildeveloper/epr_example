<?php

namespace App\Http\Controllers\Api\Finances;

use App\Http\Controllers\Controller;
use App\Models\AccountingDataHistory;
use App\Models\AccountingPeriodEnd;
use App\Models\AccountingPeriodEndDetail;
use App\Models\AccountingPeriodEndPurseDetail;
use App\Models\FoundPurse;
use App\Models\MoneyTransaction;
use App\Models\ProfitCoordinator;
use App\Models\Proposal;
use App\Models\Purse;
use App\Models\PurseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use php_rutils\RUtils;
use Pusher\Pusher;
use Tymon\JWTAuth\Facades\JWTAuth;

class PurseController extends Controller
{

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
     * @param Request $reuqest
     */
    public function store(Request $request)
    {
        $user               = JWTAuth::toUser($request->header('Authorization'));
        $purse              = new Purse();
        $purse->owner_id    = $user->id;
        $purse->name        = $request->name;
        $purse->slug        = RUtils::translit()->slugify($request->name);
        $purse->hard        = false;
        $purse->category_id = 3;
        $purse->save();

        $foundPurse                 = new FoundPurse();
        $foundPurse->purse_id       = $purse->id;
        $foundPurse->profit_procent = $request->profit_procent;
        $foundPurse->target         = $request->target;
        $foundPurse->notes          = $request->notes;
        $foundPurse->save();

        // trigger new purse cration
        $this->pusher->trigger('finances-data-change', 'data.updated', []);

        return response()->json($foundPurse, 200);
    }

    /**
     * @param Request $request
     */
    public function getAllPurses(Request $request)
    {
        $pcs = PurseCategory::all();

        foreach ($pcs as $p) {
            $p->purses = $p->purses;

            foreach ($p->purses as $purse) {
                $purse->rest = $this->pursesRestsCalculate($purse->id);
            }
        }

        return response()->json($pcs);
    }

    /**
     * @param Request $reqeust
     * @param $purse
     */
    public function purseRestCalculate(
        Request $reqeust,
        $purse
    ) {
        return response()->json([
            'rest' => $this->pursesRestsCalculate($purse),
        ], 200);
    }

    /**
     * @param $purseID
     */
    private function pursesRestsCalculate($purseID)
    {
        $purse = Purse::find($purseID);

        // Если кошелек не действителен - средства собраны, или если кошелек - фонд, то по первости возвращаем 0
        if ($purse->open === false) {
            return 0;
        }

        if ($purse->category_id === 3) {
            $tos = MoneyTransaction::where(function ($query) use ($purseID) {
                $query->where('purse_to_id', $purseID);
            })->get();
            $froms = MoneyTransaction::where(function ($query) use ($purseID) {
                $query->where('purse_from_id', $purseID);
            })->get();

            $sumTOS = 0;
            foreach ($tos as $t) {
                $sumTOS += $t->sum;
            }

            $sumFroms = 0;
            foreach ($froms as $f) {
                $sumFroms += $f->sum;
            }

            return $sumTOS - $sumFroms;
        }

        $latestPeriod = AccountingPeriodEnd::orderBy('id', 'desc')->first();

        $latest = AccountingPeriodEndPurseDetail::where(function ($query) use ($purseID, $latestPeriod) {
            $query->where('accounting_id', $latestPeriod->id)
                ->where('purse_id', $purseID);
        })->first();

        if ($latest === null) {
            $latest = AccountingPeriodEndPurseDetail::where(function ($query) use ($purseID, $latestPeriod) {
                $query->where('accounting_id', $latestPeriod->id - 1)
                    ->where('purse_id', $purseID);
            })->first();
            Log::info('Latest period id ' . $latestPeriod->id);
        } else {
            Log::info('Latest period id ' . $latestPeriod->id);
        }

        $tos = MoneyTransaction::where(function ($query) use ($purseID, $latest) {
            $query->where('purse_to_id', $purseID)
                ->where('created_at', '>', $latest->created_at);
        })->get();
        $froms = MoneyTransaction::where(function ($query) use ($purseID, $latest) {
            $query->where('purse_from_id', $purseID)
                ->where('created_at', '>', $latest->created_at);
        })->get();
        // dd($tos);

        $sumTOS = 0;
        foreach ($tos as $t) {
            $sumTOS += $t->sum;
        }

        $sumFroms = 0;
        foreach ($froms as $f) {
            $sumFroms += $f->sum;
        }

        $result = $latest->rest + $sumTOS - $sumFroms;

        return $result;
    }

    /**
     * @param Request $request
     */
    public function purseTakeDown(Request $request)
    {
        $rules = [
            'sum'      => 'required|integer|min:1',
            'purse_id' => 'required|integer',
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            Log::info('Purse take down transaction hasnt validated');
            Log::info($validator->errors()->all());
            return response()->json(['message' => 'Заполните все поля'], 422);
        }

        $purse_id = $request->purse_id;
        $sum      = $request->sum;

        if ($this->pursesRestsCalculate($purse_id) < $sum) {
            return response()->json(['message' => 'Недостаточно средств'], 402);
        }

        $transaction                = new MoneyTransaction();
        $transaction->purse_from_id = $purse_id;
        $transaction->sum           = $sum;
        $transaction->argument      = 'Снятия с кошелька';
        $transaction->save();

        $this->pusher->trigger('finances-data-change', 'data.updated', []);

        return response()->json($transaction, 200);
    }

    /**
     * @param Request $request
     */
    public function purseTakeUp(Request $request)
    {
        $rules = [
            'sum'      => 'required|integer|min:1',
            'purse_id' => 'required|integer',
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            Log::info('Purse take up transaction hasnt validated');
            Log::info($validator->errors()->all());
            return response()->json(['message' => 'Заполните все поля'], 422);
        }

        $purse_id = $request->purse_id;
        $sum      = $request->sum;

        $transaction              = new MoneyTransaction();
        $transaction->purse_to_id = $purse_id;
        $transaction->sum         = $sum;
        $transaction->argument    = 'Пополнение кошелька';
        $transaction->save();

        $this->pusher->trigger('finances-data-change', 'data.updated', []);

        return response()->json($transaction, 200);
    }

    /**
     * @param Request $request
     */
    public function updateProfitCalculator(Request $request)
    {
        // Прибыль цеха
        $val = $request->val;

        $workersProfit = $val;
        $salersProfit  = 100 - $workersProfit;

        $pc                 = ProfitCoordinator::first();
        $pc->workers_profit = $workersProfit;
        $pc->sale_profit    = $salersProfit;
        $pc->save();

        return response()->json($pc, 200);
    }

    /**
     * @api {POST} /api/purse/accounting/store EndAccountingPeriod
     * @apiGroup Accounting
     * @apiVersion 0.0.1
     * @apiDescription
     *     1. Получаем список всех незакрытых заявок за прошедший период
     *     2. Выбираем закрывающую заявку и отсекаем более позние заявки
     *     3. Считаем прибыль по заявкам
     *     4. Распределяем прибыль по % соотношению
     *     5. Вычитаем все транзакции с кошелька и на кошелек с коненой прибыли
     *     6. Перевод денег с системных на персонализированные кошельки
     *     7. Закрытие периода
     * @apiRequestExample:
     *     {
     *         proposals_to_close: [1, 2, 3, 4, 5, ...],
     *     }
     *
     * @param Request $request
     */
    public function endAccountingPeriod(Request $request)
    {
        $unclosedProposals = null; // lsit of unclosed proposals

        $accountingHistory = [];

        // 3. Считаем прибыль по заявкам
        $proposalsTotal                     = 0;
        $workersTotalIncomes                = 0; // total worker incoomes get from money transactions
        $salersTotalIncomes                 = 0; // total salers incomes get from money transactions
        $workersPurseID                     = Purse::where('slug', 'workers_profit_purse')->first()->id;
        $salersPurseID                      = Purse::where('slug', 'salers_profit_purse')->first()->id;
        $unclosedProposalsIDsArray          = array_values($request->proposals_to_close); // array for query builder of ids only
        $accounting_period_packagings_total = 0;
        $accounting_period_stickers_total   = 0;
        $accounting_period_frameworks_total = 0;

        // get all workers incomes
        $workerIncomesMoneyTransactions = MoneyTransaction::where(function ($query) use ($workersPurseID, $unclosedProposalsIDsArray) {
            $query->where('purse_to_id', $workersPurseID)
                ->whereIn('proposal_id', $unclosedProposalsIDsArray);
        })->get();

        // calculate workers Incomes
        foreach ($workerIncomesMoneyTransactions as $transaction) {
            $workersTotalIncomes += $transaction->sum;
            Log::info('Worker total incomes += ' . $transaction->sum);
        }

        // get all salers incomes
        $salersIncomesMoneyTransactions = MoneyTransaction::where(function ($query) use ($salersPurseID, $unclosedProposalsIDsArray) {
            $query->where('purse_to_id', $salersPurseID)
                ->whereIn('proposal_id', $unclosedProposalsIDsArray);
        })->get();

        // calculate salers incomes
        foreach ($salersIncomesMoneyTransactions as $transaction) {
            $salersTotalIncomes += $transaction->sum;
        }

        // 5. Вычитаем все транзакции с кошелька и на кошелек с коненой прибыли
        $latestPeriod                 = AccountingPeriodEnd::orderBy('id', 'desc')->first();
        $latestPeriodCreatedAt        = $latestPeriod->created_at;
        $workersPurseTransactionsFrom = MoneyTransaction::where(function ($query) use ($latestPeriodCreatedAt, $workersPurseID) {
            $query->where('purse_from_id', $workersPurseID)
                ->where('created_at', '>', $latestPeriodCreatedAt);
        });

        $salersPurseTransactionsFrom = MoneyTransaction::where(function ($query) use ($latestPeriodCreatedAt, $salersPurseID) {
            $query->where('purse_from_id', $salersPurseID)
                ->where('created_at', '>', $latestPeriodCreatedAt);
        });

        // minus all from transactions
        foreach ($workersPurseTransactionsFrom as $transaction) {
            $workersTotalIncomes -= $transaction->sum;
        }
        foreach ($salersPurseTransactionsFrom as $transaction) {
            $salersTotalIncomes -= $transaction->sum;
        }

        // 6. Перевод денег с системных на персонализированные кошельки
        $workersProfitTransaction                = new MoneyTransaction();
        $workersProfitTransaction->purse_from_id = $workersPurseID;
        $workersProfitTransaction->purse_to_id   = Purse::where('slug', 'workers_purse')->first()->id;
        $workersProfitTransaction->argument      = "Снятие прибыли цеха";
        $workersProfitTransaction->sum           = $workersTotalIncomes;
        $workersProfitTransaction->save();

        // write accounting history workers incomes
        $accountingHistory['workers_incomes'] = $workersTotalIncomes;

        // 7. Закрытие периода
        $accounting = new AccountingPeriodEnd();
        $accounting->save();

        $salersProfitTransaction                = new MoneyTransaction();
        $salersProfitTransaction->purse_from_id = $salersPurseID;
        $salersProfitTransaction->argument      = 'Снятие прибыли офиса';
        $salersProfitTransaction->sum           = $salersTotalIncomes;
        $salersProfitTransaction->save();

        // accounting history salers total incomes
        $accountingHistory['salers_incomes'] = $salersTotalIncomes;

        // close all unclosed proposals
        foreach ($unclosedProposalsIDsArray as $proposalID) {
            // proposals total payment
            $proposalTotalPayment = 0;
            $proposal                           = Proposal::with('wares', 'wares.ware.framework', 'wares.ware.packaging', 'wares.ware.sticker')->find($proposalID);
            $proposal->accounting_period_end_id = $accounting->id;
            $proposal->closed                   = true;

            foreach ($proposal->wares as $ware) {
                $proposalTotalPayment += $ware->price_per_count * $ware->count;
                $proposalsTotal += $proposalTotalPayment;

                // calculate framework cost
                $rest_framework_price = $ware->ware->framework->price * $ware->count;
                $accounting_period_frameworks_total += $rest_framework_price;

                // calculate packagings cost
                $ware->packaging = $ware->ware->packaging;
                $packaging_price = $ware->ware->packaging->price * $ware->count;
                $accounting_period_packagings_total += $packaging_price;

                // calculate stickers cost
                $ware->sticker = $ware->ware->sticker;
                $sticker_price = $ware->ware->sticker->price * $ware->count;
                $accounting_period_stickers_total += $sticker_price;
            }

            // if we have taxes to pay, then pay taxes
            if ($proposal->is_with_docs === 1) {
                $proposalsTotal += ($proposalTotalPayment * (int) $proposal->tax) / 100;
            }
            $proposal->save();
        }

        $accountingMainTransaction                = new MoneyTransaction();
        $accountingMainTransaction->purse_from_id = Purse::where('slug', 'salers_money')->first()->id;
        $accountingMainTransaction->argument      = 'Обнуление кассы';
        $accountingMainTransaction->sum           = $proposalsTotal;
        $accountingMainTransaction->save();

        $accountingHistory['total'] = $proposalsTotal;

        $purse_details = [];

        $purses = Purse::where('open', 1)->get();

        foreach ($purses as $purse) {
            $pp                = new AccountingPeriodEndPurseDetail();
            $pp->accounting_id = $accounting->id;
            $pp->purse_id      = $purse->id;
            $pp->rest          = $this->pursesRestsCalculate($purse->id);
            $pp->save();

            $purse_details[$purse->id] = [
                "name" => $purse->name,
                'rest' => $pp->rest,
            ];
        }

        $accountingHistory['purse_details'] = $purse_details;

        $this->pusher->trigger('finances-data-change', 'data.updated', []);

        $accountingDataHistory                = new AccountingDataHistory();
        $accountingDataHistory->period_end_id = $accounting->id;
        $accountingDataHistory->storie        = json_encode($accountingHistory);
        $accountingDataHistory->save();

        // save details about incomes
        $packaging_detail                = new AccountingPeriodEndDetail();
        $packaging_detail->accounting_id = $accounting->id;
        $packaging_detail->detail_value  = $accounting_period_packagings_total;
        $packaging_detail->detail_key    = 'packaging_detail';
        $packaging_detail->save();

        $sticker_detail                = new AccountingPeriodEndDetail();
        $sticker_detail->accounting_id = $accounting->id;
        $sticker_detail->detail_value  = $accounting_period_stickers_total;
        $sticker_detail->detail_key    = 'sticker_detail';
        $sticker_detail->save();

        $rest_framework_detail                = new AccountingPeriodEndDetail();
        $rest_framework_detail->accounting_id = $accounting->id;
        $rest_framework_detail->detail_value  = $accounting_period_frameworks_total;
        $rest_framework_detail->detail_key    = 'rest_framework_detail';
        $rest_framework_detail->save();

        $worker_incomes_detail                = new AccountingPeriodEndDetail();
        $worker_incomes_detail->accounting_id = $accounting->id;
        $worker_incomes_detail->detail_value  = $workersTotalIncomes;
        $worker_incomes_detail->detail_key    = 'workers_incomes';
        $worker_incomes_detail->save();

        $salers_incomes_detail                = new AccountingPeriodEndDetail();
        $salers_incomes_detail->accounting_id = $accounting->id;
        $salers_incomes_detail->detail_value  = $salersTotalIncomes;
        $salers_incomes_detail->detail_key    = 'salers_incomes';
        $salers_incomes_detail->save();

        return response()->json([
            'accounting'      => $accounting,
            'workers_incomes' => $workersTotalIncomes,
            'salers_incomes'  => $salersTotalIncomes,
            'details'         => [
                'packaging'      => $packaging_detail,
                'sticker'        => $sticker_detail,
                'rest_framework' => $rest_framework_detail,
            ],
        ], 200);
    }

    /**
     * @param Request $request
     */
    public function getWorkersIncomes(Request $request)
    {
        $workersPurse = Purse::where('slug', 'workers_profit_purse')->first();

        $incomes = MoneyTransaction::where('argument', 'Снятие прибыли цеха')->orderBy('id', 'desc')->paginate(12);

        return response()->json($incomes, 200);
    }

    /**
     * @param Request $request
     */
    public function getSalersIncomes(Request $request)
    {
        // $workersPurse = Purse::where('slug', 'salers')->first();

        $incomes = MoneyTransaction::where('argument', 'Снятие прибыли офиса')->orderBy('id', 'desc')->paginate(12);

        return response()->json($incomes, 200);
    }

    /**
     * @param Request $request
     */
    public function closeFund(Request $request)
    {
        $purse = Purse::find($request->purse_id);

        $purse->open = false;
        $purse->save();

        $rest = $this->pursesRestsCalculate($purse->id);

        $transaction                = new MoneyTransaction();
        $transaction->purse_from_id = $purse->id;
        $transaction->purse_to_id   = Purse::where('slug', 'workers_purse')->first()->id;
        $transaction->sum           = $rest;
        $transaction->argument      = 'Перевод денег с фонда на кошелек цеха';
        $transaction->save();

        $this->pusher->trigger('finances-data-change', 'data.updated', []);

    }

    /**
     * @param Request $request
     */
    public function getProfitCalculatorCurrentState(Request $request)
    {
        $pc = ProfitCoordinator::first();

        return response()->json($pc, 200);
    }

    /**
     * @param Request $request
     */
    public function takeProfit(Request $request)
    {
        $purse_id  = $request->purse_id;
        $sum       = $request->sum;
        $purseToId = 0;

        if ($this->pursesRestsCalculate($purse_id) < $sum) {
            return response()->json(['message' => 'Недостаточно средств'], 402);
        }

        // Вычисляем кошелек на который упадут деньги
        if ($purse_id === 8) {
            $purseToId = Purse::where('slug', 'workers_purse')->first()->id;
        } else {
            return response()->json(['message' => 'Недостаточно средств'], 401);
        }

        $transaction                = new MoneyTransaction();
        $transaction->purse_from_id = $purse_id;
        $transaction->purse_to_id   = $purseToId;
        $transaction->sum           = $sum;
        $transaction->argument      = 'Снятие прибыли с кошелька ' . Purse::find($purse_id)->name;
        $transaction->save();
        // send push notification
        $this->pusher->trigger('finances-data-change', 'data.updated', []);

        return response()->json($transaction, 200);
    }

    private function calculateComponentMoney()
    {
        // get all closed by this proposals ids
        // get wares
        // get each ware costs and update total self costs
    }
}
