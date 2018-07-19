<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AccountingPeriodEnd;
use App\Models\AccountingPeriodEndDetail;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function test(Request $request)
    {
        // 1. Берем все отчетные периоды и и пагинация по 5
        $accountingPeriods = AccountingPeriodEnd::with('accounting_period_end_details')->orderBy('id', 'desc')->paginate(5);

        foreach ($accountingPeriods as $period) {
            $details = AccountingPeriodEndDetail::where('accounting_id', $period->id)->get();

            $period_details = [
            ];

            foreach ($details as $detail) {
                $period_details[$detail->detail_key] = $detail->detail_value;
            }
            $period->details = $period_details;
        }
        // dd($accountingPeriods);
        // 3. Возвращаем результаты
        return response()->json($accountingPeriods, 200);
    }
}
