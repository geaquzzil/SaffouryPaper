<?php

namespace Etq\Restful\Repository;

use Etq\Restful\Repository\Options;
use Etq\Restful\Helpers;
use Etq\Restful\QueryHelpers;
use Exception;
use Mpdf\Tag\Option;
use Slim\Container;

abstract class BaseRepository extends BaseDataBaseFunction
{







    


    private function addforginKeys(string $tableName, &$obj, ?Options $option = null, ?string $parentTableName = null)
    {
        $forgins = $this->getCachedForginObject($tableName);
        if (!empty($forgins)) {
            foreach ($forgins as $forgin) {
                $forginTableName = QueryHelpers::getJsonKeyFromForginObject($forgin);
                if ($forginTableName == $parentTableName) {
                    echo "addforginKeys isParent skip";
                    continue;
                }
                if (
                    !QueryHelpers::isCurrentObjectIDEmpty($obj, $forgin) &&
                    QueryHelpers::isDetailObjectRequire($parentTableName, $forgin, $option?->addForginsObject)
                ) {
                    Helpers::setKeyValueFromObj(
                        $obj,
                        $forginTableName,
                        $this->view($forginTableName, QueryHelpers::getKeyValue($obj, $forgin), $tableName, $this->getOptionWithRequired(null, true))
                    );
                } else {
                    Helpers::setKeyValueFromObj($obj, $forginTableName, null);
                }
            }
        }
    }

    private function addforginKeysList(string $tableName, &$obj, ?Options $option = null, ?string $parentTableName = null)
    {
        $forgins = $this->getCachedForginList($tableName);
        if (!empty($forgins)) {
            foreach ($forgins as $forgin) {
                $isParent = QueryHelpers::isParent($forgin);
                $forginTableName = QueryHelpers::getJsonKeyFromForginArray($forgin);
                $valueKey = $isParent ? "childs" : $forginTableName;
                if ($forginTableName == $parentTableName) {
                    echo "addforginKeysList isParent skip";
                    continue;
                }
                $iD = Helpers::getKeyValueFromObj($obj, "iD");
                $where = $forgin["COLUMN_NAME"];
                if (
                    !QueryHelpers::isDetailedIDEmpty($obj, $forgin) &&
                    QueryHelpers::isDetailArrayRequire($parentTableName, $forgin, $option?->addForginsList)
                ) {
                    Helpers::setKeyValueFromObj(
                        $obj,
                        $valueKey,
                        $this->list($forginTableName, $tableName, $this->getOptionWithRequired("`$where` = '$iD'", true))
                    );
                } else {
                    Helpers::setKeyValueFromObj(
                        $obj,
                        $valueKey,
                        null
                    );
                }
                $list = Helpers::getKeyValueFromObj($obj, $valueKey);
                $count =  is_null($list) ? $this->getCount($obj, $forgin) : count($list);
                Helpers::setKeyValueFromObj(
                    $obj,
                    $valueKey . "_count",
                    $count
                );
            }
        }
    }
    private function checkToSetForgins(string $tableName, &$queryResult, ?Options $option = null, ?string $parentTableName = null, bool &$isIn = false)
    {
        if (!$option?->addForginsObject && !$option?->addForginsList) return;
        if (!is_array($queryResult)) {
            $isIn = true;
            if ($option?->isRequestedForginObjects()) {
                $this->addforginKeys($tableName, $queryResult, $option, $parentTableName);
            }
            if ($option?->isRequestedForginList()) {
                $this->addforginKeysList($tableName, $queryResult, $option, $parentTableName);
            }
            $this->after($tableName, $queryResult, ServerAction::VIEW, $option, $this);
        } else {
            foreach ($queryResult as &$res) {
                $isIn = true;
                if ($option?->isRequestedForginObjects()) {
                    $this->addforginKeys($tableName, $res, $option, $parentTableName);
                }
                if ($option?->isRequestedForginList()) {
                    $this->addforginKeysList($tableName, $res, $option, $parentTableName);
                }
                $this->after($tableName, $res, ServerAction::VIEW, $option, $this);
            }
        }
    }
    public function list(string $tableName, ?string $parentTableName = null, ?Options $option = null)
    {
        $this->before($tableName, $empty, ServerAction::LIST, $option, $this);
        $query = $this->getQuery($tableName, ServerAction::LIST,  $option, $parentTableName);
        $result = $this->getFetshALLTableWithQuery($query);
        $isIn = false;
        $this->checkToSetForgins($tableName, $result, $option, $parentTableName, $isIn);
        if (!$isIn) {
            foreach ($result as &$res) {
                $this->after($tableName, $res, ServerAction::VIEW, $option, $this);
            }
        }
        return $result;
    }

