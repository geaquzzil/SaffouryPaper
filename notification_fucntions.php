<?php
//Firebase
define('CONTENT_TYPE', 'Content-Type: application/json');
define('FB_AUTH', 'Authorization:key=AAAALy4dvOs:APA91bHTtKwlwqV3DBRbPK6uU7WRZFZarxmwWkgB4srZ75mmfMhm_M0rgAVOd2-ViZcA55amDClZ_HjL_7IeH2NozYtF0hcYEAPP1_MpAdV4mUjO5aNUvZzBJiNEfLSjhPBgfDpKWWy7');
define('FB_URL', 'https://fcm.googleapis.com/v1/projects/falconpaper-c7f81/messages:send');

define('FB_GENERAL_NOTIFCATION', 'FB_GENERAL_NOTIFCATION');
define('FB_NEW_PRODUCT', 'FB_NEW_PRODUCT');

define('FB_LOG_OUT', 'FB_LOG_OUT');

define('FB_GOOD_MOR', 'FB_GOOD_MOR');
define('FB_GOOD_BYE', 'FB_GOOD_BYE');

define('FB_SETTING_CHANGED', 'setting');

define('FB_RECALL', 'FB_RECALL');
define('FB_RETURN', 'FB_RETURN');

define('FB_ORDER_REMINDER', 'FB_ORDER_REMINDER');

define('FB_NOTIFICATION_TYPE', 'type');
define('FB_NOTIFICATION_MESSAGE', 'object');
define('FB_NOTIFICATION_DATA', 'data');
define('FB_REG_ID', 'registration_ids');
define('FB_NOTIFICATION_TO', 'to');
define('FB_TOPICS', '/topics/New');

$acceptedNotifications =
	array(
		//extenstions
		FB_SETTING_CHANGED,
		FB_GENERAL_NOTIFCATION,
		FB_NEW_PRODUCT,
		FB_LOG_OUT,

		FB_GOOD_MOR,
		FB_GOOD_BYE,

		FB_RETURN,
		FB_RECALL,
		FB_ORDER_REMINDER,

		PR_INPUT,
		PR_OUTPUT,
		CUT,
		CUT_RESULT,
		TR,
		PURCH,
		PURCH_R,
		ORDR,
		ORDR_R,
		RI,
		SP,
		INC,
		DEBT,
		CRED
	);



