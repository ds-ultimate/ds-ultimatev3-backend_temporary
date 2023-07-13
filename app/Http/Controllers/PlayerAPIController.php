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
use App\Http\Resources\VillageResource;
use App\Util\BasicFunctions;
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
        
        BasicFunctions::abort_if_translated($playerData == null && $playerTopData == null, 404,
                "404.playerNotFound", ["world" => $worldData->getDisplayName(), "player" => $player_id,
                "interpolation" => ["skipOnVariables" => false]]);

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

        $playerData = Player::player($worldData, $player_id, __("404.playerNotFound", ["world" => $worldData->server->code.$worldData->name, "player" => $player_id]));
        BasicFunctions::abort_if_translated($playerData == null, 404,
                "404.playerNotFound", ["world" => $worldData->getDisplayName(), "player" => $player_id,
                "interpolation" => ["skipOnVariables" => false]]);
        
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
            ->toJson(function($entry) {
                return new VillageResource($entry, false);
            });
    }
    
    public function playerWorldPopup(World $world, $player) {
        $player_id = (int) $player;
        $playerData = PlayerTop::player($world, $player_id);
        BasicFunctions::abort_if_translated($playerData == null, 404,
                "404.playerNotFound", ["world" => $worldData->getDisplayName(), "player" => $player_id,
                "interpolation" => ["skipOnVariables" => false]]);
        return Response::json([
            "top" => $playerData,
        ]);
    }
}
