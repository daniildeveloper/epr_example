<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proposal extends Model
{
    public function creator()
    {
        return $this->belongsTo('App\User', 'creator_id');
    }

    public function status()
    {
        return $this->belongsTo('App\Models\ProposalStatus', 'status_id');
    }

    public function services()
    {
        return $this->hasMany('App\Models\ProposalServices');
    }

    public function wares()
    {
        return $this->hasMany('App\Models\ProposalWare');
    }

    public function transactions()
    {
        return $this->hasMany('App\Models\Transactions', 'proposal_id');
    }

    public function taxType()
    {
        return $this->belongsTo('App\Models\TaxType', 'tex_type');
    }
}
