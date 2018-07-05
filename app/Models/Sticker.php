<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Sticker model
 */
class Sticker extends Model
{
    protected $fillable = ['name', 'price', 'minimal_in_stock'];
}
