<?php
/**
 * Created by IntelliJ IDEA.
 * User: crams
 * Date: 18.08.2019
 * Time: 16:10
 */

namespace App\Models\Tool\AttackPlanner;


use App\Models\CustomModel;
use App\Models\Village;
use App\Tool\AttackPlanner\AttackList;
use App\Util\BasicFunctions;
use Illuminate\Http\Request;

class AttackListItem extends CustomModel
{
    protected $fillable = [
        'attack_list_id',
        'type',
        'start_village_id',
        'target_village_id',
        'slowest_unit',
        'note',
        'send_time',
        'arrival_time',
        'spear',
        'sword',
        'axe',
        'archer',
        'spy',
        'light',
        'marcher',
        'heavy',
        'ram',
        'catapult',
        'knight',
        'snob',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'send_time' => 'datetime',
        'arrival_time' => 'datetime',
    ];

    public static $units = ['spear', 'sword', 'axe', 'archer', 'spy', 'light', 'marcher', 'heavy', 'ram', 'catapult', 'knight', 'snob'];


    /**
     * @return AttackList
     */
    public function list(){
        return $this->belongsTo(AttackList::class, 'attack_list_id');
    }

    /**
     * @return Village
     */
    public function start_village(){
        $world = $this->list->world;
        $tblName = BasicFunctions::getWorldDataTable($world, "village_latest");

        return $this->mybelongsTo(Village::class, 'start_village_id', 'villageID', $tblName);
    }

    /**
     * @return Village
     */
    public function target_village(){
        $world = $this->list->world;
        $tblName = BasicFunctions::getWorldDataTable($world, "village_latest");

        return $this->mybelongsTo(Village::class, 'target_village_id', 'villageID', $tblName);
    }

    public function unitIDToName(){
        return AttackListItem::$units[$this->slowest_unit];
    }

    public static function unitNameToID($input){
        return array_search($input, self::$units);
    }

    public function calcSend(){
        $unitConfig = $this->list->world->unitConfig();
        $dist = $this->calcDistance();
        if($dist == null) return null;
        $boost = $this->support_boost + $this->tribe_skill + 1.00;
        $unit = self::$units[$this->slowest_unit];
        $runningTime = round(((float)$unitConfig->$unit->speed * 60) * $dist / $boost);
        return $this->arrival_time->copy()->subSeconds($runningTime);
    }

    public function calcArrival(){
        $unitConfig = $this->list->world->unitConfig();
        $dist = $this->calcDistance();
        if($dist == null) return null;
        $boost = $this->support_boost + $this->tribe_skill + 1.00;
        $unit = self::$units[$this->slowest_unit];
        $runningTime = round(((float)$unitConfig->$unit->speed * 60) * $dist / $boost);
        return $this->send_time->copy()->addSeconds($runningTime);
    }

    public function calcDistance(){
        if($this->start_village == null || $this->target_village == null) return null;
        
        if($this->target_village->bonus_id >= 11 && $this->target_village->bonus_id <= 21) {
            //great siege village always same distance
            if($this->slowest_unit == 4) {
                return 3; // spy
            } else {
                return 15;
            }
        } else {
            return sqrt(pow($this->start_village->x - $this->target_village->x, 2) + pow($this->start_village->y - $this->target_village->y, 2));
        }
    }

    public function setUnits(Request $data, $forceAllow) {
        $err = [];
        foreach (self::$units as $unit){
            if(!$forceAllow && !isset($data->checkboxes[$unit])) continue;

            if ($data->{$unit} == null){
                $this->{$unit} = null;
            }else{
                $this->{$unit} = mb_strimwidth($data->{$unit}, 0, 200, "...");
            }
        }
        return $err;
    }


    public function setUnitsArr(array $data, $forceAllow=true) {
        $err = [];
        foreach (self::$units as $unit){
            if(!$forceAllow && !isset($data['checkboxes'][$unit])) continue;
            
            if (!isset($data[$unit]) || $data[$unit] == null){
                $this->{$unit} = null;
            } else {
                $this->{$unit} = mb_strimwidth($data[$unit], 0, 200, "...");
            }
        }
        return $err;
    }


    public function setEmptyUnits() {
        foreach (self::$units as $unit){
            $this->{$unit} = null;
        }
    }
    
    public static function unitVerifyArray() {
        $ret = [];
        foreach (self::$units as $unit) {
            $ret[$unit] = '';
        }
        return $ret;
    }

    public function verifyTime() {
        if($this->send_time->year <= 1970) {
            return [ __('tool.attackPlanner.sendtimeToSoon') ];
        }
        if($this->send_time->year > 2037) {
            return [ __('tool.attackPlanner.sendtimeToLate') ];
        }
        if($this->arrival_time->year <= 1970) {
            return [ __('tool.attackPlanner.arrivetimeToSoon') ];
        }
        if($this->arrival_time->year > 2037) {
            return [ __('tool.attackPlanner.arrivetimeToLate') ];
        }
        return [];
    }

    public static function errJsonReturn($err) {
        $msg = "";
        foreach($err as $e) {
            $msg .= $e . "<br>";
        }

        return \Response::json(array(
            'data' => 'error',
            'title' => __('tool.attackPlanner.errorTitle'),
            'msg' => $msg,
        ));
    }
}
