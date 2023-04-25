<?php

namespace App\Models;

use App\Http\Resources\AllyChangeResource;

class AllyChange extends CustomModel
{
    
    protected $defaultTableName = "ally_changes";

    protected $fillable = [
        'player_id',
        'old_ally_id',
        'new_ally_id',
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
        'player_id' => 'integer',
        'old_ally_id' => 'integer',
        'new_ally_id' => 'integer',
        'points' => 'integer',
        'crated_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

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
    public function oldAlly()
    {
        return $this->mybelongsTo(Ally::class, 'old_ally_id', 'allyID', $this->getRelativeTable("ally_latest"));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function newAlly()
    {
        return $this->mybelongsTo(Ally::class, 'new_ally_id', 'allyID', $this->getRelativeTable("ally_latest"));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function player()
    {
        return $this->mybelongsTo(Player::class, 'player_id', 'playerID', $this->getRelativeTable("player_latest"));
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function oldAllyTop()
    {
        return $this->mybelongsTo(AllyTop::class, 'old_ally_id', 'allyID', $this->getRelativeTable("ally_top"));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function newAllyTop()
    {
        return $this->mybelongsTo(AllyTop::class, 'new_ally_id', 'allyID', $this->getRelativeTable("ally_top"));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function playerTop()
    {
        return $this->mybelongsTo(PlayerTop::class, 'player_id', 'playerID', $this->getRelativeTable("player_top"));
    }

    /**
     * @param World $world
     * @param int $playerID
     * @return int
     */
    public static function playerAllyChangeCount(World $world, $playerID){
        $allyChangesModel = new AllyChange($world);
        
        $allyChanges = [];
        $allyChanges['total'] = $allyChangesModel->where('player_id', $playerID)->count();
        return $allyChanges;
    }

    /**
     * @param World $world
     * @param int $allyID
     * @return \Illuminate\Support\Collection
     */
    public static function allyAllyChangeCounts(World $world, $allyID){
        $allyChangesModel = new AllyChange($world);
        
        $allyChanges = [];
        $allyChanges['old'] = $allyChangesModel->where('old_ally_id', $allyID)->count();
        $allyChanges['new'] = $allyChangesModel->where('new_ally_id', $allyID)->count();
        $allyChanges['total'] = $allyChanges['old'] + $allyChanges['new'];
        return $allyChanges;
    }
    
    public static function getJoinedQuery(World $world) {
        $a = new AllyChange($world);
        return $a->select(["ally_change.*", "player.name as player__name",
            "ally_old.name as ally_old__name", "ally_old.tag as ally_old__tag",
            "ally_new.name as ally_new__name", "ally_new.tag as ally_new__tag"])
                ->from($a->getTable(), "ally_change")
                ->leftjoin($a->getRelativeTable("player_top") . " as player", 'player.playerID', '=', 'ally_change.player_id')
                ->leftjoin($a->getRelativeTable("ally_top") . " as ally_old", 'ally_old.allyID', '=', 'ally_change.old_ally_id')
                ->leftjoin($a->getRelativeTable("ally_top") . " as ally_new", 'ally_new.allyID', '=', 'ally_change.new_ally_id')
                ->setEagerLoads([]);
    }
    
    public function toArray() {
        return new AllyChangeResource($this);
    }
}
