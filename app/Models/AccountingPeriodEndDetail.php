<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountingPeriodEndDetail extends Model
{
    public function accountingPeriodEnd()
    {
        return $this->belongsTo('App\Models\AccountingPeriodEnd');
    }
}
