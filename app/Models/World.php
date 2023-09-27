<?php

namespace App\Models;

use App\Http\Resources\WorldResource;
use App\Util\BasicFunctions;
use Illuminate\Database\Eloquent\SoftDeletes;

class World extends CustomModel
{
    use SoftDeletes;
    
    public static $ACTIVE_INACTIVE = 0;
    public static $ACTIVE_DISABLED = 1;
    public static $ACTIVE_ACTIVE = 2;

    protected $connection = 'mysql';
    protected $table = 'worlds';

    protected $fillable = [
        'id',
        'server_id',
        'name',
        'ally_count',
        'player_count',
        'village_count',
        'url',
        'config',
        'units',
        'buildings',
        'active',
        'display_name',
        'maintananceMode',
        'win_condition',
        'hash_ally',
        'hash_player',
        'hash_village',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'server_id' => 'integer',
        'ally_count' => 'integer',
        'player_count' => 'integer',
        'village_count' => 'integer',
        'active' => 'boolean',
        'maintananceMode' => 'boolean',
        'win_condition' => 'integer',
        'hash_ally' => 'integer',
        'hash_player' => 'integer',
        'hash_village' => 'integer',
        'worldTop_at' => 'datetime',
        'worldUpdated_at' => 'datetime',
        'worldCleaned_at' => 'datetime',
    ];
    
    protected $with = ['database'];

    /**
     * Verbindet die world Tabelle mit der server Tabelle
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function server(){
        return $this->belongsTo(Server::class, 'server_id');
    }

    /**
     * Gibt den Welten-Typ zurück.
     *
     * @return string
     */
    public function sortType()
    {
        /*
         * Setzt den Welten Type:
         * dep => Casual
         * des => Speed
         * dec => Classic
         * de => Welt
         */
        if($this->isSpeed()){
            return "speed";
        } elseif($this->isCasual()){
            return "casual";
        } elseif($this->isNormalServer()){
            return "world";
        } else{
            return "classic";
        }
    }
    
    public function getDisplayName() {
        return '$t(ui:'.$this->type().') ' . $this->num();
    }

    /**
     * @return int
     */
    public function num()
    {
        return (int)preg_replace("/[^0-9]+/", '', $this->name);
    }
    
    /**
     * @return string
     */
    public function type()
    {
        /*
         * Setzt den Welten Type:
         * dep => Casual
         * des => Speed
         * dec => Classic
         * de => Welt
         */
        if($this->isSpeed()){
            return 'world.speed';
        } elseif($this->isCasual()){
            return 'world.casual';
        } elseif($this->isNormalServer()){
            return 'world.world';
        } else{
            return 'world.classic';
        }
    }
    
    public function isSpecialServer() {
        return static::isSpecialServerName($this->name);
    }
    
    public function isSpeed() {
        return static::isSpeedName($this->name);
    }
    
    public function isClassicServer() {
        return static::isClassicServerName($this->name);
    }
    
    public function isCasual() {
        return static::isCasualName($this->name);
    }
    
    public function isNormalServer() {
        return static::isNormalServerName($this->name);
    }

    private $unitConfCache = null;
    public function unitConfig(){
        if($this->unitConfCache == null) {
            $this->unitConfCache = simplexml_load_string($this->units);
        }
        return $this->unitConfCache;
    }

    public function save(array $options = []) {
        if($this->config == null) {
            $this->win_condition = -1;
        } else {
            $this->win_condition = simplexml_load_string($this->config)->win->check;
        }
        return parent::save($options);
    }

    public function touch($attribute = null) {
        if($this->config == null) {
            $this->win_condition = -1;
        } else {
            $this->win_condition = simplexml_load_string($this->config)->win->check;
        }
        return parent::touch($attribute);
    }
    
    public function serName() {
        BasicFunctions::ensureValidWorldName($this);
        return $this->server->code . $this->name;
    }
    
    public function database() {
        return $this->hasOne(WorldDatabase::class, 'id', 'database_id');
    }
    
    public function activeEnum() {
        if($this->active == null) {
            return self::$ACTIVE_INACTIVE;
        } else if($this->active) {
            return self::$ACTIVE_ACTIVE;
        }
        return self::$ACTIVE_DISABLED;
    }
    
    public function toArray() {
        return new WorldResource($this);
    }

    /**
     * Gibt eine bestimmte Welt zurück.
     *
     * @param string $server
     * @param $world
     * @return World
     */
    public static function getWorld($server, $world){
        $serverData = Server::getServerByCode($server);
        return World::where('name', $world)->where('server_id', $serverData->id)->first();
    }

    /**
     * Schaut ob die Welt existiert und falls ja gibt diese zurück,
     * sonst wird ein Error 404 zurück gegeben
     *
     * @param string $server
     * @param $world
     * @return World
     */
    public static function getAndCheckWorld($server, $world){
        if($server instanceof Server) {
            $serverData = $server;
        } else {
            $serverData = Server::getAndCheckServerByCode($server);
        }
        $worldData = World::where('name', $world)->where('server_id', $serverData->id)->first();
        BasicFunctions::abort_if_translated($worldData == null, 404, "404.noWorld", ["world" => "{$serverData->code}$world"]);
        BasicFunctions::abort_if_translated($worldData->maintananceMode, 503, "503.worldDown", ["world" => "{$serverData->code}$world"]);
        return $worldData;
    }
    
    public static function isSpecialServerName($name) {
        return static::isSpeedName($name) || static::isClassicServerName($name);
    }
    
    public static function isSpeedName($name) {
        return BasicFunctions::startsWith($name, 's') && is_numeric(substr($name, 1));
    }
    
    public static function isClassicServerName($name) {
        return !static::isNormalServerName($name) && !static::isCasualName($name) && !static::isSpeedName($name);
    }
    
    public static function isCasualName($name) {
        return BasicFunctions::startsWith($name, 'p') && is_numeric(substr($name, 1));
    }
    
    public static function isNormalServerName($name) {
        return preg_match("/^\d+$/", $name);
    }
}
