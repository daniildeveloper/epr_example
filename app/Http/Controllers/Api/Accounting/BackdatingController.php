<?php

namespace App\Http\Controllers\Api\Accounting;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\AccountingPeriodEnd;
use App\Models\AccountingPeriodEndPurseDetail;

class BackdatingController extends Controller
{
    /**
     * @api {POST} /api/backdating/ UpdateBackdating
     * @apiVersion 0.0.1
     * @apiGroup Backdating
     * @apiResponseExample JSON:
     *     {
     *         "period_id": 1,
     *         "diff": 233,
     *     }
     */
    public function update(Request $request) {
        // 1. get accounting period id
        $entry = AccountingPeriodEnd::with('purses')->findOrFail($request->period_id);
        // 2. get accountings after this
        $later_entries = AccountingPeriodEnd::with('purses')->where('id', '>=', $entry->id)->get();

        if (count($later_entries) ===0) {
            return response()->json(['message' => 'latest entry'], 201);
        }
        // 3. recalculate rests and save data
        // 4. send success message
    }

    // private function recalculate($)
}
