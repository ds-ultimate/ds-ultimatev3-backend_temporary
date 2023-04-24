<?php

namespace App\Http\Controllers;

use App\Models\Ally;
use App\Models\AllyTop;
use App\Models\AllyChanges;
use App\Models\Conquer;
use App\Models\Player;
use App\Models\Server;
use App\Models\World;
use App\Util\Chart;
use App\Util\DataTable;
use Illuminate\Support\Facades\Response;

class AllyAPIController extends Controller
{
    public function allyBasicData($server, $world, $ally){
        $server = Server::getAndCheckServerByCode($server);
        $worldData = World::getAndCheckWorld($server, $world);

        $allyData = Ally::ally($worldData, $ally);
        $allyTopData = AllyTop::ally($worldData, $ally);
        
        $conquer = Conquer::allyConquerCounts($worldData, $ally);
        $allyChanges = AllyChanges::allyAllyChangeCounts($worldData, $ally);
        
        abort_if($allyData == null && $allyTopData == null, 404, __("ui.errors.404.allyNotFound", ["world" => $worldData->getDistplayName(), "ally" => $ally]));
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
        
        $allyData = Ally::ally($worldData, $ally);
        abort_if($allyData == null, 404);
        
        $statsGeneral = ['points', 'rank', 'village'];
        $statsBash = ['gesBash', 'offBash', 'defBash'];

        $datas = Ally::allyDataChart($worldData, $ally);
        if(count($datas) < 1) {
            $datas[] = [
                "timestamp" => time(),
                "points" => $allyData->points,
                "rank" => $allyData->rank,
                "village" => $allyData->village,
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

        
        $query = Player::getJoinedQuery($worldData);
        $query->where("ally_id", $ally_id);

        return DataTable::generate($query)
            ->clientSide()
            ->setWhitelist([])
            ->setSearchWhitelist([])
            ->toJson();
    }
}
