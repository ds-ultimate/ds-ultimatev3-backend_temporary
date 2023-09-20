<?php

namespace App\Http\Controllers;

use App\Models\AllyTop;
use App\Models\PlayerTop;
use App\Models\Village;
use App\Models\World;
use App\Models\Server;
use App\Util\BasicFunctions;
use \Response;

class SearchController extends Controller
{
    public static $limit = 100;
    
    /**
     * Searches at all active worlds of the given server
     */
    public function search() {
        $reqData = request()->validate([
            "search" => "string|required|max:100",
            "server" => "string|required",
            "type" => "string|required",
        ]);

        $server = Server::getAndCheckServerByCode($reqData["server"]);
        $worlds = Server::getActiveWorlds($server);

        switch ($reqData["type"]){
            case 'ally':
                $result = SearchController::searchAlly($worlds, $reqData["search"]);
                break;
            case 'player':
                $result = SearchController::searchPlayer($worlds, $reqData["search"]);
                break;
            case 'village':
                $result = SearchController::searchVillage($worlds, $reqData["search"]);
                break;
            default:
                BasicFunctions::abort_translated(404, "404.unknownType", ["type" => $reqData["type"]]);
        }
        
        return Response::json($result);
    }
    
    /**
     * Searches the given worlds (10 at max.)
     */
    public function extendedSearch(){
        $reqData = request()->validate([
            "search" => "string|required|max:100",
            "worlds" => "array|required|max:20",
            "worlds.*" => "integer|required",
            "type" => "string|required",
        ]);

        $worlds = World::with("server")->find($reqData["worlds"]);

        switch ($reqData["type"]){
            case 'ally':
                $result = SearchController::searchAlly($worlds, $reqData["search"]);
                break;
            case 'player':
                $result = SearchController::searchPlayer($worlds, $reqData["search"]);
                break;
            case 'village':
                $result = SearchController::searchVillage($worlds, $reqData["search"]);
                break;
            default:
                BasicFunctions::abort_translated(404, "404.unknownType", ["type" => $reqData["type"]]);
        }
        
        return Response::json($result);
    }

    public static function searchAlly($worlds, $search){
        $ally = new AllyTop();
        $allAlly = [];

        foreach ($worlds as $world){
            $ally->setTable(BasicFunctions::getWorldDataTable($world, 'ally_top'));
            foreach ($ally->where('name', 'LIKE', '%'.BasicFunctions::likeSaveEscape(urlencode($search)).'%')->get() as $data){
                $allAlly[] = [
                    'world' => $world->toArray(),
                    'ally' => $data,
                ];
                
                if(count($allAlly) >= SearchController::$limit)
                    return $allAlly;
            }
        }
        return $allAlly;
    }

    public static function searchPlayer($worlds, $search){
        $player = new PlayerTop();
        $allPlayer = [];

        foreach ($worlds as $world){
            $player->setTable(BasicFunctions::getWorldDataTable($world, 'player_top'));
            foreach ($player->where('name', 'LIKE', '%'. BasicFunctions::likeSaveEscape(urlencode($search)).'%')->get() as $data){
                $allPlayer[] = [
                    'world' => $world,
                    'player' => $data,
                ];
                
                if(count($allPlayer) >= SearchController::$limit)
                    return $allPlayer;
            }
        }
        return $allPlayer;
    }

    public static function searchVillage($worlds, $search){
        $village = new Village();
        $allVillage = [];

        $coordsearch = false;
        if(strpos($search, '|') !== false) {
            //Coordinates search
            $temp = explode("|", $search);
            if(count($temp) == 2
                    && ctype_digit($temp[0]) && ctype_digit($temp[1])
                    && strlen($temp[0]) > 1 && strlen($temp[1]) > 1) {
                $coordsearch = true;
                $searchExp = $temp;
            }
        }
        
        foreach ($worlds as $world){
            $village->setTable(BasicFunctions::getWorldDataTable($world, 'village_latest'));
            foreach ($village->where('name', 'LIKE', '%'.BasicFunctions::likeSaveEscape(urlencode($search)).'%')->get() as $data){
                $allVillage[] = [
                    'world' => $world,
                    'village' => $data,
                ];
                
                if(count($allVillage) >= SearchController::$limit)
                    return $allVillage;
            }

            if($coordsearch) {
                foreach ($village->where('x', 'LIKE', '%'.BasicFunctions::likeSaveEscape($searchExp[0]).'%')
                        ->where('y', 'LIKE', '%'.BasicFunctions::likeSaveEscape($searchExp[1]).'%')->get() as $data){
                    $allVillage[] = [
                        'world' => $world,
                        'village' => $data,
                    ];

                    if(count($allVillage) >= SearchController::$limit)
                        return $allVillage;
                }
            }
        }
        return $allVillage;
    }
}
