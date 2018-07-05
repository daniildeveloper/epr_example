<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Ware entity model
 */
class Ware extends Model
{
    protected $fillable = ['name', 'framework_id', 'packaging_id', 'sticker_id'];

    public function framework()
    {
        return $this->belongsTo('App\Models\RestFramework', 'framework_id');
    }

    public function sticker()
    {
        return $this->belongsTo('App\Models\Sticker');
    }

    public function packaging()
    {
        return $this->belongsTo('App\Models\Packaging');
    }
}
