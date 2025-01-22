<?php
// ini_set('display_errors', 0); // todo on publish comment this
// error_reporting(E_ERROR | E_WARNING | E_PARSE);  // todo on publish comment this

// require 'vendor/autoload.php';
require("db_config.php");
require("inc/config.php");
require("extension_api.php");
require __DIR__ . "/inc/bootstrap.php";
require_once("security.php");
require_once("php_utils.php");

require_once("notification_fucntions.php");

require_once("db_api.php");
require_once("db_utils.php");
require_once("db_functions.php");

require_once("sqlBalances.php");
require_once("sqlCustomers.php");
require_once("sqlProducts.php");
require_once("sqlAnalysis.php");
require_once("sqlMoneyFunds.php");
///deprecated mysql producst_prices no use for it we can use products_type

// use Fig\Http\Message\StatusCodeInterface;
// use Psr\Http\Message\ResponseInterface as Response;
// use Psr\Http\Message\ServerRequestInterface as Request;
// use Slim\Factory\AppFactory;
// use Slim\Factory\ServerRequestCreatorFactory;
// use Slim\Exception\NotFoundException;
// use Slim\Http\StatusCode;

// AppFactory::setSlimHttpDecoratorsAutomaticDetection(false);
// ServerRequestCreatorFactory::setSlimHttpDecoratorsAutomaticDetection(false);

// $app = AppFactory::create();

// /**
//  * The routing middleware should be added earlier than the ErrorMiddleware
//  * Otherwise exceptions thrown from it will not be handled by the middleware
//  */
// $app->addRoutingMiddleware(true, true, true);

// // $app->setBasePath("index.php");

// $app->setBasePath("/SaffouryPaper2/index.php");

// /**
//  * Add Error Middleware
//  *
//  * @param bool                  $displayErrorDetails -> Should be set to false in production
//  * @param bool                  $logErrors -> Parameter is passed to the default ErrorHandler
//  * @param bool                  $logErrorDetails -> Display error details in error log
//  * @param LoggerInterface|null  $logger -> Optional PSR-3 Logger  
//  *
//  * Note: This middleware should be added last. It will not handle any exceptions/errors
//  * for middleware added after it.
//  */
// // $errorMiddleware = $app->addErrorMiddleware(true, true, true);
// // $app->configureMode('production', function () use ($app) {
// // 	$app->config(array(
// // 		'log.enable' => true,
// // 		'debug' => false
// // 	));
// // });

// // // Only invoked if mode is "development"
// // $app->configureMode('development', function () use ($app) {
// // 	$app->config(array(
// // 		'log.enable' => false,
// // 		'debug' => true
// // 	));
// // });

// $app->get('/{tableName}', function (Request $req, Response $res, array $args) {

// 	$queryParams = $req->getQueryParams();
// 	$tableName = $args["tableName"];

// 	// $objcets = null;
// 	// $details = null;

// 	// $objects = $queryParams['objectTables'];
// 	// $details = $queryParams['detailTables'];

// 	// return $res;
// 	// echo " ds" . ($req);

// 	// print_r($queryParams);

// 	// $options = getOptions();
// 	// $res->getBody()->write($args["tableName"]);
// 	// return $res;

// 	// $data = depthSearch(null, $tableName, 1, [], [], $options);
// 	// print_r($data);


// 	$data = array('name' => 'Bob', 'age' => 40);
// 	$payload = json_encode($data);

// 	$res->getBody()->write(returnResponseSlim($payload));
// 	return $res->withHeader('Content-Type', 'application/json')->withStatus(200);
// });
// // $app->post('/', function (Request $req, Response $res, $args) {

// // 	// setUser();
// // 	$res->getBody()->write("dsadas");
// // 	return $res;
// // 	// return $res;
// // 	// echo " ds" . ($req);
// // 	// print_r($req);


