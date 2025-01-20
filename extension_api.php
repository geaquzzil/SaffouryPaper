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
function checkDateRequest(&$DateOrMonth, &$IsDate)
{
	if (!checkRequestValue('date')) {
		returnBadRequest('no date');
	}
	$DateOrMonth = jsonDecode(getRequestValue('date'));
	$IsDate = !is_null(isSetKeyFromObjReturnValue($DateOrMonth, 'from')) ||  isDateTime($DateOrMonth) || isDate($DateOrMonth);

	if (!checkRequestValueInt('date') && !$IsDate) {
		returnBadRequest('date is not date or month');
	}
}
function returnResponseSearchProducts($Query)
{

	$results = getFetshALLTableWithQuery("SELECT `iD` FROM `" . PR_SEARCH . "` $Query");
	if (!empty($results) || !is_null($results)) {
		$results = array_map(function ($tmp) {
			return $tmp['iD'];
		}, $results);
		returnResponse(depthSearch(($results), PR, 1, getRequireArrayTables(), getRequireObjectTable(), getOptions()));
	} else {
		returnResponse(array());
		// no content
	}
}
function getExchangeRateResource()
{
	// libxml_use_internal_errors(true);
	//	$context  = stream_context_create(array('http' => array('header' => 'Accept: application/xml')));
	// $ctx = stream_context_create(array('http'=>
	//  array(
	//     'timeout' => 1200,  //1200 Seconds is 20 Minutes
	// )
	//));
	// $url = 'http://dollar-lira.com/syrian-exchange/prices.xml';
	// $xml = file_get_contents($url, false, $context);
	// $xml = simplexml_load_string($xml);
	$xml = false;
	$json;
	if ($xml === false) {
		$json['status'] = false;
		return $json;
	} else {
		$json['status'] = true;
		$json['dollar']['buy'] = (string)$xml->element[0]->buy;
		$json['dollar']['sell'] = (string)$xml->element[0]->sell[0];
		$json['euro']['buy'] = (string)$xml->element[1]->buy;
		$json['euro']['sell'] = (string)$xml->element[1]->sell;
		return $json;
	}
}
function getServerDataResource()
{
	$response = array();
	if (isEmployee()) {
		$response[GOV] = depthSearch(null, GOV, 0, null, null, null);
		$response[CARGO] = depthSearch(null, CARGO, 0, null, null, null);
		$response[AC_NAME_TYPE] = depthSearch(null, AC_NAME_TYPE, 0, null, null, null);
		$response[AC_NAME] = depthSearch(null, AC_NAME, 0, null, [AC_NAME_TYPE], null);
		$response[CUSTOMS] = depthSearch(null, CUSTOMS, 0, null, [EMP], null);
		$response[CUR] = getProductTables(CUR);
		$response[CUST] = getProductTables(CUST);
		$response[USR] = depthSearch(null, USR, 0, null, [], null);
	}
	if (isAdmin()) {
		//this is for blocking soso
		$response[EMP] =	depthSearch(null, EMP, 0, null, null, null);
		// getProductTables(EMP);
		// (array)getFetshALLTableWithQuery("SELECT iD,name,userlevelid,phone FROM `".EMP."`");
	}
	$response[WARE] = getProductTables(WARE);
	$response[GSM] = getProductTables(GSM);
	$response[COUNTRY] = getProductTables(COUNTRY);
	$response[MAN] = getProductTables(MAN);
	$response[GD] = getProductTables(GD);
	$response[TYPE] = depthSearch(null, TYPE, 0, null, [GD], null);
	$response[QUA] = getProductTables(QUA);
	return $response;
}
//EXTENSTION API CALLS
function addObjectExtenstion($object, $objectName)
{
	//  echo " \n $objectName \n";
	if (in_array($objectName, array_keys($GLOBALS["OBJECT_ACTIONS"]))) {
		$func = $GLOBALS["OBJECT_ACTIONS"][$objectName];
		if (is_callable($func))
			$func($object);
	}
	return $object;
}

function hasCustomSearchQueryReturnListOfID($object, $objectName, &$hasCustomFunctionFounded)
{
	$hasCustomFunctionFounded = false;
	if (in_array($objectName, array_keys($GLOBALS["CUSTOM_SEARCH_QUERY"]))) {
		$func = $GLOBALS["CUSTOM_SEARCH_QUERY"][$objectName];
		if (is_callable($func)) {
			$hasCustomFunctionFounded = true;
			return $func($object);
		}
	}
	return null;
}
function canSearchInCustomSearchQuery($object, $parentTableName, $tableName)
{
	if (in_array($parentTableName, array_keys($GLOBALS["CAN_SEARCH_IN_STRING_QUERY"]))) {
		$func = $GLOBALS["CAN_SEARCH_IN_STRING_QUERY"][$parentTableName];
		if (is_callable($func)) {
			return $func($object, $tableName);
		}
	}
	return true;
}
function hasCustomSearchColumnReturnListCustoms($tableName)
{
	if (in_array($tableName, array_keys($GLOBALS["CUSTOM_SEARCH_COL"]))) {
		$func = $GLOBALS["CUSTOM_SEARCH_COL"][$tableName];
		if (is_callable($func)) {
			return $func();
		}
	}
	return array();
}
function getCustomSearchQueryColumnReturnQuery($tableName, $key, $value)
{
	if (in_array($tableName, array_keys($GLOBALS["CUSTOM_SEARCH_COL_GET"]))) {
		$func = $GLOBALS["CUSTOM_SEARCH_COL_GET"][$tableName];
		if (is_callable($func)) {
			return $func($key, $value);
		}
	}
	return "";
}
function hasCustomJoinReturnJoinStringQuery($objectName)
{
	if (in_array($objectName, array_keys($GLOBALS["CUSTOM_JOIN"]))) {
		$func = $GLOBALS["CUSTOM_JOIN"][$objectName];
		if (is_callable($func)) {
			return $func();
		}
	}
	return null;
}

function beforeReturnResponseObjectExtenstion(&$object, $objectName)
{

	//echo " beforeReturnResponseObjectExtenstion $objectName ";
	if (is_null($objectName)) {
		//   echo " beforeReturnResponseObjectExtenstion $objectName ";
		return $object;
	}
	$func = $GLOBALS["BEFORE_SEND_RESPONSE"][$objectName];
	if (is_callable($func))
		$func($object);
	return $object;
}
function fixObjectExtenstion(&$object, $objectName)
{
	if (in_array($objectName, array_keys($GLOBALS["FIX_ADD_OBJECT"]))) {
		$func = $GLOBALS["FIX_ADD_OBJECT"][$objectName];
		if (is_callable($func))
			$func($object);
	}
	return $object;
}
//this is for fire before the add to db and after the for loop
function fixOnBeforeAddObjectExtenstion($origianlObject, &$object, $objectName)
{
	if (in_array($objectName, array_keys($GLOBALS["FIX_BEFORE_ADD_OBJECT"]))) {
		$func = $GLOBALS["FIX_BEFORE_ADD_OBJECT"][$objectName];
		if (is_callable($func))
			$func($origianlObject, $object);
	}
	return $object;
}
function deleteObjectExtenstion($object, $objectName)
{
	if (in_array($objectName, array_keys($GLOBALS["DELETE_OBJ"]))) {
		$func = $GLOBALS["DELETE_OBJ"][$objectName];
		if (is_callable($func))
			$func($object);
	}
	return $object;
}
function fixDeleteResponseObjectExtenstion(&$object, $objectName)
{
	if (in_array($objectName, array_keys($GLOBALS["FIX_RESPONSE_OBJECT_DELETE"]))) {
		$func = $GLOBALS["FIX_RESPONSE_OBJECT_DELETE"][$objectName];
		if (is_callable($func))
			$func($object);
	}
	return $object;
}
function afterAddObjectExtenstion($origianlObject, $object, $objectName)
{
	if (in_array($objectName, array_keys($GLOBALS["AFTER_ADD_OBJ"]))) {
		$func = $GLOBALS["AFTER_ADD_OBJ"][$objectName];
		if (is_callable($func))
			$func($origianlObject, $object);
	}
	return $object;
}
function beforeSearchObjectExtenstion(&$object, $objectName)
{
	if (in_array($objectName, array_keys($GLOBALS["BEFORE_SEARCH_OBJECT"]))) {
		$func = $GLOBALS["BEFORE_SEARCH_OBJECT"][$objectName];
		if (is_callable($func))
			$func($object);
	}
	return $object;
}
function customSearchQueryBeforeAddExtenstion($object, $objectName)
{
	if (in_array($objectName, array_keys($GLOBALS["CUSTOM_QUERY_BEFORE_ADD_SEARCH"]))) {
		$func = $GLOBALS["CUSTOM_QUERY_BEFORE_ADD_SEARCH"][$objectName];
		if (is_callable($func))
			return $func($object);
	}
	return null;
}