    public function listByDetailListColumn(string $masterTableName, string $detailTableName, Options $option)
    {
        $detailsResult = $this->list(
            $detailTableName,
            $masterTableName,
            $option->getClone()
                ->removeDate()->requireObjects()
        );
        if (empty($detailsResult)) return (array());
        $forginsDetails = $this->getCachedForginList($masterTableName);

        $columnNameInMaster = null;
        foreach ($forginsDetails as $f) {
            if ($f["TABLE_NAME"] === $detailTableName) {
                $columnNameInMaster = $f["COLUMN_NAME"];
            }
        }
        if (is_null($columnNameInMaster)) {
            throw new Exception("$detailTableName not found in $masterTableName");
        }

        $results = array_unique(array_map(function ($tmp) use ($columnNameInMaster) {
            return $tmp[$columnNameInMaster];
        }, $detailsResult));
        $response = array();
        foreach ($results as $iD) {
            $res = $this->view(
                $masterTableName,
                $iD,
                null,
                Options::getInstance()->withDate($option?->date)->requireObjects()
            );
            if (!$res) {
                continue;
            }
            $keys = array_keys(array_column($detailsResult, $columnNameInMaster), $iD);

            $res->$detailTableName = (array_intersect_key(
                $detailsResult,
                array_flip($keys)
            ));
            array_push($response, $res);
        }
        return $response;
    }