// // 	$options = getOptions();
// // 	$data = depthSearch(null, getRequestValue('table'), getRecursiveLevel(), getRequireArrayTables(), getRequireObjectTable(), $options);
// // 	if (!is_null($options) &&  isset($options["COMPRESS"])) {
// // 		$res->getBody()->write(returnResponseSlim($data));
// // 	} else {
// // 		$res->getBody()->write(returnResponseSlim($data));
// // 	}
// // 	return $res;
// // });
// // $app->get('/', function (Request $request, Response $response, $args) {
// // 	$response->getBody()->write("Helgglo!");
// // 	return $response;
// // });

// // $c = $app->getContainer();
// // $c['phpErrorHandler'] = function ($c) {
// // 	return function ($request, $response, $error) use ($c) {
// // 		return $response->withStatus(500)
// // 			->withHeader('Content-Type', 'text/html')
// // 			->write($error);
// // 	};
// // };
// $app->run();
// die;


header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Headers: *');
header("Access-Control-Allow-Origin: Access-Control-Allow-Origin, Accept");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET,POST,HEAD,PUT");
header("Access-Control-Allow-Headers: Access-Control-Allow-Origin, Accept");
header("Access-Control-Allow-Headers: Access-Control-Allow-Origin, X-Requested-With, Content-Type, Accept, Origin, Authorization, X-Authorization,Platform");
ini_set('zlib.output_compression_level', 6);

$User;
$RequestTableColumns = array();

$acceptedRequest = array("action", "table");
$acceptedAction = array("list", "view", "add", "edit", "delete");
//	echo "AIT";
if (!authfunction(getAuthHeader())) returnAuthErrorMessage("Authorization serror");
//	echo "END AIT";
setUser();
//Checking request
checkRequest();
//Checking action
checkAction();
//$acceptedAction["exhange_rate"]=function 	

