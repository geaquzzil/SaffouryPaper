<?php

namespace Etq\Restful\Repository;

use Etq\Restful\Repository\Options;
use Etq\Restful\Helpers;
use Etq\Restful\QueryHelpers;
use Exception;
use Illuminate\Support\Arr;

class SearchRepository extends BaseRepository
{

    private function changeImagePathFromJson($object) {}
    private function getSearchKeyValueWhereClouser($tableName, $key, $value, bool $addPercent = false,  ?string $replaceTableNameInWhereClouser = null)
    {

        $keyToFind = addslashes($replaceTableNameInWhereClouser ?? $tableName) . ".`$key`";
        if (is_null($value)) {
            return "($keyToFind IS NULL OR $keyToFind = '0' OR $keyToFind ='')   ";
        }
        if (Helpers::isArray($value)) {
            $ids = implode("','", $value);
            return "$keyToFind IN ( '" . $ids . "' )";
        }
        if (is_double($value) || is_float($value) || is_int($value) || is_numeric($value)) {
            return "($keyToFind = '$value')   ";
        }
        $addPercentQuery = $addPercent ? "%" : "";
        return "$keyToFind LIKE '$addPercentQuery" . $value . "$addPercentQuery'";
    }
    private function getSearchBetweenValue($key, $value)
    {
        if (Helpers::isArrayByJson($value)) {
            $between = array();
            foreach ($value as $v) {
                $between[] = $this->getSearchBetweenValue($key, $v);
            }
            return implode(" AND ", $between);
        }
        $from = Helpers::isSetKeyFromObjReturnValue($value, "from");
        $to = Helpers::isSetKeyFromObjReturnValue($value, "to");
        if (!$from  || !$to) {
            throw new Exception("u have to set from and to");
        }
        return  "$key BETWEEN  $from AND $to";
    }

    ///
    ///@param $betweenArray is has key : SizeID value of width:{from: , to:} or list
    public function getSearchQueryBetween($betweenArray, $tableName)
    {
        // $this->getFor
        /// @param key is ColumnName in TableName
        // @param b is the value 

        $queryR = array();
        foreach ($betweenArray as $columnInParent => $b) {
            $between = array();
            $childTableName = $this->getCachedForginObjectTableName($tableName, $columnInParent);
            foreach ($b as $columnInChild => $value) {
                if ($this->validate($childTableName, $columnInChild)) {
                    $between[] = "(" . $this->getSearchBetweenValue($columnInChild, $value) . ")";
                }
            }
            $impolded = implode(" AND ", $between);
            $query = "SELECT " . addslashes($childTableName) . ".`iD` FROM "
                . addslashes($childTableName) . " WHERE " . $impolded;
            echo "\n" . $query . "\n";
            $result = $this->getFetshALLTableWithQuery($query);
            $ids = ((array_column($result, ID)));
            // $queryR[$columnInParent] = $ids;
            $queryR[] = $this->getSearchKeyValueWhereClouser($tableName, $columnInParent, $ids);
        }
        return implode(" AND ", $queryR);
    }

    public function getSearchByColumnQuery(
        $searchByColumns,
        $tableName,
        ?string $replaceTableNameInWhereClouser = null,
        $getFromObject = null,
        ?Options $options = null
    ) {
        $tableColumns = $this->getCachedTableColumns($tableName);
        $tableColumns = array_values($tableColumns);
        $whereQuery = array();
        $searchByColumns = $getFromObject ?? $searchByColumns;
        foreach ($searchByColumns as $key => $value) {

            if (Helpers::searchInArray($key, $tableColumns)) {
                $whereQuery[] = $this->getSearchKeyValueWhereClouser($tableName, $key, $value, false, $replaceTableNameInWhereClouser);
            } else {
                if (!is_null($options)) {
                    $options->notFoundedColumns->set($key, $value);
                }
                if ($options?->throwExceptionOnColumnNotFound ?? true) {
                    throw new \Exception("$key  not Found in column");
                }
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
            $whereQuery[] = $this->getSearchKeyValueWhereClouser($tableName, $key, $value, true, null);
            //do something with your $key and $value;

        }
        return implode(" OR ", $whereQuery);
    }
    public function searchObjectDetailStringValue($object, $tableName)
    {
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
