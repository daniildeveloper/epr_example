<?php

namespace App\Models\Manufactory;

use Illuminate\Database\Eloquent\Model;

/**
 * Accounting for clean framework
 */
class CleanFrameworkAccounting extends Model
{

    protected $fillable = ['clean_framework_id', 'rest'];

    public function clean_framework()
    {
        return $this->belongsTo('App\Models\Manufactory\CleanFramework', 'clean_framework_id');
    }
}
