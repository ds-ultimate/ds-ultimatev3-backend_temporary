<?php

namespace App\Http\Controllers;

use App\Models\Ally;
use App\Models\Changelog;
use App\Models\Conquer;
use App\Models\News;
use App\Models\Player;
use App\Models\Server;
use App\Models\World;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Response;

class ContentAPIController extends Controller
{
    public function index(){
        $serverArray = Server::getServer(withWorldCount: true);
        $news = News::orderBy('order')->get();
        return Response::json([
            "servers" => $serverArray,
            "news" => $news,
        ]);
    }
    
    public function serverGetWorlds($server){
        $server = Server::getAndCheckServerByCode($server, withWorlds: true);
        return Response::json([
            "server" => $server,
            "worlds" => $server->worlds,
        ]);
    }
    
    public function worldOverview($server, $world){
        $server = Server::getAndCheckServerByCode($server);
        $worldData = World::getAndCheckWorld($server, $world);

        $playerArray = Player::top10Player($worldData);
        $allyArray = Ally::top10Ally($worldData);
        return Response::json([
            "world" => $worldData,
            "player" => $playerArray,
            "ally" => $allyArray,
        ]);
    }
    
    public function worldExtendedData($server, $world){
        $server = Server::getAndCheckServerByCode($server);
        $worldData = World::getAndCheckWorld($server, $world);

        $config = ($worldData->config !== null) ? simplexml_load_string($worldData->config) : null;
        $buildingConfig = ($worldData->buildings !== null) ? simplexml_load_string($worldData->buildings) : null;
        $unitConfig = ($worldData->units !== null) ? simplexml_load_string($worldData->units) : null;
        $conquer = new Conquer($worldData);
        $fistconquer = $conquer->first();
        return Response::json([
            "firstConquer" => $fistconquer->timestamp,
            "config" => $config,
            "buildings" => $buildingConfig,
            "units" => $unitConfig,
        ]);
    }
}
