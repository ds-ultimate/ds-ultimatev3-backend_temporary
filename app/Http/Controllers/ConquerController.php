<?php

namespace App\Http\Controllers;

use App\Models\Conquer;
use App\Models\Server;
use App\Models\World;
use App\Http\Resources\ConquerResource;
use App\Util\DataTable;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class ConquerController extends Controller
{
    private static $whitelist = ['village__name', 'old_owner_name', 'new_owner_name', 'points', 'timestamp'];
    private static $searchWhitelist = ['conquer.old_owner_name', 'conquer.new_owner_name',
        'conquer.old_ally_name', 'conquer.new_ally_name', 'conquer.old_ally_tag',
        'conquer.new_ally_tag', 'village.name'];
    private static $conquerReturnValidate = [
        'filter' => 'array:0,1,2,3,v,op,oa,np,na',
        'filter.0' => 'numeric|integer',
        'filter.1' => 'numeric|integer',
        'filter.2' => 'numeric|integer',
        'filter.3' => 'numeric|integer',
        //'filter.4' => 'numeric|integer', deleted is not possible
        //'filter.5' => 'numeric|integer', this filtering is done by the type
        //'filter.6' => 'numeric|integer', this filtering is done by the type
        'filter.v' => 'numeric|integer',
        'filter.op' => 'numeric|integer',
        'filter.oa' => 'numeric|integer',
        'filter.np' => 'numeric|integer',
        'filter.na' => 'numeric|integer',
    ];

    public static $REFERTO_VILLAGE = 0;
    public static $REFERTO_PLAYER = 1;
    public static $REFERTO_ALLY = 2;

    public function worldConquer($server, $world, $type){
        $server = Server::getAndCheckServerByCode($server);
        $worldData = World::getAndCheckWorld($server, $world);
        
        $query = Conquer::getJoinedQuery($worldData);
        switch($type) {
            case 'all':
                break;
            default:
                //TODO localized error messages current idea: return translation idx + params
                abort(404);
        }

        return $this->doConquerReturn($query, $worldData);
    }

    public function allyConquer($server, $world, $type, $allyID) {
        $server = Server::getAndCheckServerByCode($server);
        $worldData = World::getAndCheckWorld($server, $world);
        
        $query = Conquer::getJoinedQuery($worldData);
        switch($type) {
            case "all":
                $query->where(function($q) use($allyID) {
                    $q->orWhere('conquer.new_ally', $allyID)->orWhere('conquer.old_ally', $allyID);
                });
                break;
            case "old":
                $query->where(function($q) use($allyID) {
                    $q->where('conquer.new_ally', "!=", $allyID)->where('conquer.old_ally', $allyID);
                });
                break;
            case "new":
                $query->where(function($q) use($allyID) {
                    $q->where('conquer.new_ally', $allyID)->where('conquer.old_ally', "!=", $allyID);
                });
                break;
            case "own":
                $query->where(function($q) use($allyID) {
                    $q->where('conquer.new_ally', $allyID)->where('conquer.old_ally', $allyID);
                });
                break;
            default:
                abort(404, __("ui.errors.404.unknownType", ["type" => $type]));
        }

        return $this->doConquerReturn($query, $world);
    }

    public function playerConquer($server, $world, $type, $playerID) {
        $server = Server::getAndCheckServerByCode($server);
        $worldData = World::getAndCheckWorld($server, $world);
        
        $query = Conquer::getJoinedQuery($worldData);
        switch($type) {
            case "all":
                $query->where(function($q) use($playerID) {
                    $q->orWhere('conquer.new_owner', $playerID)->orWhere('conquer.old_owner', $playerID);
                });
                break;
            case "old":
                $query->where(function($q) use($playerID) {
                    $q->where('conquer.new_owner', "!=", $playerID)->where('conquer.old_owner', $playerID);
                });
                break;
            case "new":
                $query->where(function($q) use($playerID) {
                    $q->where('conquer.new_owner', $playerID)->where('conquer.old_owner', "!=", $playerID);
                });
                break;
            case "own":
                $query->where(function($q) use($playerID) {
                    $q->where('conquer.new_owner', $playerID)->where('conquer.old_owner', $playerID);
                });
                break;
            default:
                abort(404, __("ui.errors.404.unknownType", ["type" => $type]));
        }

        return $this->doConquerReturn($query, $world);
    }
    
    /**
     * Filter based on conquerChangeType + village / player / ally id
     * 
     * ConquerChangeType mappings:
     * 0: normal
     * 1: internal
     * 2: self
     * 3: barbarian
     * 4: deletion
     */
    private function doConquerReturn($dtQuery, $worldData) {
        $getArray = Request::validate(static::$conquerReturnValidate);
        $filter = $getArray["filter"] ?? [];
        
        $filterCb = function($query) use($filter) {
            $query->where(function($q) use($filter) {
                if(($filter[0] ?? 1) && ($filter[1] ?? 1) && ($filter[2] ?? 1) &&
                        ($filter[3] ?? 1)) { //&& ($filter[4] ?? 1)
                    return;
                }
                $q->orWhere("timestamp", -1);

                if($filter[0] ?? 1) {
                    $q->orWhere(function($qi) {
                        $qi->where(function($qi2) {
                            $qi2->orwhereColumn("conquer.new_ally", "!=", "conquer.old_ally");
                            $qi2->orWhere("conquer.old_ally", 0);
                        });
                        $qi->whereColumn("conquer.new_owner", "!=", "conquer.old_owner");
                        $qi->whereNot("conquer.new_owner", 0);
                        $qi->whereNot("conquer.old_owner", 0);
                    });
                }
                if($filter[1] ?? 1) {
                    $q->orWhere(function($qi) {
                        $qi->whereColumn("conquer.new_ally", "conquer.old_ally");
                        $qi->whereNot("conquer.old_ally", 0);
                        $qi->whereColumn("conquer.new_owner", "!=", "conquer.old_owner");
                    });
                }
                if($filter[2] ?? 1) {
                    $q->orWhere(function($qi) {
                        $qi->whereColumn("conquer.new_owner", "conquer.old_owner");
                        $qi->whereNot("conquer.old_owner", 0);
                    });
                }
                if($filter[3] ?? 1) {
                    $q->orWhere("conquer.old_owner", 0);
                }
//                if($filter[4] ?? 1) {
//                    $q->orWhere("conquer.new_owner", 0);
//                } not possible
            });
            if(isset($filter["v"])) {
                $query->where("conquer.village_id", (int) $filter["v"]);
            }
            if(isset($filter["op"])) {
                $query->where("conquer.old_owner", (int) $filter["op"]);
            }
            if(isset($filter["np"])) {
                $query->where("conquer.new_owner", (int) $filter["np"]);
            }
            if(isset($filter["oa"])) {
                $query->where("conquer.old_ally", (int) $filter["oa"]);
            }
            if(isset($filter["na"])) {
                $query->where("conquer.new_ally", (int) $filter["na"]);
            }
        };

        return DataTable::generate($dtQuery)
            ->setWhitelist(static::$whitelist)
            ->setSearchWhitelist(static::$searchWhitelist)
            ->setFilter($filterCb)
            ->toJson(function($entry) {
                return new ConquerResource($entry, true);
            });
    }

    public function worldConquerDailyPlayer($server, $world){
        $server = Server::getAndCheckServerByCode($server);
        $worldData = World::getAndCheckWorld($server, $world);
        $day = Request::validate(['date' => 'required|string'])['date'];
        $date = Carbon::createFromFormat('Y-m-d', $day);

        $c = new Conquer($worldData);
        $query = DB::table($c->getTable(), "conquer")
                ->select([
                    DB::raw('count(*) as total'),
                    "conquer.new_owner",
                    DB::raw("MAX(conquer.new_owner_name) as new_owner_name"),
                    DB::raw("MAX(conquer.new_ally) as new_ally"),
                    DB::raw("MAX(conquer.new_ally_name) as new_ally_name"),
                    DB::raw("MAX(conquer.new_ally_tag) as new_ally_tag")
                ])
                ->where('timestamp', '>', $date->startOfDay()->getTimestamp())
                ->where('timestamp', '<', $date->endOfDay()->getTimestamp())
                ->groupBy('conquer.new_owner')
                ->orderBy('total', 'DESC')
                ->orderBy('new_owner', 'ASC');
        

        return DataTable::generate($query)
            ->clientSide()
            ->setWhitelist([])
            ->setSearchWhitelist([])
            ->toJson(function($entry, $i) {
                $ret = [
                    "rank" => $i + 1,
                    "count" => (int) $entry->total,
                    "playerID" => (int) $entry->new_owner,
                    "name" => $entry->new_owner_name,
                    "ally_id" => (int) $entry->new_ally,
                    "ally_name" => $entry->new_ally_name,
                    "ally_tag" => $entry->new_ally_tag,
                ];
                return $ret;
            });
    }

    public function worldConquerDailyAlly($server, $world){
        $server = Server::getAndCheckServerByCode($server);
        $worldData = World::getAndCheckWorld($server, $world);
        $day = Request::validate(['date' => 'required|string'])['date'];
        $date = Carbon::createFromFormat('Y-m-d', $day);

        $c = new Conquer($worldData);
        $query = DB::table($c->getTable(), "conquer")
                ->select([
                    DB::raw('count(*) as total'),
                    "conquer.new_ally",
                    DB::raw("MAX(conquer.new_ally_name) as new_ally_name"),
                    DB::raw("MAX(conquer.new_ally_tag) as new_ally_tag")
                ])
                ->where('timestamp', '>', $date->startOfDay()->getTimestamp())
                ->where('timestamp', '<', $date->endOfDay()->getTimestamp())
                ->where('conquer.new_ally', "!=", 0)
                ->groupBy('conquer.new_ally')
                ->orderBy('total', 'DESC')
                ->orderBy('new_ally', 'ASC');
        

        return DataTable::generate($query)
            ->clientSide()
            ->setWhitelist([])
            ->setSearchWhitelist([])
            ->toJson(function($entry, $i) {
                $ret = [
                    "rank" => $i + 1,
                    "count" => (int) $entry->total,
                    "allyID" => (int) $entry->new_ally,
                    "name" => $entry->new_ally_name,
                    "tag" => $entry->new_ally_tag,
                ];
                return $ret;
            });
    }
}
