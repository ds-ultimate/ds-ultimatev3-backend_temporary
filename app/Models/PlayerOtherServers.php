<?php

namespace App\Models;

use App\World;
use Illuminate\Database\Eloquent\Model;

class PlayerOtherServers extends Model
{
    protected $primaryKey = 'playerID';
    protected $fillable = [
        'playerID',
        'name',
        'worlds'
    ];

    public $timestamps = true;

    /**
     * Player constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
    
    public function getWorldIds() {
        if(!isset($this->worlds) || $this->worlds === null) return [];
        $ret = [];
        foreach(explode(";", $this->worlds) as $w_id) {
            $ret[] = (int) $w_id;
        }
        return $ret;
    }
    
    public function isWorldActive($worldID) {
        return in_array($worldID, $this->getWorldIds());
    }
    
    public function addWorld($worldID) {
        if($this->isWorldActive($worldID)) return;
        if(!isset($this->worlds) || $this->worlds === null) {
            $this->worlds = $worldID;
        } else {
            $curWorlds = $this->getWorldIds();
            $curWorlds[] = $worldID;
            sort($curWorlds);
            $this->worlds = "";
            $first = true;
            foreach($curWorlds as $world) {
                if(!$first) $this->worlds .= ";";
                $this->worlds .= $world;
                $first = false;
            }
        }
    }
    
    public function getWorlds() {
        $worldIds = $this->getWorldIds();
        return (new World())->query()->whereIn("id", $worldIds)->get();
    }

    /**
     * @param string $server
     * @return $this
     */
    public static function prepareModel($server){
        $playerModel = new PlayerOtherServers();
        $playerModel->setTable('other_servers_' . $server->code);
        return $playerModel;
    }

    /**
     * @param string $server
     * @param int $player
     * @return $this
     */
    public static function player($server, $player){
        return static::prepareModel($server)->find($player);
    }
}
