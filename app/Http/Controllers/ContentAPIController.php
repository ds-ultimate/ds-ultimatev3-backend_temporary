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
        $serverArray = Server::getServer();
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

    /*
     * https://ds-ultimate.de/de/164/allys
     * */
    public function allys($server, $world){
        $server = Server::getAndCheckServerByCode($server);
        $worldData = World::getAndCheckWorld($server, $world);

        return view('content.worldAlly', compact('worldData', 'server'));
    }

    /*
     * https://ds-ultimate.de/de/164/players
     * */
    public function players($server, $world){
        $server = Server::getAndCheckServerByCode($server);
        $worldData = World::getAndCheckWorld($server, $world);

        return view('content.worldPlayer', compact('worldData', 'server'));
    }

    public function conquer($server, $world, $type){
        $server = Server::getAndCheckServerByCode($server);
        $worldData = World::getAndCheckWorld($server, $world);

        switch($type) {
            case "all":
                $typeName = ucfirst(__('ui.conquer.all'));
                break;
            default:
                abort(404, __("ui.errors.404.unknownType", ["type" => $type]));
        }

        $allHighlight = ['s', 'i', 'b', 'd'];
        if(\Auth::check()) {
            $profile = \Auth::user()->profile;
            $userHighlight = explode(":", $profile->conquerHightlight_World);
        } else {
            $userHighlight = $allHighlight;
        }

        $who = $worldData->getDistplayName();
        $routeDatatableAPI = route('api.worldConquer', [$worldData->id, $type]);
        $routeHighlightSaving = route('user.saveConquerHighlighting', ['world']);
        $tableStateName = "tableStateName";

        return view('content.conquer', compact('server', 'worldData', 'typeName',
                'who', 'routeDatatableAPI', 'routeHighlightSaving',
                'allHighlight', 'userHighlight', 'tableStateName'));
    }

    public function conquereDaily($server, $world){
        $server = Server::getAndCheckServerByCode($server);
        $worldData = World::getAndCheckWorld($server, $world);
        
        $conquer = new Conquer($worldData);
        $fistconquer = $conquer->first();

        return view('content.conquerDaily', compact('server', 'worldData', 'fistconquer'));
    }

    public function sitemap() {
        $servers = array();
        $serverArray = Server::getServer();

        foreach($serverArray as $server) {
            $worldsArray = World::worldsCollection($server);
            $servers[$server->code] = [];

            if(isset($worldsArray['world']) && count($worldsArray['world']) > 0) {
                $servers[$server->code] = array_merge($servers[$server->code], $worldsArray['world']);
            }
            if(isset($worldsArray['speed']) && count($worldsArray['speed']) > 0) {
                $servers[$server->code] = array_merge($servers[$server->code], $worldsArray['speed']);
            }
            if(isset($worldsArray['casual']) && count($worldsArray['casual']) > 0) {
                $servers[$server->code] = array_merge($servers[$server->code], $worldsArray['casual']);
            }
            if(isset($worldsArray['classic']) && count($worldsArray['classic']) > 0) {
                $servers[$server->code] =  array_merge($servers[$server->code], $worldsArray['classic']);
            }
        }

        return response()->view('sitemap', compact('servers'))->header('Content-Type', 'text/xml');
    }

    public function changelog(){
        $changelogModel = new Changelog();

        $changelogs = $changelogModel->orderBy('created_at', 'DESC')->get();

        $locale = in_array(App::getLocale(), config('dsUltimate.changelog_lang_key'))? App::getLocale() : 'en';

        return view('content.changelog', compact('changelogs', 'locale'));
    }
}