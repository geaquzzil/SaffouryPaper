<?php
/*
 * All database connection variables
 */
define("ROOT", dirname(__FILE__) . "/");
define("JSONTOMYSQL_LOCKED", false);
define("ROOT_LOCAL", dirname(__FILE__) . "\\");
define("DATABASE_HOST", "localhost");
define("DATABASE_NAME", "saffoury_paper");
define("DATABASE_USER", "saffoury_qussai");
define("DATABASE_PASS", "K-O-K-Y1");
define('DB_ANDROID',"HIIAMANANDROIDUSERFROMSAFFOURYCOMPANY");
//define("KEY",md5(DB_ANDROID));
//define("SALT",md5(DB_ANDROID));

define('DB_SERVER', "localhost"); // db server
define('DB_DATABASE', "saffoury_paper"); // database name
define('DB_USER', "saffoury_qussai"); // db user
define('DB_PASSWORD', "K-O-K-Y1"); // db password (mention your db password here)


define('IMAGES_PATH',"saffoury.com/SaffouryPaper2/Images");
define('AC_NAME',"account_names");
define('AC_NAME_TYPE',"account_names_types");
define('COUNTRY',"countries");
define('CMC',"countries_manufactures");
define('CRED',"credits");
define('CUR',"currency");
define('CUST',"customers");
define('CUSTOMS',"customs_declarations");
define('CUSTOMS_IMAGES',"customs_declarations_images");
define('CUT',"cut_requests");
define('CUT_RESULT',"cut_request_results");
define('DEAL',"dealers");
define('DEBT',"debits");
define('EMP',"employees");
define('EQ',"equalities");
define('GD',"grades");
define('GSM',"gsms");
define('INC',"incomes");
define('JO',"journal_voucher");
define('MAN',"manufactures");

define('ORDR',"orders");
define('ORDR_D',"orders_details");
define('ORDR_R',"orders_refunds");
define('ORDR_R_D',"order_refunds_order_details");

define('RI',"reservation_invoice");
define('RI_D',"reservation_invoice_details");

define('CRS',"customers_request_sizes");
define('CRS_D',"customers_request_sizes_details");

define('PH',"phones");
define('PR',"products");
define('PR_SEARCH',"products_search_view");
define('PR_INV',"inventory_products");

define('PR_INPUT',"products_inputs");
define('PR_INPUT_D',"products_inputs_details");
define('PR_OUTPUT',"products_outputs");
define('PR_OUTPUT_D',"products_outputs_details");

define('TYPE',"products_types");

define('PURCH',"purchases");
define('PURCH_D',"purchases_details");
define('PURCH_R',"purchases_refunds");
define('PURCH_R_D',"purchases_refunds_purchases_details");

define('QUA',"qualities");
define('SETTING',"setting");

define('SIZE',"sizes");
define('SIZE_CUT',"sizes_cut_requests");
define('SP',"spendings");
define('SP_O',"spendings_orders");
define('SP_T',"spendings_transfers");

define('TR',"transfers");
define('TR_D',"transfers_details");

define('GOV',"governorates");
define('CARGO',"cargo_transporters");

define('HOME_IMAGE',"home_image_list");
define('HOME_IMAGE_D',"home_image_list_action");
define('HOME_ADS',"home_ads_image_list");
define('HOME_ADS_D',"home_ads_image_list_action");

define('WARE',"warehouse");
define('WARE_E',"warehouse_employees");

define('USR',"userlevels");

define('ID',"iD");
define('KCUST',"CustomerID");
define('KEMP',"EmployeeID");

define('KP',"ProductID");
define('PARENTID',"ParentID");
define('KPURCH',"PurchasesID");
define('KORDER',"OrderID");
define('KTYPE',"ProductTypeID");

