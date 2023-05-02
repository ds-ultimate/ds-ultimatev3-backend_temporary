<?php

namespace App\Models;

use App\Http\Resources\PlayerTopResource;

class PlayerTop extends CustomModel
{
    protected $primaryKey = 'playerID';
    protected $fillable = [
        'playerID',
        'name',
        'village_count_top',
        'village_count_date',
        'points_top',
        'points_date',
        'rank_top',
        'rank_date',
        'offBash_top',
        'offBash_date',
        'offBashRank_top',
        'offBashRank_date',
        'defBash_top',
        'defBash_date',
        'defBashRank_top',
        'defBashRank_date',
        'supBash_top',
        'supBash_date',
        'supBashRank_top',
        'supBashRank_date',
        'gesBash_top',
        'gesBash_date',
        'gesBashRank_top',
        'gesBashRank_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'playerID' => 'integer',
        'rank_top' => 'integer',
        'village_count_top' => 'integer',
        'points_top' => 'integer',
        'offBash_top' => 'integer',
        'offBashRank_top' => 'integer',
        'defBash_top' => 'integer',
        'defBashRank_top' => 'integer',
        'supBash_top' => 'integer',
        'supBashRank_top' => 'integer',
        'gesBash_top' => 'integer',
        'gesBashRank_top' => 'integer',
        'rank_date' => 'datetime',
        'village_count_date' => 'datetime',
        'points_date' => 'datetime',
        'offBash_date' => 'datetime',
        'offBashRank_date' => 'datetime',
        'defBash_date' => 'datetime',
        'defBashRank_date' => 'datetime',
        'supBash_date' => 'datetime',
        'supBashRank_date' => 'datetime',
        'gesBash_date' => 'datetime',
        'gesBashRank_date' => 'datetime',
    ];
    
    protected $defaultTableName = "player_top";

    public function __construct($arg1 = [], $arg2 = null)
    {
        if($arg1 instanceof World && $arg2 == null) {
            //allow calls without table name
            $arg2 = $this->defaultTableName;
        }
        parent::__construct($arg1, $arg2);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function allyChanges()
    {
        return $this->myhasMany(AllyChange::class, 'player_id', 'playerID', $this->getRelativeTable("ally_changes"));
    }

    /**
     * @param World $world
     * @param int $player
     * @return $this
     */
    public static function player(World $world, $player){
        $playerModel = new PlayerTop($world);
        return $playerModel->find($player);
    }
    

    public function signature() {
        return $this->morphMany(Signature::class, 'element');
    }

    public function getSignature(World $worldData) {
        $sig = $this->morphOne(Signature::class, 'element')->where('world_id', $worldData->id)->first();
        if($sig != null) {
            return $sig;
        }

        $sig = new Signature();
        $sig->world_id = $worldData->id;
        $this->signature()->save($sig);
        return $sig;
    }
    
    public function toArray() {
        return new PlayerTopResource($this);
    }
}
