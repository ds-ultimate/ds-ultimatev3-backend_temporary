<?php

namespace App\Models;

use App\Http\Resources\NewsResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class News extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'content_de',
        'content_en',
    ];
    
    public function toArray() {
        return new NewsResource($this);
    }
}
