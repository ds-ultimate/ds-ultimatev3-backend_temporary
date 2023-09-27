<?php

namespace App\Http\Controllers;

use App\Models\AllyChange;
use App\Models\Server;
use App\Models\World;
use App\Util\BasicFunctions;
use App\Util\DataTable;

use Illuminate\Support\Facades\Request;

class AllyChangeController extends Controller
{
    private static $whitelist = ['created_at', 'player_name', 'new_ally_name', 'old_ally_name', 'points'];
    private static $whitelist_search = ['ally_change.created_at', 'player.name', 'ally_old.name', 'ally_old.tag', 'ally_new.name', 'ally_new.tag', 'ally_change.points'];
    private static $conquerReturnValidate = [
        'filter' => 'array:p,oa,na',
        'filter.p' => 'numeric|integer',
        'filter.oa' => 'numeric|integer',
        'filter.na' => 'numeric|integer',
    ];
    
    public function allyAllyChange($server, $world, $type, $allyID)
    {
        $server = Server::getAndCheckServerByCode($server);
        $worldData = World::getAndCheckWorld($server, $world);
        
        $query = AllyChange::getJoinedQuery($worldData);
        
        switch($type) {
            case "all":
                $query->where(function($q) use($allyID) {
                    $q->orWhere('ally_change.new_ally_id', $allyID);
                    $q->orWhere('ally_change.old_ally_id', $allyID);
                });
                break;
            case "old":
                $query->where('ally_change.old_ally_id', $allyID);
                break;
            case "new":
                $query->where('ally_change.new_ally_id', $allyID);
                break;
            default:
                BasicFunctions::abort_translated(404, "404.unknownType", ["type" => $type]);
        }

        return $this->doAllyChangeReturn($query);
    }
    
    public function playerAllyChange($server, $world, $type, $playerID)
    {
        $server = Server::getAndCheckServerByCode($server);
        $worldData = World::getAndCheckWorld($server, $world);
        
        $query = AllyChange::getJoinedQuery($worldData);
        
        switch($type) {
            case "all":
                $query->where('ally_change.player_id', $playerID);
                break;
            default:
                BasicFunctions::abort_translated(404, "404.unknownType", ["type" => $type]);
        }

        return $this->doAllyChangeReturn($query);
    }
    
    private function doAllyChangeReturn($dtQuery) {
        $getArray = Request::validate(static::$conquerReturnValidate);
        $filter = $getArray["filter"] ?? [];
        
        $filterCb = function($query) use($filter) {
            if(isset($filter["p"])) {
                $query->where("ally_change.player_id", (int) $filter["p"]);
            }
            if(isset($filter["oa"])) {
                $query->where("ally_change.old_ally_id", (int) $filter["oa"]);
            }
            if(isset($filter["na"])) {
                $query->where("ally_change.new_ally_id", (int) $filter["na"]);
            }
        };

        return DataTable::generate($dtQuery)
            ->setWhitelist(static::$whitelist)
            ->setSearchWhitelist(static::$whitelist_search)
            ->setFilter($filterCb)
            ->toJson();
    }
}
