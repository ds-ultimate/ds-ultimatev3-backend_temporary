<?php

namespace App\Models;

use App\Http\Resources\VillageResource;

class Village extends CustomModel
{
    protected $primaryKey = 'villageID';
    protected $fillable =[
        'villageID', 'name', 'x', 'y', 'points', 'owner', 'bonus_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'villageID' => 'integer',
        'x' => 'integer',
        'y' => 'integer',
        'points' => 'integer',
        'owner' => 'integer',
        'bonus_id' => 'integer',
    ];
    
    public $timestamps = true;
    
    protected $defaultTableName = "village_latest";
    
    protected $with = ['playerLatest'];

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
     * @param int $villageID
     * @return \Illuminate\Support\Collection
     */
    public static function villageDataChart(World $world, $villageID){
        $villageID = (int) $villageID;
        $tabelNr = $villageID % $world->hash_village;
        $villageModel = new Village($world, "village_$tabelNr");
        
        $villageDataArray = $villageModel->where('villageID', $villageID)->orderBy('updated_at', 'ASC')->get();

        $villageDatas = [];
        foreach ($villageDataArray as $village){
            $villageDatas[] = [
                'timestamp' => (int)$village->updated_at->timestamp,
                'points' => $village->points,
            ];
        }

        return $villageDatas;
    }

    /**
     * @return string
     */
    public function coordinates() {
        return $this->x."|".$this->y;
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
    
    public static function getJoinedQuery(World $world) {
        $v = new Village($world);
        return $v->newQuery()
                ->setEagerLoads([]);
    }
    
    public function toArray() {
        return new VillageResource($this);
    }
}
