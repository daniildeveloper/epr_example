<?php

namespace App\Models\Manufactory;

use Illuminate\Database\Eloquent\Model;

class Watch extends Model
{
    public function watcher() {
        return $this->belongsTo('App\User', 'watcher_id');
    }

    public function watch_money_transactions() {
        return $this->hasMany('App\Models\Manufactory\WatchMoneyTransaction');
    }
}
