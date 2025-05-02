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
        // echo $tableName . "\n";
        // print_r($forgins);
        if (!empty($forgins)) {
            foreach ($forgins as $forgin) {
                $forginTableName = QueryHelpers::getJsonKeyFromForginObject($forgin);
                if ($forginTableName == $parentTableName) {
                    continue;
                }
                //TODO skip parent tableName on child
                if (QueryHelpers::isParent($forgin)) {
                    $iD = Helpers::getKeyValueFromObj($obj, "ParentID");
                    if ($option?->isRequireParent() && $iD) {
                        Helpers::setKeyValueFromObj(
                            $obj,
                            "parents",
                            $this->list(
                                QueryHelpers::getJsonKeyFromForginArray($forgin),
                                $parentTableName,
                                $this->getOptionWithRequired($tableName . ".`iD` = '$iD'", true)
                            )
                        );
                    }
                } else if (
                    !QueryHelpers::isCurrentObjectIDEmpty($obj, $forgin) &&
                    QueryHelpers::isDetailObjectRequire($parentTableName, $forgin, $option?->addForginsObject)
                    // && !Helpers::isActionTableIs($pp)TODO
                ) {
                    // echo "SDASDCAS\n";
                    // $theResult =    $this->getFetshTableWithQuery(
                    //     QueryHelpers::getQueryFromForginCurrent($obj, $forgin)
                    // );

                    //TODO $theResult = addObjectExtenstion($theResult, $pp);

                    Helpers::setKeyValueFromObj(
                        $obj,
                        $forginTableName,
                        $this->view($forginTableName, QueryHelpers::getKeyValue($obj, $forgin), $tableName, $this->getOptionWithRequired(null, true))
                    );

                    // $theResult;
                    // $result[$forginTableName] =$this->view($)


                } else {
                    Helpers::setKeyValueFromObj($obj, $forginTableName, null);
                }
            }
        }
    }

    private function addforginKeysList(string $tableName, &$obj, ?Options $option = null, ?string $parentTableName = null)
    {

        $forgins = $this->getCachedForginList($tableName);
        // echo $tableName . "\n";
        // print_r($forgins);
        if (!empty($forgins)) {
            foreach ($forgins as $forgin) {
                $isParent = QueryHelpers::isParent($forgin);
                $forginTableName = QueryHelpers::getJsonKeyFromForginArray($forgin);
                if ($forginTableName == $parentTableName) {

                    continue;
                }
                $iD = Helpers::getKeyValueFromObj($obj, "iD");
                $where = $forgin["COLUMN_NAME"];
                if ($isParent && $iD) {
                    if ($option?->isRequireParent()) {
                        Helpers::setKeyValueFromObj(
                            $obj,
                            "childs",
                            $this->list(
                                QueryHelpers::getJsonKeyFromForginArray($forgin),
                                $parentTableName,
                                $this->getOptionWithRequired("`$where` = '$iD'", true)
                            )
                        );
                    }
                } else if (
                    !QueryHelpers::isDetailedIDEmpty($obj, $forgin) &&
                    QueryHelpers::isDetailArrayRequire($parentTableName, $forgin, $option?->addForginsList)
                ) {

                    Helpers::setKeyValueFromObj(
                        $obj,
                        $forginTableName,
                        $this->list($forginTableName, $tableName, $this->getOptionWithRequired("`$where` = '$iD'", true))
                    );

                    // $result[$t] = depthSearch(null, $t, $recursiveLevel, true, true, $options);
                } else {
                    Helpers::setKeyValueFromObj(
                        $obj,
                        $isParent ? "childs" : $forginTableName,
                        array()
                    );
                }
                Helpers::setKeyValueFromObj(
                    $obj,
                    $isParent ? "childs_count" :  $forginTableName . "_count",
                    QueryHelpers::isDetailedIDEmpty($obj, $forgin) ? 0 :
                        Helpers::getKeyValueFromObj($this->getFetshTableWithQuery(QueryHelpers::getCountQuery($obj, $forgin)), "result")
                );
            }
        }
    }
    private function checkToSetForgins(string $tableName, &$queryResult, ?Options $option = null, ?string $parentTableName = null)
    {
        if (!$option?->addForginsObject && !$option?->addForginsList) return;
        if (!is_array($queryResult)) {
            if ($option?->isRequestedForginObjects()) {
                $this->addforginKeys($tableName, $queryResult, $option, $parentTableName);
            }
            if ($option?->isRequestedForginList()) {
                $this->addforginKeysList($tableName, $queryResult, $option, $parentTableName);
            }
        } else {
            foreach ($queryResult as &$res) {
                if ($option?->isRequestedForginObjects()) {
                    $this->addforginKeys($tableName, $res, $option, $parentTableName);
                }
                if ($option?->isRequestedForginList()) {
                    $this->addforginKeysList($tableName, $res, $option, $parentTableName);
                }
            }
        }
    }
    public function list(string $tableName, ?string $parentTableName = null, ?Options $option = null)
    {
        $query = $this->getQuery($tableName, ServerAction::LIST,  $option, $parentTableName);
        $result = $this->getFetshALLTableWithQuery($query);
        $this->checkToSetForgins($tableName, $result, $option, $parentTableName);
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
        // print_r($result);
        if ($result) {
            $this->checkToSetForgins($tableName, $result, $option);
        }
        return $result;
    }
    public function edit(string $tableName, object $object) {}
    public function add(string $tableName, $object, ?Options $option = null)
    {
        $origianlObject = Helpers::cloneByJson($object);
        Helpers::convertToObject($object);
        $this->unsetKeysThatNotFoundInObject($tableName, $object);





        // $searchedArray = $this->search($tableName, $object);



        return $object;
    }
    public function search(string $tableName, $object)
    {


        $cloned =  $object;
        Helpers::unSetKeyFromObj($cloned, "iD");
        $option = Options::getInstance();
        $option->searchRepository = $this->getSearchRepository();
        $option->searchOption = new SearchOption(null, null, $cloned);

        return $this->list($tableName, null,  $option);
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
