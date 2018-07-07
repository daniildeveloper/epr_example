<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FoundPurse extends Model
{
    public function purse()
    {
        return $this->belongsTo('App\Models\Purse', 'purse_id');
    }
}
