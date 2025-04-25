<?php

namespace Etq\Restful;

use Etq\Restful\Helpers;

class QueryHelpers
{
    public static function getQueryMaxID($tableName)
    {
        return "SELECT iD FROM $tableName ORDER BY iD DESC LIMIT 1";
    }
    public static function getQueryOfTablesWithOrderByForginKey()
    {
        return "SELECT
         TABLE_NAME,COLUMN_NAME,Count(REFERENCED_TABLE_NAME),REFERENCED_COLUMN_NAME
        FROM
         INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE
         TABLE_SCHEMA = '" . $_SERVER['DB_NAME'] . "'
        GROUP BY
         TABLE_NAME
        Order by
         Count(REFERENCED_TABLE_NAME) ASC";
    }

    public static function getInsertQuery(int $iD, $object, $tableName)
    {
        $action = ($iD == -1 ? "INSERT INTO " : " UPDATE ");
        if ($iD == -1) {
            $query = $action . addslashes($tableName) .
                " (`" . implode('`,`',  array_keys(($object))) .
                "`) VALUES ('" . implode("','", array_values(($object))) . "')";
            //   echo "\n $query \n";
            return $query;
        } else {
            $query = "UPDATE `" . addslashes($tableName) . "` SET ";
            $sep = '';
            foreach ($object as $key => $value) {
                $query .= $sep . "`" . $key . "` = '" . $value . "'";
                $sep = ',';
            }
            //	 echo "\n $query WHERE iD=$iD \n";
            return $query . "  WHERE `iD`='$iD'";
        }
    }
    public static function getWhereQuery($iD)
    {
        if (is_numeric($iD)) {
            return "WHERE `iD`='$iD'";
        }
        if (Helpers::isJson($iD)) {
            if (!is_array(jsonDecode($iD))) {
                return "WHERE `iD`='" . jsonDecode($iD)["iD"] . "'";
            }
        }
        if (is_array(jsonDecode($iD))) {
            return    "WHERE `iD` IN ( '" . implode("','", jsonDecode($iD),) . "' )" . "";
        }

        return "WHERE `iD`='$iD'";
    }
    public static function isParent($forgin)
    {
        return $forgin["COLUMN_NAME"] === PARENTID;
    }
    public static function getKeyValue($object, $key)
    {
        return $object[$key["COLUMN_NAME"]];
    }
    public static function getJsonKeyFromForginObject($key)
    {
        return $key["REFERENCED_TABLE_NAME"];
    }
    public static function getQueryFromForginCurrent($object, $key)
    {
        $tableName = self::getJsonKeyFromForginObject($key);
        $iD = self::getKeyValue($object, $key);
        return "SELECT * FROM  " . addslashes($tableName) . "  WHERE iD='$iD'";
    }
    public static function getJsonKeyFromForginArray($key)
    {
        return $key["TABLE_NAME"];
    }
    public static function isCurrentObjectIDEmpty($object, $key)
    {
        $iD = self::getKeyValue($object, $key);
        return is_null($iD);
    }
    public static function isDetailedIDEmpty($object, $key)
    {

        $iD = Helpers::getKeyValueFromObj($object, "iD");
        return is_null($iD);
    }
    public static function getQueryFromFroginArray($object, $key)
    {
        $tableName = self::getJsonKeyFromForginArray($key);
        $primaryKey = $key["COLUMN_NAME"];
        $iD = Helpers::getKeyValueFromObj($object, "iD");
        return "SELECT * FROM  " . addslashes($tableName) . "  WHERE $primaryKey='$iD'";
    }
    public static function getCountQuery($object, $key)
    {
        $tableName = self::getJsonKeyFromForginArray($key);
        $primaryKey = $key["COLUMN_NAME"];
        $iD = Helpers::getKeyValueFromObj($object, "iD");
        return "SELECT Count(iD) AS result FROM  `" . addslashes($tableName) . "`  WHERE `$primaryKey`='$iD'";
    }
    public static function isDetailObjectRequire($ParentKey, $key, $detailObjectTable)
    {
        if ($ParentKey === $key || $ParentKey == $key) {
            return false;
        }
        if (is_bool($detailObjectTable)) {
            return ((bool)$detailObjectTable);
        }
        //print_r($detailObjectTable);
        if (($i = array_search(self::getJsonKeyFromForginObject($key), $detailObjectTable)) !== FALSE) {
            return true;
        }

        return false;
    }
}
