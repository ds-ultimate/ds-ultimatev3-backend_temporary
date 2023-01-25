<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Changelog extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'version',
        'de',
        'en',
        'content',
        'repository_html_url',
        'icon',
        'color',
    ];
}
