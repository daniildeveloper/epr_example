<?php

namespace App\Http\Controllers\Api\Finances;

use App\Http\Controllers\Controller;
use App\Models\AccountingDataHistory;
use App\Models\AccountingPeriodEnd;
use Illuminate\Http\Request;

class AccountingEditController extends Controller
{
    /**
     * @param Request $request
     */
    public function index(Request $request)
    {
        // 1. Берем все отчетные периоды и и пагинация по 5
        $accountingPeriods = AccountingPeriodEnd::with('accounting_period_end_details')->orderBy('id', 'desc')->paginate(5);

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
