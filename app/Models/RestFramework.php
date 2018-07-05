<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Rest framework entity
 */
class RestFramework extends Model
{
    protected $fillable = ['name', 'price', 'minimal_in_stock'];
}
