<?php

namespace App\Models\Manufactory;

use Illuminate\Database\Eloquent\Model;

class CleanFrameworkManipulation extends Model
{
    public function clean_framework()
    {
        return $this->belongsTo('App\Models\Manufactory\CleanFramework', 'clean_framework_id');
    }
}
