<?php

namespace App\Http\Controllers\Api\Finances;

use App\Models\AccountingDataHistory;
use App\Models\AccountingPeriodEnd;
use App\Models\Purse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AccountingEditController extends Controller
{
    /**
     * @param Request $request
     */
    public function index(Request $request)
    {
        // 1. Берем все отчетные периоды и и пагинация по 5
        $accountingPeriods = AccountingPeriodEnd::orderBy('id', 'desc')->paginate(5);

        // 2. Идем по отчетным периодам и вытаскивае заявки, закрытые за отчтные период, товары и транзакции
        foreach ($accountingPeriods as $period) {
            $period->accountingProposals = $period->accountingProposals;

            foreach ($period->accountingProposals as $proposal) {
                $proposal->transactions = $proposal->transactions;
                $proposal->wares        = $proposal->wares;
            }
        }

        // 3. Возвращаем результаты
        return response()->json($accountingPeriods, 200);
    }

    public function historyData()
    {
        $accountingPeriods = AccountingPeriodEnd::orderBy('id', 'desc')->paginate(12);

        foreach ($accountingPeriods as $accPer) {
            $accPer->history_data = AccountingDataHistory::where('period_end_id', $accPer->id)->first()->storie;
        }

        return response()->json($accountingPeriods, 200);
    }

    
}
