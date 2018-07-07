<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Модель для хранения информации о покупке упаковки
 */
class PackagingBy extends Model
{
    public function packaging()
    {
        return $this->belongsTo('App\Models\Packaging');
    }
}
