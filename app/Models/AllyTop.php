<?php

namespace App\Models;

use App\Http\Resources\AllyTopResource;

class AllyTop extends CustomModel
{
    protected $primaryKey = 'allyID';
    protected $fillable = [
        'allyID',
        'name',
        'tag',
        'member_count_top',
        'member_count_date',
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
        'allyID' => 'integer',
        'member_count_top' => 'integer',
        'village_count_top' => 'integer',
        'points_top' => 'integer',
        'rank_top' => 'integer',
        'offBash_top' => 'integer',
        'offBashRank_top' => 'integer',
        'defBash_top' => 'integer',
        'defBashRank_top' => 'integer',
        'gesBash_top' => 'integer',
        'gesBashRank_top' => 'integer',
        'member_count_date' => 'datetime',
        'village_count_date' => 'datetime',
        'points_date' => 'datetime',
        'offBash_date' => 'datetime',
        'offBashRank_date' => 'datetime',
        'defBash_date' => 'datetime',
        'defBashRank_date' => 'datetime',
        'gesBash_date' => 'datetime',
        'gesBashRank_date' => 'datetime',
    ];
    
    protected $defaultTableName = "ally_top";

    public function __construct($arg1 = [], $arg2 = null)
    {
        if($arg1 instanceof World && $arg2 == null) {
            //allow calls without table name
            $arg2 = $this->defaultTableName;
        }
        parent::__construct($arg1, $arg2);
    }

    /**
     * @return Ally
     */
    public function allyLatest()
    {
        return $this->myhasMany(Player::class, 'ally_id', 'allyID', $this->getRelativeTable("ally_latest"));
    }

    /**
     * @return AllyChange
     */
    public function allyChangesOld()
    {
        return $this->myhasMany(AllyChange::class, 'old_ally_id', 'allyID', $this->getRelativeTable("ally_changes"));
    }

    /**
     * @return AllyChange
     */
    public function allyChangesNew()
    {
        return $this->myhasMany(AllyChange::class, 'new_ally_id', 'allyID', $this->getRelativeTable("ally_changes"));
    }

    /**
     * @param World $world
     * @param  int $ally
     * @return $this
     */
    public static function ally(World $world, $ally){
        $allyModel = new AllyTop($world);
        return $allyModel->find($ally);
    }
    
    public function toArray() {
        return new AllyTopResource($this);
    }
}
