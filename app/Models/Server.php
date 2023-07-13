<?php

namespace App\Models;

use App\Util\BasicFunctions;
use App\Http\Resources\ServerResource;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Server extends Model
{
    use SoftDeletes;

    protected $table = 'server';
    protected $connection = 'mysql';

    protected $fillable = [
        'code',
        'flag',
        'url',
        'active',
        'speed_active',
        'classic_active',
        'locale',
    ];

    /**
     * Connects the server table with the worlds table
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function worlds()
    {
        return $this->hasMany(World::class, 'server_id', 'id');
    }
    
    public function toArray() {
        return new ServerResource($this);
    }

    /**
     * Returns all servers
     *
     * @return Collection
     */
    public static function getServer($withWorlds=false, $withWorldCount=false){
        if($withWorlds) {
            return Server::with("worlds")->get();
        } else if($withWorldCount) {
            return Server::withCount("worlds")->get();
        } else {
            return (new Server())->get();
        }
    }

    /**
     * Returns the server with the given code if it exists, 404 otherwise
     *
     * @param string $server
     * @return World
     */
    public static function getAndCheckServerByCode($server, $withWorlds=false){
        $serverData = Server::getServerByCode($server, $withWorlds);
        BasicFunctions::abort_if_translated($serverData == null, 404, "404.noServer", ["server" => $server]);
        return $serverData;
    }

    /**
     * Returns the server with the given code if it exists, null otherwise
     *
     * @param string $code
     * @return $this
     */
    public static function getServerByCode($code, $withWorlds=false){
        if($withWorlds) {
            return Server::with("worlds.server")->where('code', $code)->first();
        } else {
            return Server::where('code', $code)->first();
        }
    }

    /**
     * Returns all worlds of a given serverModel
     *
     * @param \App\Server $server
     * @return Collection
     */
    public static function getWorlds(Server $server){
        $collect = $server->worlds;
        return $collect->sortByDesc('id');
    }
}
