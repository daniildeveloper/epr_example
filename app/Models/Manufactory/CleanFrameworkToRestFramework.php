<?php

namespace App\Models\Manufactory;

use Illuminate\Database\Eloquent\Model;

class CleanFrameworkToRestFramework extends Model
{
    protected $fillable = ['clean_framework_id', 'rest_framework_id', 'count'];
    public function clean_framework()
    {
        return $this->belongsTo('App\Models\Manufactory\CleanFramework');
    }

    public function rest_framework()
    {
        return $this->belongsTo('App\Models\RestFramework');
    }
}
