<?php

namespace App\Http\Controllers;

use App\Models\Ally;
use App\Models\Player;
use App\Models\Server;
use App\Models\World;
use App\Util\DataTable;
use App\Http\Resources\PlayerResource;
use Carbon\Carbon;

class DatatableController extends Controller
{
    public function worldAlly($server, $world){
        $server = Server::getAndCheckServerByCode($server);
        $worldData = World::getAndCheckWorld($server, $world);

        $whitelist = ['rank', 'name', 'tag', 'points', 'member_count', 'village_count', 'gesBash', 'offBash', 'defBash'];
        $searchWhitelist = ['rank', 'name', 'tag', 'points', 'member_count', 'village_count', 'gesBash', 'offBash', 'defBash'];

        return DataTable::generate((new Ally($worldData))->select())
            ->setWhitelist($whitelist)
            ->setSearchWhitelist($searchWhitelist)
            ->toJson();
    }
    
    public function worldPlayer($server, $world){
        $server = Server::getAndCheckServerByCode($server);
        $worldData = World::getAndCheckWorld($server, $world);

        $whitelist = ['rank', 'name', 'points', 'village_count', 'gesBash', 'offBash', 'defBash', 'supBash', 'allyLatest__name'];
        $searchWhitelist = ['player.rank', 'player.name', 'player.points', 'player.village_count', 'player.gesBash',
            'player.offBash', 'player.defBash', 'player.supBash', 'ally.name', 'ally.tag'];

        return DataTable::generate(Player::getJoinedQuery($worldData))
            ->setWhitelist($whitelist)
            ->setSearchWhitelist($searchWhitelist)
            ->toJson();
    }
    
    public function worldAllyHist($server, $world){
        $datValid = request()->validate([
            'day' => 'required|date_format:Y-m-d',
        ]);
        $server = Server::getAndCheckServerByCode($server);
        $worldData = World::getAndCheckWorld($server, $world);
        
        $timestamp = Carbon::createFromFormat('Y-m-d', $datValid['day']);

        $whitelist = ['rank', 'name', 'tag', 'points', 'member_count', 'village_count', 'gesBash', 'offBash', 'defBash'];
        $searchWhitelist = ['rank', 'name', 'tag', 'points', 'member_count', 'village_count', 'gesBash', 'offBash', 'defBash'];
        
        $allyHistCache = collect();
        return DataTable::generate((new Ally($worldData))->select())
            ->setMaxOnce(100)
            ->setWhitelist($whitelist)
            ->setSearchWhitelist($searchWhitelist)
            ->prepareHook(function($data) use($worldData, $timestamp, $allyHistCache) {
                $tables = [];
                foreach($data as $d) {
                    $tableNr = $d->allyID % ($worldData->hash_ally);
                    if(! isset($tables[$tableNr])) {
                        $tables[$tableNr] = [];
                    }
                    $tables[$tableNr][] = $d->allyID;
                }
                
                foreach($tables as $tbl => $allyIDs) {
                    $allyModel = new Ally($worldData, "ally_$tbl");
                    $tmp = $allyModel->where(function ($query) use($allyIDs) {
                            foreach($allyIDs as $a) {
                                $query = $query->orWhere('allyID', $a);
                            }
                            return $query;
                        })
                            ->whereDate('updated_at', $timestamp->toDateString())
                            ->orderBy('updated_at', 'DESC')
                            ->setEagerLoads([])
                            ->limit(count($allyIDs))
                            ->get();
                    
                    foreach($tmp as $t) {
                        if(! isset($allyHistCache[$t->allyID])) {
                            $allyHistCache[$t->allyID] = $t;
                        }
                    }
                }
            })
            ->toJson(function($entry) use($allyHistCache) {
                return [
                    $entry,
                    $allyHistCache[$entry->allyID] ?? null,
                ];
            });
    }
    
    public function worldPlayerHist($server, $world){
        $datValid = request()->validate([
            'day' => 'required|date_format:Y-m-d',
        ]);
        $server = Server::getAndCheckServerByCode($server);
        $worldData = World::getAndCheckWorld($server, $world);
        
        $timestamp = Carbon::createFromFormat('Y-m-d', $datValid['day']);

        $whitelist = ['rank', 'name', 'points', 'village_count', 'gesBash', 'offBash', 'defBash', 'supBash', 'allyLatest__name'];
        $searchWhitelist = ['player.rank', 'player.name', 'player.points', 'player.village_count', 'player.gesBash',
            'player.offBash', 'player.defBash', 'player.supBash', 'ally.name', 'ally.tag'];
        
        $playerHistCache = collect();
        return DataTable::generate(Player::getJoinedQuery($worldData))
            ->setMaxOnce(100)
            ->setWhitelist($whitelist)
            ->setSearchWhitelist($searchWhitelist)
            ->prepareHook(function($data) use($worldData, $timestamp, $playerHistCache) {
                $tables = [];
                foreach($data as $d) {
                    $tableNr = $d->playerID % ($worldData->hash_player);
                    if(! isset($tables[$tableNr])) {
                        $tables[$tableNr] = [];
                    }
                    $tables[$tableNr][] = $d->playerID;
                }
                
                foreach($tables as $tbl => $playerIDs) {
                    $allyModel = new Player($worldData, "player_$tbl");
                    $tmp = $allyModel->where(function ($query) use($playerIDs) {
                            foreach($playerIDs as $a) {
                                $query = $query->orWhere('playerID', $a);
                            }
                            return $query;
                        })
                            ->whereDate('updated_at', $timestamp->toDateString())
                            ->orderBy('updated_at', 'DESC')
                            ->setEagerLoads([])
                            ->limit(count($playerIDs))
                            ->get();
                    
                    foreach($tmp as $t) {
                        if(! isset($playerHistCache[$t->playerID])) {
                            $playerHistCache[$t->playerID] = $t;
                        }
                    }
                }
            })
            ->toJson(function($entry) use($playerHistCache) {
                $hist = $playerHistCache[$entry->playerID] ?? null;
                return [
                    $entry,
                    ($hist == null)?(null):(new PlayerResource($hist, false)),
                ];
            });
    }

        /*
         * For AttackPlan
        select `items`.*,
        `s_vil`.`name` as `s_village_name`,`t_vil`.`name` as `t_village_name`,
        `s_ply`.`name` as `s_player_name`, `t_ply`.`name` as `t_player_name`,
        `s_aly`.`name` as `s_ally_name`, `s_aly`.`name` as `t_ally_name`, `s_aly`.`tag` as `s_ally_tag`, `s_aly`.`tag` as `t_ally_tag`
        from `attack_list_items` as `items`
        right join `dsultimate_welt_de208`.`village_latest` as s_vil on `items`.`start_village_id` = `s_vil`.`villageID`
        right join `dsultimate_welt_de208`.`village_latest` as t_vil on `items`.`target_village_id` = `t_vil`.`villageID`
        right join `dsultimate_welt_de208`.`player_latest` as s_ply on `s_vil`.`owner` = `s_ply`.`playerID`
        right join `dsultimate_welt_de208`.`player_latest` as t_ply on `t_vil`.`owner` = `t_ply`.`playerID`
        right join `dsultimate_welt_de208`.`ally_latest` as s_aly on `s_ply`.`ally_id` = `s_aly`.`allyID`
        right join `dsultimate_welt_de208`.`ally_latest` as t_aly on `t_ply`.`ally_id` = `t_aly`.`allyID`
        WHERE `attack_list_id` = 147262 ORDER BY t_ally_tag DESC limit 100 offset 89;
         */
}
