<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\Server;
use App\Models\World;
use App\Util\DataTable;

class DatatableController extends Controller
{
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
