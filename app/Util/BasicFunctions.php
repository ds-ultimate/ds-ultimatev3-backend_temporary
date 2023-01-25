<?php
/**
 * Created by IntelliJ IDEA.
 * User: crams
 * Date: 23.03.2019
 * Time: 13:08
 */
namespace App\Util;

use App\Models\World;

class BasicFunctions
{
    /**
     * @param int $num
     * @param int $round_to
     * @return string
     */
    public static function numberConv($num, $round_to = 0){
        return number_format($num, $round_to, ',', '.');
    }

    /**
     * This function only decodes the Data
     * the output must be escaped properly afterwards
     * {{ BasicFunctions::decodeName($test) }}
     *
     * @param string $name
     * @return string
     */
    public static function decodeName($name) {
        return urldecode($name);
    }
    
    /**
     * Returns the raw database name where that world will be stored in
     * Intended only for creating that database / makting sure it exists
     * 
     * If a shared database is beeing used the returned database will be
     * the same for multiple worlds
     * 
     * @param World $model
     * @return type
     */
    public static function getWorldDataDatabase(World $model) {
        if($model->database_id != null) {
            //shared db
            $replaceArray = array(
                '{server}' => $model->database->name,
                '{world}' => '',
            );
        } else {
            $replaceArray = array(
                '{server}' => $model->server->code,
                '{world}' => $model->name,
            );
        }
        return str_replace(array_keys($replaceArray),
            array_values($replaceArray),
            config('dsUltimate.db_database_world'));
    }

    /**
     * @param World $model
     * @param $tableName
     * @return string
     */
    public static function getWorldDataTable(World $model, $tableName) {
        static::ensureValidWorldName($model);
        if($model->database_id != null) {
            //shared db
            return static::getWorldDataDatabase($model) . ".{$model->server->code}{$model->name}_{$tableName}";
        }
        return static::getWorldDataDatabase($model) . "." . $tableName;
    }
    
    public static function hasWorldDataTable(World $model, $tableName) {
        static::ensureValidWorldName($model);
        if($model->database_id != null) {
            //shared db
            return static::existTable(static::getWorldDataDatabase($model), "{$model->server->code}{$model->name}_{$tableName}");
        }
        return static::existTable(static::getWorldDataDatabase($model), $tableName);
    }
    
    /**
     * @param World $model
     * @param $tableName
     * @return string
     */
    public static function getUserWorldDataTable(World $model, $tableName) {
        static::ensureValidWorldName($model);
        return config('dsUltimate.db_database_wData') . ".{$model->server->code}{$model->name}_{$tableName}";
    }
    
    public static function hasUserWorldDataTable(World $model, $tableName) {
        static::ensureValidWorldName($model);
        return static::existTable(config('dsUltimate.db_database_wData'), "{$model->server->code}{$model->name}_{$tableName}");
    }
    
    public static function ensureValidWorldName(World $model) {
        if(! ctype_alnum($model->name)) {
            //this should never happen
            throw new \InvalidArgumentException("World name is not allowed");
        }
    }

    /**
     * @param $haystack
     * @param $needle
     * @return bool
     */
    public static function startsWith($haystack, $needle) {
        return $needle === "" || strpos($haystack, $needle) === 0;
    }

    /**
     * @param $haystack
     * @param $needle
     * @return bool
     */
    public static function endsWith($haystack, $needle) {
        return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
    }

    /**
     * @param $toEscape
     * @return mixed
     */
    public static function likeSaveEscape($toEscape) {
        $search = array( "\\"  , "%"  , "_"  , "["  , "]"  , "'"  , "\""  );
        $replace = array("\\\\", "\\%", "\\_", "[[]", "[]]", "\\'", "\\\"");
        return str_replace($search, $replace, $toEscape);
    }
}
