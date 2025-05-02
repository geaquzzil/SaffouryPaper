<?php


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
	return getFetshALLTableWithQuery("SELECT
  TABLE_NAME,
  COLUMN_NAME,
  REFERENCED_TABLE_NAME,
  REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE
  REFERENCED_TABLE_NAME = '$tableName' AND REFERENCED_TABLE_NAME IS NOT NULL AND TABLE_SCHEMA = '" . $_SERVER['DB_NAME'] . "'");
}
//Field Type Key
function getTableColumns($tableName)
{
	$result = getFetshALLTableWithQuery("SHOW COLUMNS FROM `" . $tableName . "`");
	$r = array();
	if (empty($result)) return array();
	foreach ($result as $res) {
		$r[] = $res["Field"];
	}
	
	return $r;
}
function getObjectForginKeys($tableName)
{
	return getFetshALLTableWithQuery("SELECT
  TABLE_NAME,
  COLUMN_NAME,
  REFERENCED_TABLE_NAME,
  REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE
  TABLE_NAME = '$tableName' AND REFERENCED_TABLE_NAME IS NOT NULL AND TABLE_SCHEMA = '" . $_SERVER['DB_NAME'] . "'");
}
function getShowTablesWithOrderByForginKey()
{
	return getFetshALLTableWithQuery("SELECT
  TABLE_NAME,
  COLUMN_NAME,
  Count(REFERENCED_TABLE_NAME),
  REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE
 TABLE_SCHEMA = '" . $_SERVER['DB_NAME'] . "'
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
 TABLE_SCHEMA = '" . $_SERVER['DB_NAME'] . "'
    GROUP BY TABLE_NAME
   Order by Count(REFERENCED_TABLE_NAME) ASC";
}
//TABLE_COMMENT not VIEW if you want to show only tables
function getAllTables()
{
	global $tablesNames;
	$tablesNames = getFetshAllTableWithQuery("SELECT table_name FROM information_schema.tables WHERE table_schema ='" . $_SERVER['DB_NAME'] . "'");
	return $tablesNames;
}

function getAllTablesString()
{
	return getStrings("SELECT table_name FROM information_schema.tables WHERE table_schema ='" . $_SERVER['DB_NAME'] . "'", TABLE_NAME);
}
function getAllTablesWithoutViewString()
{
	return getStrings(
		"SELECT table_name FROM information_schema.tables WHERE table_schema ='" . $_SERVER['DB_NAME'] . "' AND TABLE_TYPE <> 'VIEW' ",
		TABLE_NAME
	);
}
function getAllTablesViewString()
{
	return getStrings(
		"SELECT table_name FROM information_schema.tables WHERE table_schema ='" . $_SERVER['DB_NAME'] . "' AND TABLE_TYPE = 'VIEW' ",
		TABLE_NAME
	);
}
function getStrings($query, $value)
{
	$result = getFetshALLTableWithQuery($query);
	$response = array();
	if ($result == null) return $response;
	// print_r($result);
	if (!is_null($result)) {
		if (is_array($result)) {
			foreach ($result as $r) {
				array_push($response, $r[$value]);
			}
		}
	}
	return $response;
}
function getLastID($tableName)
{
	$result = getFetshTableWithQuery(getQueryMaxID($tableName));
	return $result["iD"];
}

/**
 * Check if a table exists in the current database.
 *
 * @param PDO $pdo PDO instance connected to a database.
 * @param string $table Table to search for.
 * @return bool TRUE if table exists, FALSE if no table found.
 */
function tableExists($table)
{
	$pdo = setupDatabase();
	// Try a select statement against the table
	// Run it in try/catch in case PDO is in ERRMODE_EXCEPTION.
	try {
		$result = $pdo->query("SELECT 1 FROM `$table` LIMIT 1");
	} catch (Exception $e) {
		// We got an exception == table not found
		return FALSE;
	}

	// Result is either boolean FALSE (no table found) or PDOStatement Object (table found)
	return $result !== FALSE;
}

function getSearchObjectStringValue($object, $tableName)
{
	$whereQuery = array();
	$tableColumns = getTableColumns($tableName);
	$forgins = getObjectForginKeys($tableName);

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
			if (canSearchInCustomSearchQuery($object, $tableName, $forginTableName)) {
				$res = searchObjectDetailStringValue($object, $forginTableName);
				if (!is_null($res)) {
					$objectToCheck[$table] = $res;
				}
			}
		}
	}
	return getSearchQueryAttributesOrDontUnSetID($objectToCheck, $tableName);
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
		} else {
			if (addKeyValueToSearchExtenstion($tableName, $key)) {
				if (is_numeric($value)) {
					$query = addslashes($tableName) . ".`" . $key . "` LIKE '" . $value . "'";
				} else {
					$query = addslashes($tableName) . ".`" . $key . "` LIKE '%" . $value . "%'";
				}

				$whereQuery[] = $query;
			}
		}
	}
	return implode(" OR ", $whereQuery);
}
function getSearchQueryAttributesOr($object, $tableName)
{
	unSetKeyFromObj($object, 'iD');
	$whereQuery = array();
	foreach ($object as $key => $value) {
		//do something with your $key and $value;
		if (addKeyValueToSearchExtenstion($tableName, $key)) {
			$query = " `" . $key . "` LIKE '" . $value . "' ";
			$whereQuery[] = $query;
		}
	}
	return implode(" OR ", $whereQuery);
}
function getSearchQueryAttributes($object, $tableName)
{
	unSetKeyFromObj($object, 'iD');
	$whereQuery = array();
	foreach ($object as $key => $value) {
		//do something with your $key and $value;
		if (addKeyValueToSearchExtenstion($tableName, $key)) {
			$query = " `" . $key . "` LIKE '" . $value . "' ";
			$whereQuery[] = $query;
		}
	}
	return implode(" AND ", $whereQuery);
}

