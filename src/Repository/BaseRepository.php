<?php

namespace Etq\Restful\Repository;

use Etq\Restful\Repository\Options;
use Etq\Restful\Helpers;
use Etq\Restful\QueryHelpers;
use Slim\Container;

abstract class BaseRepository
{

    protected $DB_NAME = "";

    private $cacheForginObjects = [];
    private $cacheForginList = [];
    protected $cacheTableColumns = [];



    public function __construct(protected \PDO $database, protected Container $container)
    {
        $this->DB_NAME = $_SERVER['DB_NAME'];
    }
    protected function getCachedForginList($tableName)
    {
        if (key_exists($tableName, $this->cacheForginList)) {
            return $this->cacheForginList[$tableName];
        } else {
            $this->cacheForginList[$tableName] = $this->getArrayForginKeys($tableName);
            return $this->cacheForginList[$tableName];
        }
    }
    protected function getCachedForginObject($tableName)
    {
        if (key_exists($tableName, $this->cacheForginObjects)) {
            return $this->cacheForginObjects[$tableName];
        } else {
            $this->cacheForginObjects[$tableName] = $this->getObjectForginKeys($tableName);
            return  $this->cacheForginObjects[$tableName];
        }
    }
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

    public function view(string $tableName, int $iD, ?string $parentTableName = null, ?Options $option = null)
    {
        if (!$option) {
            $option = Options::withStaticWhereQuery("iD = '$iD'");
        } else {
            $option->addStaticQuery("iD = '$iD'");
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
    public function add(string $tableName, object $object) {}
    public function delete(string $tableName, int $iD) {}



    private function changeTableNameToExtended(string $tableName)
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


    private function getQuery(string $tableName, ServerAction $action, ?Options $option = null, ?string $parentTableName = null): string
    {
        $query = "";
        $tableName = $this->changeTableNameToExtended($tableName);
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
                $query = "";
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

        return $option->getQuery($tableName, new SearchRepository($this->database, $this->container));
    }


    protected function getDb(): \PDO
    {
        return $this->database;
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

    public function getFetshALLTableWithQuery($query)
    {
        $statement = $this->database->prepare($query);
        $statement->execute();
        return (array) $statement->fetchAll();
    }
    public function getFetshTableWithQuery($query)
    {
        $statement = $this->database->prepare($query);
        $statement->execute();
        return  $statement->fetchObject();
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
    function getLastIncrementID($tableName)
    {
        return getFetshTableWithQuery("
        SELECT
            AUTO_INCREMENT
        FROM
            INFORMATION_SCHEMA.TABLES
        WHERE
            TABLE_SCHEMA = 'saffoury_paper' AND TABLE_NAME = '$tableName';")["AUTO_INCREMENT"];
    }
    function getArrayForginKeys($tableName)
    {
        return $this->getFetshALLTableWithQuery("
        SELECT 
            TABLE_NAME,COLUMN_NAME,REFERENCED_TABLE_NAME,REFERENCED_COLUMN_NAME
        FROM
            INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE
            REFERENCED_TABLE_NAME = '$tableName' AND REFERENCED_TABLE_NAME IS NOT NULL AND TABLE_SCHEMA = '" . $this->DB_NAME . "'");
    }

    //Field Type Key
    function getTableColumns($tableName)
    {
        $result = $this->getFetshALLTableWithQuery("SHOW COLUMNS FROM `" . $tableName . "`");
        $r = array();
        if (empty($result)) return array();
        foreach ($result as $res) {
            $r[] = $res["Field"];
        }
        return $r;
    }
    public function getCachedTableColumns($tableName)
    {
        if (key_exists($tableName, $this->cacheForginList)) {
            return $this->cacheTableColumns[$tableName];
        } else {
            $this->cacheTableColumns[$tableName] = $this->getTableColumns($tableName);
            return $this->cacheTableColumns[$tableName];
        }
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
    function getObjectForginKeys($tableName)
    {
        return $this->getFetshALLTableWithQuery("
        SELECT
            TABLE_NAME,COLUMN_NAME,REFERENCED_TABLE_NAME,REFERENCED_COLUMN_NAME
        FROM
            INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE
            TABLE_NAME = '$tableName' AND REFERENCED_TABLE_NAME IS NOT NULL AND TABLE_SCHEMA = '" . $this->DB_NAME . "'");
    }
    function getShowTablesWithOrderByForginKey()
    {
        return $this->getFetshALLTableWithQuery("
        SELECT
            TABLE_NAME, COLUMN_NAME, Count(REFERENCED_TABLE_NAME), REFERENCED_COLUMN_NAME
        FROM
            INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE
            TABLE_SCHEMA = '" . $this->DB_NAME . "'
        GROUP BY TABLE_NAME
        ORDER BY Count(REFERENCED_TABLE_NAME) ASC");
    }
    function QueryOfTablesWithOrderByForginKey()
    {
        return "
        SELECT
            TABLE_NAME,COLUMN_NAME,Count(REFERENCED_TABLE_NAME),REFERENCED_COLUMN_NAME
        FROM
            INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE
            TABLE_SCHEMA = '" . $this->DB_NAME . "'
        GROUP BY TABLE_NAME
        ORDER BY Count(REFERENCED_TABLE_NAME) ASC";
    }
    //TABLE_COMMENT not VIEW if you want to show only tables
    public function getAllTables()
    {
        $tablesNames = $this->getFetshAllTableWithQuery("
        SELECT
            table_name
        FROM
            information_schema.tables
        WHERE
            table_schema ='" . $this->DB_NAME . "'");
        return $tablesNames;
    }
    public function getAllTablesWithoutView()
    {
        $tablesNames = $this->getFetshAllTableWithQuery("
        SELECT
            table_name
        FROM
            information_schema.tables
        WHERE
            table_schema ='" . $this->DB_NAME . "'" . " AND TABLE_TYPE <> 'VIEW' ");
        return $tablesNames;
    }
    function getAllTablesString()
    {
        return getStrings("
        SELECT
            table_name
        FROM
            information_schema.tables
        WHERE
            table_schema ='" . $this->DB_NAME . "'", TABLE_NAME);
    }
    function getAllTablesWithoutViewString()
    {
        return getStrings(
            "
        SELECT
            table_name
        FROM
            information_schema.tables
        WHERE
            table_schema ='" . $this->DB_NAME . "' AND TABLE_TYPE <> 'VIEW' ",
            TABLE_NAME
        );
    }
    function getAllTablesViewString()
    {
        return getStrings(
            "
        SELECT
            table_name
        FROM
            information_schema.tables
        WHERE
            table_schema ='" . $this->DB_NAME . "' AND TABLE_TYPE = 'VIEW' ",
            TABLE_NAME
        );
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
