<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\Server;
use App\Models\World;
use App\Util\Map\AbstractMapGenerator;
use App\Util\Map\SkinSymbols;
use App\Util\Map\SQLMapGenerator;

use Illuminate\Routing\Controller as BaseController;

class MapController extends BaseController
{
    public function getSizedOverviewMap($server, $world, $type, $id, $width, $height, $ext){
        $worldData = World::getAndCheckWorld($server, $world);

        $skin = new SkinSymbols();
        $map = new SQLMapGenerator($worldData, $skin, $this->decodeDimensions($width, $height), config('app.debug'));
        switch($type) {
            case 'a':
                $map->markAlly($id, [255, 255, 255], false, true);
                break;
            case 'p':
                $map->markPlayer($id, [255, 255, 255], false, true);
                break;
            case 'v':
                $map->markVillage($id, [255, 255, 255], false, true);
                break;
            default:
                abort(404, __("ui.errors.404.unknownType", ["type" => $type]));
        }
        $map->setLayerOrder([AbstractMapGenerator::$LAYER_MARK, AbstractMapGenerator::$LAYER_GRID]);
        $map->setMapDimensions([
            'xs' => 0,
            'ys' => 0,
            'xe' => 1000,
            'ye' => 1000,
        ]);
        $map->setOpaque(100);
        $map->setAutoResize(true);
        $map->render();
        return $map->output($ext);
    }

    public function getOverviewMap($server, $world, $type, $id, $ext){
        return $this->getSizedOverviewMap($server, $world, $type, $id, null, null, $ext);
    }

    private function decodeDimensions($width, $height)
    {
        if($width == 'w') {
            return array(
                'width' => $height,
            );
        } else if($width == 'h') {
            return array(
                'height' => $height,
            );
        } else {
            return array(
                'width' => $width,
                'height' => $height,
            );
        }
    }

    public function mapTop10P($server, $world){
        $worldData = World::getAndCheckWorld($server, $world);
        
        $playerModel = new Player($worldData);
        $players = $playerModel->orderBy('rank')->limit(10)->get();

        $skin = new SkinSymbols();
        $map = new SQLMapGenerator($worldData, $skin, $this->decodeDimensions(800, 800), config('app.debug'));

        $color = [[138,43,226],[72,61,139],[69,139,116],[188,143,143],[139,105,105],[244,164,96],[139,35,35],[139,115,85],[139,69,19],[0,100,0]];
        $i = 0;
        foreach ($players as $player){
            $map->markPlayer($player->playerID, $color[$i], false, true);
            $i++;
        }
        $map->setLayerOrder([AbstractMapGenerator::$LAYER_MARK, AbstractMapGenerator::$LAYER_GRID]);
        $map->setMapDimensions([
            'xs' => 0,
            'ys' => 0,
            'xe' => 1000,
            'ye' => 1000,
        ]);
        $map->setOpaque(100);
        $map->setAutoResize(true);
        $map->render();
        return $map->output('png');
    }

    public function mapTop10($server, $world){
        $server = Server::getAndCheckServerByCode($server);
        $worldData = World::getAndCheckWorld($server, $world);
        
        $playerModel = new Player($worldData);
        $players = $playerModel->orderBy('rank')->limit(10)->get();

        $skin = new SkinSymbols();
        $map = new SQLMapGenerator($worldData, $skin, $this->decodeDimensions(800, 800), config('app.debug'));

        $color = [[138,43,226],[72,61,139],[69,139,116],[188,143,143],[139,105,105],[244,164,96],[139,35,35],[139,115,85],[139,69,19],[0,100,0]];

        foreach ($players as $key => $player){
            $ps[$key] = ['name' => $player->name, 'color' => $color[$key]];
        }

        return view('content.mapTop10', compact('worldData', 'server', 'ps'));
    }
}
