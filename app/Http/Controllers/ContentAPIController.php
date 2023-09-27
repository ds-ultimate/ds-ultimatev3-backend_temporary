<?php

namespace App\Http\Controllers;

use App\Models\Ally;
use App\Models\Changelog;
use App\Models\Conquer;
use App\Models\News;
use App\Models\Player;
use App\Models\Server;
use App\Models\World;
use Illuminate\Support\Facades\Response;

class ContentAPIController extends Controller
{
    public function getServers() {
        $serverArray = Server::getServer(withWorldCount: true);
        return Response::json($serverArray);
    }

    public function getNews(){
        $news = News::orderBy('order')->get();
        return Response::json($news);
    }
    
    public function getChangelogs(){
        $changelog = new Changelog();
        return Response::json($changelog->orderBy("created_at", "DESC")->get());
    }
    
    public function getWorlds(){
        $worlds = World::with("server")->get();
        return Response::json($worlds);
    }
    
    public function worldOverview($server, $world){
        $server = Server::getAndCheckServerByCode($server);
        $worldData = World::getAndCheckWorld($server, $world);

        $playerArray = Player::top10Player($worldData);
        $allyArray = Ally::top10Ally($worldData);
        return Response::json([
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
        ], options: JSON_NUMERIC_CHECK);
    }
}