function addKeyValueToSearchExtenstion($tableName, $key)
{
	if (in_array($tableName, array_keys($GLOBALS["ADD_KEY_VALUE_TO_SEARCH_QUERY"]))) {
		$func = $GLOBALS["ADD_KEY_VALUE_TO_SEARCH_QUERY"][$tableName];
		if (is_callable($func))
			return $func($key);
	}
	return true;
}
$API_ACTIONS["available_product_type"] = function () {
	checkPermissionAction(TYPE);
	$results = getFetshALLTableWithQuery("SELECT DISTINCT  `ProductTypeID` AS iD FROM `" . PR_SEARCH . "`");
	if (!empty($results) || !is_null($results)) {
		$results = array_map(function ($tmp) {
			return $tmp['iD'];
		}, $results);
		returnResponse(depthSearch(($results), TYPE, 1, getRequireArrayTables(), getRequireObjectTable(), getOptions()));
	} else {
		returnResponse(array());
		// no content
	}
};
$API_ACTIONS["set_customs_declarations"] = function () {
	// checkEditAddRequest();
	//  checkPermissionAction("list_customers");
	$data = json_decode(getRequestValue('data'), false);
	$customsDeclaration = isSetKeyFromObjReturnValue($data, CUSTOMS);
	$productType = isSetKeyFromObjReturnValue($data, TYPE);

	$CDID = isSetKeyFromObjReturnValue($customsDeclaration, 'iD');
	$PRID = isSetKeyFromObjReturnValue($productType, 'iD');

	if ($CDID > 0 and $PRID > 0) {
		$query =  " UPDATE `products` Set `CustomsDeclarationID` = '$CDID' WHERE ProductTypeID = '$PRID'";
		getUpdateTableWithQuery($query);
		returnResponse($data);
	} else {
		returnResponse(-1);
	}



	//  $countryManufacture=isSetKeyFromObjReturnValue($data,CMC);
	if (!is_null($customsDeclaration)) {
		if (isNewRecord($customsDeclaration)) {
			$customsDeclaration = addEditObject($customsDeclaration, CUSTOMS, getDefaultAddOptions());
		}
	}
	if (!is_null($purchases)) {
		foreach (getKeyValueFromObj($purchases, PURCH_D) as &$purch) {
			$purch->{PR}->{CUSTOMS} = $customsDeclaration;
			$purch->{PR}->{CMC} = $countryManufacture;
		}
		returnResponse($data);
	}


	// echo " NO WE ADDING PURCHASIG\n ";



	//json_decode(getRequestValue('data'),false)
};
$API_ACTIONS["add_from_importer"] = function () {
	checkEditAddRequest();
	// checkPermissionAction("list_customers");
	$data = json_decode(getRequestValue('data'), false);
	//  $customsDeclaration=isSetKeyFromObjReturnValue($data,CUSTOMS);
	$purchases = isSetKeyFromObjReturnValue($data, PURCH);
	$countryManufacture = isSetKeyFromObjReturnValue($data, CMC);
	//  if(!is_null($customsDeclaration)){
	//      if(isNewRecord($customsDeclaration)){ $customsDeclaration=addEditObject($customsDeclaration,CUSTOMS,getDefaultAddOptions()); }
	//   }
	if (!is_null($purchases)) {
		foreach (getKeyValueFromObj($purchases, PURCH_D) as &$purch) {
			// $purch->{PR}->{CUSTOMS}=$customsDeclaration;
			$purch->{PR}->{CMC} = $countryManufacture;
		}
		returnResponse(addEditObject($purchases, PURCH, getDefaultAddOptions()));
	}


	// echo " NO WE ADDING PURCHASIG\n ";



	//json_decode(getRequestValue('data'),false)
};
$API_ACTIONS["action_exchange_rate"] = function () {
	returnResponse(getExchangeRateResource());
};
$API_ACTIONS["list_server_data"] = function () {
	returnResponse(getServerDataResource());
};
$API_ACTIONS["login_flutter"] = function () {
	global $User;
	returnResponse($User);
};
$API_ACTIONS["login"] = function () {
	global $User;
	$User["serverData"] = getServerDataResource();
	returnResponse($User);
};

