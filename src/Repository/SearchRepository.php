<?php

namespace Etq\Restful\Repository;

use Etq\Restful\Repository\Options;
use Etq\Restful\Helpers;
use Etq\Restful\QueryHelpers;

class SearchRepository extends BaseRepository
{

    public function getSearchQueryMasterStringValue($object, $tableName)
    {
        $res = $this->getSearchObjectStringValue($object, $tableName);
        return ($res);
    }
    public function getSearchByColumnQuery(
        $searchByColumns,
        $tableName,
        ?string $replaceTableNameInWhereClouser = null,
        $getFromObject = null
    ) {
        $tableColumns = $this->getCachedTableColumns($tableName);
        $tableColumns = array_values($tableColumns);
        $whereQuery = array();
        $searchByColumns = $getFromObject ?? $searchByColumns;
        $isNullGetFromObject = $getFromObject ? true : false;
        foreach ($searchByColumns as $key => $value) {
            if (($i = array_search($key, $tableColumns)) !== FALSE) {
                if (Helpers::isArray($value)) {
                    $ids = implode("','", $value);
                    $query = addslashes($replaceTableNameInWhereClouser ?? $tableName) . ".`$key` IN ( '" . $ids . "' )";
                    $whereQuery[] = $query;
                } else {
                    $isNull = is_null($value);
                    //if getFromObject then we want to enable is null to get the exact result from query
                    if (!$isNullGetFromObject && $isNull) {

                        $whereQuery[] =    addslashes($replaceTableNameInWhereClouser ?? $tableName) . ".`$key` IS NULL ";
                    } else {
                        $whereQuery[] =    addslashes($replaceTableNameInWhereClouser ?? $tableName) . ".`$key` LIKE '" . $value . "'";
                    }
                }
            } else {
                throw new \Exception("$key  not Found in column");
            }
        }

        return implode(" AND ", $whereQuery);
    }
    public function getSearchObjectStringValue($object, $tableName)
    {
        $tableColumns = $this->getCachedTableColumns($tableName);

        $forgins = $this->getCachedForginObject($tableName);

        $forginsKey = array_map(function ($tmp) {
            return  $tmp["COLUMN_NAME"];
        }, $forgins);

        $objectToCheck = array();
        foreach ($tableColumns as $table) {
            if ($table === "iD" && !is_numeric($object)) {
                continue;
            }
            //do something with your $key and $value;
            if ((($i = array_search((string)$table, $forginsKey)) === FALSE)) {
                $objectToCheck[$table] = $object;
            } else {
                $forginTableName = $forgins[$i]["REFERENCED_TABLE_NAME"];
                if ($forginTableName === $tableName) {
                    //its parent id skip 
                    continue;
                }
                //TODO
                // if (canSearchInCustomSearchQuery($object, $tableName, $forginTableName)) {
                $res = $this->searchObjectDetailStringValue($object, $forginTableName);
                if (!is_null($res)) {
                    $objectToCheck[$table] = $res;
                }
                // }
            }
        }
        return $this->getSearchQueryAttributesOrDontUnSetID($objectToCheck, $tableName);
    }
    public  function getSearchQueryAttributesOrDontUnSetID($object, $tableName)
    {
        //unSetKeyFromObj($object,'iD');
        $whereQuery = array();
        foreach ($object as $key => $value) {
            //do something with your $key and $value;
            if (is_array($value)) {
                $ids = implode("','", $value);
                $query = addslashes($tableName) . ".`$key` IN ( '" . $ids . "' )";
                $whereQuery[] = $query;
            } else {
                //TODO
                // if (addKeyValueToSearchExtenstion($tableName, $key)) {
                if (is_numeric($value)) {
                    $query = addslashes($tableName) . ".`" . $key . "` LIKE '" . $value . "'";
                } else {
                    $query = addslashes($tableName) . ".`" . $key . "` LIKE '%" . $value . "%'";
                }

                $whereQuery[] = $query;
                // }
            }
        }
        return implode(" OR ", $whereQuery);
    }
    public function searchObjectDetailStringValue($object, $tableName)
    {
        $hasCustomFunctionFounded = false;
        $searchQuery = null;  //TODO hasCustomSearchQueryReturnListOfID($object, $tableName, $hasCustomFunctionFounded);
        if (!is_null($searchQuery)) {
            return $searchQuery;
        }
        if ($hasCustomFunctionFounded) {
            return null;
        }

        $searchQuery = $this->getSearchObjectStringValue($object, $tableName);

        if (Helpers::isEmptyString($searchQuery)) {
            return null;
        }
        $query = "SELECT " . addslashes($tableName) . ".`iD` FROM "
            . addslashes($tableName) . " WHERE " . $searchQuery;

        $result = $this->getFetshTableWithQuery($query);
        return empty($result) ? null : Helpers::getKeyValueFromObj($result, 'iD');
    }
}
