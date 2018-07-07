<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StickerManipulation extends Model
{
    public function sticker()
    {
        return $this->belongsTo('App\Models\Sticker', 'sticker_id');
    }
}