$API_ACTIONS["list_block"] = function () {
	$response[CUST] = getFetshALLTableWithQuery("SELECT iD,name,activated FROM " . CUST);
	$employess = getFetshALLTableWithQuery("SELECT iD,name,activated FROM `" . EMP . "` WHERE `" . KLVL . "` <> ' " . ADMIN_ID . "'");
	foreach ($employess as $e) {
		$e["isEmployee"] = true;
		array_push($response[CUST], $e);
	}
	//	$response[EMP]=getFetshALLTableWithQuery("SELECT iD,name,activated FROM `".EMP."` WHERE `".KLVL."` <> ' ".ADMIN_ID."'");
	return returnResponse($response[CUST]);
};
$API_ACTIONS["action_cut_request_change_quantity"] = function () {
	$data = json_decode(getRequestValue('data'), false);
	$cut_requests = getKeyValueFromObj($data, CUT);
	$resultCount = getKeyValueFromObj($cut_requests, "cut_request_results_count");
	if ($resultCount == 0) {
		//  $iD=getKeyValueFromObj($cut_requests,"iD");
		//   getUpdateTableWithQuery(" UPDATE `cut_requests` SET `cut_status` = 'PROCESSING' WHERE `cut_requests`.`iD` = '$iD'");
	}
	setKeyValueFromObj($data, CUT, addEditObjectWithoutNoti($cut_requests, CUT, getAddOptions()));
	// $data[CUT]=;

	return returnResponse($data);
};
$API_ACTIONS["action_cut_request_scan_by_product"] = function () {
	//to get product id to search if there are any pending requests
	$data = getRequestValue("data");
	$response = array();
	$response = $data;
	$productID = $data['iD'];
	$results = getFetshALLTableWithQuery("SELECT `CutRequestID` FROM `pending_cut_requests`  WHERE ProductID='$productID'");
	if (!empty($results) || !is_null($results)) {
		$results = array_map(function ($tmp) {
			return $tmp['CutRequestID'];
		}, $results);
		$response["cut_requests"] = depthSearch($results, CUT, 1, true, true, null);
	} else {
		return null;
	}
	return $response;
};
//iD tableName blockValue 1 or 0
$API_ACTIONS["action_block"] = function () {
	if (
		!checkRequestValueInt('iD')  ||
		!checkRequestValue('tableName') || !checkRequestValue('blockValue')
	) {
		returnBadRequest("Not set iD or tableName");
	}
	$Action = getRequestValue('tableName');
	if ($Action == EMP) {
		returnResponseMessage(block(getRequestValue('iD'), false, getRequestValue('blockValue')));
	}
	if ($Action == CUST) {
		returnResponseMessage(block(getRequestValue('iD'), true, getRequestValue('blockValue')));
	}
	if ($Action == "ALL") {
		returnResponseMessage(blockALL(true));
	}
	if ($Action == "NONE") {
		returnResponseMessage(blockALL(false));
	}
};
//iD token
$API_ACTIONS["token"] = function () {
	if (!checkRequestValue('token')) {
		returnBadRequest("no token int");
	}
	if (isEmployee() || isCustomer()) {
		try {
			$tableName = isEmployee() ? EMP : CUST;
			$pdo = setupDatabase();
			$params["userID"] = getUserID();
			$params["token"] = getRequestValue('token');
			$stmt = $pdo->prepare("UPDATE `$tableName` SET `token`=:token WHERE `iD`=:userID");
			$stmt->execute($params);
			returnResponseMessage($stmt->rowCount());
		} catch (Exception $e) {
			returnResponseErrorMessage($e->getMessage());
		}
	}
};
//data is 
//tableName = credits
//ids=[1,2,3,4]
//to Warehouse
$API_ACTIONS["action_transfer_money"] = function () {
	if (isAdmin()) {
		$count = 0;
		if (checkRequestValue('data')) {
			if (!isJson(getRequestValue('data'))) {
				returnBadRequest('data is not json');
			}
		} else {
			returnBadRequest('data is not json');
		}
		$Data = json_decode(getRequestValue('data'), true);
		foreach ($Data as $TransferAction) {
			$IDs = implode($TransferAction['iDs'], "','");
			$updateQuery = "UPDATE `" . $TransferAction['tableName'] .
				"` SET `CashBoxID` ='" . $TransferAction['to'] . "' WHERE `iD` IN ('$IDs')";
			//echo $updateQuery;
			$count += getUpdateTableWithQuery($updateQuery);
		}
		returnResponse($count);
	}
};
//from to 
$API_ACTIONS["action_transfer_account"] = function () {
	if (!checkRequestValueInt('from') || !checkRequestValueInt('to')) {
		returnBadRequest("Not int");
	}
	$count = 0;
	$from = getRequestValue('from');
	$to = getRequestValue('to');
	$query = "SELECT TABLE_NAME,COLUMN_NAME 
	FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE COLUMN_NAME='" . KCUST . "' AND 
	REFERENCED_TABLE_NAME IS NOT NULL AND TABLE_SCHEMA = '" . DATABASE_NAME . "'";
	$results = getFetshALLTableWithQuery($query);
	if (!empty($results)) {
		foreach ($results as $res) {
			$updateQuery = "UPDATE `" . $res["TABLE_NAME"] .
				"` SET `" . KCUST . "` ='$to' WHERE `" . KCUST . "` ='$from'";
			//echo $updateQuery;
			$count += getUpdateTableWithQuery($updateQuery);
		}
	}
	returnResponseMessage($count);
};
//iD from to 
//limit - searchQuery - iDs
$API_ACTIONS["list_search_products"] = function () {
	$response = getSearchDataFromProductsOrCustomers(true);
	$Query = $response['Query'];
	$SearchQuery = $response['SearchQuery'];
	$pdo = setupDatabase();
	$stmt = $pdo->prepare("SELECT iD FROM " . PR_SEARCH . " $Query");
	$stmt->bindParam(
		':search_query',
		$SearchQuery,
		checkRequestValueInt('searchQuery') ? PDO::PARAM_INT : PDO::PARAM_STR
	);
	$stmt->execute();
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	if (!empty($results) || !is_null($results)) {
		$results = array_map(function ($tmp) {
			return $tmp['iD'];
		}, $results);
		returnResponse(depthSearch(($results), PR, 1, getRequireArrayTables(), getRequireObjectTable(), getOptions()));
	} else {
		returnResponse(array());
	}
};
$API_ACTIONS["list_advanced_search"] = function () {
	if (!checkRequestValue('data')) {
		returnBadRequest("Bad request");
	}
	if (!isJson(getRequestValue('data'))) {
		returnBadRequest("Bad request");
	}
	$Query = getQueryFromAdvancedSearch(false);
	returnResponseSearchProducts($Query);
};
$API_ACTIONS["list_search_size_analyzier"] = function () {
	if (!checkRequestValue('data')) {
		returnBadRequest("Bad request");
	}
	if (!isJson(getRequestValue('data'))) {
		returnBadRequest("Bad request");
	}
	$Query = getQueryFromAdvancedSearch(false);
	returnResponseSearchProducts($Query);
};
$API_ACTIONS["list_similars_products"] = function () {
	if (!checkRequestValue('data')) {
		returnBadRequest("Bad request");
	}
	if (!isJson(getRequestValue('data'))) {
		returnBadRequest("Bad request");
	}
	$Query = getQueryFromAdvancedSearch(true);
	returnResponseSearchProducts($Query);
};
$API_ACTIONS["list_products_movements"] = function () {
	checkPermissionAction("list_products_movements");
	if (!checkRequestValueInt('<ProductID>')) {
		returnBadRequest('id is null or not int');
	}
	$iD = getRequestValue('<ProductID>');

	//	print_r( );
	//	die;

	$response[PR] = depthSearch($iD, PR, 1, [PR], true, null);

	$response[PURCH] = depthSearchByDetailTable(PURCH, PURCH_D, "ProductID", $iD, true);
	$response[PURCH . "Analysis"] = getGrowthRateByInvoiceDetailsQuery(PURCH, PURCH_D, "PurchaseID", "quantity", " ProductID='$iD' ");

	$response[PURCH_R] = depthSearchByDetailTable(PURCH_R, PURCH_R_D, "ProductID", $iD, true);
	$response[PURCH_R . "Analysis"] = getGrowthRateByInvoiceDetailsQuery(PURCH_R, PURCH_R_D, "PurchaseRefundID", "quantity", " ProductID='$iD' ");

	$response[ORDR] = depthSearchByDetailTable(ORDR, ORDR_D, "ProductID", $iD, true);
	$response[ORDR . "Analysis"] = getGrowthRateByInvoiceDetailsQuery(ORDR, ORDR_D, "OrderID", "quantity", " ProductID='$iD' ");

	$response[RI] = depthSearchByDetailTable(RI, RI_D, "ProductID", $iD, true);
	$response[RI . "Analysis"] = getGrowthRateByInvoiceDetailsQuery(RI, RI_D, "ReservationID", "quantity", " ProductID='$iD' ");


	$response[ORDR . "Analysis"] = getGrowthRateByInvoiceDetailsQuery(ORDR, ORDR_D, "OrderID", "quantity", " ProductID='$iD' ");

	$response[ORDR_R] = depthSearchByDetailTable(ORDR_R, ORDR_R_D, "ProductID", $iD, true);
	$response[ORDR_R . "Analysis"] = getGrowthRateByInvoiceDetailsQuery(ORDR_R, ORDR_R_D, "OrderRefundID", "quantity", " ProductID='$iD' ");


	$response[PR_INPUT] = depthSearchByDetailTable(PR_INPUT, PR_INPUT_D, "ProductID", $iD, true);
	$response[PR_INPUT . "Analysis"] = getGrowthRateByInvoiceDetailsQuery(PR_INPUT, PR_INPUT_D, "ProductInputID", "quantity", " ProductID='$iD' ");


	$response[PR_OUTPUT] = depthSearchByDetailTable(PR_OUTPUT, PR_OUTPUT_D, "ProductID", $iD, true);
	$response[PR_OUTPUT . "Analysis"] = getGrowthRateByInvoiceDetailsQuery(PR_OUTPUT, PR_OUTPUT_D, "ProductOutputID", "quantity", " ProductID='$iD' ");

	$response[TR] = depthSearchByDetailTable(TR, TR_D, "ProductID", $iD, true);
	$response[TR . "Analysis"] = getGrowthRateByInvoiceDetailsQuery(TR, TR_D, "TransferID", "quantity", " ProductID='$iD' ");


	$option["WHERE_EXTENSION"] = " `ProductID`='$iD'";
	$response[CUT] = depthSearch(null, CUT, 1, [CUT_RESULT, SIZE_CUT], [SIZE_CUT], $option);
	$response[CUT . "Analysis"] = getGrowthRateByQuery(CUT, "quantity", " ProductID='$iD' ");
	returnResponse($response);
};
$API_ACTIONS["list_customers_profit"] = function () {
	checkPermissionAction("list_customers");
	$DateOrMonth;
	$IsDate;
	checkDateRequest($DateOrMonth, $IsDate);

	$FROM = date("Y-m-d", strtotime($DateOrMonth['from']));
	$TO = date("Y-m-d", strtotime($DateOrMonth['to']));
	$E_FROM = date('Y-m-d', (strtotime('-1 day', strtotime($FROM))));

	$iD = null;
	if (checkRequestValueInt('iD')) {
		$iD = getRequestValue('iD');
	} else {
		returnBadRequest('NO ID ');
	}

	returnResponse(getGrowthRateAfterAndBeforeWithWhereQuery(changeToExtendedTableDashboard("profits_orders"), "total", $FROM, $TO, " CustomerID='$iD'"));
	// 	returnResponse(getProfitsByMonthsAndCustomers($iD));
};
$API_ACTIONS["list_customers_pay_next"] = function () {
	checkPermissionAction("list_customers");
	returnResponse(customerToPayNext());
};
$API_ACTIONS["list_customers_not_payed"] = function () {
	checkPermissionAction("list_customers");
	returnResponse(notPayedCustomers());
};
$API_ACTIONS["list_customers_terms"] = function () {
	checkPermissionAction("list_customers");
	$iD = null;
	if (checkRequestValueInt('iD')) {
		$iD = getRequestValue('iD');
	}
	returnResponse(customersTerms($iD));
};
$API_ACTIONS["list_customers_balances"] = function () {
	checkPermissionAction("list_customers");
	$requireTerms = false;
	if (checkRequestValue("requireTerms")) {
		$requireTerms =  getRequestValue("requireTerms");
	}

	$customers = depthSearch(null, CUST, 1, [], [], null);
	$response = array();
	if ($requireTerms) {
		$response["customers"] = array();
	}
	$totalBalance = 0;
	$termsBreakCount = 0;
	$nextPaymentCount = 0;
	foreach ($customers as $cust) {
		if ($cust["balance"] != 0) {
			$totalBalance += $cust["balance"];
			$iD = $cust['iD'];
			$option = array();
			$option["WHERE_EXTENSION"] = "`CustomerID` = '$iD'";
			$option["LIMIT"] = "LIMIT 1";
			$option["ORDER_BY_EXTENSTION"] = "ORDER BY `date` DESC ";
			$cust["lastCredit"] = depthSearch(null, "equality_credits", 1, [], [], $option);
			if (toBoolean($requireTerms)) {
				$terms = customersTerms($iD);
				$nextPayemnt = customerToPayNextByID($iD);
				$cust["customerTerms"] = $terms;
				$cust["termsBreakCount"] = count($terms);

				$cust["customerToPayNext"] = $nextPayemnt;
				$cust["nextPaymentCount"] = count($nextPayemnt);
				$termsBreakCount += count($terms);
				$nextPaymentCount += count($nextPayemnt);
			}
			if ($requireTerms) {
				array_push($response["customers"], $cust);
			} else {
				array_push($response, $cust);
			}
		}
	}
	if ($requireTerms) {
		$response["customers"] =	array_values(array_sort_e($response["customers"], 'balance', SORT_DESC));
	} else {
		$response =	array_values(array_sort_e($response, 'balance', SORT_DESC));
	}
	if ($requireTerms) {
		$response["totalBalance"] = $totalBalance;
		$response["termsBreakCount"] = $termsBreakCount;
		$response["nextPaymentCount"] = $nextPaymentCount;
	}
	returnResponse($response);
};
$API_ACTIONS["list_search_by_barcode"] = function () {
	if (!checkRequestValue('<barcode>')) {
		returnBadRequest("barcode is empty");
	}
	$Barcode = getRequestValue('<barcode>');
	$ZeroBarcode = "0" . $Barcode;
	$option = array();
	$option["WHERE_EXTENSION"] = "`barcode` IS NOT NULL AND `barcode` <> '' AND (  `barcode` = '$Barcode' OR `barcode` = '$ZeroBarcode' ) ";
	//$result= depthSearch(null,PR,1,null,[PR,SIZE,CUSTOMS,GSM,TYPE,CMC,GD,QUA],$option);
	$result = depthSearch(null, PR, 1, getRequireArrayTables(), getRequireObjectTable(), $option);
	if (empty($result) || is_null($result)) {
		$res['similarBarcode'] = false;
		$option = array();
		$option["WHERE_EXTENSION"] = "`barcode` IS NOT NULL AND `barcode` <> '' AND CHAR_LENGTH(`barcode`)>4 AND ( 
			SUBSTRING(`barcode`,1,(CHAR_LENGTH(`barcode`)-4))
			=
			SUBSTRING('$Barcode',1, (CHAR_LENGTH('$Barcode')-4))
			OR 
			SUBSTRING(`barcode`,1,(CHAR_LENGTH(`barcode`)-4))
			=
			SUBSTRING('$ZeroBarcode',1,(CHAR_LENGTH('$ZeroBarcode')-4))
			)";
		//$secResult=depthSearch(null,PR,1,null,[PR,SIZE,CUSTOMS,GSM,TYPE,CMC,GD,QUA],$option);
		$secResult = depthSearch(null, PR, 1, getRequireArrayTables(), getRequireObjectTable(), $option);
		if (!empty($secResult)) {
			$singleSecResult = $secResult[0];
			$singleSecResult["similarBarcode"] = true;
			$res = $singleSecResult;
		}
		$res['iD'] = -1;
		$res['barcode'] = $Barcode;
		returnResponse($res);
	} else {
		returnResponse($result[0]);
	}
};
$API_ACTIONS["list_search_customers"] = function () {
	checkPermissionAction("list_customers");
	$response = getSearchDataFromProductsOrCustomers(false);
	$Query = $response['Query'];
	$SearchQuery = $response['SearchQuery'];
	//echo $Query;
	$pdo = setupDatabase();
	$stmt = $pdo->prepare("SELECT iD FROM `" . CUST . "` $Query");
	$stmt->bindParam(':search_query', $SearchQuery, PDO::PARAM_STR);
	$stmt->execute();
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

	if (!empty($results) || !is_null($results)) {
		$results = array_map(function ($tmp) {
			return $tmp['iD'];
		}, $results);
		returnResponse(depthSearch(($results), CUST, 0, null, null, null));
	} else {
		// no content
	}
};
// iD the customer iD  data is notification message
$API_ACTIONS["action_notification"] = function () {
	$iD = null;
	$Message = null;
	if (checkRequestValue('iD')) {
		if (!checkRequestValueInt('iD')) {
			returnBadRequest("iD is not int");
		} else {
			$iD = getRequestValue('iD');
		}
	}
	if (!checkRequestValue('data')) {
		returnBadRequest("data message not found");
	} else {
		$Message = jsonDecode(getRequestValue('data'));
	}
	if (is_null($iD)) {
		doNotification($Message, null, FB_GENERAL_NOTIFCATION);
		returnResponse(jsonDecode(getRequestValue('data')));
	} else {
		$customer = jsonDecode($_POST["NOTI"]);
		$notificationObject = jsonDecode($_POST["NOTI_OBJECT"]);
		send_notification(
			getRegestrationsIDTable($customer, CUST),
			get_notification_object($notificationObject, FB_GENERAL_NOTIFCATION)
		);

		returnResponse(jsonDecode(getRequestValue('data')));
	}
};
function hasPermissionForDashboardIf($permissionTable, $tableName, $action)
{
	if (isAdmin()) {
		//  echo " is ADmin\n ";
		return true;
	} else if (isGuest()) {
		//    echo " is isGuest\n ";
		return false;
	} else  if (isCustomer()) {
		//  echo " is isCustomer\n ";
		return true;
	} else {

		return checkPermissionForActionTableResultAndAction($permissionTable, $tableName, $action);
	}
}
$API_ACTIONS["view_customer_statment_by_employee"] = function () {
	// checkPermissionAction("list_customers_balances");
	$DateOrMonth;
	$IsDate;
	$currentYear = date("Y");
	$currentYear = $currentYear . "-01-01";

	checkDateRequest($DateOrMonth, $IsDate);
	$FROM = date("Y-m-d", strtotime($DateOrMonth['from']));
	$TO = date("Y-m-d", strtotime($DateOrMonth['to']));
	$E_FROM = date('Y-m-d', (strtotime('-1 day', strtotime($FROM))));

	if (!checkRequestValueInt('<iD>')) {
		returnBadRequest('<id> is null or not int');
	}
	$iD = getRequestValue('<iD>');
	$customerByEmployee;
	$customerByEmployee[CUST] = array();

	$customes = getFetshAllTableWithQuery("SELECT * FROM customers WHERE EmployeeID='$iD'");

	$requiredListTable = getRequireArrayTables();
	// 	$customerByEmployee["ordersAnalysisGeneral"]=array();
	foreach ($customes as $cust) {

		foreach ($requiredListTable as $table) {
			$val = getGrowthRateWithCustomerIDAfterAndBefore(changeToExtendedTableDashboard($table), getValueToCalculateGrowthRate($table), $cust['iD'], $FROM, $TO);
			$cust[$table . "Analysis"] = $val;
			checkToAdd($cust[$table . "Analysis"], $customerByEmployee, $table . "AnalysisGeneral");
		}
		$customerByEmployee[CUST][] = $cust;
	}
	$customerByEmployee["dateObject"] = $DateOrMonth;
	foreach ($customerByEmployee as $key => &$value) {
		if ($key != CUST && $key != "dateObject" && is_array($customerByEmployee[$key])) {
			if (!empty($customerByEmployee[$key])) {
				$value = $value[0];
			}
		}
	}
	returnResponse($customerByEmployee);
};
function checkToAdd($analsisObj, &$response, $key)
{
	if (empty($analsisObj)) return;
	$founded = false;
	if (empty($response[$key])) {
		$response[$key][] = $analsisObj;
		//   array_push($response[$key],$analsisObj);
		return;
	}
	foreach ($response[$key] as &$it) {
		foreach ($it as &$soso) {
			foreach ($analsisObj as $singleObj) {
				if ($singleObj['day'] == $soso['day'] && $soso['year'] == $singleObj['year'] && $soso['month'] == $singleObj['month']) {
					$founded = true;
					$soso['total'] = $soso['total'] + $singleObj['total'];
				}
			}
		}
	}
	if (!$founded) {
		// array_push($response[$key],$analsisObj);
		$response[$key][] = $analsisObj;
	}
}
$API_ACTIONS["view_customer_statement"] = function () {
	checkPermissionAction("list_customers_balances");
	$DateOrMonth;
	$IsDate;
	$withAnalysis =	checkRequestValue('withAnalysis');
	$currentYear = date("Y");
	$currentYear = $currentYear . "-01-01";

	$FROM_STARTED = date("Y-m-d", strtotime("2020-01-01"));

	checkDateRequest($DateOrMonth, $IsDate);
	if (!checkRequestValueInt('<iD>')) {
		returnBadRequest('<id> is null or not int');
	}
	//	if(checkDat)

	$iD = getRequestValue('<iD>');
	$customer = getFetshTableWithQuery("SELECT * FROM customers WHERE iD='$iD'");
	if (is_null($customer) || empty($customer)) {
		return null;
	} // no content}
	$FROM = date("Y-m-d", strtotime($DateOrMonth['from']));
	$TO = date("Y-m-d", strtotime($DateOrMonth['to']));
	$E_FROM = date('Y-m-d', (strtotime('-1 day', strtotime($FROM))));

	$option = array();
	$option["WHERE_EXTENSION"] = "`CustomerID`='$iD' AND Date(`date`) >= '$FROM' AND Date(`date`) <= '$TO' ORDER BY `date` DESC ";



	$customer[CUST] = depthSearch($iD, CUST, 1, [], true, null);
	$customer[CRED] = depthSearch(null, CRED, 1, null, [EQ, CUST, EMP, WARE], $option);
	if ($withAnalysis) {
		$customer[CRED . "Analysis"] = getGrowthRateWithCustomerIDAfterAndBefore("equality_" . CRED, "value", $iD, $FROM_STARTED, $TO);
	}

	$customer[DEBT] = depthSearch(null, DEBT, 1, null, [EQ, CUST, EMP, WARE], $option);
	if ($withAnalysis) {
		$customer[DEBT . "Analysis"] = getGrowthRateWithCustomerIDAfterAndBefore("equality_" . DEBT, "value", $iD, $FROM_STARTED, $TO);
	}


	//order
	$customer[ORDR] = depthSearch(null, ORDR, 1, [], [CUST, EMP], $option);
	if ($withAnalysis) {
		$customer[ORDR . "Analysis"] = getGrowthRateWithCustomerIDAfterAndBefore("extended_order_refund", "quantity", $iD, $FROM_STARTED, $TO);
	}
	if ((!empty($customer[ORDR]) || !is_null($customer[ORDR]))) {
		$results = array_map(function ($tmp) {
			return $tmp['iD'];
		}, $customer[ORDR]);
		$op["KEY"] = "OrderID";
		$customer[ORDR_R] = depthSearch($results, ORDR_R, 1, [], true, $op);
	} else {
		$customer[ORDR_R] = array();
	}


	//purchases
	$customer[PURCH] = depthSearch(null, PURCH, 1, [], [CUST, EMP], $option);
	if ($withAnalysis) {
		$customer[PURCH . "Analysis"] = getGrowthRateWithCustomerIDAfterAndBefore("extended_purchases_refund", "quantity", $iD, $FROM_STARTED, $TO);
	}
	if ((!empty($customer[PURCH]) || !is_null($customer[PURCH]))) {
		$results = array_map(function ($tmp) {
			return $tmp['iD'];
		}, $customer[PURCH]);
		$op["KEY"] = "PurchaseID";
		$customer[PURCH_R] = depthSearch($results, PURCH_R, 1, [], true, $op);
	} else {
		$customer[PURCH_R] = array();
	}

	$customer[RI] = depthSearch(null, RI, 1, [], true, $option);
	if ($withAnalysis) {
		$customer[RI . "Analysis"] = getGrowthRateWithCustomerIDAfterAndBefore("extended_reservation_invoice", "quantity", $iD, $FROM_STARTED, $TO);
	}

	$customer[CRS] = depthSearch(null, CRS, 1, [], true, $option);

	$customer[CUT] = depthSearch(null, CUT, 1, [], true, $option);
	if ($withAnalysis) {
		$customer[CUT . "Analysis"] = getGrowthRateWithCustomerIDAfterAndBefore(CUT, "quantity", $iD, $FROM_STARTED, $TO);
	}

	//it was	$customer["previousBalance"]=getBalanceDueTo($iD,$E_FROM)["balance"];
	$customer["previousBalance"] = getBalanceDueTo($iD, $E_FROM)["balance"];
	$balanceResult = getBalanceDueFromTo($iD, $FROM, $TO);

	$customer["balance"] = $balanceResult["balance"];
	$customer["totalCredits"] = $balanceResult["sumPay"];
	$customer["totalDebits"] = $balanceResult["Sum_eq"];
	$customer["totalOrders"] = $balanceResult["Sum_ExtendedPrice"];
	$customer["totalPurchases"] = $balanceResult["Sum_sumPurchuses"];
	$customer["dateObject"] = $DateOrMonth;
	returnResponse($customer);
};
$API_ACTIONS["list_home"] = function () {

	$response = array();
	global $User;
	$response["user"] = $User;



	$date = date('Y-m-d');

	$lastSearchedProducts =	(checkRequestValue("lastSearchedProducts")) ? getRequestValue('lastSearchedProducts') : null;
	if (!is_null($lastSearchedProducts)) {
		//  $response["lastSearchedProducts"]=
	}
	$option = array();

	$option["WHERE_EXTENSION"] = " Date(`date`) >= '$date' AND Date(`endDate`) <= '$date' ORDER BY `date` DESC ";
	$response[HOME_IMAGE] = depthSearch(null, HOME_IMAGE, 1, [HOME_IMAGE_D], true, $option);
	$response[HOME_ADS] = depthSearch(null, HOME_ADS, 1, [HOME_ADS_D], true, $option);


	$results = getBestSellingType(5);
	$response["bestSellingTYPE"] = array();
	foreach ($results as $res) {
		$iD = $res['iD'];
		$product = depthSearch($iD, PR, 1, getRequireArrayTables(), getRequireObjectTable(), null);
		$product['total'] = $res['total'];
		array_push($response["bestSellingTYPE"], $product);
	}
};
$API_ACTIONS["list_not_used_records"] = function () {
	$table = getRequestValue('table');
	if (!isAdmin()) {
		returnPermissionResponse($table, 0);
	}
	if (($i = array_search($table, getAllTablesString())) === FALSE) {
		http_response_code(400);
		returnResponseErrorMessage("Bad request");
	}
	$requireObjects = getRequestValue('requireObjects');
	$response = array();
	$response["list"] = getNotUsedRecords($table);
	if (toBoolean($requireObjects)) {
		$response["listObjects"] = (depthSearch(($response["list"]), changeToExtendedTableFromNotUsedRecords($table), 1, [], true, null));
	}
	returnResponse($response);
};
$API_ACTIONS["list_changes_records_table"] = function () {
	$permission = getUserPermissionTable();
	$table = getRequestValue('table');
	//getNotUsedRecords($table);
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
function getOnlyName($iD, $tableName)
{
	return getFetshTableWithQuery("SELECT name from $tableName where iD=$iD");
}
function getValueToCalculateChangesRecord($tableName)
{
	switch ($tableName) {
		case CUST:
		case EMP:
			return "activated";
		case CUT:
			return "cut_status";
		case ORDR:
			return "status";
		case RI:
		case PR_INPUT:
		case PR_OUTPUT:
		case TR:
		case PURCH:

			return "quantity";
		case INC:
		case SP:
		case DEBT:
		case CRED:
			//fromBox isDirect
			return "value";
		default:
			return null;
	}
}
$API_ACTIONS["list_reduce_size"] = function () {
	$table = getRequestValue('table');
	$column = getRequestValue('<fieldToSelectList>');
	checkPermissionAction($table);
	$forgins = getTableColumns($table);
	// print_r($forgins);
	if ((($i = array_search((string)$column, $forgins)) === FALSE)) {
		returnResponseErrorMessage("Bad field non founded request ");
	}
	returnResponse(getFetshAllTableWithQuery("SELECT iD,$column FROM `$table`"));
};
$API_ACTIONS["list_dashboard_single_item"] = function () {
	$DateOrMonth;
	$IsDate;
	checkDateRequest($DateOrMonth, $IsDate);

	$FROM = date("Y-m-d", strtotime($DateOrMonth['from']));
	$TO = date("Y-m-d", strtotime($DateOrMonth['to']));
	$E_FROM = date('Y-m-d', (strtotime('-1 day', strtotime($E_FROM))));
	$permission = getUserPermissionTable();
	$table = getRequestValue('table');
	// 	jsonDecode(getRequestValue('date'));
	$interval = getRequestValue('interval');
	$customAction = jsonDecode(getRequestValue('customAction'));
	if (($i = array_search($table, getAllTablesString())) !== FALSE) {
	} else {
		http_response_code(400);
		returnResponseErrorMessage("Bad request");
	}
	$permission = checkPermissionForActionTableResultAndAction($permission, $table, "list");
	if (!$permission) {
		returnPermissionResponse($table, 0);
	}
	$response = array();

	$currentYear = date("Y");
	$currentYear = $currentYear . "-01-01";
	$option = array();

	if (isCustomer()) {
		$option["WHERE_EXTENSION"] = " `CustomerID`='" . getUserID() . " AND Date(`date`) >= '$FROM' AND Date(`date`) <= '$TO' ORDER BY `date` DESC ";
	} else {
		$option["WHERE_EXTENSION"] = " Date(`date`) >= '$FROM' AND Date(`date`) <= '$TO' ORDER BY `date` DESC ";
	}
	//	$response["responseList"]=depthSearch(null,$table,1,getRequireArrayTables(),true,$option);
	$customQuery = $customAction == null ? null : getQueryFromJson($customAction);
	if ($interval === "daily") {
		if (is_null($customQuery)) {
			$response["responseListAnalysis"] = getGrowthRateAfterAndBeforeDaysInterval(changeToExtendedTableDashboard($table), getValueToCalculateGrowthRate($table), $FROM, $TO);
		} else {
			$response["responseListAnalysis"] = getGrowthRateAfterAndBeforeDaysIntervalWithWhereQuery(changeToExtendedTableDashboard($table), getValueToCalculateGrowthRate($table), $FROM, $TO, $customQuery);
		}
	} else {
		if (is_null($customQuery)) {
			$response["responseListAnalysis"] = getGrowthRateAfterAndBefore(changeToExtendedTableDashboard($table), getValueToCalculateGrowthRate($table), $FROM, $TO);
		} else {
			$response["responseListAnalysis"] = getGrowthRateAfterAndBeforeWithWhereQuery(changeToExtendedTableDashboard($table), getValueToCalculateGrowthRate($table), $FROM, $TO, $customQuery);
		}
	}
	$response["date"] = $DateOrMonth;
	$response["enteryInteval"] = $interval;
	returnResponse($response);
};
function getQueryFromJson($customAction)
{
	$query = "";
	foreach ($customAction as $key => $value) {
		$query .= "`$key` = '$value' ";
	}
	return $query;
}
function getValueToCalculateGrowthRate($tableName)
{
	switch ($tableName) {
		case RI:
		case PR_INPUT:
		case PR_OUTPUT:
		case TR:
		case PURCH:
		case ORDR:
			return "quantity";
		case INC:
		case SP:
		case DEBT:
		case CRED:
			return "value";
		case EQ:
			return "value";
		default:
			return "quantity";
	}
}
function changeToExtendedTableFromNotUsedRecords($tableName)
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
function changeToExtendedTableDashboard($tableName)
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
		case INC:
		case SP:
		case DEBT:
		case CRED:
			return "equality_" . $tableName;
	}
}
$API_ACTIONS["list_dashboard_orders_overdues"] = function () {
	checkPermissionAction(ORDR);
	//   $DateOrMonth;
	//	$IsDate;
	//	checkDateRequest($DateOrMonth,$IsDate);

	//	$FROM = date("Y-m-d",strtotime($DateOrMonth['from']));
	//	$TO = date("Y-m-d",strtotime($DateOrMonth['to']));
	//	$E_FROM= date('Y-m-d',(strtotime ( '-1 day' , strtotime ( $TO) ) ));
	returnResponse(invoicesOverduesAndDesposited($FROM, $TO));
};
$API_ACTIONS["list_dashboard"] = function () {
	$DateOrMonth;
	$IsDate;
	checkDateRequest($DateOrMonth, $IsDate);
	$FROM_STARTED = date("Y-m-d", strtotime("2020-01-01"));
	$FROM = date("Y-m-d", strtotime($DateOrMonth['from']));
	$TO = date("Y-m-d", strtotime($DateOrMonth['to']));
	$E_FROM = date('Y-m-d', (strtotime('-1 day', strtotime($FROM))));
	$permission = getUserPermissionTable();
	$interval = null;
	if (checkRequestValue('interval')) {
		$interval = getRequestValue('interval');
	}

	//	print_r($permission);
	if (isEmployee() || isCustomer()) {
		$currentYear = $FROM;

		$option = array();
		if (isCustomer()) {
			$option["WHERE_EXTENSION"] = " `CustomerID`='" . getUserID() . "' ORDER BY `date` DESC ";
		} else {
			//old 16-12-2023
			$option["WHERE_EXTENSION"] = $IsDate ?
				("Date(`date`) = '$TO' ORDER BY `date` DESC ") : ("Month(`date`) = '$TO' ORDER BY `date` DESC ");

			$option["WHERE_EXTENSION"] = " Date(`date`) >= '$FROM' AND Date(`date`) <= '$TO' ORDER BY `date` DESC ";
		}

		if (isEmployee()) {

			if (checkPermissionForActionTableResultAndAction($permission, PR_INPUT, "list")) {
				$response[PR_INPUT] = depthSearch(null, PR_INPUT, 1, [], [CUST, EMP], $option);
			}
			if (checkPermissionForActionTableResultAndAction($permission, PR_OUTPUT, "list")) {
				$response[PR_OUTPUT] = depthSearch(null, PR_OUTPUT, 1, [], [CUST, EMP], $option);
			}
			if (checkPermissionForActionTableResultAndAction($permission, TR, "list")) {
				$response[TR] = depthSearch(null, TR, 1, [], true, $option);
			}
			if (checkPermissionForActionTableResultAndAction($permission, ORDR, "list")) {
				$response["notPayedCustomers"] = notPayedCustomers();
				$response["customerToPayNext"] = customerToPayNext();
			}
			if (checkPermissionForActionTableResultAndAction($permission, SP, "list")) {
				$response[SP] = depthSearch(null, SP, 1, [], true, $option);
				$response[SP . "Analysis"] = getGrowthRateAfterAndBefore("equality_" . SP, "value", $FROM_STARTED, $TO);
			}
			if (checkPermissionForActionTableResultAndAction($permission, INC, "list")) {
				$response[INC] = depthSearch(null, INC, 1, [], true, $option);
				$response[INC . "Analysis"] = getGrowthRateAfterAndBefore("equality_" . INC, "value", $FROM_STARTED, $TO);
			}

			$response[DEBT . "Due"] = balanceDue(DEBT, $TO);
			$response[CRED . "Due"] = balanceDue(CRED, $TO);
			$response[INC . "Due"] = balanceDue(INC, $TO);
			$response[SP . "Due"] = balanceDue(SP, $TO);


			$response[DEBT . "BalanceToday"] = balanceDueFromTo(DEBT, $FROM, $TO);
			$response[CRED . "BalanceToday"] = balanceDueFromTo(CRED, $FROM, $TO);
			$response[INC . "BalanceToday"] = balanceDueFromTo(INC, $FROM, $TO);
			$response[SP . "BalanceToday"] = balanceDueFromTo(SP, $FROM, $TO);


			if ($IsDate) {
				$response["previous" . DEBT . "Due"] = balanceDuePrevious(DEBT, $FROM);
				$response["previous" . CRED . "Due"] = balanceDuePrevious(CRED, $FROM);
				$response["previous" . INC . "Due"] = balanceDuePrevious(INC, $FROM);
				$response["previous" . SP . "Due"] = balanceDuePrevious(SP, $FROM);
			}
		}
		if (hasPermissionForDashboardIf($permission, CRED, "list")) {
			$response[CRED] = depthSearch(null, CRED, 1, [], true, $option);

			if ($interval === "daily") {
				$response[CRED . "Analysis"] = getGrowthRateAfterAndBeforeDaysInterval(changeToExtendedTableDashboard(CRED), getValueToCalculateGrowthRate(CRED), $FROM_STARTED, $TO);
			} else {
				$response[CRED . "Analysis"] = getGrowthRateAfterAndBefore("equality_" . CRED, "value", $FROM_STARTED, $TO);
			}
		}
		if (hasPermissionForDashboardIf($permission, DEBT, "list")) {
			$response[DEBT] = depthSearch(null, DEBT, 1, [], true, $option);
			if ($interval === "daily") {
				$response[DEBT . "Analysis"] = getGrowthRateAfterAndBeforeDaysInterval(changeToExtendedTableDashboard(DEBT), getValueToCalculateGrowthRate(DEBT), $FROM_STARTED, $TO);
			} else
				$response[DEBT . "Analysis"] = getGrowthRateAfterAndBefore("equality_" . DEBT, "value", $FROM_STARTED, $TO);
		}
		if (hasPermissionForDashboardIf($permission, ORDR, "list")) {
			$response[ORDR] = depthSearch(null, ORDR, 1, [ORDR_D], [EQ, CUST, EMP], $option);
			$response[ORDR . "Analysis"] = getGrowthRateAfterAndBefore("extended_order_refund", "quantity", $FROM_STARTED, $TO);
		}
		if ((!empty($response[ORDR]) || !is_null($response[ORDR])) && isCustomer()) {
			$results = array_map(function ($tmp) {
				return $tmp['iD'];
			}, $response[ORDR]);
			$op["KEY"] = "OrderID";
			if (hasPermissionForDashboardIf($permission, ORDR_R, "list")) {
				$response[ORDR_R] = depthSearch($results, ORDR_R, 1, [ORDR_R_D], true, $op);
			}
		} else {
			if (isCustomer()) {
				$response[ORDR_R] = array();
			} else {
				if (hasPermissionForDashboardIf($permission, ORDR_R, "list")) {
					$response[ORDR_R] = depthSearch(null, ORDR_R, 1, [], true, $option);
				}
			}
		}

		if (hasPermissionForDashboardIf($permission, PURCH, "list")) {
			//PURCH_D
			$response[PURCH] = depthSearch(null, PURCH, 1, [], [CUST, EMP], $option);
			$response[PURCH . "Analysis"] = getGrowthRateAfterAndBefore("extended_purchases_refund", "quantity", $FROM_STARTED, $TO);
		}
		if ((!empty($response[PURCH]) || !is_null($response[PURCH])) && isCustomer()) {
			$results = array_map(function ($tmp) {
				return $tmp['iD'];
			}, $response[PURCH]);
			$op["KEY"] = "PurchaseID";
			if (hasPermissionForDashboardIf($permission, PURCH_R, "list")) {
				$response[PURCH_R] = depthSearch($results, PURCH_R, 1, [], true, $op);
			}
		} else {
			if (isCustomer()) {
				$response[PURCH_R] = array();
			} else {
				if (hasPermissionForDashboardIf($permission, PURCH_R, "list")) {
					$response[PURCH_R] = depthSearch(null, PURCH_R, 1, [], true, $option);
				}
			}
		}
		if (hasPermissionForDashboardIf($permission, CUT, "list")) {
			$response[CUT] = depthSearch(null, CUT, 1, [], true, $option);
			$response[CUT . "Analysis"] = getGrowthRateAfterAndBefore(CUT, "quantity", $FROM_STARTED, $TO);
		}
		if (hasPermissionForDashboardIf($permission, RI, "list")) {
			$response[RI] = depthSearch(null, RI, 1, [], [CUST, EMP], $option);
			$response[RI . "Analysis"] = getGrowthRateAfterAndBefore("extended_reservation_invoice", "quantity", $FROM_STARTED, $TO);
		}

		if (hasPermissionForDashboardIf($permission, CRS, "list")) {
			$response[CRS] = depthSearch(null, CRS, 1, [], [CUST, EMP], $option);
		}

		//pending reservation
		if (hasPermissionForDashboardIf($permission, RI, "list")) {
			if (isCustomer()) {
				$option["WHERE_EXTENSION"] = "`CustomerID`='" . getUserID() . "' AND Date(`termsDate`) > Date('$TO') AND ORDER BY `date` DESC ";
			} else {
				$option["WHERE_EXTENSION"] = "Date(`termsDate`) > Date('$TO') ORDER BY `date` ASC ";
			}
			$response["pending_reservation_invoice"] = depthSearch(null, RI, 1, true, true, $option);

			if (isCustomer()) {
				$option["WHERE_EXTENSION"] = "`CustomerID`='" . getUserID() . "' AND Date(`termsDate`) < Date('$TO') AND ORDER BY `date` DESC ";
			} else {
				$option["WHERE_EXTENSION"] = "Date(`termsDate`) < Date('$TO') ORDER BY `date` ASC ";
			}
			$response["overdue_reservation_invoice"] = depthSearch(null, RI, 1, [], true, $option);
		}

		//overdue reservation

		if (hasPermissionForDashboardIf($permission, CUT, "list")) {
			$query = "";
			if (isCustomer()) {
				$iD = getUserID();
				$query = "WHERE `CustomerID`='$iD'";
			}

			$results = getFetshALLTableWithQuery("SELECT `CutRequestID` FROM `pending_cut_requests`  $query");
			if (!empty($results) || !is_null($results)) {
				$results = array_map(function ($tmp) {
					return $tmp['CutRequestID'];
				}, $results);
				$response["pending_cut_requests"] = depthSearch($results, CUT, 1, [], true, null);
			} else {
				$response["pending_cut_requests"] = array();
			}
		}
		$response["date"] = $DateOrMonth;
		returnResponse($response);
	}
};
//from to 
$API_ACTIONS["list_fund"] = function () {
	$DateOrMonth;
	$IsDate;
	checkDateRequest($DateOrMonth, $IsDate);

	$FROM = date("Y-m-d", strtotime($DateOrMonth['from']));
	$TO = date("Y-m-d", strtotime($DateOrMonth['to']));
	$E_FROM = date('Y-m-d', (strtotime('-1 day', strtotime($FROM))));


	if (isEmployee()) {

		$option = array();
		$option["WHERE_EXTENSION"] = " Date(`date`) >= '$FROM' AND Date(`date`) <= '$TO' ORDER BY `date` DESC ";

		$response[SP] = depthSearch(null, SP, 1, [], [EQ, CUST, EMP, WARE, AC_NAME], $option);
		$response[SP . "Analysis"] = getGrowthRateAfterAndBefore("equality_" . SP, "value", $FROM, $TO);

		$response[INC] = depthSearch(null, INC, 1, [], [EQ, CUST, EMP, WARE, AC_NAME], $option);
		$response[INC . "Analysis"] = getGrowthRateAfterAndBefore("equality_" . INC, "value", $FROM, $TO);

		$response[DEBT] = depthSearch(null, DEBT, 1, [], [EQ, CUST, EMP, WARE], $option);
		$response[DEBT . "Analysis"] = getGrowthRateAfterAndBefore("equality_" . DEBT, "value", $FROM, $TO);

		$response[CRED] = depthSearch(null, CRED, 1, [], [EQ, CUST, EMP, WARE], $option);
		$response[CRED . "Analysis"] = getGrowthRateAfterAndBefore("equality_" . CRED, "value", $FROM, $TO);


		$response[DEBT . "Due"] = balanceDue(DEBT, $TO);
		$response[CRED . "Due"] = balanceDue(CRED, $TO);
		$response[INC . "Due"] = balanceDue(INC, $TO);
		$response[SP . "Due"] = balanceDue(SP, $TO);


		$response[DEBT . "BalanceToday"] = balanceDueFromTo(DEBT, $FROM, $TO);
		$response[CRED . "BalanceToday"] = balanceDueFromTo(CRED, $FROM, $TO);
		$response[INC . "BalanceToday"] = balanceDueFromTo(INC, $FROM, $TO);
		$response[SP . "BalanceToday"] = balanceDueFromTo(SP, $FROM, $TO);

		if ($IsDate) {
			$response["previous" . DEBT . "Due"] = balanceDuePrevious(DEBT, $FROM);
			$response["previous" . CRED . "Due"] = balanceDuePrevious(CRED, $FROM);
			$response["previous" . INC . "Due"] = balanceDuePrevious(INC, $FROM);
			$response["previous" . SP . "Due"] = balanceDuePrevious(SP, $FROM);
		}

		$response["date"] = $DateOrMonth;
		returnResponse($response);
	}
};
$API_ACTIONS["list_most_popular_products"] = function () {
	$results = getBestSellingType(5);
	$response = array();
	foreach ($results as $res) {
		$iD = $res['iD'];
		$product = depthSearch($iD, PR, 1, [], true, null);
		$response[] = $product;
	}
	returnResponse($response);
};
//date

