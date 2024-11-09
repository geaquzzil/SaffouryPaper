<?php

namespace Etq\Restful\Repository;

abstract class BaseRepository
{

    protected $DB_NAME = "";
    public function __construct(protected \PDO $database)
    {
        $this->DB_NAME = $_SERVER['DB_NAME'];
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
    function getFetshTableWithQuery($query)
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
        return getFetshTableWithQuery("SELECT AUTO_INCREMENT
    FROM  INFORMATION_SCHEMA.TABLES
    WHERE TABLE_SCHEMA = 'saffoury_paper'
    AND   TABLE_NAME   = '$tableName';")["AUTO_INCREMENT"];
    }
    function getArrayForginKeys($tableName)
    {
        return $this->getFetshALLTableWithQuery("SELECT
  TABLE_NAME,
  COLUMN_NAME,
  REFERENCED_TABLE_NAME,
  REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
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
    function getObjectForginKeys($tableName)
    {
        return $this->getFetshALLTableWithQuery("SELECT
  TABLE_NAME,
  COLUMN_NAME,
  REFERENCED_TABLE_NAME,
  REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE
  TABLE_NAME = '$tableName' AND REFERENCED_TABLE_NAME IS NOT NULL AND TABLE_SCHEMA = '" . $this->DB_NAME . "'");
    }
    function getShowTablesWithOrderByForginKey()
    {
        return $this->getFetshALLTableWithQuery("SELECT
  TABLE_NAME,
  COLUMN_NAME,
  Count(REFERENCED_TABLE_NAME),
  REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE
 TABLE_SCHEMA = '" . $this->DB_NAME . "'
    GROUP BY TABLE_NAME
   Order by Count(REFERENCED_TABLE_NAME) ASC");
    }
    function QueryOfTablesWithOrderByForginKey()
    {
        return "SELECT
  TABLE_NAME,
  COLUMN_NAME,
  Count(REFERENCED_TABLE_NAME),
  REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE
 TABLE_SCHEMA = '" . $this->DB_NAME . "'
    GROUP BY TABLE_NAME
   Order by Count(REFERENCED_TABLE_NAME) ASC";
    }
    //TABLE_COMMENT not VIEW if you want to show only tables
    public function getAllTables()
    {
        $tablesNames = $this->getFetshAllTableWithQuery("SELECT table_name FROM information_schema.tables WHERE table_schema ='" . $this->DB_NAME . "'");
        return $tablesNames;
    }
    function getAllTablesString()
    {
        return getStrings("SELECT table_name FROM information_schema.tables WHERE table_schema ='" . $this->DB_NAME . "'", TABLE_NAME);
    }
    function getAllTablesWithoutViewString()
    {
        return getStrings(
            "SELECT table_name FROM information_schema.tables WHERE table_schema ='" . $this->DB_NAME . "' AND TABLE_TYPE <> 'VIEW' ",
            TABLE_NAME
        );
    }
    function getAllTablesViewString()
    {
        return getStrings(
            "SELECT table_name FROM information_schema.tables WHERE table_schema ='" . $this->DB_NAME . "' AND TABLE_TYPE = 'VIEW' ",
            TABLE_NAME
        );
    }
}
