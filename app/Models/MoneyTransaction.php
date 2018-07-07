<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MoneyTransaction extends Model
{
    public function purseFrom()
    {
        return $this->belongsTo('App\Models\Purse', 'purse_from_id');
    }

    public function purseTo()
    {
        return $this->belongsTo('App\Models\Purse', 'purse_to_id');
    }

    public function proposal()
    {
        return $this->belongsTo('App\Models\Proposal', 'proposal_id');
    }
}
