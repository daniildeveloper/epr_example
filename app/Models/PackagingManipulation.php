<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackagingManipulation extends Model
{
    public function packaging()
    {
        return $this->belongsTo('App\Models\Packaging', 'packaging_id');
    }
}
