<?php

namespace App\Models;

class DsConnection extends CustomModel
{
    protected $fillable = [
        'user_id',
        'world_id',
        'player_id',
        'key',
    ];

    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }

    public function world(){
        return $this->belongsTo(World::class,'world_id');
    }
}
