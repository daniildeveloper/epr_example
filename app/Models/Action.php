<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Action extends Model
{
    protected $fillable = ['action_type_id', 'action', 'user_id'];

    public static function do($typeID, $action, $userID) {
        self::create([
            'action_type_id' => $typeID,
            'action'         => $action,
            'user_id'        => $userID,
        ]);
    }

    public function actionType()
    {
        return $this->belongsTo('App\Models\ActionType', 'action_type_id');
    }

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }
}
