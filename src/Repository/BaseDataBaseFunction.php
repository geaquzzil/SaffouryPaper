<?php



namespace Etq\Restful\Repository;

use Etq\Restful\Helpers;
use Etq\Restful\Repository\BaseRepository;
use Slim\Container;


abstract class BaseDataBaseFunction
{
    protected $DB_NAME = "";

    public function __construct(protected \PDO $database, protected Container $container)
    {
        $this->DB_NAME = $_SERVER['DB_NAME'];
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
        return getFetshTableWithQuery("
        SELECT
            AUTO_INCREMENT
        FROM
            INFORMATION_SCHEMA.TABLES
        WHERE
            TABLE_SCHEMA = 'saffoury_paper' AND TABLE_NAME = '$tableName';")["AUTO_INCREMENT"];
    }
    public function unsetKeysThatNotFoundInObject($tableName, &$object){
        
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
}
