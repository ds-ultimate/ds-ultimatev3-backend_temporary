<?php
/**
 * Created by IntelliJ IDEA.
 * User: crams
 * Date: 14.05.2019
 * Time: 19:08
 */

namespace App\Util;


class Chart
{
    public static function validType($data){
        switch($data) {
            case 'points':
            case 'rank':
            case 'village':
            case 'gesBash':
            case 'offBash':
            case 'defBash':
            case 'supBash':
                return true;
            default:
                return false;
        }
    }

    public static function generateChart($rawData, $chartType, $gapFill=false){
        if (!Chart::validType($chartType)) {
            return;
        }
        $entryDiff = 4*60*60;

        $old = [
            't' => null, 'd' => null, 'l' => -1,
        ];
        $result = [];
        
        foreach ($rawData as $data){
            if($old['t'] != null && $old['t'] != $old['l']) {
                $oldDiff = abs($old['t'] - $old['l'] - $entryDiff);
                $newDiff = abs($data['timestamp'] - $old['l'] - $entryDiff);
                if($oldDiff < $newDiff) {
                    $result[] = static::rowFormat($old['t'], $old['d']);
                    $old['l'] = $old['t'];
                }
            }
            
            if($gapFill && $old['t'] != null) {
                while($old['t'] + $entryDiff + 300 < $data['timestamp']) {
                    $old['t'] += $entryDiff;
                    $result[] = static::rowFormat($old['t'], $old['d']);
                }
            }
            
            $old['t'] = $data['timestamp'];
            $old['d'] = $data[$chartType];
            
            if($old['l'] + $entryDiff - 300 < $data['timestamp']) {
                $result[] = static::rowFormat($data['timestamp'], $data[$chartType]);
                $old['l'] = $data['timestamp'];
            }
        }
        
        if (count($result) < 2){
            $result[] = static::rowFormat($data['timestamp'] - $entryDiff, $data[$chartType]);
        }
        return $result;
    }
    
    private static function rowFormat($date, $val) {
        return [date('Y-m-d H:i:s', $date), $val];
    }
}
