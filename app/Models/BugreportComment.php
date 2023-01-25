<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BugreportComment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'bugreport_id',
        'user_id',
        'content',
    ];

    public function users(){
        return $this->belongsTo(User::class, 'user_id');
    }

}
