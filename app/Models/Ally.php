<?php

namespace App\Models;

use App\Http\Resources\AllyResource;
use Illuminate\Database\Eloquent\Collection;

class Ally extends CustomModel
{
    protected $primaryKey = 'allyID';
    protected $fillable = [
        'allyID',
        'name',
        'tag',
        'member_count',
        'village_count',
        'points',
        'rank',
        'offBash',
        'offBashRank',
        'defBash',
        'defBashRank',
        'gesBash',
        'gesBashRank',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'allyID' => 'integer',
        'member_count' => 'integer',
        'village_count' => 'integer',
        'points' => 'integer',
        'rank' => 'integer',
        'offBash' => 'integer',
        'offBashRank' => 'integer',
        'defBash' => 'integer',
        'defBashRank' => 'integer',
        'gesBash' => 'integer',
        'gesBashRank' => 'integer',
    ];
    
    protected $defaultTableName = "ally_latest";

    public function __construct($arg1 = [], $arg2 = null)
    {
        if($arg1 instanceof World && $arg2 == null) {
            //allow calls without table name
            $arg2 = $this->defaultTableName;
        }
        parent::__construct($arg1, $arg2);
    }

    /**
     * @return AllyChanges
     */
    public function allyChangesOld()
    {
        return $this->myhasMany(AllyChanges::class, 'old_ally_id', 'allyID', $this->getRelativeTable("ally_changes"));
    }

    /**
     * @return AllyChanges
     */
    public function allyChangesNew()
    {
        return $this->myhasMany(AllyChanges::class, 'new_ally_id', 'allyID', $this->getRelativeTable("ally_changes"));
    }

    /**
     * Gibt die besten 10 Stämme zurück
     *
     * @param World $world
     * @return Collection
     */
    public static function top10Ally(World $world){
        $allyModel = new Ally($world);
        return $allyModel->orderBy('rank')->limit(10)->get();
    }

    /**
     * @param World $world
     * @param  int $ally
     * @return $this
     */
    public static function ally(World $world, $ally){
        $allyModel = new Ally($world);
        return $allyModel->find((int) $ally);
    }

    /**
     * @param World $world
     * @param int $ally
     * @return array
     */
    public static function allyDataChart(World $world, $ally){
        $allyID = (int) $ally;
        $tabelNr = $allyID % ($world->hash_ally);

        $allyModel = new Ally($world, 'ally_'.$tabelNr);

        $allyDataArray = $allyModel->where('allyID', $allyID)->orderBy('updated_at', 'ASC')->get();

        $allyDatas = [];

        foreach ($allyDataArray as $ally){
            $allyData = [];
            $allyData['timestamp'] = (int)$ally->updated_at->timestamp;
            $allyData['points'] = $ally->points;
            $allyData['rank'] = $ally->rank;
            $allyData['village'] = $ally->village_count;
            $allyData['gesBash'] = $ally->gesBash;
            $allyData['offBash'] = $ally->offBash;
            $allyData['defBash'] = $ally->defBash;
            $allyDatas[] = $allyData;
        }

        return $allyDatas;
    }
    
    public function toArray() {
        return new AllyResource($this);
    }
}