    public function view(string $tableName, int $iD, ?string $parentTableName = null, ?Options $option = null)
    {
        if (!$option) {
            $option = Options::withStaticWhereQuery("iD = '$iD'");
        } else {
            $option = $option->getClone()->addStaticQuery("iD = '$iD'");
        }

        $query = $this->getQuery($tableName, ServerAction::VIEW,  $option, $parentTableName);
        $result = $this->getFetshTableWithQuery($query);
        $this->validatePhoneNumber($result);


        // print_r($result);
        $isIn = false;
        if ($result) {
            $this->checkToSetForgins($tableName, $result, $option, null, $isIn);
        }
        if (!$isIn) {
            $this->after($tableName, $result, ServerAction::VIEW, $option, $this);
        }

        return $result;
    }
    public function edit(string $tableName, int $iD, $object, ?Options $option = null)
    {
        $origianlObject = Helpers::cloneByJson($object);
        $this->before($tableName, $object, ServerAction::EDIT, $option, $this);
        Helpers::convertToObject($object);
        $this->validateObjectAndAdd($tableName, $object, $this);
        echo $this->getInsertQuery($tableName, (array)$object, $iD);

        return $this->view($tableName, $iD, null, $option);
    }
    public function add(string $tableName, $object, ?Options $option = null, bool $isAlreadyValidated = false, bool $isSearchedBefore = false, $type = ForginCheckType::NONE)
    {
        $origianlObject = Helpers::cloneByJson($object);
        Helpers::convertToObject($origianlObject);


        $this->before($tableName, $object, ServerAction::ADD, $option, $this);
        // todo unset foringlists to another array 
        Helpers::convertToObject($object);
        if (!$isAlreadyValidated) {
            $this->validateObjectAndAdd($tableName, $object, $this, $type);
        }
        $resultsForingList = [];

        if (!$isSearchedBefore) {
            echo " not searching before $tableName ";
            $searchedArray = $this->search($tableName, $object, true, false, true, $resultsForingList);
            if ($searchedArray) {
                $iD = $searchedArray['iD'];
                Helpers::setKeyValueFromObj($object, "iD", $iD);
                echo "founded  $tableName--->->-> $iD\n";
                if (!empty($resultsForingList)) {
                    $this->addForginListFromObject($tableName, $object, $this, $resultsForingList);
                }

                return $searchedArray;
            }
        }
        // $forginsDetails= $this->getCachedForginList($tableName);

        // $hasForginList=
        echo "----->->->->->to be added to $tableName \n\n";

        Helpers::setKeyValueFromObj($object, "iD", null);

        $insertID = $this->getLastIncrementID($tableName);
        echo $this->getInsertQuery($tableName, (array) $this->unsetAllForginListWithOutRefrence($tableName, $object, $iDontWantToUse, true)) . " withID: $insertID";

        Helpers::setKeyValueFromObj($object, "iD", $insertID);
        // print_r($object);
        if (!empty($resultsForingList)) {
            $this->addForginListFromObject($tableName, $object, $this, $resultsForingList);
            echo " \n im in add for $tableName\n";
            die;
        }
        //




        // $this->addForginListFromObject($tableName, $object, $this);

        $this->after($tableName, $object, ServerAction::ADD, $option, $this);


        return $object;
    }
    public function search(
        string $tableName,
        $object,
        ?bool $getFirstResult = true,
        ?bool $addWhenNotFounded = false,
        ?bool $isAlreadyValidated = true,
        &$resultsForingList = [],

    ) {
        $cloned =   Helpers::cloneByJson($object);
        echo "search for $tableName\n";
        if ($this->isSearchDisbled($tableName)) {
            echo "search is disabled for $tableName\n";
            return array();
        }
        Helpers::unSetKeyFromObj($cloned, "iD");
        if ($this->unsetDateWhenSearch($tableName)) {
            Helpers::unSetKeyFromObj($cloned, "date");
        }

        Helpers::unSetKeyFromObj($cloned, "token");
        Helpers::unSetKeyFromObj($cloned, "password");
        Helpers::unSetKeyFromObj($cloned, "profile");
        $cloned = $this->unsetAllForginList($tableName, $cloned, $resultsForingList, true);
        // print_r($cloned);

        $option = Options::getInstance();
        $option->searchRepository = $this->getSearchRepository();
        $option->searchOption = new SearchOption(null, null, (array)$cloned);

        $res = $this->list($tableName, null, $option);
        if (empty($res)) {
            if ($addWhenNotFounded) {
                return $this->add($tableName, $cloned, null, $isAlreadyValidated, true);
            } else {
                return array();
            }
        }
        if (!empty($res) && $getFirstResult) {
            return $res[0];
        }
        return $res;
    }
    public function delete(string $tableName, ?int $iD, ?Options $option = null)
    {
        if ((!$iD && !$option?->isSetRequestColumnsKey("iD")) || ($iD && $option?->isSetRequestColumnsKey("iD"))) {
            throw new Exception("cant determine id");
        }
        if ($iD) {
            if (!$option) {
                $option = Options::withStaticWhereQuery("iD = '$iD'");
            } else {
                $option = $option->getClone()->addStaticQuery("iD = '$iD'");
            }
        }
        $this->before($tableName, $iD, ServerAction::DELETE, $option, $this);
        $query = $this->getQuery($tableName, ServerAction::DELETE,  $option);
        $rowCount = $this->getDeleteTableWithQuery($query);
        $requestArrayCount = count($option?->getRequestColumnValue("iD"));
        $requestCount = $iD ? 1 : $requestArrayCount;
        $requestIDS = $iD ? [$iD] : ($option?->getRequestColumnValue("iD") ?? []);
        $response = array();
        $response["requestCount"] = $requestCount;
        $response["requestIDS"] = $requestIDS;
        $response["serverCount"] = $rowCount;
        $response["serverStatus"] = $rowCount == $requestCount;
        $this->after($tableName, $response, ServerAction::DELETE, $option, $this);
        return $response;
        // return $this->getDeleteTableWithQuery($query);
    }




