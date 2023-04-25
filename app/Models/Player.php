<?php

namespace App\Models;

use App\Http\Resources\PlayerResource;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class Player extends CustomModel
{
    protected $primaryKey = 'playerID';
    protected $fillable = [
        'playerID',
        'name',
        'ally_id',
        'village_count',
        'points',
        'rank',
        'offBash',
        'offBashRank',
        'defBash',
        'defBashRank',
        'supBash',
        'supBashRank',
        'gesBash',
        'gesBashRank',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'playerID' => 'integer',
        'ally_id' => 'integer',
        'village_count' => 'integer',
        'points' => 'integer',
        'rank' => 'integer',
        'offBash' => 'integer',
        'offBashRank' => 'integer',
        'defBash' => 'integer',
        'defBashRank' => 'integer',
        'supBash' => 'integer',
        'supBashRank' => 'integer',
        'gesBash' => 'integer',
        'gesBashRank' => 'integer',
    ];
    
    protected $with = ['allyLatest'];
    
    public $timestamps = true;
    
    protected $defaultTableName = "player_latest";

    public function __construct($arg1 = [], $arg2 = null)
    {
        if($arg1 instanceof World && $arg2 == null) {
            //allow calls without table name
            $arg2 = $this->defaultTableName;
        }
        parent::__construct($arg1, $arg2);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function allyLatest()
    {
        return $this->mybelongsTo(Ally::class, 'ally_id', 'allyID', $this->getRelativeTable("ally_latest"));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function allyChanges()
    {
        return $this->myhasMany(AllyChange::class, 'player_id', 'playerID', $this->getRelativeTable("ally_changes"));
    }

    /**
     * Gibt die Top 10 Spieler zurück
     *
     * @param World $world
     * @return Collection
     */
    public static function top10Player(World $world){
        return (new Player($world))->orderBy('rank')->limit(10)->get();
    }

    /**
     * @param World $world
     * @param int $player
     * @return $this
     */
    public static function player(World $world, $player) {
        $playerModel = new Player($world);
        return $playerModel->find($player);
    }
    
    public static function getJoinedQuery(World $world) {
        $p = new Player($world);
        return $p->select(["player.*", "ally.name as allyLatest__name", "ally.tag as allyLatest__tag"])
                ->from($p->getTable(), "player")
                ->leftjoin($p->getRelativeTable("ally_latest") . " as ally", 'player.ally_id', '=', 'ally.allyID')
                ->setEagerLoads([]);
    }

    public function follows(){
        return $this->morphToMany(User::class, 'followable', 'follows');
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
        return new PlayerResource($this);
    }
}
