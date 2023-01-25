<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

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

    private $allyHistCache = [];
    public function allyHistory($days, World $world){
        if(! isset($this->allyHistCache[$days])) {
            $tableNr = $this->allyID % ($world->hash_ally);

            $allyModel = new Ally($world, "ally_$tableNr");
            $timestamp = Carbon::now()->subDays($days);
            $this->allyHistCache[$days] =  $allyModel->where('allyID', $this->allyID)
                    ->whereDate('updated_at', $timestamp->toDateString())
                    ->orderBy('updated_at', 'DESC')
                    ->first();
        }
        return $this->allyHistCache[$days];
    }
}