    protected function changeTableNameToExtended(string $tableName)
    {
        switch ($tableName) {
            default:
                return $tableName;
            case RI:
            case PR_INPUT:
            case PR_OUTPUT:
            case TR:
                return "extended_" . $tableName;
            case PURCH:
                return "extended_purchases_refund";
            case ORDR:
                return "extended_order_refund";
        }
    }

    private function canChangeToExtended(ServerAction $action)
    {
        return $action == ServerAction::LIST || $action == ServerAction::VIEW;
    }
    private function getQuery(string $tableName, ServerAction $action, ?Options $option = null, ?string $parentTableName = null): string
    {
        $query = "";
        $tableName = $this->canChangeToExtended($action) ? $this->changeTableNameToExtended($tableName) : $tableName;
        $optionQuery = $this->getOption($tableName, $option);
        $selectColumn = $this->getSelectColumn($tableName, $parentTableName);

        echo "\n $tableName from-->->-getQuery-->->OPTION QUERY ==> " . $optionQuery . " \n";

        switch ($action) {
            case ServerAction::ADD:
                $query = "";
                break;
            case ServerAction::EDIT:
                $query = "";
                break;

            case ServerAction::LIST:
                if ($option?->searchOption?->searchByField != null) {
                    $fieldName = $option->searchOption->searchByField;
                    $query = "SELECT DISTINCT(`$tableName`.`$fieldName`) FROM `" . $tableName . "` $optionQuery";
                } else {
                    $query = "SELECT $selectColumn FROM $tableName $optionQuery";
                }
                break;

            case ServerAction::DELETE:
                $query = "DELETE FROM $tableName $optionQuery";
                break;

            case ServerAction::VIEW:
                $query = "SELECT $selectColumn FROM $tableName $optionQuery";
                break;

            default:
                $query = "NON";
        }
        return $query;
    }
    private function getSelectColumn(string $tableName, ?string $parentTableName)
    {
        if ($parentTableName) {
            // if (isset($this->container[SENS][$tableName])) {
            //     $func = $this->container[SENS][$tableName];

            //     return implode(",", $func($tableName));
            // }
            return "*";
            // if (in_array($objectName, array_keys($GLOBALS["OBJECT_ACTIONS"]))) {
            //     $func = $GLOBALS["OBJECT_ACTIONS"][$objectName];
            //     if (is_callable($func))
            //         $func($object);
            // }
        }
        return "*";
    }
    private function getOption(string $tableName, ?Options $option): string
    {
        if (!$option) return "";

        return $option->getQuery($tableName);
    }




    /**
     * @param array<string, int|string> $params
     */
    protected function getResultsWithPagination(
        string $query,
        int $page,
        int $perPage,
        array $params,
        int $total
    ): array {
        return [
            'pagination' => [
                'totalRows' => $total,
                'totalPages' => ceil($total / $perPage),
                'currentPage' => $page,
                'perPage' => $perPage,
            ],
            'data' => $this->getResultByPage($query, $page, $perPage, $params),
        ];
    }

    /**
     * @param array<string, int|string> $params
     *
     * @return array<float|int|string>
     */
    protected function getResultByPage(
        string $query,
        int $page,
        int $perPage,
        array $params
    ): array {
        $offset = ($page - 1) * $perPage;
        $query .= " LIMIT $perPage OFFSET $offset";
        $statement = $this->database->prepare($query);
        $statement->execute($params);

        return (array) $statement->fetchAll();
    }


