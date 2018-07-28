<?php

namespace App\ParsingHelpers;

use App\Models\AccountingDataHistory;
use App\Models\AccountingPeriodEnd;
use App\Models\AccountingPeriodEndDetail;
use App\Models\AccountingPeriodEndPurseDetail;
use App\Models\FoundPurse;
use App\Models\MoneyTransaction;
use App\Models\Packaging;
use App\Models\ProfitCoordinator;
use App\Models\Proposal;
use App\Models\ProposalNotAllowedArgument as Argument;
use App\Models\Purse;
use App\Models\RestFramework;
use App\Models\Sticker;
use App\Models\Ware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProposalHelper
{
    /**
     * Store proposal data
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public static function store($data)
    {
        $proposal_id = DB::table('proposals')->insertGetId(
            [
                'created_at'       => $data['created_at'],
                'updated_at'       => $data['created_at'],
                'client'           => $data['client'],
                'client_phone'     => $data['phone'],
                'object'           => $data['object'],
                'is_with_docs'     => $data['is_with_docs'],
                'tax'              => $data['tax'],
                'client_deadline'  => $data['created_at'],
                'workers_deadline' => $data['created_at'],
                'partner_payment'  => $data['partner_payment'],
                'partner_notes'    => $data['partner_notes'],
                'status_id'        => 1,
                'creator_id'       => 5,
            ]
        );

        foreach ($data['wares'] as $ware) {
            DB::table('proposal_wares')->insert([
                'proposal_id'     => $proposal_id,
                'ware_id'         => $ware['id'],
                'price_per_count' => $ware['price_per_count'],
                'count'           => $ware['count'],
                'created_at'      => $data['created_at'],
                'updated_at'      => $data['created_at'],
            ]);
        }

        return $proposal_id;
    }

    /**
     * @param $proposalID
     */
    public static function allowProposal($proposalID)
    {
        // find proposal by id
        $proposal = Proposal::with('wares', 'wares.ware', 'wares.ware.packaging', 'wares.ware.framework', 'wares.ware.sticker')->findOrFail($proposalID);
        Log::info($proposal);

        $proposalWaresPrice = 0; // total incomes from wares
        // set tax and partners amount
        $waresSelfCost      = 0; // wares self cost
        $partnerAmount      = $proposal->partner_payment ? (int) $proposal->partner_payment : 0; // partner payment
        $workersColorProfit = 0;

        // send to purses to
        foreach ($proposal->wares as $proposal_ware) {
            $proposalWareSelfCost = 0;
            // calculte total incomes for this wares
            $ware = $proposal_ware->ware;

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

        if ($proposal->warranty_case != 1) {

            // define tax
            $tax = 0;

            if ($proposal->is_with_docs === 1) {
                $tax = self::proposalTaxManipulate($proposal->tax, $proposalWaresPrice, $proposal->id, $proposal->code);
            }

            self::partnersMoneyTransaction($partnerAmount, $proposal->id, $proposal->partner_notes);

            // send to total incomes purse
            $totalTransaction              = new MoneyTransaction();
            $totalTransaction->purse_to_id = Purse::where('slug', 'salers_money')->first()->id;
            $totalTransaction->sum         = $proposalWaresPrice + (($proposalWaresPrice * $proposal->tax) / 100) + $workersColorProfit;
            $totalTransaction->proposal_id = $proposal->id;
            $totalTransaction->argument    = "Общий доход за заявку $proposal->code";
            $totalTransaction->save();

            // calculate profit
            $profitTotal = $proposalWaresPrice - $waresSelfCost;
            Log::info('Profit from ' . $proposal->code . ' ' . $profitTotal);
            self::profitCoordinate($profitTotal, $proposal->id, $proposal->tax_type);
        }

        $proposal->status_id = 3;
        $proposal->save();
    }

    public static function parse_ware_name($name)
    {
        $id = 1;
        switch ($name) {
            case 'ДЗ':
                $id = 2;
                break;
            case 'СТС':
                $id = 3;
                break;
            case 'ПР':
                $id = 1;
                break;
            case 'СТЖ':
                $id = 4;
                break;
            case 'мрамор':
                $id = 1;
                break;
            case 'СТ':
                $id = 4;
                break;
            case 'МРС':
                $id = 10;
                break;
            case 'ЭК':
                $id = 4;
                break;

            default:
                // code...
                break;
        }

        return $id;
    }

    /**
     * @param $partnerAmount
     * @param $proposalID
     * @param $partner_notes
     */
    private static function partnersMoneyTransaction(
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
     * Coordinate profit.
     * @param  [type] $profit [description]
     * @return [type]         [description]
     */
    private static function profitCoordinate(
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
     * @param $taxProcent
     * @param $totalAmount
     * @param $proposalID
     * @param $proposalCode
     * @return mixed
     */
    private static function proposalTaxManipulate(
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
    public static function endAccountingPeriod($proposals_to_close)
    {
        $unclosedProposals = null; // lsit of unclosed proposals

        $accountingHistory = [];

        // 3. Считаем прибыль по заявкам
        $proposalsTotal                     = 0;
        $workersTotalIncomes                = 0; // total worker incoomes get from money transactions
        $salersTotalIncomes                 = 0; // total salers incomes get from money transactions
        $workersPurseID                     = Purse::where('slug', 'workers_profit_purse')->first()->id;
        $salersPurseID                      = Purse::where('slug', 'salers_profit_purse')->first()->id;
        $unclosedProposalsIDsArray          = array_values($proposals_to_close); // array for query builder of ids only
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
            $proposal                           = Proposal::with('wares', 'wares.ware.framework', 'wares.ware.packaging', 'wares.ware.sticker')->find($proposalID);
            $proposal->accounting_period_end_id = $accounting->id;
            $proposal->closed                   = true;
            $proposal->save();

            foreach ($proposal->wares as $ware) {
                $proposalsTotal += $ware->price_per_count * $ware->count;

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
            $pp->rest          = self::pursesRestsCalculate($purse->id);
            $pp->save();

            $purse_details[$purse->id] = [
                "name" => $purse->name,
                'rest' => $pp->rest,
            ];
        }

        $accountingHistory['purse_details'] = $purse_details;

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
    }

    public static function closeProposalSuccessFly($proposal_id)
    {
        $proposal            = Proposal::find($proposal_id);
        $proposal->status_id = 8;
        $proposal->save();
    }

    /**
     * @param $purseID
     */
    private static function pursesRestsCalculate($purseID)
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
}
