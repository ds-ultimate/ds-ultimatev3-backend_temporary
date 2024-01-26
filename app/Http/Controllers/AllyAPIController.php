<?php

namespace App\Http\Controllers;

use App\Models\Ally;
use App\Models\AllyTop;
use App\Models\AllyChange;
use App\Models\Conquer;
use App\Models\Player;
use App\Models\Server;
use App\Models\Village;
use App\Models\World;
use App\Http\Resources\VillageResource;
use App\Util\BasicFunctions;
use App\Util\Chart;
use App\Util\DataTable;
use Illuminate\Support\Facades\Response;

class AllyAPIController extends Controller
{
    public function allyBasicData($server, $world, $ally){
        $server = Server::getAndCheckServerByCode($server);
        $worldData = World::getAndCheckWorld($server, $world);
        $ally_id = (int) $ally;

        $allyData = Ally::ally($worldData, $ally_id);
        $allyTopData = AllyTop::ally($worldData, $ally_id);
        
        $conquer = Conquer::allyConquerCounts($worldData, $ally_id);
        $allyChanges = AllyChange::allyAllyChangeCounts($worldData, $ally_id);
        
        BasicFunctions::abort_if_translated($allyData == null && $allyTopData == null, 404,
                "404.allyNotFound", ["world" => $worldData->getDisplayName(), "ally" => $ally_id,
                "interpolation" => ["skipOnVariables" => false]]);
        return Response::json([
            "cur" => $allyData,
            "top" => $allyTopData,
            "conquer" => $conquer,
            "changes" => $allyChanges,
        ]);
    }
    
    public function allyAllyHist($server, $world, $ally) {
        $server = Server::getAndCheckServerByCode($server);
        $worldData = World::getAndCheckWorld($server, $world);
        
        $ally = validator(['a' => $ally], [
            'a' => 'required|numeric|integer',
        ])->validate()['a'];
        
        $tableNr = $ally % $worldData->hash_ally;
        
        $allyModel = new Ally($worldData, "ally_$tableNr");
        $data = $allyModel->where('allyID', $ally)->get();
        
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
    
    public function allyChartData($server, $world, $ally) {
        $server = Server::getAndCheckServerByCode($server);
        $worldData = World::getAndCheckWorld($server, $world);
        $ally_id = (int) $ally;
        
        $allyData = Ally::ally($worldData, $ally_id);
        BasicFunctions::abort_if_translated($allyData == null, 404,
                "404.allyNotFound", ["world" => $worldData->getDisplayName(), "ally" => $ally_id,
                "interpolation" => ["skipOnVariables" => false]]);
        
        $statsGeneral = ['points', 'rank', 'village'];
        $statsBash = ['gesBash', 'offBash', 'defBash'];

        $datas = Ally::allyDataChart($worldData, $ally_id);
        if(count($datas) < 1) {
            $datas[] = [
                "timestamp" => time(),
                "points" => $allyData->points,
                "rank" => $allyData->rank,
                "village" => $allyData->village_count,
                "gesBash" => $allyData->gesBash,
                "offBash" => $allyData->offBash,
                "defBash" => $allyData->defBash,
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
    
    public function allyPlayer($server, $world, $ally){
        $server = Server::getAndCheckServerByCode($server);
        $worldData = World::getAndCheckWorld($server, $world);
        $ally_id = (int) $ally;
        //TODO return maybe 422 here for ally_id < 1?
        BasicFunctions::abort_if_translated($ally_id < 1, 404,
                "404.allyNotFound", ["world" => $worldData->getDisplayName(), "ally" => $ally_id,
                "interpolation" => ["skipOnVariables" => false]]);
        
        $query = Player::getJoinedQuery($worldData);
        $query->where("ally_id", $ally_id);

        return DataTable::generate($query)
            ->clientSide()
            ->setWhitelist([])
            ->setSearchWhitelist([])
            ->toJson();
    }
    
    public function allyPlayerVillage($server, $world, $ally){
        $server = Server::getAndCheckServerByCode($server);
        $worldData = World::getAndCheckWorld($server, $world);
        $ally_id = (int) $ally;
        //TODO return maybe 422 here for ally_id < 1?
        BasicFunctions::abort_if_translated($ally_id < 1, 404,
                "404.allyNotFound", ["world" => $worldData->getDisplayName(), "ally" => $ally_id,
                "interpolation" => ["skipOnVariables" => false]]);
        
        $query = Village::getJoinedQuery($worldData, loadPlayers: True);
        $query->where("player.ally_id", $ally_id);

        return DataTable::generate($query)
            ->clientSide()
            ->setWhitelist([])
            ->setSearchWhitelist([])
            ->toJson(function($entry) {
                return new VillageResource($entry, true, false);
            });
    }
}
