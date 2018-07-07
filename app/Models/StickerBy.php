<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Модель для хранения информации о покупке наклейки
 */
class StickerBy extends Model
{
    public function sticker()
    {
        return $this->belongsTo('App\Models\Sticker');
    }
}
