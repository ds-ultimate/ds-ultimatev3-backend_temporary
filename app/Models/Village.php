<?php

namespace App\Models;

class Village extends CustomModel
{
    protected $primaryKey = 'villageID';
    protected $fillable =[
        'id', 'name', 'x', 'y', 'points', 'owner', 'bonus_id',
    ];
    
    public $timestamps = true;
    
    protected $defaultTableName = "village_latest";

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
    public function playerLatest()
    {
        return $this->mybelongsTo(Player::class, 'owner', 'playerID', $this->getRelativeTable("player_latest"));
    }

    /**
     * @param World $world
     * @param int $village
     * @return $this
     */
    public static function village(World $world, $village){
        $villageModel = new Village($world);
        return $villageModel->find($village);
    }
    
    public function getHistoryData(World $world) {
        $villageID = (int) $this->villageID;
        $tableNr = $villageID % $world->hash_village;
        $villageModel = new Village($world, "village_$tableNr");
        
        $villageDataArray = $villageModel->where('villageID', $villageID)->orderBy('updated_at', 'ASC')->get();
        return $villageDataArray;
    }
}
