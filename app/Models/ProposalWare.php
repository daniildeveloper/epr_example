<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProposalWare extends Model
{
    public function proposal()
    {
        return $this->belongsTo('App\Models\Proposal', 'proposal_id');
    }

    public function ware()
    {
        return $this->belongsTo('App\Models\Ware');
    }
}
