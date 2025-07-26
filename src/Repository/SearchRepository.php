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
        // print_r($value);
        $from = Helpers::getKeyValueFromObj($value, "from");
        $to = Helpers::getKeyValueFromObj($value, "to");
        // echo " from: $from to :$to\n";
        if (is_null($from)  || is_null($to)) {
            throw new Exception("u have to set from and to");
        }
        return  "$key BETWEEN  '$from' AND '$to'";
    }

    ///
    ///@param $betweenArray is has key : SizeID value of width:{from: , to:} or list
    public function getSearchQueryBetween($betweenArray, $tableName)
    {

        $queryR = array();
        foreach ($betweenArray as $forginID => $item) {
            $between = array();
            foreach (array_values($item) as  $value) {
                $be = array();
                foreach ($value as $oneItem) {

                    $field = Helpers::getKeyValueFromObj($oneItem, "field");
                    $value = Helpers::getKeyValueFromObj($oneItem, "fromTo");
                    $childTableName = $this->getCachedForginObjectTableName($tableName, $forginID);

                    // echo " validate parent tableName $tableName tableName $childTableName Column:$field\n";
                    // print_r($value);
                    // die;
                    if ($this->validate($childTableName, $field)) {
                        $be[] = "(" . $this->getSearchBetweenValue($field, array_values($value)) . ")";
                    }
                }
                $between["and"][] =  "( " . implode(" AND ", $be) . " )";
            }

            $impolded = " ( " . implode(" OR ", $between["and"]) . " )";
            $query = "SELECT " . addslashes($childTableName) . ".`iD` FROM "
                . addslashes($childTableName) . " WHERE " . $impolded;
            // echo "\n" . $query . "\n";
            $result = $this->getFetshALLTableWithQuery($query);
            $ids = array_column($result, ID);
            // $queryR[$columnInParent] = $ids;
            $queryR[] = $this->getSearchKeyValueWhereClouser($tableName, $forginID, $ids);
        }
        $query = implode(" AND ", $queryR);
        return $query;
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
    public function getSearchObjectStringValue($searchQuery, $tableName)
    {
        // echo "\n getSearchObjectStringValue for $object tablename $tableName\n";
        $tableColumns = $this->getCachedTableColumns($tableName);

        $forgins = $this->getCachedForginObject($tableName);

        $forginsKey = array_map(function ($tmp) {
            return  $tmp["COLUMN_NAME"];
        }, $forgins);

        $objectToCheck = array();
        foreach ($tableColumns as $table) {
            if ($table === "iD" && !is_numeric($searchQuery)) {
                // echo " cop\n";
                continue;
            }
            //do something with your $key and $value;
            if ((($i = array_search((string)$table, $forginsKey)) === FALSE)) {
                $objectToCheck[$table] = [false => $searchQuery];
            } else {
                $forginTableName = $forgins[$i]["REFERENCED_TABLE_NAME"];
                if ($forginTableName === $tableName) {
                    //its parent id skip 
                    // echo " its parent id skip  cop\n";
                    continue;
                }
                //TODO
                // if (canSearchInCustomSearchQuery($object, $tableName, $forginTableName)) {
                $res = $this->searchObjectDetailStringValue($searchQuery, $forginTableName);
                // echo "dsadsa $res\n";
                if (!is_null($res)) {
                    $objectToCheck[$table] = [true => $res];
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
            // echo "\n getSearchQueryAttributesOrDontUnSetID key :$key";
            $is = key($value);
            $query = '';
            if ($is) {
                $query = $this->getSearchKeyValueWhereClouser($tableName, $key, $value[$is], false, null);
            } else {
                $query = $this->getSearchKeyValueWhereClouser($tableName, $key, $value[$is], true, null);
            }
            // echo "  \nkey $key $query \n";
            $whereQuery[] = $query;
            //do something with your $key and $value;

        }
        $query = implode(" OR ", $whereQuery);
        // echo "\ngetSearchQueryAttributesOrDontUnSetID $query\n";
        return $query;
    }
    public function searchObjectDetailStringValue($object, $tableName)
    {
        $searchQuery = $this->getSearchObjectStringValue($object, $tableName);
        // echo "\nsearchObjectDetailStringValue $searchQuery";
        if (Helpers::isEmptyString($searchQuery)) {
            return null;
        }
        $query = "SELECT " . addslashes($tableName) . ".`iD` FROM "
            . addslashes($tableName) . " WHERE " . $searchQuery;

        $result = $this->getFetshALLTableWithQuery($query);
        // print_r($result);

        return empty($result) ? null : Helpers::getIDFromArray($result,);
    }
}