function getRecursiveLevel()
{
	if (checkRequestValue('recursiveLevel')) {
		if (!checkRequestValueInt('recursiveLevel')) {
			returnBadRequest('F');
		}
		return getRequestValue('recursiveLevel');
	}
	return 1;
}
function getRequireArrayTables()
{
	if (checkRequestValue('detailTables')) {
		if (toBoolean(getRequestValue('detailTables'))) {
			return (bool)getRequestValue('detailTables');
		}
		return jsonDecode(getRequestValue('detailTables'));
	}
	return array();
}
function getRequireObjectTable()
{
	if (checkRequestValue('objectTables')) {
		if (toBoolean(getRequestValue('objectTables'))) {
			return (bool)getRequestValue('objectTables');
		}
		return jsonDecode(getRequestValue('objectTables'));
	}
	return array();
}
function getDefaultAddOptions()
{
	$option["DEPTH_EDIT_ADD"] = true;
	$option["SEARCH_BEFORE_ADD"] = true;
	$option["ALLOW_UPDATE"] = true;
	$option["ALLOW_UPDATE_DETAILS"] = true;
	$option["IS_EDIT"] = false;
	$option["REMOVE_ZERO_VALUE"] = true;
	$option["ALLOW_UPDATE_OBEJCTS"] = false;
	//$option["DEPTH_LEVEL"]=checkRequestValue('depth_level')?getRequestValue('depth_level'):0;
	return $option;
}
function getAddOptions()
{
	$option["DEPTH_EDIT_ADD"] = checkRequestValue('depth') ? toBoolean(getRequestValue('depth')) : true;
	$option["SEARCH_BEFORE_ADD"] = checkRequestValue('sba') ? toBoolean(getRequestValue('sba')) : false;
	$option["DONT_SEARCH_BEFORE_ADD"] = checkRequestValue('sbaDontTables') ? jsonDecode(getRequestValue('sbaDontTables')) : null;
	$option["ALLOW_UPDATE"] = checkRequestValue('allow_update') ? toBoolean(getRequestValue('allow_update')) : false;
	$option["ALLOW_UPDATE_DETAILS"] = checkRequestValue('allow_update_details') ? toBoolean(getRequestValue('allow_update_details')) : true;
	$option["IS_EDIT"] = getRequestValue('action') == 'edit';
	$option["REMOVE_ZERO_VALUE"] = checkRequestValue('removeZeroValue') ? false : true;
	//	$option["DELETING_THEN_ADD"]=checkRequestValue('allowDeletingThenAdd')?true:false;
	$option["ALLOW_UPDATE_OBEJCTS"] = checkRequestValue('allowUpdateObjects') ? true : false;
	//$option["DEPTH_LEVEL"]=checkRequestValue('depth_level')?getRequestValue('depth_level'):0;
	return $option;
}
function getRequestDate()
{
	$date = jsonDecode(getRequestValue('date'));
	$date['from'] = date("Y-m-d", strtotime($date['from']));
	$date['to'] = date("Y-m-d", strtotime($date['to']));
	return $date;
}
function getOptions()
{
	global $RequestTableColumns;
	global $RequestTableColumnsCustom;
	$option = array();
	if (getRequestValue('action') === 'add' || getRequestValue('action') === 'edit') {
		return getAddOptions();
	}
	$SEARCH_QUERY = checkRequestValue('searchStringQuery');
	$SEARCH_BY_FIELD = checkRequestValue('searchByFieldName');
	$SEARCH_VA_BY_FIELD = checkRequestValue('searchViewAbstractByFieldName');
	if ($SEARCH_BY_FIELD || $SEARCH_VA_BY_FIELD) {
		$option["LIMIT"] = "LIMIT 10 ";
	}
	$ASC = checkRequestValue('ASC');
	$DESC = checkRequestValue('DESC');
	//which contains the last iD
	$COMPRESS = checkRequestValue('COMPRESS');
	$WHERE_EXTENSION = !empty($RequestTableColumns) || !empty($RequestTableColumnsCustom);
	$LIMIT = isListLimit();
	$DATE = checkRequestValue('date');
	$CUSTOM_JOIN_STRING = hasCustomJoinReturnJoinStringQuery(getRequestValue('table'));
	$CUSTOM_JOIN_BOOL = !is_null($CUSTOM_JOIN_STRING);
	if (!$ASC && !$DESC && !$WHERE_EXTENSION && !$LIMIT && !$DATE && !$SEARCH_QUERY && !$CUSTOM_JOIN_BOOL) return null;
	$tableName = getRequestValue('table');
	$tableNameIsEmpty = isEmptyString($tableName);
	if (!$tableNameIsEmpty) {
		$tableName = changeToExtended($tableName);
	}
	if ($CUSTOM_JOIN_BOOL) {
		$option["CUSTOM_JOIN"] = $CUSTOM_JOIN_STRING;
	}
	if ($ASC) {
		if ($tableNameIsEmpty) {
			$option["ORDER_BY_EXTENSTION"] = " ORDER BY `" . getRequestValue('ASC') . "` ASC ";
		} else {
			$option["ORDER_BY_EXTENSTION"] = " ORDER BY " . addslashes($tableName) . ".`" . getRequestValue('ASC') . "` ASC ";
		}
	}
	if ($DESC) {
		if ($tableNameIsEmpty) {
			$option["ORDER_BY_EXTENSTION"] = " ORDER BY `" . getRequestValue('DESC') . "` DESC ";
		} else {
			$option["ORDER_BY_EXTENSTION"] = " ORDER BY " . addslashes($tableName) . ".`" . getRequestValue('DESC') . "` DESC ";
		}
	}
	if ($SEARCH_QUERY) {

		$option["SEARCH_QUERY"] =  getSearchQueryMasterStringValue(getRequestValue('searchStringQuery'), $tableName);
		if (!empty($RequestTableColumnsCustom)) {

			$whereQuery = array();

			//this line to add AND VIA implode because the implode function does not add any value if array ===1
			foreach ($RequestTableColumnsCustom as $rtc) {
				$requestValue = getRequestValue("<" . $rtc . ">");
				$query = getCustomSearchQueryColumnReturnQuery($tableName, $rtc, $requestValue);
				if (!isEmptyString($query)) {
					$whereQuery[] =	$query;
				}
			}
			$joinedQuery = "";
			if (!empty($RequestTableColumns)) {
				$joinedQuery = ($option["WHERE_EXTENSION"] . " AND " . implode(" AND ", $whereQuery));
			} else {
				$joinedQuery = implode(" AND ", $whereQuery);
			}

			$option["WHERE_EXTENSION"] =  $joinedQuery;
		}
		if (!empty($RequestTableColumns)) {

			$whereQuery = array();

			//this line to add AND VIA implode because the implode function does not add any value if array ===1
			foreach ($RequestTableColumns as $rtc) {

				$requestValue = getRequestValue("<" . $rtc . ">");

				if (!isEmptyString($requestValue)) {
					$whereQuery[] =	$rtc . " LIKE '" . $requestValue . "'";
				}
			}

			$joinedQuery = "";
			$joinedQuery = implode(" AND ", $whereQuery);
			//echo $joinedQuery."   sdasda";
			$option["WHERE_EXTENSION"] =  $joinedQuery;
		}
	} else {
		if ($WHERE_EXTENSION) {
			$whereQuery = array();
			foreach ($RequestTableColumns as $rtc) {
				$requestValue = getRequestValue("<" . $rtc . ">");
				$isJson = isJson($requestValue);

				if ($rtc == 'date') {
					if ($isJson) {
						$jsonValue = json_decode($requestValue, true);
						$dates = array();
						if (is_array($jsonValue)) {
							foreach ($jsonValue as $d) {
								$dates[] = date('Y:m:d', strtotime($d));
							}
							$jsonArray = implode("','", $dates);
							if ($tableNameIsEmpty) {
								$query = " Date(`" . $rtc . "`) IN  ('" . $jsonArray . "') ";
							} else {
								$query = " Date(" . addslashes($tableName) . ".`" . $rtc . "`) IN  ('" . $jsonArray . "') ";
							}
						} else {
							returnBadRequest("Bad request requst table coulmn  should be array");
							die();
						}
					} else {
						if ($tableNameIsEmpty) {
							$query = " Date(`" . $rtc . "`) = Date('" . getRequestValue("<" . $rtc . ">") . "') ";
						} else {
							$query = " Date(" . addslashes($tableName) . ".`" . $rtc . "`) = Date('" . getRequestValue("<" . $rtc . ">") . "') ";
						}
					}
				} else {

					if ($isJson) {
						$jsonValue = json_decode($requestValue, true);
						if (is_array($jsonValue)) {
							$jsonArray = implode("','", $jsonValue);
							if ($tableNameIsEmpty) {
								$query = " `" . $rtc . "` IN ('" . $jsonArray . "') ";
							} else {
								$query = addslashes($tableName) . ".`" . $rtc . "` IN ('" . $jsonArray . "') ";
							}

							// echo "$query query "; 
						} else {
							returnBadRequest("Bad request requst table coulmn  should be array");
							die();
						}
					} else {
						if ($tableNameIsEmpty) {
							if ($SEARCH_BY_FIELD || $SEARCH_VA_BY_FIELD) {
								$query = " `" . $rtc . "` LIKE '%$requestValue%'";
							} else {
								$query = " `" . $rtc . "` = '" . $requestValue . "' ";
							}
						} else {
							if ($SEARCH_BY_FIELD || $SEARCH_VA_BY_FIELD) {
								$query = addslashes($tableName) . ".`" . $rtc . "` LIKE '%" . $requestValue . "%' ";
							} else {
								$query = addslashes($tableName) . ".`" . $rtc . "` = '" . $requestValue . "' ";
							}
						}
					}
				}

				$whereQuery[] =	$query;
			}
			$option["WHERE_EXTENSION"] = implode(" AND ", $whereQuery);
		}
		if (!empty($RequestTableColumnsCustom)) {
			$whereQuery = array();

			//this line to add AND VIA implode because the implode function does not add any value if array ===1
			foreach ($RequestTableColumnsCustom as $rtc) {
				$requestValue = getRequestValue("<" . $rtc . ">");
				$query = getCustomSearchQueryColumnReturnQuery($tableName, $rtc, $requestValue);
				if (!isEmptyString($query)) {
					$whereQuery[] =	$query;
				}
			}
			$joinedQuery = "";
			if (!empty($RequestTableColumns)) {
				$joinedQuery = ($option["WHERE_EXTENSION"] . " AND " . implode(" AND ", $whereQuery));
			} else {
				$joinedQuery = implode(" AND ", $whereQuery);
			}
			$option["WHERE_EXTENSION"] =  $joinedQuery;
		}
	}
	if ($LIMIT) {
		//todo start is changed here to count per page
		$From = getRequestValue('start');
		//todo end is changed here to page number
		$To = getRequestValue('end');
		$count_per_page = $From;
		$next_offset = $To * $count_per_page;
		$option["LIMIT"] = "LIMIT $count_per_page OFFSET $next_offset";
	}
	if ($DATE) {


		$date = getRequestDate();
		$from = $date['from'];
		$to = $date['to'];
		if (isset($option["WHERE_EXTENSION"])) {
			if ($tableNameIsEmpty) {
				$option["WHERE_EXTENSION"] = $option["WHERE_EXTENSION"] . " AND  Date(date)  >= '$from' AND Date(date)<= '$to'";
			} else {
				$option["WHERE_EXTENSION"] = $option["WHERE_EXTENSION"] . " AND  Date(" . addslashes($tableName) . ".`date`)  >= '$from' AND Date(" . addslashes($tableName) . ".`date`)<= '$to'";
			}
			//echo "IS SET";

		} else {
			if ($tableNameIsEmpty) {
				$option["WHERE_EXTENSION"] = "Date(date)  >= '$from' AND Date(date)<= '$to'";
			} else {
				$option["WHERE_EXTENSION"] = "Date(" . addslashes($tableName) . ".`date`)  >= '$from' AND Date(" . addslashes($tableName) . ".`date`)<= '$to'";
			}
		}
	}
	if ($COMPRESS) {
		$option["COMPRESS"] = true;
	}
	//print_r($option);
	return $option;
}
//function get	
function checkAction()
{
	checkPermissionAction(getPermissionFieldName());
	switch (getRequestValue('action')) {
		case 'list':
			$options = getOptions();
			$data = depthSearch(null, getRequestValue('table'), getRecursiveLevel(), getRequireArrayTables(), getRequireObjectTable(), $options);
			if (!is_null($options) &&  isset($options["COMPRESS"])) {
				returnResponseCompress($data);
			} else {
				returnResponse($data);
			}
			break;
		case 'view':
			$options = getOptions();
			$data = depthSearch(checkRequestValue('iD') ? getRequestValue('iD') : null, getRequestValue('table'), getRecursiveLevel(), getRequireArrayTables(), getRequireObjectTable(), $options);
			if (!is_null($options) &&  isset($options["COMPRESS"])) {
				returnResponseCompress($data);
			} else {
				returnResponse($data);
			}
			break;
		case 'add':
		case 'edit':
			$ob = json_decode(getRequestValue('data'), false);
			if (isArray($ob)) {
				$response = array();
				foreach ($ob as $singleObject) {
					array_push($response, addEditObject($singleObject, getRequestValue('table'), getOptions()));
				}
				returnResponse($response);
			} else {
				returnResponse(addEditObject($ob, getRequestValue('table'), getOptions()));
			}

			//checkPermission
			break;
		case 'delete':
			returnResponse(deleteObject(
				checkRequestValue('iD') ? getRequestValue('iD') : getRequestValue('data'),
				getRequestValue('table'),
				checkRequestValue('notify') ? getRequestValue('notify') : true
			));
			//delete
			//checkPermission
			break;
	}
}
function checkRequest()
{
	global $acceptedRequest, $acceptedAction;
	//here we handle api extentions first
	if (in_array(getRequestValue('action'), array_keys($GLOBALS["API_ACTIONS"]))) {
		checkPermissionAction(getRequestValue('action'));
		$func = $GLOBALS["API_ACTIONS"][getRequestValue('action')];
		if (is_callable($func))
			$func();
		die;
	}
	foreach ($acceptedRequest as $request) {
		if (!checkRequestValue($request) || empty(getRequestValue($request))) {
			http_response_code(400);
			returnResponseErrorMessage(" checkRequest Bad request");
		}
	}
	if (($i = array_search(getRequestValue('table'), getAllTablesString())) !== FALSE) {
	} else {
		http_response_code(400);
		returnResponseErrorMessage("Bad request");
	}
	if (($i = array_search(getRequestValue('action'), $acceptedAction)) !== FALSE) {
		checkIfRequirePost();
	} else {
		http_response_code(400);
		returnResponseErrorMessage("Bad request no");
	}
}

