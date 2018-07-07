<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProposalNotAllowedArgument extends Model
{
    public function proposal()
    {
        return $this->belongsTo('App\Models\Proposal');
    }
}
