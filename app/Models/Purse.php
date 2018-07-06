<?php

namespace App\Models;

use App\Models\AccountingPeriodEnd;
use App\Models\AccountingPeriodEndPurseDetail;
use App\Models\MoneyTransactions;
use App\Models\Purse;
use Illuminate\Database\Eloquent\Model;

class Purse extends Model
{
    /**
     * @return mixed
     */
    public function owner()
    {
        return $this->belongsTo('App\User', 'owner_id');
    }

    /**
     * @return mixed
     */
    public function category()
    {
        return $this->belongsTo('App\Models\PursesCategory', 'category_id');
    }

    /**
     * @return mixed
     */
    public static function restCalculate($purseID)
    {
        $latestPeriod = AccountingPeriodEnd::orderBy('id', 'desc')->first();
        // Log::info('latest period id' . $latest);
        $latest = AccountingPeriodEndPurseDetail::where(function ($query) use ($purseID, $latestPeriod) {
            $query->where('accounting_id', $latestPeriod->id)
                ->where('purse_id', $purseID);
        })->first();
        $tos = MoneyTransactions::where(function ($query) use ($purseID, $latest) {
            $query->where('purse_to_id', $purseID)
                ->where('created_at', '>', $latest->created_at);
        })->get();
        $froms = MoneyTransactions::where(function ($query) use ($purseID, $latest) {
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
