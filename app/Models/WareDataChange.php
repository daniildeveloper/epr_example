<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WareDataChange extends Model
{
    public function ware()
    {
        return $this->belongsTo('App\Models\Ware');
    }
}
