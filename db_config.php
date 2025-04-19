<?php
function returnResponseCompress($response)
{
	if (is_null($response) || empty($response)) {
		//TODO remove comment on publish	http_response_code(204);
	}
	$data = (gzcompress(beforeReturnResponseObjectExtenstion(json_encode($response, JSON_NUMERIC_CHECK), getRequestValue('table')), 9));
	echo ($data);
	die;
}
function returnResponseSlim($response)
{
	if (is_null($response) || empty($response)) {
		//TODO remove comment on publish	http_response_code(204);
	}
	$data = "";
	if (isFlutterRequest()) {
		// $data=beforeReturnResponseObjectExtenstion((json_encode($response,JSON_NUMERIC_CHECK )),getRequestValue('table')); 

		$data = getResponseDataForFlutter($response);
	} else {
		$data = beforeReturnResponseObjectExtenstion((json_encode($response)), getRequestValue('table'));
	}
	return $data;
}
function returnResponse($response)
{
	if (is_null($response) || empty($response)) {
		//TODO remove comment on publish	http_response_code(204);
	}
	$data = "";
	if (isFlutterRequest()) {
		// $data=beforeReturnResponseObjectExtenstion((json_encode($response,JSON_NUMERIC_CHECK )),getRequestValue('table')); 


		$data = getResponseDataForFlutter($response);
	} else {
		$data = beforeReturnResponseObjectExtenstion(json_encode($response), getRequestValue('table'));
	}
	echo $data;
	die;
}
function getResponseDataForFlutter($data)
{
	$numeric = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
	$nonnumeric = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	preg_match_all("/\"[0\+]+(\d+)\"/", $nonnumeric, $vars);
	foreach ($vars[0] as $k => $v) {
		$numeric = preg_replace("/\:\s*{$vars[1][$k]},/", ": {$v},", $numeric);
	}
	return $numeric;
}
function isFlutterRequest()
{
	$headers = apache_request_headers();
	$result = isset($headers['platform']) && ($headers['platform'] == 'Flutter' || $headers['platform'] === 'Flutter')  ||
		isset($headers['Platform']) && ($headers['Platform'] == 'Flutter' || $headers['Platform'] === 'Flutter');
	return $result;
}
function getResponseData(&$response)
{
	$data = json_decode(json_encode($response, JSON_NUMERIC_CHECK), true);
	$response = json_encode(replaceIntToStringFrom($data));
}
function replaceIntToStringFrom($array)
{
	foreach ($array as $key => $value) {


		if ($key == 'password') {
			setKeyValueFromObj($array, $key, (string)$value);
		}
		if ($key == 'phone') {
			setKeyValueFromObj($array, $key, (string)"0" . $value);
		}
		if ($key == CUST || $key == EMP) {
			if (is_object($value)) {
				$array[$key] = replaceIntToStringFrom($array[$key]);
			}
			if (is_array($value)) {
				$array[$key] = replaceIntToStringFrom($array[$key]);
			}
		}
	}
	return $array;
}
function returnAuthErrorMessage($response)
{
	http_response_code(401);
	returnResponseErrorMessage($response);
}
function returnServerError($response)
{
	http_response_code(500);
	returnResponseErrorMessage($response);
}
function returnBadRequest($response)
{
	http_response_code(400);
	returnResponseErrorMessage($response);
}

function returnResponseErrorMessage($response)
{
	$json = array();
	$json["error"] = true;
	$json["message"] = $response;
	$response = array();
	$response["serverResponse"] = array();
	$response["serverResponse"] = $json;
	echo (json_encode($response));
	die;
}
function returnResponseMessage($resopnse)
{
	$json = array();
	$json["response"] = $resopnse;
	echo (json_encode($json));
	die;
}
function returnArrayResponseMessage($resopnse, $array)
{
	$json = array();
	global $User;
	$json[LOGIN] = $User[LOGIN];
	$json[PERV] = $User[PERV];
	$json["RESPONSE"] = $resopnse;
	$json["iDList"] = $array;
	$response["serverResponse"] = array();
	$response["serverResponse"] = $json;
	echo (json_encode($response));
	die;
}
function returnPermissionResponse($action, $code)
{
	$json = array();
	global $User;
	http_response_code(401);
	$json[ACTIVATION_FIELD] = $User[ACTIVATION_FIELD];
	$json[PERV] = $User[PERV];
	$json[LOGIN] = $User[LOGIN];
	$json["message"] = $action;
	$json["code"] = $code;
	$response["serverResponse"] = $json;
	echo (json_encode($response));
	die;
}
function checkResponse($resonse)
{
	if (strpos($resonse, 'ERROR:') === false) {
		return true;
	}
	return false;
}