define('KCMC',"Country_Manufacture_CompanyID");
define('KGSM',"GSMID");
define('KSIZE',"SizeID");
define('KCOUNTRY',"CountryID");
define('KMANUFACTURE',"ManufactureID");
define('KLVL',"userlevelid");
function returnResponseCompress($response){	
	if(is_null($response) || empty($response)){
	//TODO remove comment on publish	http_response_code(204);
	}
	 $data= (gzcompress(beforeReturnResponseObjectExtenstion(json_encode($response,JSON_NUMERIC_CHECK ),getRequestValue('table')),9)); 
	 echo( $data);
	 die;		
}
function returnResponse($response){	
	if(is_null($response) || empty($response)){
	//TODO remove comment on publish	http_response_code(204);
	}
    $data="";
    if(isFlutterRequest()){
    // $data=beforeReturnResponseObjectExtenstion((json_encode($response,JSON_NUMERIC_CHECK )),getRequestValue('table')); 
    
    $data=getResponseDataForFlutter($response);
    
    }else{
    $data=beforeReturnResponseObjectExtenstion((json_encode($response )),getRequestValue('table'));
    }
	echo($data);
	die;		
}
function getResponseDataForFlutter($data){
    $numeric = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
    $nonnumeric = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    preg_match_all("/\"[0\+]+(\d+)\"/",$nonnumeric, $vars);
    foreach($vars[0] as $k => $v){
        $numeric = preg_replace("/\:\s*{$vars[1][$k]},/",": {$v},",$numeric);
    }
    return $numeric;
}
function isFlutterRequest(){
	$headers = apache_request_headers();
	return isset($headers['Platform']) && $headers['Platform']=='Flutter';

}
function getResponseData(&$response){
    $data=json_decode(json_encode($response,JSON_NUMERIC_CHECK ),true);
    $response= json_encode(replaceIntToStringFrom($data));
  
}
function replaceIntToStringFrom($array){
    foreach($array as $key => $value) {
      
        
        if($key=='password'){
            setKeyValueFromObj($array,$key,(string)$value);

        }
        if( $key=='phone'){
            setKeyValueFromObj($array,$key,(string)"0".$value);
        }
        if($key==CUST || $key ==EMP){
            if(is_object($value)){
                $array[$key]=replaceIntToStringFrom($array[$key]);
            }
            if(is_array($value)){
                $array[$key]=replaceIntToStringFrom($array[$key]);
            }
        }
    }
    return $array;
}
function returnAuthErrorMessage($response){
	http_response_code(401);
	returnResponseErrorMessage($response);
	
}
function returnServerError($response){
	http_response_code(500);
	returnResponseErrorMessage($response);
}
function returnBadRequest($response){
	http_response_code(400);
	returnResponseErrorMessage($response);
}

function returnResponseErrorMessage($response){
	$json=array();
	$json["error"]=true;
	$json["message"]=$response;
	$response=array();
	$response["serverResponse"]=array();
	$response["serverResponse"]=$json;
	echo(json_encode($response));
	die;
}
function returnResponseMessage($resopnse)	{
	$json=array();
	$json["response"]=$resopnse ;
	echo(json_encode($json));
	die;
}
function returnArrayResponseMessage($resopnse,$array)	{
	$json=array();
	global $User;
	$json[LOGIN]=$User[LOGIN];
	$json[PERV]=$User[PERV];
	$json["RESPONSE"]=$resopnse ;
	$json["iDList"]=$array;
	$response["serverResponse"]=array();
	$response["serverResponse"]=$json;
	echo(json_encode($response));
	die;
}
function returnPermissionResponse($action,$code)	{
	$json=array();
	global $User;
	http_response_code(401);
	$json[ACTIVATION_FIELD]=$User[ACTIVATION_FIELD];
	$json[PERV]=$User[PERV];
	$json[LOGIN]=$User[LOGIN];
	$json["message"]=$action;
	$json["code"]=$code;
	$response["serverResponse"]=$json;
	echo(json_encode($response));
	die;
}  
function checkResponse($resonse)	{
	if (strpos($response, 'ERROR:') === false) {return true;}
	return false;
}


require_once  ("security.php");
require_once  ("php_utils.php");

require_once  ("notification_fucntions.php");

require_once ("db_api.php");
require_once  ("db_utils.php");
require_once  ("db_functions.php");

require_once ("sqlBalances.php");
require_once ("sqlCustomers.php");
require_once ("sqlProducts.php");
require_once ("sqlAnalysis.php");
require_once ("sqlMoneyFunds.php");


?>