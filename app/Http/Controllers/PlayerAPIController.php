<?php

namespace App\Http\Controllers;

use App\Models\AllyChange;
use App\Models\Conquer;
use App\Models\Player;
use App\Models\PlayerOtherServers;
use App\Models\PlayerTop;
use App\Models\Server;
use App\Models\Village;
use App\Models\World;
use App\Util\Chart;
use App\Util\DataTable;
use Illuminate\Support\Facades\Response;

class PlayerAPIController extends Controller
{
    public function playerBasicData($server, $world, $player){
        $server = Server::getAndCheckServerByCode($server);
        $worldData = World::getAndCheckWorld($server, $world);
        $player_id = (int) $player;

        $playerData = Player::player($worldData, $player_id);
        $playerTopData = PlayerTop::player($worldData, $player_id);
        
        $conquer = Conquer::playerConquerCounts($worldData, $player_id);
        $playerAllyChanges = AllyChange::playerAllyChangeCount($worldData, $player_id);
        //TODO create a job that deletes duplicate playerOtherServers and also make sure that this does not happen again
        $playerOtherServers = PlayerOtherServers::player($worldData->server, $player_id);
        
        abort_if($playerData == null && $playerTopData == null, 404, __("ui.errors.404.playerNotFound", ["world" => $worldData->getDistplayName(), "player" => $player_id]));
        return Response::json([
            "cur" => $playerData,
            "top" => $playerTopData,
            "conquer" => $conquer,
            "changes" => $playerAllyChanges,
            "otherServers" => ($playerOtherServers !== null)?$playerOtherServers->getWorldIds():[],
        ]);
    }
    
    public function playerPlayerHistory($server, $world, $player) {
        $server = Server::getAndCheckServerByCode($server);
        $worldData = World::getAndCheckWorld($server, $world);
        $player_id = (int) $player;
        
        $tableNr = $player_id % $worldData->hash_player;
        
        $playerModel = new Player($worldData, "player_$tableNr");
        $data = $playerModel->where('playerID', $player_id)->get();
        
        $newData = [];
        $dates = [];
        $lData = null;
        foreach($data as $d) {
            $date = $d->created_at->format("Y-m-d");
            if(! isset($dates[$date])) {
                $dates[$date] = 1;
                $d->last = $lData;
                $newData[] = $d;
                $lData = $d;
            }
        }
        
        return DataTable::of($newData)
            ->clientSide()
            ->setWhitelist([])
            ->setSearchWhitelist([])
            ->toJson(function($entry, $i) use($newData) {
                $ret = [
                    "cur" => $entry,
                    "last" => $entry->last,
                    "date" => $entry->created_at,
                ];
                return $ret;
            });
    }
    
    public function playerChartData($server, $world, $player) {
        $server = Server::getAndCheckServerByCode($server);
        $worldData = World::getAndCheckWorld($server, $world);
        $player_id = (int) $player;

        $playerData = Player::player($worldData, $player_id);
        abort_if($playerData == null, 404);
        
        $statsGeneral = ['points', 'rank', 'village'];
        $statsBash = ['gesBash', 'offBash', 'defBash', 'supBash'];

        $datas = Player::playerDataChart($worldData, $player_id);
        if(count($datas) < 1) {
            $datas[] = [
                "timestamp" => time(),
                "points" => $playerData->points,
                "rank" => $playerData->rank,
                "village" => $playerData->village,
                "gesBash" => $playerData->gesBash,
                "offBash" => $playerData->offBash,
                "defBash" => $playerData->defBash,
                "supBash" => $playerData->supBash,
            ];
        }
        
        $chartData = [
            "general" => [],
            "bash" => [],
        ];
        foreach($statsGeneral as $statGen){
            $chartData["general"][$statGen] = Chart::generateChart($datas, $statGen);
        }
        foreach($statsBash as $statGen){
            $chartData["bash"][$statGen] = Chart::generateChart($datas, $statGen);
        }
        
        return Response::json($chartData);
    }
        
    public function playerVillage($server, $world, $player) { 
        $server = Server::getAndCheckServerByCode($server);
        $worldData = World::getAndCheckWorld($server, $world);
        $player_id = (int) $player;
        
        $query = Village::getJoinedQuery($worldData);
        $query->where("owner", $player_id);

        return DataTable::generate($query)
            ->clientSide()
            ->setWhitelist([])
            ->setSearchWhitelist([])
            ->toJson();
    }
    
    public function playerWorldPopup(World $world, $playerId){
        $playerData = PlayerTop::player($world, $playerId);
        abort_if($playerData == null, 404, __("ui.errors.404.playerNotFound", ["world" => $world->getDistplayName(), "player" => $playerId]));
        return Response::json([
            "top" => $playerData,
        ]);
    }
    
    //TODO allyChanges -> just front end backend moves to allyChangeController
    public function allyChanges($server, $world, $type, $playerID) {
        $server = Server::getAndCheckServerByCode($server);
        $worldData = World::getAndCheckWorld($server, $world);

        $playerTopData = PlayerTop::player($worldData, $playerID);
        abort_if($playerTopData == null, 404, __("ui.errors.404.playerNotFound", ["world" => $worldData->getDistplayName(), "player" => $playerID]));

        switch($type) {
            case "all":
                $typeName = ucfirst(__('ui.allyChanges.all'));
                break;
            default:
                abort(404, __("ui.errors.404.unknownType", ["type" => $type]));
        }
        return view('content.playerAllyChange', compact('worldData', 'server', 'playerTopData', 'typeName', 'type'));
    }
    
    //TODO conquer -> just front end backend moves to conquerController
    public function conquer($server, $world, $type, $playerID) {
        $server = Server::getAndCheckServerByCode($server);
        $worldData = World::getAndCheckWorld($server, $world);
        
        $playerTopData = PlayerTop::player($worldData, $playerID);
        abort_if($playerTopData == null, 404, __("ui.errors.404.playerNotFound", ["world" => $worldData->getDistplayName(), "player" => $playerID]));

        switch($type) {
            case "all":
                $typeName = ucfirst(__('ui.conquer.all'));
                break;
            case "old":
                $typeName = ucfirst(__('ui.conquer.lost'));
                break;
            case "new":
                $typeName = ucfirst(__('ui.conquer.won'));
                break;
            case "own":
                $typeName = ucfirst(__('ui.conquer.playerOwn'));
                break;
            default:
                abort(404, __("ui.errors.404.unknownType", ["type" => $type]));
        }
        
        $allHighlight = ['s', 'i', 'b', 'd', 'w', 'l'];
        if(\Auth::check()) {
            $profile = \Auth::user()->profile;
            $userHighlight = explode(":", $profile->conquerHightlight_Player);
        } else {
            $userHighlight = $allHighlight;
        }
        
        $who = BasicFunctions::decodeName($playerTopData->name);
        $routeDatatableAPI = route('api.playerConquer', [$worldData->id, $type, $playerTopData->playerID]);
        $routeHighlightSaving = route('user.saveConquerHighlighting', ['player']);
        $tableStateName = "tableStateName";
        
        return view('content.conquer', compact('server', 'worldData', 'typeName',
                'who', 'routeDatatableAPI', 'routeHighlightSaving',
                'allHighlight', 'userHighlight', 'tableStateName'));
    }
}
