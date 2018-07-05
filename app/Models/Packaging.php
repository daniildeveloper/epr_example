<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Ware component - packaging entity.
 */
class Packaging extends Model
{
    protected $fillable = ['name', 'price', 'minimal_in_stock'];
}
