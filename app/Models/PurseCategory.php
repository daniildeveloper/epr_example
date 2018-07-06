<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurseCategory extends Model
{
    public function purses()
    {
        return $this->hasMany('App\Models\Purse', 'category_id')->where('open', true);
    }
}
