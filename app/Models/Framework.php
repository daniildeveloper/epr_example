<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Framework entity
 */
class Framework extends Model
{
    protected $fillable = ['name', 'price', 'minimal_in_stock'];
}
