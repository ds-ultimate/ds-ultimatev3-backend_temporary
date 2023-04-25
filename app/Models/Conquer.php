<?php

namespace App\Models;

use App\Http\Resources\ConquerResource;

class Conquer extends CustomModel
{
    protected $fillable = [
        'village_id',
        'timestamp',
        'new_owner',
        'old_owner',
        'id',
        'old_owner_name',
        'new_owner_name',
        'old_ally',
        'new_ally',
        'old_ally_name',
        'new_ally_name',
        'old_ally_tag',
        'new_ally_tag',
        'points',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'village_id' => 'integer',
        'timestamp' => 'integer',
        'new_owner' => 'integer',
        'old_owner' => 'integer',
        'id' => 'integer',
        'old_ally' => 'integer',
        'new_ally' => 'integer',
        'points' => 'integer',
        'village__x' => 'integer',
        'village__y' => 'integer',
        'village__bonus_id' => 'integer',
        'total' => 'integer',
    ];
    
    protected $defaultTableName = "conquer";

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
    public function oldPlayer()
    {
        return $this->mybelongsTo(Player::class, 'old_owner', 'playerID', $this->getRelativeTable("player_latest"));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function newPlayer()
    {
        return $this->mybelongsTo(Player::class, 'new_owner', 'playerID', $this->getRelativeTable("player_latest"));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function oldAlly()
    {
        return $this->mybelongsTo(Ally::class, 'old_ally', 'allyID', $this->getRelativeTable("ally_latest"));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function newAlly()
    {
        return $this->mybelongsTo(Ally::class, 'new_ally', 'allyID', $this->getRelativeTable("ally_latest"));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function village()
    {
        return $this->mybelongsTo(Village::class, 'village_id', 'villageID', $this->getRelativeTable("village_latest"));
    }

    /**
     * @param World $world
     * @param int $playerID
     * @return \Illuminate\Support\Collection
     */
    public static function playerConquerCounts(World $world, $playerID){
        $conquerModel = new Conquer($world);

        $conquer = [];
        $conquer['old'] = $conquerModel->where([['old_owner', "=", $playerID],['new_owner', '!=', $playerID]])->count();
        $conquer['new'] = $conquerModel->where([['old_owner', "!=", $playerID],['new_owner', '=', $playerID]])->count();
        $conquer['own'] = $conquerModel->where([['old_owner', "=", $playerID],['new_owner', '=', $playerID]])->count();
        $conquer['total'] = $conquer['old'] + $conquer['new'] + $conquer['own'];

        return $conquer;
    }

    /**
     * @param World $world
     * @param int $allyID
     * @return \Illuminate\Support\Collection
     */
    public static function allyConquerCounts(World $world, $allyID){
        $conquerModel = new Conquer($world);
        $playerModel = new Player($world);

        $conquer = [];
        $conquer['old'] = $conquerModel->where('old_ally', $allyID)->whereNot('new_ally', $allyID)->count();
        $conquer['new'] = $conquerModel->whereNot('old_ally', $allyID)->where('new_ally', $allyID)->count();
        $conquer['own'] = $conquerModel->where('old_ally', $allyID)->where('new_ally', $allyID)->count();
        $conquer['total'] = $conquer['old'] + $conquer['new'] + $conquer['own'];

        return $conquer;
    }

    /**
     * @param World $world
     * @param int $villageID
     * @return \Illuminate\Support\Collection
     */
    public static function villageConquerCounts(World $world, $villageID){
        $conquerModel = new Conquer($world);

        $conquer = [];
        $conquer['total'] = $conquerModel->where('village_id', $villageID)->count();

        return $conquer;
    }

    private function getOldAllyID() {
        $oldAllyID = $this->old_ally;
        if($this->old_ally_name == null &&
                $this->oldPlayer != null && $this->oldPlayer->allyLatest != null) {
            $oldAllyID = $this->oldPlayer->ally_id;
        }
        return $oldAllyID;
    }

    private function getNewAllyID() {
        $newAllyID = $this->new_ally;
        if($this->new_ally_name == null &&
                $this->newPlayer != null && $this->newPlayer->allyLatest != null) {
            $newAllyID = $this->newPlayer->ally_id;
        }
        return $newAllyID;
    }
    
    public static function getJoinedQuery(World $world) {
        $c = new Conquer($world);
        return $c->select(["conquer.*", "village.name as village__name",
            "village.x as village__x", "village.y as village__y", "village.bonus_id as village__bonus_id"])
                ->from($c->getTable(), "conquer")
                ->leftjoin($c->getRelativeTable("village_latest") . " as village", 'conquer.village_id', '=', 'village.villageID')
                ->setEagerLoads([]);
    }
    
    public function toArray() {
        return new ConquerResource($this);
    }
}
