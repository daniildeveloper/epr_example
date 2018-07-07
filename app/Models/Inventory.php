<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Модель для хранения инвентаризаций
 */
class Inventory extends Model
{
    public function answered() {
      return $this->belongsTo('App\Models\User', 'answered_id');
    }
}
