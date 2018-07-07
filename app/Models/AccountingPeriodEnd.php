<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountingPeriodEnd extends Model
{
    public function purses()
    {
        return $this->hasMany('App\Models\AccountingPeriodEndPurseDetail', 'purse_id');
    }

    public function lastProposal()
    {
        return $this->belongsTo('App\Models\Proposal', 'last_proposal_id');
    }

    public function accountingProposals()
    {
        return $this->hasMany('App\Models\AccountingProposal', 'accounting_id');
    }
}
