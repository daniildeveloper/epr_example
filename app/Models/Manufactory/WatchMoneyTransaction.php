<?php

namespace App\Models\Manufactory;

use Illuminate\Database\Eloquent\Model;

class WatchMoneyTransaction extends Model
{
    public function watch()
    {
        return $this->belongsTo('App\Models\Manufactory\Watch');
    }
}
