<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Ware order model
 */
class WareOrder extends Model
{
    public function ware() {
        return $this->belongsTo('App\Models\Ware');
    }
}
