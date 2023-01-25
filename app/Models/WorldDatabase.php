<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorldDatabase extends Model
{
    protected $table = 'world_databases';

    protected $fillable = [
        'id',
        'name',
    ];
}
