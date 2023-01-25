<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Signature extends Model
{
    protected $connection = 'mysql';
    protected $table = 'signature';

    protected $fillable = [
        'id',
        'worlds_id',
        'element_id',
        'element_type',
        'cached',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'cached' => 'datetime',
    ];
    
    public function isCached() {
        if(!isset($this->cached) || $this->cached == null) return false;
        
        return Carbon::now()->subSeconds(config('tools.signature.cacheDuration'))->lt($this->cached);
    }
    
    public function getCacheFileName() {
        $constrained = explode("\\", $this->element_type);
        return "{$this->id}-{$constrained[count($constrained) - 1]}-{$this->element_id}";
    }
    
    public function getCacheFile() {
        $constrained = explode("\\", $this->element_type);
        return storage_path(config('tools.signature.cacheDir')."{$this->id}-{$constrained[count($constrained) - 1]}-{$this->element_id}.png");
    }
}
