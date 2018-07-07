<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Warranty case proposals items
 */
class WarrantyCase extends Model
{
    public function purse()
    {
        return $this->belongsTo('App\Models\Purse', 'answered_purse_id');
    }

    public function proposal()
    {
        return $this->belongsTo('App\Models\Proposal', 'proposal_id');
    }

    public function transaction()
    {
        return $this->belongsTo('App\Models\MoneyTransactions', 'transaction_id');
    }
}
