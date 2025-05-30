<?php

require_once("extension_api_before_search.php");
require_once("extension_api_delete.php");
require_once("extension_api_fix_add.php");
require_once("extension_api_list_object_action.php");
require_once("extension_api_after_add.php");
function getSearchDataFromProductsOrCustomers($isProduct)
{
	$Limit = 2;
	$SearchQuery = '';
	$IDs = "";
	$Query = '';
	$hasQuery = false;
	$warehouse = (checkRequestValue("WarehouseID")) ? getRequestValue('WarehouseID') : null;

	$status = (checkRequestValue("status")) ? getRequestValue('status') : null;

	if (
		!checkRequestValue('searchQuery') ||
		((checkRequestValue('searchQuery') && empty(getRequestValue('searchQuery'))))
	) {
		$hasQuery = false;
		//	returnResponse(array());
	} else {
		$hasQuery = true;
		if (checkRequestValueInt('searchQuery')) {
			$SearchQuery = getRequestValue('searchQuery');
		} else {
			$SearchQuery = '%' . getRequestValue('searchQuery') . '%';
		}
	}
	if (checkRequestValue('limit')) {
		if (!checkRequestValueInt('limit')) {
			returnBadRequest("LIMIT");
		} else {
			$Limit = getRequestValue('limit');
		}
	}

	if (checkRequestValue('iDs')) {
		if (!isJson(getRequestValue('iDs'))) {
			returnBadRequest("iDs should be array");
		} else {
			$IDs = implode(json_decode(getRequestValue('iDs'), true), "','");
		}
	}
	$Query = "WHERE iD NOT IN ('" . $IDs . "') ";
	if ($isProduct) {
		if (!$hasQuery) {
			if (!is_null($warehouse)) {
				$Query = $Query . " AND `WarehouseID`='$warehouse'";
				//  echo "$Query \n"; 
			}
			if (!is_null($status)) {
				$Query = $Query . " AND `status`='$status'";
				//  echo "$Query \n"; 
			}
		} else 
		if (checkRequestValueInt('searchQuery')) {
			//	$Query= $Query." AND 
			//	( 
			//		(Length = :search_query) OR 
			//		( ABS(Length - :search_query) <=30) OR
			//		(Width = :search_query ) OR 
			//		( ABS(Width - :search_query) <=30)
			//		OR (GSM = :search_query)) 
			//	ORDER BY Length ASC ,Width ASC";

			$Query = $Query . " AND 
			( 
				(Length = :search_query) OR 
				( ABS(Length - :search_query) <=10) OR
				(Width = :search_query ) OR 
				( ABS(Width - :search_query) <=10)
				OR (GSM = :search_query)) 
			ORDER BY Length ASC ,Width ASC";
		} else {
			$Query = $Query . " AND 
			(		
				Type LIKE :search_query
				OR Manufacture LIKE :search_query 
				OR Country LIKE :search_query
			)";
		}
	} else {
		if (!$hasQuery) {
		} else {
			$Query = $Query . " AND 
			(		
				phone LIKE :search_query
				OR name LIKE :search_query 
			) LIMIT $Limit";
		}
	}

	$response['Query'] = $Query;
	$response['SearchQuery'] = $SearchQuery;
	return $response;
}
//advanced search or size analysis or similar
function getQueryFromAdvancedSearch($isSimilar)
{
	$data = jsonDecode(getRequestValue('data'));

	$Type = isset($data['type']) ? $data['type'] : "All";
	$Country = isset($data['country']) ? $data['country'] : "All";
	$GSM = isset($data['gsm']) ? $data['gsm'] : "All";
	$Unit = isset($data['unit']) ? $data['unit'] : "All";
	$Date = isset($data['date']) ? $data['date'] : "All";
	$Quality = isset($data['quality']) ? $data['quality'] : "All";
	$Grade = isset($data['grade']) ? $data['grade'] : "All";
	$Warehouse = isset($data['warehouse']) ? $data['warehouse'] : "All";

	if ($Type == "All" or $Type == "الكل") {
		$Type = "";
	} else {
		$Type = "AND `Type` = '$Type' ";
	}
	if ($GSM == "All") {
		$GSM = "";
	} else {
		$GSM = "AND `GSM` = '$GSM' ";
	}
	if ($Unit == "Roll" or $Unit == "رول" or $Unit == "Reel") {
		$Unit = "AND (`Length` ='0' OR `Length` IS NULL) ";
	} else if ($Unit == "Pallet" or $Unit == "بالة") {
		$Unit = "AND `Length` <>'0' ";
	} else {
		$Unit = "";
	}
	//Date statement
	if ($Date == "All" or $Date == "الكل") {
		$Date = "";
	} else if ($Date == "Today" or $Date == "اليوم") {
		$Date = "AND DATE(date) = CURDATE() "; //DATE(`timestamp`) = CURDATE()
	} else if ($Date == "This week" or $Date == "هذا الأسبوع") {
		$Date = "AND YEARWEEK(date, 1) = YEARWEEK(CURDATE(), 1) "; //YEARWEEK(`date`, 1) = YEARWEEK(CURDATE(), 1)
	} else if ($Date == "This month" or  $Date == "هذا الشهر") {
		$Date = "AND month(date) = month(CURDATE()) ";
	} else {
		$Date = "AND YEAR(date) = YEAR(CURDATE()) ";
		//this year
	}
	if ($Country == "All" or $Country == "الكل") {
		$Country = "";
	} else {
		$Country = "AND `Country` = '$Country' ";
	}
	if ($Quality == "All" or $Quality == "الكل") {
		$Quality = "";
	} else {
		$Quality = "AND `Quality` = '$Quality' ";
	}
	if ($Grade == "All" or $Grade == "الكل") {
		$Grade = "";
	} else {
		$Grade = "AND `Grade` = '$Grade' ";
	}
	if ($Warehouse == "All" or $Warehouse == "الكل") {
		$Warehouse = "";
	} else {
		$Warehouse = "AND `Warehouse` = '$Warehouse' ";
	}
	$SubQuery = "$Type $GSM $Unit $Date $Country $Quality $Grade $Warehouse";
	$Query = "";
	////Size Analysis
	if (isset($data['length']) || isset($data['width'])) {
		$Length = $data['length'];
		$Width = $data['width'];

		$MLength = ((int)$Length) * 10;
		$MWidth = ((int)$Width) * 10;

		$SizeStatement = '';
		if ($isSimilar) {

			if ($Length === 0) {
				$SizeStatement = " AND (Length = '0' OR `Length` IS NULL) AND Width = '$Width' ";
			} else {
				$SizeStatement = " AND Length = '$Length' AND Width = '$Width' ";
			}
		} else {
			$SizeStatement = " AND (`Length` BETWEEN '$Length' AND '$MLength'  AND `Width` BETWEEN '$Width' AND '$MWidth'  
				OR `Length` BETWEEN '$Width' AND '$MWidth' AND `Width` BETWEEN '$Length' AND '$MLength'
			    OR `Length` ='0' AND `Width` BETWEEN '$Length' AND '$MLength'
			    OR `Length` ='0' AND `Width` BETWEEN '$Length' AND '$MLength')";
		}
		$SubQuery = $SubQuery . $SizeStatement;
	}

	if (isEmptyString($SubQuery)) {
		$Query = "";
		//	echo "EMPTY";
	} else {
		$Query = "WHERE `iD` IS NOT NULL $SubQuery";
	}
	return $Query;
}
$API_ACTIONS["list_changes_records_table"] = function () {
	$fieldToGroupBy = getRequestValue('fieldToGroupBy');
	$fieldToSumBy = null;
	if (checkRequestValue('fieldToSumBy')) {
		$fieldToSumBy = getRequestValue('fieldToSumBy');
	}

	if (($i = array_search($table, getAllTablesString())) !== FALSE) {
	} else {
		http_response_code(400);
		returnResponseErrorMessage("Bad request");
	}
	$permission = checkPermissionForActionTableResultAndAction($permission, $table, "list");
	if (!$permission) {
		returnPermissionResponse($table, 0);
	}
	$forgins = getTableColumns($table);
	if ((($i = array_search((string)$fieldToGroupBy, $forgins)) === FALSE)) {
		returnResponseErrorMessage("Bad field non founded request");
	}
	if (!is_null($fieldToSumBy)) {
		if ((($i = array_search((string)$fieldToSumBy, $forgins)) === FALSE)) {
			returnResponseErrorMessage("Bad field non to sum by founded request");
		}
	}
	$hasDetailObj = null;
	if ($fieldToGroupBy != "ProductID") {
		$objects = getObjectForginKeys($table);

		foreach ($objects as $o) {
			$forginKeyColName = $o["COLUMN_NAME"];
			$forginTableName = $o["REFERENCED_TABLE_NAME"];

			if ($forginKeyColName == $fieldToGroupBy) {
				$hasDetailObj = $forginTableName;
				break;
			}
		}
	}

	$totalCount = getFetshTableWithQuery("SELECT COUNT(*) as count FROM $table")['count'];
	$response = array();
	$response['total'] = $totalCount;
	if (is_null($fieldToSumBy)) {
		$response['totalGrouped'] = getFetshALLTableWithQuery("SELECT COUNT(*) as count,$fieldToGroupBy as groupBy FROM $table GROUP BY $fieldToGroupBy ORDER BY count ASC");
	} else {
		$response['totalGrouped'] = getFetshALLTableWithQuery("SELECT COUNT(*) as count,$fieldToGroupBy as groupBy,Sum($fieldToSumBy) as total FROM $table GROUP BY $fieldToGroupBy ORDER BY count ASC");
	}

	if (!is_null($hasDetailObj)) {

		foreach ($response['totalGrouped'] as &$it) {
			if (is_null($it["groupBy"])) {
				$it["groupBy"] = "";
			} else {
				$it["groupBy"] = getOnlyName($it["groupBy"], $hasDetailObj)["name"];
			}
		}
	}
	$response['fieldToSumBy'] = is_null($fieldToSumBy) ? null : $fieldToSumBy;
	returnResponse($response);
	//only check if it has list details tables ;
	//if true then we have to check if the id is used 
	// or else its not used 

};
$API_ACTIONS["backup_database"] = function () {
	if (isAdmin()) {
		require_once('Utils/db_backupAndRestore.php');
		require_once('cryptor.php');
		$mcrypt = new MCrypt();

		/**
		 * Instantiate Backup_Database and perform backup
		 */
		// Report all errors
		error_reporting(E_ALL);
		// Set script max execution time
		set_time_limit(900); // 15 minutes
		$backupDatabase = new Backup_Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, CHARSET);
		$result = $backupDatabase->backupTables(TABLES, BACKUP_DIR) ? 'OK' : 'KO';
		$backupDatabase->obfPrint('Backup result: ' . $result, 1);
		// Use $output variable for further processing, for example to send it by email
		//$output = $backupDatabase->getOutput();
		// header('Content-Type: application/octet-stream'); 
		header('Content-Type: application/x-gzip');
		header("Content-Transfer-Encoding: Binary");
		header("Content-disposition: attachment; filename=\"" . $backupDatabase->backupFile . "\"");
		$txtOfFile = $mcrypt->encrypt($backupDatabase->content);
		echo gzencode(gzcompress($txtOfFile, 9));
		exit;
	} else {
		echo "No permission";
	}
};
$API_ACTIONS["restore_database"] = function () {
	if (isAdmin()) {
		require_once('Utils/db_backupAndRestore.php');
		$txtOfFile = "";
		if (getFileTextFromRequest($txtOfFile)) {
			require_once('cryptor.php');
			$mcrypt = new MCrypt();
			$txtOfFile = $mcrypt->decrypt(gzuncompress($txtOfFile));
			error_reporting(E_ALL);
			// Set script max execution time
			set_time_limit(900); // 15 minutes
			$restoreDatabase = new Restore_Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
			//$result = $restoreDatabase->restoreDbText($txtOfFile) ? 'OK' : 'KO';
			//   $restoreDatabase->obfPrint("Restoration result: ".$result, 1);

		} else {
			echo "Error ";
		}
	}
};