$API_ACTIONS["list_product_analysis"] = function () {
	checkPermissionAction("list_profit_loses");

	$Limit = 5;
	if (checkRequestValue('limit')) {
		if (!checkRequestValueInt('limit')) {
			returnBadRequest("LIMIT");
		} else {
			$Limit = getRequestValue('limit');
		}
	}
	if (!checkRequestValue('iD')) {
		returnBadRequest("iDs should be action");
	}
	$DateOrMonth;
	$IsDate;
	checkDateRequest($DateOrMonth, $IsDate);

	$FROM = date("Y-m-d", strtotime($DateOrMonth['from']));
	$TO = date("Y-m-d", strtotime($DateOrMonth['to']));
	$E_FROM = date('Y-m-d', (strtotime('-1 day', strtotime($TO))));
	$results = getBestSellingSize($Limit);
	$response["bestSellingSize"] = array();
	foreach ($results as $res) {
		$iD = $res['iD'];
		$product = depthSearch($iD, PR, 1, getRequireArrayTables(), getRequireObjectTable(), null);
		$product['total'] = $res['total'];
		array_push($response["bestSellingSize"], $product);
	}

	$results = getBestSellingGSM($Limit);
	$response["bestSellingGSM"] = array();
	foreach ($results as $res) {
		$iD = $res['iD'];
		$product = depthSearch($iD, PR, 1, getRequireArrayTables(), getRequireObjectTable(), null);
		$product['total'] = $res['total'];
		array_push($response["bestSellingGSM"], $product);
	}

	$results = getBestSellingType($Limit);
	$response["bestSellingTYPE"] = array();
	foreach ($results as $res) {
		$iD = $res['iD'];
		$product = depthSearch($iD, PR, 1, getRequireArrayTables(), getRequireObjectTable(), null);
		$product['total'] = $res['total'];
		array_push($response["bestSellingTYPE"], $product);
	}

	$results = getBestProfitableType($Limit);
	$response["bestProfitableType"] = array();
	foreach ($results as $res) {
		$iD = $res['iD'];
		$product = depthSearch($iD, PR, 1, getRequireArrayTables(), getRequireObjectTable(), null);
		$product['iD'] = $res['iD'];
		$product['sellPrice'] = $res['sellPrice'];
		$product['purchasePrice'] = $res['purchasePrice'];
		$product['Count_ProductID'] = $res['Count_ProductID'];

		array_push($response["bestProfitableType"], $product);
	}
};

