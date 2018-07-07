<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountingDataHistory extends Model
{
    public function accountingPeriodEnd()
    {
        return $this->belongsTo('App\Models\AccountingPeriodEnd');
    }
}
