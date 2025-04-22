<?php

namespace Etq\Restful\Repository;

use Etq\Restful\Repository\Options;

abstract class BaseRepository
{

    protected $DB_NAME = "";

    public function __construct(protected \PDO $database)
    {
        $this->DB_NAME = $_SERVER['DB_NAME'];
    }

    private function addforginKeys(string $tableName, &$obj, ?Options $option = null)
    {
        $obj['addforginKeys'] = "sa";
        // $obj = [];
    }

    private function addforginKeysList(string $tableName, &$obj, ?Options $option = null)
    {
        $obj['addforginKeysList'] = "sa";
    }
    private function checkToSetForgins(string $tableName, &$queryResult, ?Options $option = null)
    {
        if (!$option?->addForginsObject && !$option?->addForginsList) return;
        if (!is_array($queryResult)) {
            if ($option?->addForginsObject) {
                $this->addforginKeys($tableName, $queryResult, $option);
            }
            if ($option?->addForginsList) {

                $this->addforginKeysList($tableName, $queryResult, $option);
            }
        } else {
            foreach ($queryResult as &$res) {
                if ($option?->addForginsObject) {
                    $this->addforginKeys($tableName, $res, $option);
                }
                if ($option?->addForginsList) {

                    $this->addforginKeysList($tableName, $res, $option);
                }
            }
        }
    }
    public function list(string $tableName, ?Options $option = null)
    {
        $query = $this->getQuery($tableName, ServerAction::LIST,  $option);
        $result = $this->getFetshALLTableWithQuery($query);
        $this->checkToSetForgins($tableName, $result, $option);

        return $result;
    }

    public function view(string $tableName, ?int $iD = null, ?Options $option = null)
    {
        $query = $this->getQuery($tableName, ServerAction::LIST,  $option);
        $result = $this->getFetshTableWithQuery($query);
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


    private function getQuery(string $tableName, ServerAction $action, ?Options $option = null): string
    {
        $query = "";
        $tableName = $this->changeTableNameToExtended($tableName);
        $optionQuery = $this->getOption($option);
        echo "\n OPTION QUERY ==> " . $optionQuery . " \n";

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
                    $query = "SELECT * FROM $tableName $optionQuery";
                }
                break;

            case ServerAction::DELETE:
                $query = "";
                break;

            case ServerAction::VIEW:
                $query = "SELECT * FROM $tableName $optionQuery";
                break;

            default:
                $query = "NON";
        }
        return $query;
    }
    private function getOption(?Options $option): string
    {
        if (!$option) return "";

        return $option->getQuery();
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
        return (array) $statement->fetch();
    }
    function getQueryMaxID($tableName)
    {
        return "SELECT iD FROM $tableName ORDER BY iD DESC LIMIT 1";
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