function send_notification($tokens, $message)
{
	if (isNotificationDisabled() || empty($tokens)) return;
	$url = FB_URL;
	$headers = array(
		FB_AUTH,
		CONTENT_TYPE
	);
	$fields = array(
		FB_REG_ID => $tokens,
		FB_NOTIFICATION_DATA => $message
	);


	$object = array();
	$object['json'] =  substr(json_encode($message['message']), 1, -1);
	$object['tokens'] = json_encode($tokens);


	$ob = json_decode(json_encode($object), false);
	// print_r($ob);
	addEditObjectWithoutNoti($ob, "notifications", getOptions());

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
	$result = curl_exec($ch);
	if ($result === FALSE) {
		//    die('Curl failed: ' . curl_error($ch));
		// die();
		return null;
	}
	curl_close($ch);
	//  print_r($result);
	return $result;
}
function send_notification_new($object, $ACTION)
{
	if (isNotificationDisabled()) return;
	$json = array();
	$json[FB_NOTIFICATION_TYPE] = $ACTION;
	$json[FB_NOTIFICATION_MESSAGE] = json_encode($object);
	$message = array(
		FB_NOTIFICATION_TO => FB_TOPICS,
		FB_NOTIFICATION_DATA => array("message" => json_encode($json))
	);
	$message_status = send_to_topic($message);
}
function send_notification_all($object, $ACTION)
{
	if (isNotificationDisabled() && $ACTION != FB_SETTING_CHANGED) return;

	$json = array();
	$json[FB_NOTIFICATION_TYPE] = $ACTION;
	$json[FB_NOTIFICATION_MESSAGE] = json_encode($object);
	$message = array(
		FB_NOTIFICATION_TO => FB_TOPICS,
		FB_NOTIFICATION_DATA => array("message" => json_encode($json))
	);
	$message_status = send_to_topic($message);
}
function send_to_topic($arrayData)
{
	if (isNotificationDisabled()) return;
	$url = FB_URL;
	$headers = array(
		FB_AUTH,
		CONTENT_TYPE
	);
	$fields = $arrayData;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
	$result = curl_exec($ch);
	if ($result === FALSE) {
		//  die('Curl failed: ' . curl_error($ch));
		return null;
	}
	curl_close($ch);
	//print_r($result);
	//	echo " OK dfs";
	return $result;
}
// set object to null if u want only employees
function get_regestrations_id($object, $tableName)
{
	$tokens = array();
	//	echo " \n CUSTOMER dfds NUL \n";
	//	print_r($object);
	$customerID = is_null($object[CUST]) ? null : $object[CUST][ID];
	//	echo " \n CUSTOMER dfds NUL $customerID \n";
	if (!is_null($customerID)) {
		//  echo " \n CUSTOMER NOT NUL \n";
		$customerToken = getFetshTableWithQuery("SELECT `userlevelid`,`token` ,`activated` FROM " . CUST . " WHERE iD='" . $customerID . "' AND 
		token IS NOT NULL OR token =''");
		if (!empty($customerToken)) {
			if (checkNotificationPermission($customerToken["userlevelid"], $tableName) && $customerToken[ACTIVATION_FIELD] == 1) {
				$tokens[] = $customerToken["token"];
			}
		}
	}
	$employees = getFetshALLTableWithQuery("SELECT `userlevelid`,`token`,`activated` FROM " . EMP . " 
	WHERE `token` IS NOT NULL OR `token` =''");
	if (!empty($employees)) {
		// echo " \n employee dfds NUL  \n";
		foreach ($employees as $employeeToken) {
			if (checkNotificationPermission($employeeToken["userlevelid"], $tableName) && $employeeToken[ACTIVATION_FIELD] == 1) {
				$tokens[] = $employeeToken["token"];
			}
		}
	}
	//	print_r($tokens);
	return $tokens;
}
function getRegestrationsIDTable($object, $tableName)
{
	$id = is_numeric($object) ? $object : $object["iD"];
	$conn = mysqli_connect(DB_SERVER, DB_USER, DB_PASSWORD, $_SERVER['DB_NAME']);
	$sql = " Select token From " . $tableName . " WHERE iD='$id'";
	$result = mysqli_query($conn, $sql);
	$tokens = array();
	if (!is_bool($result)) {
		if (mysqli_num_rows($result) > 0) {
			while ($row = mysqli_fetch_assoc($result)) {
				$tokens[] = $row["token"];
			}
		}
	}
	mysqli_close($conn);
	return $tokens;
}

function send_reminder($customer, $message)
{
	// send_notification(get_regestrations_id_non_checkpermission($customer), get_notification_object($message, FB_GENERAL_NOTIFCATION));
}


// SEND ALL CUSTOMER AND EMPLOYESS ( ONLY NOT TOPICS )- ONLY CUSTOMER -  ONLY EMPLYOEE
function sendAll($object, $fbAction)
{
	send_notification(get_regestrations_id($object, $fbAction), get_notification_object($object, $fbAction));
}
function sendCustomer($object, $customer, $fbAction)
{
	send_notification(getRegestrationsIDTable($customer, CUST), get_notification_object($object, $fbAction));
}
function sendEmployees($object, $fbAction)
{
	send_notification(get_regestrations_id(null, $fbAction), get_notification_object($object, $fbAction));
}

function getCustomer($object)
{
	if (isset($object[KCUST])) return $object[KCUST];
	return null;
}
function getEmployee($object)
{
	if (isset($object[KEMP])) return $object[KEMP];
	return null;
}
function setBreifUsers(&$object)
{
	$object[CUST] = getBreifCustomer($object);
	$object[EMP] = getBreifEmployee($object);
}
function getBreifCustomer($object)
{
	$iD = getCustomer($object);
	$object[CUST] = is_null($iD) ? null : getFetshTableWithQuery("SELECT iD,name FROM " . CUST . " WHERE iD='$iD'");
	return $object[CUST];
}
function getBreifEmployee($object)
{
	$iD = getEmployee($object);
	$object[EMP] = is_null($iD) ? null : getFetshTableWithQuery("SELECT iD,name FROM " . EMP . " WHERE iD='$iD'");
	return $object[EMP];
}
function doNotificationEmployee($object, $action)
{
	doNotification($object, null, $action);
}
/// if its general notification then set table name  to null 
///
function doNotification($object, $tableName, $action)
{
	//   echo "  sad ";
	$object = (array)$object;
	global $acceptedNotifications;
	switch ($tableName) {
		case FB_SETTING_CHANGED:
			send_notification_all($object, FB_SETTING_CHANGED);
			return;
	}
	switch ($action) {
		case FB_GENERAL_NOTIFCATION:
			send_notification_all($object, $action);
			return;
		case FB_NEW_PRODUCT:
			send_notification_new($object, $action);
			return;
			// only enables on customers
		case FB_LOG_OUT:
			sendCustomer(null, $object, $action);
			return;
			//	case "delete":sendDelete($object,$tableName);return;

	}
	if (($i = array_search($tableName, $acceptedNotifications)) !== FALSE) {
		//   echo "  sad ";
		switch ($action) {
			case "delete":
				sendDelete($object, $tableName);
				break;
			//new -  edit
			default:
				sendNotificationProcess($object, $tableName);
				break;
		}
	}
}
function sendNotificationProcess($object, $tableName)
{
	if (isNotificationDisabled()) return;
	setBreifUsers($object);
	switch ($tableName) {
		case RI:
		case PR_INPUT:
		case PR_OUTPUT:
		case TR:
		case PURCH:
		case PURCH_R:
		case ORDR_R:
		case ORDR:
			sendAll(getFirebaseDetailsObject($object, $tableName), $tableName);
			break;
		case INC:
		case DEBT:
		case CRED:
		case SP:
			sendAll(getFirebaseObject($object, $tableName), $tableName);
			break;

		case CUT:
			CUT_RESULT:
			sendAll(getFirebaseObject($object, $tableName), $tableName);
			break;
	}
}


function sendDelete($object, $tableName)
{
	if (isNotificationDisabled()) return;
	setBreifUsers($object);
	$object["fb_edit"] = "delete";
	switch ($tableName) {
		case RI:
		case PR_INPUT:
		case PR_OUTPUT:
		case TR:
		case PURCH:
		case PURCH_R:
		case ORDR_R:
		case ORDR:
			sendAll(getFirebaseDetailsObject($object, $tableName), $tableName);
			break;
			break;
		case SP:
		case INC:
		case DEBT:
		case CRED:
			sendAll(getFirebaseObject($object, $tableName), $tableName);
			break;

		case CUT:
			CUT_RESULT:
			sendAll(getFirebaseObject($object, $tableName), $tableName);
			break;
	}
}
function getExtendedTableName($tableName)
{
	switch ($tableName) {
		case RI:
		case PR_INPUT:
		case PR_OUTPUT:
		case TR:
			return "extended_" . $tableName;
		case PURCH:
			return "extended_purchases_refund";
		case PURCH_R:
		case ORDR_R:
			return $tableName;
		case ORDR:
			return "extended_order_refund";
	}
}
function getFirebaseObject($object, $tableName)
{

	$ID = $object[ID];
	$employee = isSetKeyFromObjReturnValue($object, EMP);
	$customer = isSetKeyFromObjReturnValue($object, CUST);
	$accountName = isSetKeyFromObjReturnValue($object, "NameID");
	$equality = isSetKeyFromObjReturnValue($object, "EqualitiesID");
	$fromWarehouse = isSetKeyFromObjReturnValue($object, "fromWarehouse");
	$toWarehouse = isSetKeyFromObjReturnValue($object, "toWarehouse");
	$warehouse = isSetKeyFromObjReturnValue($object, "WarehouseID");

	if (!is_null($customer)) {
		$customerID = $customer[ID];
		$object[CUST]["balance"] = getBalanceDue($customerID)["balance"];
	}
	if (!empty($accountName)) {
		$object[AC_NAME] = depthSearch($accountName, AC_NAME, 1, [AC_NAME_TYPE], [AC_NAME_TYPE], null);
	}
	if (!empty($fromWarehouse)) {
		$object["fromWarehouse"] = depthSearch($fromWarehouse, WARE, 1, [], [], null);
	}
	if (!empty($toWarehouse)) {
		$object["toWarehouse"] = depthSearch($toWarehouse, WARE, 1, [], [], null);
	}
	if (!empty($warehouse)) {
		$object["warehouse"] = depthSearch($warehouse, WARE, 1, [], [], null);
	}
	if (!empty($equality)) {
		$object[EQ] = depthSearch($equality, EQ, 1, [CUR], [CUR], null);
	}

	switch ($tableName) {
		case CUT:

			$iD = $object['iD'];
			// echo " KUT $iD";
			$option = array();
			$option["WHERE_EXTENSION"] = " `PCRID` = '$iD' ";
			//require_once("db_api.php");
			//print_r(depthSearch(null,SIZE_CUT,1,[],[SIZE],$option));
			$object[SIZE_CUT] = depthSearch(null, SIZE_CUT, 1, [], [SIZE], $option);

			break;
	}
	//  print_r($object);
	return $object;
}
function getFirebaseDetailsObject($object, $tableName)
{
	$ID = $object[ID];
	$employee = $object[EMP];
	$customer = $object[CUST];
	$action = $object["fb_edit"];
	//	echo "\n $action  aa \n";
	$fromWarehouse = isSetKeyFromObjReturnValue($object, "fromWarehouse");
	$toWarehouse = isSetKeyFromObjReturnValue($object, "toWarehouse");
	$warehouse = isSetKeyFromObjReturnValue($object, "WarehouseID");


	$firebaseObject =
		getFetshTableWithQuery("SELECT * FROM "
			. getExtendedTableName($tableName) .
			" WHERE iD = '$ID'");

	if (empty($firebaseObject)) return null;

	$firebaseObject[EMP] = $employee;
	$firebaseObject[CUST] = $customer;
	if (!empty($fromWarehouse)) {
		$firebaseObject["fromWarehouse"] = depthSearch($fromWarehouse, WARE, 1, [], [], null);
	}
	if (!empty($toWarehouse)) {
		$firebaseObject["toWarehouse"] = depthSearch($toWarehouse, WARE, 1, [], [], null);
	}
	if (!empty($warehouse)) {
		$firebaseObject["warehouse"] = depthSearch($warehouse, WARE, 1, [], [], null);
	}
	if (!is_null($customer)) {
		$customerID = $customer[ID];
		$firebaseObject[CUST]["balance"] = getBalanceDue($customerID)["balance"];
	}
	$firebaseObject["fb_edit"] = $action;
	//	print_r($firebaseObject);
	return $firebaseObject;
}
function isNotificationDisabled()
{
	$sql = " SELECT DISABLE_NOTIFICATIONS FROM " . SETTING;
	$result = getFetshTableWithQuery($sql);
	$result = $result["DISABLE_NOTIFICATIONS"];
	return $result === 1 || $result == 1;
}
function get_notification_object($object, $ACTION)
{
	if (is_null($object)) return null;
	$json = array();
	$json[FB_NOTIFICATION_TYPE] = $ACTION;
	$json[FB_NOTIFICATION_MESSAGE] = json_encode($object);
	return array("message" => json_encode($json));
}
