<?php

namespace App\Http\Controllers;

use App\Models\Conquer;
use App\Models\Server;
use App\Models\Village;
use App\Models\World;
use App\Util\BasicFunctions;
use App\Util\Chart;
use Illuminate\Support\Facades\Response;

class VillageAPIController extends Controller
{
    public function villageBasicData($server, $world, $village){
        $server = Server::getAndCheckServerByCode($server);
        $worldData = World::getAndCheckWorld($server, $world);
        $village_id = (int) $village;

        $villageData = Village::village($worldData, $village_id, true);
        BasicFunctions::abort_if_translated($villageData == null, 404,
                "404.villageNotFound", ["world" => $worldData->getDisplayName(), "village" => $village_id, 
                "interpolation" => ["skipOnVariables" => false]]);
        
        $conquer = Conquer::villageConquerCounts($worldData, $village_id);

        $datas = Village::villageDataChart($worldData, $village_id);
        if(count($datas) < 1) {
            $datas[] = [
                "timestamp" => time(),
                "points" => $villageData->points,
            ];
        }
        
        return Response::json([
            "data" => $villageData, //TODO add owner information here
            "conquer" => $conquer,
            "history" => Chart::generateChart($datas, 'points'),
        ]);
    }
}
