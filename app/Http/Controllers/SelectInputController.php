<?php

namespace App\Http\Controllers;

use App\Models\Ally;
use App\Models\AllyTop;
use App\Models\Player;
use App\Models\PlayerTop;
use App\Models\Village;
use App\Models\World;
use App\Util\BasicFunctions;
use App\Http\Controllers\Controller;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class SelectInputController extends Controller
{
    public function getSelect2Village(World $world){
        $villageModel = new Village($world);
        return $this->select2return($villageModel, array('name', 'x', 'y'), 'villageID', function($rawData) {
            return array(
                'value' => $rawData->villageID,
                'label' => "[" . $rawData->coordinates() . "] " . BasicFunctions::decodeName($rawData->name),
            );
        });
    }
    
    public function getSelect2Player(World $world){
        $playerModel = new Player($world);
        return $this->select2return($playerModel, array('name'), 'playerID', function($rawData) {
            return array(
                'value' => $rawData->playerID,
                'label' => BasicFunctions::decodeName($rawData->name),
            );
        });
    }

    public function getSelect2Ally(World $world){
        $allyModel = new Ally($world);
        return $this->select2return($allyModel, array('name', 'tag'), 'allyID', function($rawData) {
            return array(
                'value' => $rawData->allyID,
                'label' => BasicFunctions::decodeName($rawData->name) . ' [' . BasicFunctions::decodeName($rawData->tag) . ']',
            );
        });
    }

    public function getSelect2PlayerTop(World $world){
        $playerModel = new PlayerTop($world);
        return $this->select2return($playerModel, array('name'), 'playerID', function($rawData) {
            return array(
                'value' => $rawData->playerID,
                'label' => BasicFunctions::decodeName($rawData->name),
            );
        });
    }

    public function getSelect2AllyTop(World $world){
        $allyModel = new AllyTop($world);
        return $this->select2return($allyModel, array('name', 'tag'), 'allyID', function($rawData) {
            return array(
                'value' => $rawData->allyID,
                'label' => BasicFunctions::decodeName($rawData->name) . ' [' . BasicFunctions::decodeName($rawData->tag) . ']',
            );
        });
    }
    
    private function select2return(Model $model, $searchIn, $idRow, callable $extractOne) {
        $getArray = Request::validate([
            'search' => 'string',
            'page' => 'numeric|integer',
        ]);
        $perPage = 50;
        $search = (isset($getArray['search']))?('%'.BasicFunctions::likeSaveEscape(urlencode($getArray['search'])).'%'):('%');
        $page = (isset($getArray['page']))?($getArray['page']-1):(0);
        
        foreach($searchIn as $row) {
            $model = $model->orWhere($row, 'like', $search);
        }
        if(isset($getArray['search']) && ctype_digit($getArray['search'])) {
            //search by ID
            $model = $model->orWhere($idRow, 'like', '%' . intval($getArray['search']) . '%');
        }
        
        $dataAll = $model->offset($perPage*$page)->limit($perPage+1)->get();
        $converted = array('results' => array(), 'pagination' => array('more' => false));
        $i = 0;
        foreach($dataAll as $data) {
            if($i < $perPage) {
                $converted['results'][] = $extractOne($data);
            } else {
                $converted['pagination']['more'] = true;
            }
            $i++;
        }
        return response()->json($converted);
    }
}