    private function getOptionWithRequired(?string $staticWhere = null, ?bool $requireObjects = null, ?bool $requireLists = null)
    {
        $obj = new Options();
        if (!is_null($staticWhere)) {
            $obj = $obj->addStaticQuery($staticWhere);
        }
        if (!is_null($requireObjects)) {
            $obj = $obj->requireObjects();
        }
        if (!is_null($requireLists)) {
            $obj = $obj->requireDetails();
        }
        return $obj;
    }



    function getSearchObjectStringValue($object, $tableName)
    {
        $whereQuery = array();
        $tableColumns = $this->getTableColumns($tableName);
        $forgins = $this->getObjectForginKeys($tableName);

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
                if ($this->canSearchInCustomSearchQuery($object, $tableName, $forginTableName)) {
                    $res = $this->searchObjectDetailStringValue($object, $forginTableName);
                    if (!is_null($res)) {
                        $objectToCheck[$table] = $res;
                    }
                }
            }
        }
        return $this->getSearchQueryAttributesOrDontUnSetID($objectToCheck, $tableName);
    }
    function getSearchQueryAttributesOrDontUnSetID($object, $tableName)
    {
        //unSetKeyFromObj($object,'iD');
        $whereQuery = array();
        foreach ($object as $key => $value) {
            //do something with your $key and $value;
            if (is_array($value)) {
                $ids = implode("','", $value);
                $query = addslashes($tableName) . ".`$key` IN ( '" . $ids . "' )";
                $whereQuery[] = $query;
            }
        }
        return implode(" OR ", $whereQuery);
    }











    function getGrowthRateBeforeDate($tableName, $toFind, $before)
    {
        $iD = getUserID();
        return getFetshAllTableWithQuery(
            "SELECT Year($tableName.`date`) AS `year`,
            Month(`$tableName`.date) AS month,
            COALESCE( round(Sum($tableName.`$toFind`),3) ,0) AS `total`
            FROM $tableName   " . (isCustomer() ? " WHERE CustomerID= '$iD' && Date(`date`) <= '$before' " : " Date(`date`) <= '$before'  ") . "
            GROUP BY Year($tableName.`date`),Month($tableName.`date`)
            ORDER BY `year`,
            month"
        );
    }
    function getGrowthRateByQuery($tableName, $toFind, $whereQuery)
    {
        $iD = getUserID();
        return getFetshAllTableWithQuery(
            "SELECT Year($tableName.`date`) AS `year`,
            Month(`$tableName`.date) AS month,
            COALESCE( round(Sum($tableName.`$toFind`),3) ,0) AS `total`
            FROM $tableName   " . (isCustomer() ? " WHERE CustomerID= '$iD' AND $whereQuery" : "WHERE  $whereQuery") . "
            GROUP BY Year($tableName.`date`),Month($tableName.`date`)
            ORDER BY `year`,
            month "
        );
    }
    function getGrowthRateByInvoiceDetailsQuery($tableName, $detailsTable, $joinID, $toFind, $whereQuery)
    {
        $iD = getUserID();
        $qurey = "SELECT Year($tableName.`date`) AS `year`,
            Month(`$tableName`.date) AS month,
            COALESCE( round(Sum(`$detailsTable`.`$toFind`),3) ,0) AS `total`
            FROM `$detailsTable`   
            INNER JOIN `$tableName` ON `$tableName`.`iD` = `$detailsTable`.`$joinID`
            " . (isCustomer() ? " WHERE CustomerID= '$iD' AND $whereQuery" : "WHERE  $whereQuery") . "
            GROUP BY Year(`$tableName`.`date`),Month(`$tableName`.`date`)
            ORDER BY `year`,
            month ";
        //	echo $qurey;
        return getFetshAllTableWithQuery($qurey);
    }
    function getGrowthRateAfterAndBeforeCount($tableName, $after, $before)
    {
        $iD = getUserID();
        return getFetshAllTableWithQuery(
            "SELECT Year($tableName.`date`) AS `year`,
            Month(`$tableName`.date) AS month,
            Count(*) AS `total`
            FROM $tableName   " . (isCustomer() ? " WHERE CustomerID= '$iD' &&  ( (Date(`date`) >= '$after') AND (Date(`date`) <= '$before') )" : "WHERE   (  (Date(`date`) >= '$after') AND (Date(`date`) <= '$before') ) ") . "
            GROUP BY Year(`$tableName`.`date`),Month(`$tableName`.`date`)
            ORDER BY `year`,
            month
            "
        );
    }
    function getGrowthRateAfterAndBefore($tableName, $toFind, $after, $before)
    {
        $iD = getUserID();
        return getFetshAllTableWithQuery(
            "SELECT Year($tableName.`date`) AS `year`,
            Month(`$tableName`.date) AS month,
            COALESCE( round(Sum($tableName.`$toFind`),3) ,0) AS `total`
            FROM $tableName   " . (isCustomer() ? " WHERE CustomerID= '$iD' &&  ( (Date(`date`) >= '$after') AND (Date(`date`) <= '$before') )" : "WHERE   (  (Date(`date`) >= '$after') AND (Date(`date`) <= '$before') ) ") . "
            GROUP BY Year(`$tableName`.`date`),Month(`$tableName`.`date`)
            ORDER BY `year`,
            month"
        );
    }
    function getGrowthRateAfterAndBeforeWithWhereQuery($tableName, $toFind, $after, $before, $query)
    {
        $iD = getUserID();
        return getFetshAllTableWithQuery(
            "SELECT Year($tableName.`date`) AS `year`,
            Month(`$tableName`.date) AS month,
            COALESCE( round(Sum($tableName.`$toFind`),3) ,0) AS `total`
            FROM $tableName   " . (isCustomer() ? " WHERE CustomerID= '$iD' &&  ( (Date(`date`) >= '$after') AND (Date(`date`) <= '$before') )" : "WHERE   (  (Date(`date`) >= '$after') AND (Date(`date`) <= '$before') ) ") . "
            AND $query GROUP BY Year(`$tableName`.`date`),Month(`$tableName`.`date`)
            ORDER BY `year`,
            month"
        );
    }
    function getGrowthRateAfterAndBeforeWithWhereQueryCount($tableName, $after, $before, $query)
    {
        $iD = getUserID();
        return getFetshAllTableWithQuery(
            "SELECT Year($tableName.`date`) AS `year`,
            Month(`$tableName`.date) AS month,
            Count(*) AS `total`
            FROM $tableName   " . (isCustomer() ? " WHERE CustomerID= '$iD' &&  ( (Date(`date`) >= '$after') AND (Date(`date`) <= '$before') )" : "WHERE   (  (Date(`date`) >= '$after') AND (Date(`date`) <= '$before') ) ") . "
            AND $query GROUP BY Year(`$tableName`.`date`),Month(`$tableName`.`date`)
            ORDER BY `year`,
            month"
        );
    }
    function getGrowthRateAfterAndBeforeDaysInterval($tableName, $toFind, $after, $before)
    {
        $iD = getUserID();
        return getFetshAllTableWithQuery(
            "SELECT Year($tableName.`date`) AS `year`,
            Month(`$tableName`.date) AS month,
            Day(`$tableName`.date) As day,
            COALESCE( round(Sum($tableName.`$toFind`),3) ,0) AS `total`
            FROM $tableName   " . (isCustomer() ? " WHERE CustomerID= '$iD' &&  ( (Date(`date`) >= '$after') AND (Date(`date`) <= '$before') )" : "WHERE   (  (Date(`date`) >= '$after') AND (Date(`date`) <= '$before') ) ") . "
            GROUP BY Year(`$tableName`.`date`),Month(`$tableName`.`date`),Day(`$tableName`.date)
            ORDER BY `year`,month,day"
        );
    }
    function getGrowthRateAfterAndBeforeDaysIntervalWithWhereQuery($tableName, $toFind, $after, $before, $whereQuery)
    {
        $iD = getUserID();
        return getFetshAllTableWithQuery(
            "SELECT Year($tableName.`date`) AS `year`,
            Month(`$tableName`.date) AS month,
            Day(`$tableName`.date) As day,
            COALESCE( round(Sum($tableName.`$toFind`),3) ,0) AS `total`
            FROM $tableName   " . (isCustomer() ? " WHERE CustomerID= '$iD' &&  ( (Date(`date`) >= '$after') AND (Date(`date`) <= '$before') )" : "WHERE   (  (Date(`date`) >= '$after') AND (Date(`date`) <= '$before') ) ") . "
            AND $whereQuery GROUP BY Year(`$tableName`.`date`),Month(`$tableName`.`date`),Day(`$tableName`.date)
            ORDER BY `year`,month,day"
        );
    }
    function getGrowthRateAfterAndBeforeWithGroup($tableName, $toFind, $groupText, $after, $before)
    {
        $iD = getUserID();
        return getFetshAllTableWithQuery(
            "SELECT Year($tableName.`date`) AS `year`,
            Month(`$tableName`.date) AS month,
            COALESCE( round(Sum($tableName.`$toFind`),3) ,0) AS `total`
            FROM $tableName   " . (isCustomer() ? " WHERE CustomerID= '$iD' &&  ( (Date(`date`) >= '$after') AND (Date(`date`) <= '$before') )" : "WHERE   (  (Date(`date`) >= '$after') AND (Date(`date`) <= '$before') ) ") . "
            GROUP BY Year($tableName.`date`),Month($tableName.`date`),$groupText 
            ORDER BY `year`,
            month"
        );
    }

    public function getGrowthRate(
        $tableName,
        $toFind = null,
        Options $option,
        bool $requireTotalAsCount = false,
        bool $requireDayInterval = false
    ) {
        echo "\n";
        $toFind = $toFind ?? $this->getGrowthRateFindKey($tableName);
        $tableName = $this->changeTableNameToExtendedForGrowthRate($tableName);
        $selectColumn = $requireTotalAsCount ? "Count(*)" : "COALESCE( round(Sum($tableName.`$toFind`),3) ,0)";
        $selectColumnDay = $requireDayInterval ? "Day(`$tableName`.date) As day," : "";


        $option = $option->getClone()->withGroupByArray(
            [
                "Year($tableName.`date`)",
                "Month($tableName.`date`)",

            ]
        )->withASCArray([
            "`year`",
            "`month`"

        ]);
        if ($requireDayInterval) {
            $option = $option
                ->addGroupBy("Day(`$tableName`.date)")
                ->addOrderBy("`day`");
        }
        $query = $option->getQuery($tableName);
        $query = "
            SELECT
                Year(`$tableName`.`date`) AS `year`,
                Month(`$tableName`.`date`) AS month,
                $selectColumnDay
                $selectColumn AS `total`
            FROM
                $tableName   
            $query";
        echo "\nsoso :" . $query . "\n";
        // die;
        // die;
        return $this->getFetshAllTableWithQuery(
            $query

        );
    }
    public function getGrowthRateByListByDetail(
        $tableName,
        $detailsTable,
        $toFind = null,
        Options $option,
        bool $requireTotalAsCount = false,
        bool $requireDayInterval = false
    ) {

        $iD = getUserID();
        $qurey = "SELECT Year($tableName.`date`) AS `year`,
            Month(`$tableName`.date) AS month,
            COALESCE( round(Sum(`$detailsTable`.`$toFind`),3) ,0) AS `total`
            FROM `$detailsTable`   
            INNER JOIN `$tableName` ON `$tableName`.`iD` = `$detailsTable`.`$joinID`
            " . (isCustomer() ? " WHERE CustomerID= '$iD' AND $whereQuery" : "WHERE  $whereQuery") . "
            GROUP BY Year(`$tableName`.`date`),Month(`$tableName`.`date`)
            ORDER BY `year`,
            month ";

        echo "\n";
        $toFind = $toFind ?? $this->getGrowthRateFindKey($tableName);
        $tableName = $this->changeTableNameToExtendedForGrowthRate($tableName);
        $selectColumn = $requireTotalAsCount ? "Count(*)" : "COALESCE( round(Sum($tableName.`$toFind`),3) ,0)";
        $selectColumnDay = $requireDayInterval ? "Day(`$tableName`.date) As day," : "";


        $option = $option->getClone()->withASCArray(
            [
                "Year($tableName.`date`)",
                "Month($tableName.`date`)",

            ]
        )->withASCArray([
            "`year`",
            "`month`"

        ]);
        if ($requireDayInterval) {
            $option = $option
                ->addGroupBy("Day(`$tableName`.date)")
                ->addOrderBy("`day`");
        }
        $query = $option->getQuery($tableName);
        $query = "
            SELECT
                Year(`$tableName`.`date`) AS `year`,
                Month(`$tableName`.`date`) AS month,
                $selectColumnDay
                $selectColumn AS `total`
            FROM
                $tableName   
            $query";
        echo "\nsoso :" . $query . "\n";
        // die;
        // die;
        return $this->getFetshAllTableWithQuery(
            $query

        );
    }
    //view should be 
    //SELECT Year(equality_credits.`date`) AS `year`,
    //		Month(`equality_credits`.date) AS month,
    //		COALESCE( round(Sum(equality_credits.`value`),3) ,0) AS `total`
    //		FROM equality_credits WHERE CustomerID= '25' 
    //		GROUP BY Year(equality_credits.`date`),Month(equality_credits.`date`)
    //		ORDER BY `year`,
    //		month 
    function getGrowthRateWithCustomerID($tableName, $toFind, $iD)
    {
        return getFetshAllTableWithQuery(
            "SELECT Year($tableName.`date`) AS `year`,
            Month(`$tableName`.date) AS month,
            COALESCE( round(Sum($tableName.`$toFind`),3) ,0) AS `total`
            FROM $tableName  WHERE CustomerID= '$iD'  
            GROUP BY Year($tableName.`date`),Month($tableName.`date`)
            ORDER BY `year`,
            month"
        );
    }
    function getGrowthRateWithCustomerIDAfterAndBefore($tableName, $toFind, $iD, $after, $before)
    {
        return getFetshAllTableWithQuery(
            "SELECT Year($tableName.`date`) AS `year`,
            Month(`$tableName`.date) AS month,
            COALESCE( round(Sum($tableName.`$toFind`),3) ,0) AS `total`
            FROM $tableName  WHERE CustomerID= '$iD'  &&  ( (Date(`date`) >= '$after') AND (Date(`date`) <= '$before') )
            GROUP BY Year($tableName.`date`),Month($tableName.`date`)
            ORDER BY `year`,
            month"
        );
    }

    private function changeTableNameToExtendedForGrowthRate($tableName)
    {
        switch ($tableName) {
            default:
                return $tableName;
            case CRED:
            case DEBT:
            case SP:
            case INC:
                return "equality_" . $tableName;
            case RI:
            case PR_INPUT:
            case PR_OUTPUT:
            case TR:
                return "extended_" . $tableName;
            case PURCH:
                return "extended_purchases_refund";
            case ORDR:
                return "extended_order_refund";
        }
    }
    private function getGrowthRateFindKey($tableName)
    {

        switch ($tableName) {
            default:
                return $tableName;
            case CRED:
            case INC:
            case SP:
            case DEBT:
                return "value";
            case CUT:
            case RI:
            case PR_INPUT:
            case PR_OUTPUT:
            case TR:
            case PURCH:
            case ORDR:
                return "quantity";
        }
    }
}
enum ServerAction: string
{
    case VIEW = "view";
    case LIST = "list";
    case EDIT = "edit";
    case DELETE = "delete";
    case ADD = "add";
}