function checkEditAddRequest()
{
	if (!checkRequestValue('data')) {
		http_response_code(400);
		returnResponseErrorMessage("Bad request data is empty");
	} else {
		if (!isJson(getRequestValue('data'))) {
			http_response_code(400);
			returnResponseErrorMessage("Bad request data should be json");
		}
	}
	if (checkRequestValue('depthAddEdit')) {
		if (!toBoolean(getRequestValue('depthAddEdit'))) {
			http_response_code(400);
			returnResponseErrorMessage("Bad request depthAddEdit should be bool");
		}
	}
}
function checkDeleteRequest()
{
	if (!is_numeric(getRequestValue('iD')) && ! is_array(jsonDecode(getRequestValue('iD')))) {
		http_response_code(400);
		returnResponseErrorMessage("Bad request iD should be integer or array of integer or json");
	}
}
function validateRequest()
{
	if (getRequestValue('action') === 'delete' || getRequestValue('action') === 'view') {
		global $RequestTableColumns;
		if (!empty($RequestTableColumns)) {
		} else if (!checkRequestValue('iD') && !checkRequestValue('data')) {
			http_response_code(400);
			returnResponseErrorMessage("Bad request no data or iD is set");
		} else if (checkRequestValue('iD')) {
			checkDeleteRequest();
		} else if (checkRequestValue('data')) {
			checkEditAddRequest();
		}
	} else {
		// add edit should be json
		checkEditAddRequest();
	}
}
// check start or end
function isListLimit()
{
	return checkRequestValue('start');
}
function validateRequestDAndOTables()
{
	if (checkRequestValue('objectTables')) {
		if (!toBoolean(getRequestValue('objectTables'))) {
			if (!isJson(getRequestValue('objectTables'))) {
				returnBadRequest("Bad request objectTables should be array or false if none or true if all");
			}
		}
	}
	if (checkRequestValue('detailTables')) {
		if (!toBoolean(getRequestValue('detailTables'))) {
			if (!is_array(jsonDecode(getRequestValue('detailTables')))) {
				returnBadRequest("Bad request detailTables should be array or false if none or true if all");
			}
		}
	}
	if (checkRequestValue('sbaDontTables')) {
		if (!toBoolean(getRequestValue('sbaDontTables'))) {
			if (!is_array(jsonDecode(getRequestValue('sbaDontTables')))) {
				returnBadRequest("Bad request sbaDontTables should be array");
			}
		}
	}
	$ASC = checkRequestValue('ASC');
	$DESC = checkRequestValue('DESC');

	$SEARCH_QUERY = checkRequestValueWithoutCheckingEmpty('searchStringQuery');
	if ($SEARCH_QUERY) {
		if (isEmptyString(getRequestValue('searchStringQuery'))) {

			returnResponse(array());
			die();
		}
	}
	$tableColumns = array();
	$tableName = getRequestValue('table');
	$customTableColumns = hasCustomSearchColumnReturnListCustoms($tableName);
	if ($DESC) {
		if ($ASC) {
			returnBadRequest("DESC or ASC one should set only");
		}
		$tableColumns = getTableColumns($tableName);
		if (!in_array(getRequestValue('DESC'), ($tableColumns))) {
			returnBadRequest("DESC col not found");
		}
	}
	if ($ASC) {
		$tableColumns = getTableColumns($tableName);
		if (!in_array(getRequestValue('ASC'), ($tableColumns))) {
			returnBadRequest("DESC col not found");
		}
	}
	$requestAttributes = getListRequestAction();
	global $RequestTableColumns;
	global $RequestTableColumnsCustom;
	$RequestTableColumns = array();
	$RequestTableColumnsCustom = array();

	foreach (array_keys($requestAttributes) as $ra) {
		$tableColumn = getTableColumnFromRequest($ra);

		if (isTableColumn($ra)) {
			if (empty($tableColumns)) {
				$tableColumns = getTableColumns($tableName);
			}
			if (!in_array($tableColumn, ($tableColumns))) {
				if (!in_array($tableColumn, ($customTableColumns))) {
					returnBadRequest("$ra is not founded in table ");
				} else {
					if (empty(getRequestValue($ra))) {
						returnBadRequest("$ra is empty ");
					}
					$RequestTableColumnsCustom[] = $tableColumn;
				}
			} else {
				if (empty(getRequestValue($ra))) {
					returnBadRequest("$ra is empty ");
				}
				$RequestTableColumns[] = $tableColumn;
			}
		}
	}
}
function validateRequestDAndOTablesAndSetColumnToAction($tableName)
{
	global $RequestTableColumns;
	$tableColumns = getTableColumns($tableName);
	// print_r($tableColumns);
	$requestAttributes = getListRequestAction();
	$RequestTableColumns = array();
	foreach (array_keys($requestAttributes) as $ra) {
		if (isTableColumn($ra)) {
			if (empty($tableColumns)) {
				$tableColumns = getTableColumns($tableName);
			}
			if (!in_array(
				getTableColumnFromRequest($ra),
				($tableColumns)
			)) {
				returnBadRequest("$ra is not founded in table $tableName ");
			} else {
				if (empty(getRequestValue($ra))) {
					returnBadRequest("$ra is empty ");
				}
				$RequestTableColumns[] = getTableColumnFromRequest($ra);
			}
		}
	}
}
function checkIfRequirePost()
{
	if (
		getRequestValue('action') === 'add' || getRequestValue('action') === 'edit'
		|| getRequestValue('action') === 'delete'
	) {
		if (getReqestMethod() != 'POST') {
			http_response_code(400);
			returnResponseErrorMessage("Bad request add edit delete should be post ");
		} else {
			validateRequest();
		}
	} else if (getRequestValue('action') === 'view') {
		validateRequestDAndOTables();
		validateRequest();
	} else if (getRequestValue('action') === 'list') {
		validateRequestDAndOTables();
		if (checkRequestValue('start')) {
			if (!is_numeric(getRequestValue('start')) || !checkRequestValue('end')) {
				http_response_code(400);
				returnResponseErrorMessage("Bad request start should be integer or end not set");
			}
		}
		if (checkRequestValue('end')) {
			if (!is_numeric(getRequestValue('end')) || !checkRequestValue('start')) {
				http_response_code(400);
				returnResponseErrorMessage("Bad request start should be integer or end not set");
			}
		}
	}
}
