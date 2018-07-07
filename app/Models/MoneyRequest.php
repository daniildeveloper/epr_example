<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Модель для хранения денежных запросов
 */
class MoneyRequest extends Model
{
    public function purseFrom()
    {
        return $this->belongsTo('App\Models\Purse', 'purse_from_id');
    }

    public function purseTo()
    {
        return $this->belogsTo('App\Models\Purse', 'purse_to_id');
    }
}
