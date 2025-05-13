<?php



namespace Etq\Restful\Repository;

use Etq\Restful\Helpers;
use Etq\Restful\Repository\BaseRepository;
use Etq\Restful\QueryHelpers;
use Exception;
use Etq\Restful\Handler\Validater;
use Slim\Container;


abstract class BaseDataBaseFunction
{
    protected $DB_NAME = "";
    private static $cacheForginObjects = [];
    private static $cacheForginList = [];
    protected static $cacheTableColumns = [];
    private static $cacheColumnType = [];

    public function __construct(protected \PDO $database, protected Container $container)
    {
        $this->DB_NAME = $_SERVER['DB_NAME'];
    }

    public function getCachedColumnType($tableName)
    {

        if (key_exists($tableName, self::$cacheColumnType)) {

            return self::$cacheColumnType[$tableName];
        } else {
            self::$cacheColumnType[$tableName] = $this->getObjcetColumnType($tableName);
            return self::$cacheColumnType[$tableName];
        }
    }
    public function getCachedForginList($tableName)
    {
        if (key_exists($tableName, self::$cacheForginList)) {

            return self::$cacheForginList[$tableName];
        } else {
            self::$cacheForginList[$tableName] = $this->getArrayForginKeys($tableName);
            return self::$cacheForginList[$tableName];
        }
    }
    public function getCachedForginObject($tableName)
    {
        if (key_exists($tableName, self::$cacheForginObjects)) {

            return self::$cacheForginObjects[$tableName];
        } else {
            self::$cacheForginObjects[$tableName] = $this->getObjectForginKeys($tableName);
            return  self::$cacheForginObjects[$tableName];
        }
    }
    public function getCachedTableColumns($tableName)
    {
        if (key_exists($tableName, self::$cacheTableColumns)) {

            return self::$cacheTableColumns[$tableName];
        } else {
            self::$cacheTableColumns[$tableName] = $this->getTableColumns($tableName);
            return self::$cacheTableColumns[$tableName];
        }
    }
    public function getDashbaordRepository(): DashboardRepository
    {
        return $this->container->get("dashboard_repository");
    }
    public function getSearchRepository()
    {
        return $this->container->get("search_repository");
    }
    public function getFetshALLTableWithQuery($query)
    {
        $statement = $this->database->prepare($query);
        $statement->execute();
        return (array) $statement->fetchAll();
    }
    public function getCount($obj, $foreing)
    {
        return
            Helpers::getKeyValueFromObj(
                $this->getFetshTableWithQuery(QueryHelpers::getCountQuery($obj, $foreing)),
                "result"
            );
    }
    public function getFetshTableWithQuery($query)
    {
        $statement = $this->database->prepare($query);
        $statement->execute();
        return  $statement->fetchObject();
    }
    public function getDeleteTableWithQuery($query)
    {
        $stmt = $this->database->prepare($query);
        $stmt->execute();
        return $stmt->rowCount();
    }
    protected function getCustomerRepo(): CustomerRepository
    {
        return $this->container["customer_repository"];
    }
    protected function getDb(): \PDO
    {
        return $this->database;
    }
    public function getInsertQuery($tableName, $object, ?int $iD = null)
    {
        $action = (!$iD ? "INSERT INTO " : " UPDATE ");
        $insert = $iD ? false : true;
        if ($insert) {
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
    public function getLastIncrementID($tableName)
    {
        return Helpers::getKeyValueFromObj($this->getFetshTableWithQuery("
        SELECT
            AUTO_INCREMENT
        FROM
            INFORMATION_SCHEMA.TABLES
        WHERE
            TABLE_SCHEMA = 'saffoury_paper' AND TABLE_NAME = '$tableName';"), "AUTO_INCREMENT",);
    }
    protected function validatePhoneNumber(&$object, bool $isResponse = true)
    {
        $phone = Helpers::isSetKeyFromObjReturnValue($object, "phone");
        if ($phone) {

            Helpers::setKeyValueFromObj($object, "phone", $isResponse ? urlencode($phone) : urldecode($phone));
        }
    }
    protected function unsetAllForginListWithOutRefrence($tableName, $object, &$resultsForginLists = [], ?bool $unset = false)
    {
        $cloned =  clone $object;
        $forginsListsOriginal = array_values(array_map(function ($va) {
            return $va[TABLE_NAME];
        }, $this->getCachedForginList($tableName)));

        foreach ($object as $key => &$val) {
            if (in_array($key, $forginsListsOriginal)) {
                if (is_null($val) || empty($val)) {
                    Helpers::setKeyValueFromObj($cloned, $key, "");
                    echo "\n unsetAllForginListWithOutRefrence is  null or empty ------>$key\n ";
                    Helpers::unSetKeyFromObj($cloned, $key);
                    continue;
                }
                $resultsForginLists[] = $key;
                if ($unset) {
                    Helpers::unSetKeyFromObj($cloned, $key);
                }
            }
        }
        return $cloned;
    }
    protected function unsetAllForginList($tableName, &$object, &$resultsForginLists = [], ?bool $unset = false)
    {
        $forginsListsOriginal = array_values(array_map(function ($va) {
            return $va[TABLE_NAME];
        }, $this->getCachedForginList($tableName)));
        $this->changeParentsToChild($tableName, $forginsListsOriginal);
        foreach ($object as $key => &$val) {
            if (in_array($key, $forginsListsOriginal)) {
                if (is_null($val) || empty($val)) {
                    Helpers::setKeyValueFromObj($object, $key, "");
                    echo "\n unsetAllForginList is  null or empty ------>$key\n ";
                    Helpers::unSetKeyFromObj($object, $key);
                    continue;
                }
                $resultsForginLists[] = $key;
                if ($unset) {
                    echo "\nunsetAllForginList unset $key\n";
                    Helpers::unSetKeyFromObj($object, $key);
                }
            }
        }

        return $object;
    }
    private function changeParentsToChild($tableName, &$forginsLists)
    {
        $hasChilds = false;
        Helpers::removeFromArray($forginsLists, $tableName, $hasChilds);
        if ($hasChilds) {
            $forginsLists[] = "childs";
        }
    }
    private function validateObject($tableName, &$object, ?bool $addNonFoundColumns = true, bool $throwExceptionAndValidate = true)
    {

        $tableColumns = $this->getCachedTableColumns($tableName);
        $forginsObjectsOriginal = $this->getCachedForginObject($tableName);
        $forginsListsOriginal = $this->getCachedForginList($tableName);

        $forginsObjects =  array_values(array_map(function ($va) {
            return $va[rtn];
        }, $forginsObjectsOriginal));

        $forginsLists =  array_values(array_map(function ($va) {
            return $va[TABLE_NAME];
        }, $forginsListsOriginal));

        $tableColumnType = $this->getCachedColumnType($tableName);

        $this->validatePhoneNumber($object, false);

        $this->changeParentsToChild($tableName, $forginsLists);
        $onlyTableColumn = array_values($tableColumns);

        echo "\nvalidateObject $tableName\n";



        $tableColumns = array_values($tableColumns);
        $tableColumns = array_merge($tableColumns, ($forginsObjects));
        $tableColumns = array_merge($tableColumns, ($forginsLists));

        Helpers::removeAllNonFoundInTowArray(array_keys((array)$object), $tableColumns, true, $removedColumns);
        if (!empty($removedColumns)) {
            foreach ($removedColumns as $c) {
                unset($object->$c);
            }
        }
        if ($addNonFoundColumns) {
            $diff = (Helpers::setValuesThatNotFoundInTowArray($onlyTableColumn, array_keys((array)$object)));
            foreach ($diff as $d) {
                Helpers::setKeyValueFromObj(
                    $object,
                    $d,
                    null
                );
            }
        }
        $exceptions = [];
        if ($throwExceptionAndValidate) {
            foreach ($tableColumnType as $tc) {
                $isNullable = $tc["IS_NULLABLE"] == "YES" ? true : false;
                $columnName = $tc[cn];
                $searchValue = Helpers::searchInArrayGetValue($columnName, $forginsObjectsOriginal, cn);
                $refrenceTable = $searchValue ? $searchValue[rtn] : "";
                $valByID =    Helpers::isSetKeyFromObjReturnValue($object, $columnName);
                $valueByObject = is_null($searchValue) ? [] : Helpers::isSetKeyFromObjReturnValue($object, $searchValue[rtn]);
                $valueIsNull = is_null($valByID) && is_null($valueByObject);
                if (!$isNullable && $valueIsNull) {
                    $exceptions[] = " value must be not null in $tableName   column name :'$columnName', '$refrenceTable' ";
                }
            }
        }
        if (!empty($exceptions)) {
            throw new Validater(implode("\n", $exceptions));
        }

        // Helpers::unSetKeyFromObj($object, "iD");

        return $object;
    }
    private function getValueToCheckForeing($object, $foreing, $typeArray, $currentForeingTableName, &$type)
    {
        $type = ForginCheckType::NONE;
        if (is_array($typeArray)) {
            if (key_exists($currentForeingTableName, $typeArray)) {
                $type = $typeArray[$currentForeingTableName];
            }
        }
        switch ($type) {
            case ForginCheckType::NONE:
                $key = $foreing[rtn];
                echo "\ngetValueToCheckForeing NONE -->->key is  $key\n";
                return Helpers::isSetKeyFromObjReturnValue($object, $key);
            case ForginCheckType::BY_FOREING_ID:
                $key = $foreing[cn];
                echo "\ngetValueToCheckForeing BY_FOREING_ID -->->key is  $key\n";
                return Helpers::isSetKeyFromObjReturnValue($object, $key);
            case ForginCheckType::BY_ID_IN_VALUE:
                $key = $foreing[rtn];
                echo "\ngetValueToCheckForeing BY_ID_IN_VALUE -->->key is  $key\n";
                $val =
                    Helpers::isSetKeyFromObjReturnValue($object, $foreing[rtn]);
                if (!$val) {
                    echo "---> is NULL";
                    return null;
                }
                $val = Helpers::isSetKeyFromObjReturnValue($val, "iD");
                echo "\ngetValueToCheckForeing BY_ID_IN_VALUE -->->value $key is  $val\n";
                return $val;
        }
    }
    private function addForginObjectsFromObject($tableName, &$object, BaseRepository $baseRepository, $type = ForginCheckType::NONE, ?string $parentTableName = null)
    {
        $forginsObjectsOriginal = $this->getCachedForginObject($tableName);
        foreach ($forginsObjectsOriginal as $fo) {
            $childTableName = $fo[rtn];
            $forginIDInParent = $fo[cn];
            $ob = $fo[rtn]; //tablename

            echo "\ntableName $tableName\n";
            $val = $this->getValueToCheckForeing($object, $fo, $type, $childTableName, $type);

            if ($childTableName == $parentTableName) {
                Helpers::setKeyValueFromObj($object, $forginIDInParent, $val);
                echo "\n addingForginOBjectsFromOBject skip parent $parentTableName and child $childTableName and set $forginIDInParent: $val ";
                unset($object->$childTableName);
                continue;
            }
            if ($type != ForginCheckType::NONE) {
                $array = ["iD" => $val];
                if (Helpers::isNewRecord($array)) {
                    echo "\n addingForginOBjectsFromOBject is new record ";
                    $val = $this->getValueToCheckForeing($object, $fo,  ForginCheckType::NONE, $childTableName, $type);
                } else {
                    echo "\n addingForginOBjectsFromOBject skip parent $parentTableName and set $forginIDInParent: $val ";
                    Helpers::setKeyValueFromObj($object, $forginIDInParent, $val);
                    unset($object->$childTableName);
                    continue;
                }
            }
            print_r($val);
            if (!is_null($val)) {
                Helpers::convertToObject($object->$ob);
                Helpers::setKeyValueFromObj(
                    $object,
                    $ob,
                    $this->validateObjectAndAdd($ob, $object->$ob, $baseRepository, $type)
                );
                $res =
                    ($baseRepository)->search(
                        $ob,
                        Helpers::getKeyValueFromObj($object, $ob),
                        true,
                        true,
                        true
                    );


                if ($res) {
                    $iD =
                        Helpers::getKeyValueFromObj($res, "iD");
                    echo "founded  $childTableName--->->-> $iD\n";
                    Helpers::setKeyValueFromObj($object, $forginIDInParent, $iD);
                    Helpers::unSetKeyFromObj($object, $childTableName);
                }
            } else {
                Helpers::setKeyValueFromObj(
                    $object,
                    $forginIDInParent,
                    null
                );
            }
            unset($object->$childTableName);
        }
    }

    protected function addForginListFromObject($tableName, &$object,  BaseRepository $baseRepository, $resultsForingList)
    {
        $type = [$tableName => ForginCheckType::BY_ID_IN_VALUE];
        $forginsLists = $this->getCachedForginList($tableName);
        $iD = Helpers::getKeyValueFromObj($object, 'iD');
        foreach ($resultsForingList as $fo) {
            $objectValueArray = Helpers::isSetKeyFromObjReturnValue($object, $fo);
            // print_r($objectValueArray);
            foreach ((array)$objectValueArray as &$item) {

                // echo "\naddForginListFromObject getting $fo from $tableName\n";
                $res =  array_search($fo, array_column($forginsLists, TABLE_NAME));
                $res = $forginsLists[$res];
                Helpers::convertToObject($item);
                Helpers::setKeyValueFromObj($item, $res[cn], $iD);
                Helpers::setKeyValueFromObj($item, $tableName, $object);
                // echo "\nstarting adding foring list for $fo\n\n\n";
                if ($fo == "childs") {
                    // echo "\nis childs chnging to $tableName \n";
                    $fo = $tableName;
                }
                $baseRepository->add($fo, $item, null, false, false, $type);
                // echo " \nfinished\n";

            }
            unset($object->$fo);
        }
    }
    public function validateObjectAndAdd($tableName, &$object, BaseRepository $baseRepository, $type = ForginCheckType::NONE, ?string $parentTableName = null)
    {

        $isArray = is_array($object)  ? true : false;
        if ($isArray) {
            foreach ((array)$object as $key => &$item) {
                Helpers::convertToObject($item);
                Helpers::setKeyValueFromObj(
                    $object,
                    $key,
                    $this->validateObjectAndAdd($tableName, $item, $baseRepository, $type, $parentTableName)
                );
            }
            // print_r($object);
            return $object;
        }
        $object = $this->validateObject($tableName, $object, true);
        $this->addForginObjectsFromObject($tableName, $object, $baseRepository, $type, $parentTableName);

        return $object;
    }
    protected function before($tableName, &$object, ServerAction $action, ?Options &$option, BaseRepository $repo)
    {
        if ($this->container->offsetExists($tableName)) {
            echo "\n before $tableName\n";
            $list =    $this->container->get($tableName);
            $key = BEFORE . $this->getServerActionString($action);
            if (key_exists($key, $list)) {
                $list[$key]($object, $option, $repo);
            }
        }
    }
    protected function onBeforeForEach($tableName, &$arr, ?Options &$option, BaseRepository $repo)
    {
        if ($this->container->offsetExists($tableName)) {

            $list =    $this->container->get($tableName);
            $key = ONFEH;
            if (key_exists($key, $list)) {
                $list[$key]($arr, $option, $repo);
            }
        }
    }
    protected function after($tableName, &$object, ServerAction $action, ?Options &$option, BaseRepository $repo)
    {
        echo "\nafter $tableName \n";
        $bool = is_null(($option->auth)) ? " $tableName is auth null\n" : " $tableName not auth null\n";
        echo "\n" . $bool . " \n";

        if ($this->container->offsetExists($tableName)) {

            $list =    $this->container->get($tableName);
            $key = AFTER . $this->getServerActionString($action);
            if (key_exists($key, $list)) {
                $list[$key]($object, $option, $repo);
            }
        }
    }
    private function getServerActionString(ServerAction $action)
    {
        switch ($action) {
            case ServerAction::ADD:
                return ADD;
            case ServerAction::EDIT:
                return EDIT;
            case ServerAction::DELETE:
                return DELETE;

            case ServerAction::VIEW:
                return VIEW;

            case ServerAction::LIST:
                return LISTO;
        }
    }


    public function getArrayForginKeys($tableName)
    {
        return $this->getFetshALLTableWithQuery("
        SELECT 
            TABLE_NAME,COLUMN_NAME,REFERENCED_TABLE_NAME,REFERENCED_COLUMN_NAME
        FROM
            INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE
            REFERENCED_TABLE_NAME = '$tableName' AND REFERENCED_TABLE_NAME IS NOT NULL AND TABLE_SCHEMA = '" . $this->DB_NAME . "'");
    }


    public function getObjectForginKeys($tableName)
    {
        return $this->getFetshALLTableWithQuery("
        SELECT
            TABLE_NAME,COLUMN_NAME,REFERENCED_TABLE_NAME,REFERENCED_COLUMN_NAME
        FROM
            INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE
            TABLE_NAME = '$tableName' AND REFERENCED_TABLE_NAME IS NOT NULL AND TABLE_SCHEMA = '" . $this->DB_NAME . "'");
    }
    public function getObjcetColumnType($tableName)
    {
        return $this->getFetshALLTableWithQuery("
        SELECT
            COLUMN_NAME, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, NUMERIC_PRECISION, DATETIME_PRECISION,IS_NULLABLE 
        FROM
            INFORMATION_SCHEMA.COLUMNS
        WHERE
            TABLE_NAME='$tableName'
        ");
    }
    public function getShowTablesWithOrderByForginKey()
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
    public function QueryOfTablesWithOrderByForginKey()
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
    public function getTables()
    {
        return $this->getFetshALLTableWithQuery("show full tables");
    }


    //Field Type Key
    public function getTableColumns($tableName)
    {
        $result = $this->getFetshALLTableWithQuery("SHOW COLUMNS FROM `" . $tableName . "`");
        $r = array();
        if (empty($result)) return array();
        foreach ($result as $res) {
            $r[] = $res["Field"];
        }
        return $r;
    }

    protected function unsetDateWhenSearch($tableName)
    {
        switch ($tableName) {
            case EQ:
            case PR:
                return true;
            default:
                return false;
        }
    }
    protected function isSearchDisbled($tableName)
    {
        //todo get from list
        switch ($tableName) {
            case ORDR_D:
            case ORDR_R_D:
            case PURCH_R_D:
            case PURCH_D:
            case PR_INPUT_D:
            case PR_OUTPUT_D:
            case CUT_RESULT:
            case CRS_D:
            case RI_D:
            case TR_D:
                return true;
            default:
                return false;
        }
    }
}

enum ForginCheckType
{
    //this will search for values and unset id
    case NONE;
        //this will get the id from value and set it without search
    case BY_ID_IN_VALUE;
        //this will get the foreing id value and set it without search
    case BY_FOREING_ID;
}