$API_ACTIONS["list_sales"] = function () {
	$Limit = 5;
	if (checkRequestValue('limit')) {
		if (!checkRequestValueInt('limit')) {
			returnBadRequest("LIMIT");
		} else {
			$Limit = getRequestValue('limit');
		}
	}
	$DateOrMonth;
	$IsDate;
	checkDateRequest($DateOrMonth, $IsDate);

	$FROM = date("Y-m-d", strtotime('2022-01-01'));
	$TO = date("Y-m-d", strtotime($DateOrMonth['to']));
	$E_FROM = date('Y-m-d', (strtotime('-1 day', strtotime($FROM))));

	//add option to which month or before a selected month;

	//	$results=getFetshALLTableWithQuery("SELECT `iD` FROM `".PR_SEARCH."` $Query");
	//if(!empty($results) || !is_null($results)){	
	//		$results = array_map(function($tmp) { return $tmp['iD']; }, $results);
	///	returnResponse(depthSearch(($results),PR,1,getRequireArrayTables(),getRequireObjectTable(),getOptions()));		
	//	}else{
	//    returnResponse(array());
	// no content
	//}




	$response[ORDR . "_offline_count"] = getGrowthRateAfterAndBeforeWithWhereQueryCount(changeToExtendedTableDashboard(ORDR), $FROM, $TO, " status = 'NONE'");

	$response[ORDR . "_online_count"] = getGrowthRateAfterAndBeforeWithWhereQueryCount(changeToExtendedTableDashboard(ORDR), $FROM, $TO, " status != 'NONE'");

	$response[CUST . "_count"] = getGrowthRateAfterAndBeforeCount(changeToExtendedTableDashboard(CUST), $FROM, $TO);

	$response[PR] = getGrowthRateAfterAndBeforeCount(changeToExtendedTableDashboard(PR), $FROM, $TO);


	$response["totalSalesQuantity"] = getGrowthRateAfterAndBefore(changeToExtendedTableDashboard(ORDR), "quantity", $FROM, $TO);

	$response["totalReturnsQuantity"] = getGrowthRateAfterAndBefore(changeToExtendedTableDashboard(ORDR), "refundQuantity", $FROM, $TO);

	$response["totalNetSalesQuantity"] = getGrowthRateAfterAndBefore(changeToExtendedTableDashboard(ORDR), "extendedNetQuantity", $FROM, $TO);


	$response["totalSalesPrice"] = getGrowthRateAfterAndBefore(changeToExtendedTableDashboard(ORDR), "extendedPrice", $FROM, $TO);

	$response["totalReturnsPrice"] = getGrowthRateAfterAndBefore(changeToExtendedTableDashboard(ORDR), "extendedRefundPrice", $FROM, $TO);

	$response["totalNetSalesPrice"] = getGrowthRateAfterAndBefore(changeToExtendedTableDashboard(ORDR), "extendedNetPrice", $FROM, $TO);

	//marging list_profit_loses and remove list_profit_loses??

	$response["profitsByOrder"] = getGrowthRateAfterAndBefore(changeToExtendedTableDashboard("profits_orders"), "total", $FROM, $TO);



	$response["profitsByCutRequests"] = getGrowthRateAfterAndBefore(changeToExtendedTableDashboard("profits_cut_requests_products"), "totalPrice", $FROM, $TO);

	$response["wastesByCutRequests"] = getGrowthRateAfterAndBefore(changeToExtendedTableDashboard("wasted_cut_requests_products"), "total", $FROM, $TO);



	$response[INC . "Due"] = balanceFromToAccountName("equality_" . INC, $FROM, $TO);
	$response[INC . "Analysis"] = getGrowthRateAfterAndBefore("equality_" . INC, "value", $FROM, $TO);
	$response[SP . "Due"] = balanceFromToAccountName("equality_" . SP, $FROM, $TO);
	$response[SP . "Analysis"] = getGrowthRateAfterAndBefore("equality_" . SP, "value", $FROM, $TO);
	//  $response[AC_NAME]=depthSearch(null,AC_NAME,1,[SP,INC],null,null);
	$response["dateObject"] = $DateOrMonth;

	return returnResponse($response);
};
//extends find way to remove employee customer not needed
$API_ACTIONS["list_profit_loses"] = function () {

	$response = array();
	$tomorrow = new DateTime('tomorrow');
	$EDate = $tomorrow->format('Y-m-d');


	$DateOrMonth;
	$IsDate;
	checkDateRequest($DateOrMonth, $IsDate);

	$FROM = date("Y-m-d", strtotime($DateOrMonth['from']));
	$TO = date("Y-m-d", strtotime($DateOrMonth['to']));
	$E_FROM = date('Y-m-d', (strtotime('-1 day', strtotime($FROM))));


	$response["profits"] = getProfitsByMonths($FROM, $TO);
	$response["wastes"] = getWastsByMonths();
	$response["bestProfitableType"] = getBestProfitableType();
	$response[DEBT . "Due"] = balanceDue(DEBT, $EDate);
	$response[CRED . "Due"] = balanceDue(CRED, $EDate);
	$response[INC . "Due"] = balanceDue(INC, $EDate);
	$response[SP . "Due"] = balanceDue(SP, $EDate);
	$response[AC_NAME] = depthSearch(null, AC_NAME, 1, [SP, INC], null, null);
	returnResponse($response);
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
$API_ACTIONS["tables"] = function () {

	if (isAdmin()) {
		$response = array();
		$response = getFetshAllTableWithQuery("show full tables");
		returnResponse($response);
	} else {
		returnPermissionResponse("tables", 0);
	}
	//   executeMultiQuery("SET FOREIGN_KEY_CHECKS=0;REPLACE INTO purchases VALUES(11,900,1,'2020-01-21 12:57:08',null,null);");
};
$API_ACTIONS["test"] = function () {
	echo "TEST";
	//  executeMultiQuery("SET FOREIGN_KEY_CHECKS=0;REPLACE INTO purchases VALUES(11,900,1,'2020-01-21 12:57:08',null,null);");
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