// is array of iD / is json / is iD
function getWhereQuery($iD)
{
	if (is_numeric($iD)) {
		return "WHERE `iD`='$iD'";
	}
	if (isJson($iD)) {
		if (!is_array(jsonDecode($iD))) {
			return "WHERE `iD`='" . jsonDecode($iD)["iD"] . "'";
		}
	}
	if (is_array(jsonDecode($iD))) {
		return	"WHERE `iD` IN ( '" . implode(jsonDecode($iD), "','") . "' )" . "";
	}

	return "WHERE `iD`='$iD'";
}
function isParent($forgin)
{
	return $forgin["COLUMN_NAME"] === PARENTID;
}
function getKeyValue($object, $key)
{
	return $object[$key["COLUMN_NAME"]];
}
function getJsonKeyFromForginObject($key)
{
	return $key["REFERENCED_TABLE_NAME"];
}
function getQueryFromForginCurrent($object, $key)
{
	$tableName = getJsonKeyFromForginObject($key);
	$iD = getKeyValue($object, $key);
	return "SELECT * FROM  " . addslashes($tableName) . "  Where iD='$iD'";
}
function getJsonKeyFromForginArray($key)
{
	return $key["TABLE_NAME"];
}
function isCurrentObjectIDEmpty($object, $key)
{
	$tableName = getJsonKeyFromForginObject($key);
	$iD = getKeyValue($object, $key);
	return is_null($iD);
}
function isDetailedIDEmpty($object, $key)
{
	$tableName = getJsonKeyFromForginArray($key);
	$primaryKey = $key["COLUMN_NAME"];
	$iD = getKeyValueFromObj($object, "iD");
	return is_null($iD);
}
function getQueryFromFroginArray($object, $key)
{
	$tableName = getJsonKeyFromForginArray($key);
	$primaryKey = $key["COLUMN_NAME"];
	$iD = getKeyValueFromObj($object, "iD");
	return "SELECT * FROM  " . addslashes($tableName) . "  Where $primaryKey='$iD'";
}
function getCountQuery($object, $key)
{
	$tableName = getJsonKeyFromForginArray($key);
	$primaryKey = $key["COLUMN_NAME"];
	$iD = getKeyValueFromObj($object, "iD");
	return "SELECT Count(iD) AS result FROM  `" . addslashes($tableName) . "`  Where `$primaryKey`='$iD'";
}

///DATA BASE CONFING
function EXPORT_TABLES($host, $user, $pass, $name,  $tables = false, $backup_name = false, $asText = false)
{
	$mysqli = new mysqli($host, $user, $pass, $name);
	$mysqli->select_db($name);
	$mysqli->query("SET NAMES 'utf8'");
	$queryTables =
		//$mysqli->query('show full tables where Table_type = "BASE TABLE"');
		$mysqli->query(QueryOfTablesWithOrderByForginKey());
	while ($row = $queryTables->fetch_row()) {
		$target_tables[] = $row[0];
	}
	if ($tables !== false) {
		$target_tables = array_intersect($target_tables, $tables);
	}
	foreach ($target_tables as $table) {
		$result = $mysqli->query('SELECT * FROM ' . $table);
		$fields_amount = $result->field_count;
		$rows_num = $mysqli->affected_rows;
		$res = $mysqli->query('SHOW CREATE TABLE ' . $table);
		$TableMLine = $res->fetch_row();
		//   $content = (!isset($content) ?  '' : $content) . "\n\n".$TableMLine[1].";\n\n";
		$content = (!isset($content) ?  '' : $content) . "\n\n\n\n";
		for ($i = 0, $st_counter = 0; $i < $fields_amount; $i++, $st_counter = 0) {
			while ($row = $result->fetch_row()) { //when started (and every after 100 command cycle):
				if ($st_counter % 100 == 0 || $st_counter == 0) {
					$content .= "\nREPLACE INTO " . $table . " VALUES";
				}
				$content .= "\n(";
				for ($j = 0; $j < $fields_amount; $j++) {
					//echo $row[$j] ."\n";
					if (IsNullOrEmptyString($row[$j])) {
						// echo "EMPTY \n";
						$content .= 'null';
					} else {
						$row[$j] = str_replace("\n", "\\n", addslashes($row[$j]));
						if (isset($row[$j])) {
							$content .= '"' . $row[$j] . '"';
						} else {
							$content .= '""';
						}
					}
					if ($j < ($fields_amount - 1)) {
						$content .= ',';
					}
				}
				$content .= ")";
				//every after 100 command cycle [or at last line] ....p.s. but should be inserted 1 cycle eariler
				if ((($st_counter + 1) % 100 == 0 && $st_counter != 0) || $st_counter + 1 == $rows_num) {
					$content .= ";";
				} else {
					$content .= ",";
				}
				$st_counter = $st_counter + 1;
			}
		}
		$content .= "\n\n\n";
	}
	$backup_name = $backup_name ? $backup_name : $name . "___(" . date('h-i-sa') . "_" . date('d-m-Y') . ").sql";
	if ($asText) {
		return $content;
	} else {
		header('Content-Type: application/octet-stream');
		header("Content-Transfer-Encoding: Binary");
		header("Content-disposition: attachment; filename=\"" . $backup_name . "\"");
		echo $content;
		exit;
	}
}
