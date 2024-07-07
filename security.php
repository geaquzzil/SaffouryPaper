<?php
///should require db_config
//permission table
define('PER',"permissions_levels");
define('USR_L',"userlevels");

define('PERV',"permission");
define ("ACTIVATION_FIELD","activated");
define ("USER_FIELD","phone");
define ("PASSWORD_FIELD","password");
define ("ADMIN_ID",-2);
define ("LOGIN","login");

function setUser(){
	global $User;
	try {
	if(!is_null(getUserHeader()) || checkRequestValue("user")){
	   
		$Data= !is_null(getUserHeader()) ? decryptData(getUserHeader(),false) :decrypt('user',false);
// 		echo " x ($Data)";
		if(!isJson($Data)){setGuestUser();return;}
		$result=signInUser(jsonDecode($Data),EMP);
		if(is_null($result)|| empty($result)){
			$result=signInUser(jsonDecode($Data),CUST);
		}
		if(is_null($result)|| empty($result)){
			setGuestUser();
		}else{
			$User=$result;
			$User[LOGIN]=true;
			$User[PERV]=true;
			if(isEmployee()){
			   $option["WHERE_EXTENSION"]="`EmployeeID` = '".$User['iD']."'";
	           $User[WARE_E]=depthSearch(null,WARE_E,0,null,[WARE],$option);
			}
		}	
	}else{
		setGuestUser();
	}	
	}catch(Exception $e){
	    setGuestUser();
	}
}
function signInUser($userJson,$tableName){
	$password=$userJson[PASSWORD_FIELD];
	$phone=is_numeric($userJson[USER_FIELD])?$userJson[USER_FIELD]:"" ;
	$value=getFetshTableWithQuery("SELECT * FROM `$tableName`
	WHERE ".USER_FIELD." <> '' AND ".USER_FIELD." IS NOT NULL 
	AND ".PASSWORD_FIELD." <> '' AND ".PASSWORD_FIELD." IS NOT NULL 
	AND ".PASSWORD_FIELD."='".($password).
	"' AND ".USER_FIELD."='".($phone)."'");
	if(empty($value)){return null;}
	if($value[ACTIVATION_FIELD]==0){return null;}
	$value[USR_L]=depthSearch($value[KLVL],USR_L,0,[PER],[],null);// getProductTable($value[KLVL],USR_L);
	$value[SETTING]=getFetshTableWithQuery("SELECT * FROM `".SETTING."` LIMIT 1");
	$value[DEAL]=depthSearch(1,DEAL,0,true,true,null);
	return $value;
}
function setGuestUser(){
	global $User;
	$User[USER_FIELD]="";
	$User[PASSWORD_FIELD]="";
	$User[ACTIVATION_FIELD]=1;
	$User[KLVL]=0;
	$User[PERV]=false;
	$User[LOGIN]=false;
	$User[SETTING]=getFetshTableWithQuery("SELECT * FROM `".SETTING."` LIMIT 1");
	$User[USR_L]=depthSearch(0,USR_L,0,[PER],[],null);
	$User[DEAL]=depthSearch(1,DEAL,0,true,true,null);
}
function getUserLevelID(){
	return $GLOBALS["User"][KLVL];
}
function getUserID(){
	return $GLOBALS["User"]["iD"];
}
//android actions 
$permissionExtentions=array(
	"action_exchange_rate",
	"action_block",
	"action_transfer_account",
	"action_notification",
	"action_change_customers_password",
	"action_cut_request_scan_by_product",
	"action_cut_request_change_quantity",
	"text_products_quantity",
	"text_customers_password",
	"text_customers_balances",
	"text_transaction_added_by",
	"text_prices_for_customer",
	"text_products_notes",
	"text_purchase_price",
	"text_balance_due",
	"text_balance_due_today",
	"text_balance_due_previous",
	//"list_customers_terms",
	"add_from_importer",
	
	"set_customs_declarations" ,
		
	"list_product_movement",
	"list_customers_balances",
	"list_block",	
	"list_fund",
	"list_dashboard",	
	"list_sales",
	"list_profit_loses",
	"list_products_movements",
	"view_customer_statment_by_employee"
	
	

);
$allowedStaticPermissions=array(
    "login_flutter",
	"login",
	"token",//Self check 
	"add_from_importer",
	"action_transfer_money",//To check is Admin 
	"list_most_popular_products",
	"list_server_data",
	"list_search_products",
	"list_dashboard_single_item", //to check permission table from action
    "list_home",
	"list_advanced_search",
	"list_search_by_barcode",
	"list_search_size_analyzier",
	"list_similars_products",
	"list_not_used_records",//to check permission table from action
	"list_changes_records_table",//to check permission table from action
	"list_dashboard_orders_overdues", //to check permission table to orders
	"list_search_customers",//To check list_customer
	"list_customers_not_payed",//To check list_customer
	"list_customers_pay_next",//To check list_customer
	"list_customers_profit",//To check list_customer
	"list_customers_terms",//To check list_customer
	"view_customer_statement",//To check list_customer
	"available_product_type",//To check list_products_type
	"list_product_analysis",//To check list_profit_loses
// 	"view_customer_statment_by_employee",
	//To check edit_customs_declarations
	"list_reduce_size",
	"test",
	"backup_database",
	"restore_database",
	"tables"
	);
//TODO FIX THIS WHEN PERMISSIONS TABLE IS DONE
//TODO 0 is deactivated
//	  -1 is not allowed

function getPermissionActionFromTableFromUserLevelID($userLevelID,$action){
    $query="SELECT * FROM `".PER."` WHERE `userlevelid`='".$userLevelID."' AND `table_name`='$action'";
    $result=getFetshTableWithQueryWithoutEx($query);
	return is_null( $result ) || empty($result)? null:$result;
}
function getPermissionActionFromTable($action){
    $query="SELECT * FROM `".PER."` WHERE `userlevelid`='".getUserLevelID()."' AND `table_name`='$action'";
   // echo $query;
    $result=getFetshTableWithQueryWithoutEx($query);
   // echo $action."\n";
   // print_r($result) ;
	return is_null( $result ) || empty($result)? null:$result;
}

function checkPermissionForActionTableResultAndAction($resultPermissionTable,$tableName,$action){
         //   echo "table izssss  is  $tableName  \n";
    if(is_bool($resultPermissionTable)){
         // echo " is is_bool\n ";
        return $resultPermissionTable;
    }
    if(is_null($resultPermissionTable)){
        return false;
    }
  //  print_r($resultPermissionTable);
   // $searchResult = array_filter($resultPermissionTable, function ($p) use ($tableName) {
  //      return $p["table_name"] == $tableName;
  //  });
    
    $key = array_search($tableName,array_column($resultPermissionTable, 'table_name')); 
    //var_dump($searchResult);
  //  print_r($searchResult);
   // echo $resultPermissionTable[$key][$action]  . " $action   key is $key tr \n ";
	return $resultPermissionTable[$key][$action]==1;
}
function getUserPermissionTable(){
    $userLevelID=getUserLevelID();
    if($userLevelID==ADMIN_ID){return true;}
     $query="SELECT * FROM `".PER."` WHERE `userlevelid`='".getUserLevelID()."'";
   // echo $query;
    $result=getFetshAllTableWithQuery($query);
   // echo $action."\n";
  //  print_r($result) ;
	return is_null( $result ) || empty($result)? null:$result;
}
function checkPermissionForActionAndTable($table,$action){
    $userLevelID=getUserLevelID();
    if($userLevelID==ADMIN_ID){return true;}
    $result= getPermissionActionFromTable($userLevelID,$action);
	if(is_null($result))return false;
    return $result[$action]==1;
}
function checkPermissionActionFromTable($action,$isExtenstionPermission){
   // echo "  dsadas ";
    //$action is the table or action
    //getRequestValue('action') is the action
    if(!$isExtenstionPermission){
        $theAction=getRequestValue('action') ;
        $result= getPermissionActionFromTable($action);

		if(is_null($result))return false;
		if($theAction=='delete'){
		    $theAction='delete_action';
		}
		return $result[$theAction]==1;
		 
    }else{
        $result= getPermissionActionFromTable($action);
            //    print_r($result);
		if(is_null($result))return false;
			return $result['view']==1||$result['list']==1;
    }
         
}
function checkPermissionActionWAR($action){
	if(getUserLevelID()==ADMIN_ID){$User[PERV]=true;return true;}
	global $User,$allowedStaticPermissions,$permissionExtentions;
//	echo  "Permission Action ".$action;
	if (($i = array_search($action,$allowedStaticPermissions)) === FALSE){
		if (($i = array_search($action,$permissionExtentions)) === FALSE){
		        //its table or view we need to check all
		        return checkPermissionActionFromTable($action,false);
		}else{
		    //its permission $permissionExtentions and we nedd to check view or list only;
		     return checkPermissionActionFromTable($action,true);
		}
	}else{
	    //is static permission allowed by default
		return true;
	}
}
function checkNotificationPermission($userLevelID,$action){
    if($userLevelID==ADMIN_ID){return true;}
     $result= getPermissionActionFromTableFromUserLevelID($userLevelID,$action);
	if(is_null($result))return false;
	return $result['notification']==1;
}
//this is throw error on permission deinid;
function checkPermissionAction($action){
	if(getUserLevelID()==ADMIN_ID){$User[PERV]=true;return true;}
	global $User,$allowedStaticPermissions,$permissionExtentions;
	// "Permission Action ".$action;
	if($User[ACTIVATION_FIELD]==0){
		returnPermissionResponse($action,0);
	}
	
	if (($i = array_search($action,$allowedStaticPermissions)) === FALSE){
		if (($i = array_search($action,$permissionExtentions)) === FALSE){
		        //its table or view we need to check all
		        $User[PERV]= checkPermissionActionFromTable($action,false);
		        if(!$User[PERV]){returnPermissionResponse($action,-1);}
		        return true;
		}else{
		    //its permission $permissionExtentions and we nedd to check view or list only;
		     $User[PERV]= checkPermissionActionFromTable($action,true);
		      if(!$User[PERV]){returnPermissionResponse($action,-1);}
		      return true;
		}
	}else{
	    //is static permission allowed by default
		$User[PERV]=true;
		return true;
	}
}
function isGuest(){
	return $GLOBALS["User"][KLVL]==0;
}
function isCustomer(){
	return $GLOBALS["User"][KLVL]>0;
}
function isEmployee(){
	return $GLOBALS["User"][KLVL]<0;
}
function isAdmin(){
	return $GLOBALS["User"][KLVL]==ADMIN_ID;
}
function getPermissionLevel(){
    $userLevelID=$GLOBALS["User"][KLVL];
    getFetshAllTableWithQuery("SELECT * FROM `".PER."` WHERE `userlevelid`='".$userLevelID."'");
}

function getPermissionFieldName(){
	return is_null(getRequestValue('table')) || isEmptyString(getRequestValue('table'))?getRequestValue('action'): getRequestValue('table');
}

function checkUserWithoutPermissionOrLoginResponse(){
	$mcrypt = new MCrypt();
	$decrypt=$mcrypt->decrypt($_POST["user"]);
	$employee=jsonDecode($decrypt);
	$tableName=EMP;
	$dataBaseUser=signInUser($employee,$tableName);
	return $dataBaseUser;
	
}
function checkUser()	{
	$mcrypt = new MCrypt();
	$decrypt=$mcrypt->decrypt($_POST["user"]);
	$employee=jsonDecode($decrypt);
	$tableName=EMP;
	$dataBaseUser=signInUser($employee,$tableName);
	if(empty($dataBaseUser)){returnLoginFailedResponse();}
	else{checkPermission($dataBaseUser);}
}

function checkPermission($userJson)	{
	if($userJson[KLVL]>0 OR $userJson[ACTIVATION_FIELD]==0){
		returnPermissionResponse();
	}else{	    }
}
function checkPermissionSERVER($userJson){
	if($userJson[KLVL]>0 OR $userJson[ACTIVATION_FIELD]==0){
		returnPermissionResponse();
	}else{returnPermissionGrandedResponse();}
}
function checkUserSERVER($userJson)	{
	$tableName=EMP;
	$dataBaseUser=signInUser($userJson,$tableName);
	if(empty($dataBaseUser)){returnLoginFailedResponse();}
	else{checkPermissionSERVER($dataBaseUser);}
}
function blockALL($ALL){
	$pdo = setupDatabase();
	try{
		$stmt = $ALL ? $pdo->prepare("UPDATE ".CUST." SET ".ACTIVATION_FIELD."='0'") :$pdo->prepare("UPDATE ".CUST." SET ".ACTIVATION_FIELD."='1'") ;  
		$stmt->execute();
		if($ALL){
			$customers=getFetshALLTableWithQuery("SELECT iD FROM ".CUST." WHERE token<>'' OR token is not null"); 
			if(is_array($customers)){
				foreach($customers as $customer){
					doNotification($customer["iD"],null,FB_LOG_OUT);
				}
			}
		}			
		return 1;
	}catch(PDOException $e) {return -1;}
}	
function block($TOBLOCK,$ISCUSTOMER,$VALUE){
	$pdo = setupDatabase();
	try {
        $stmt = $ISCUSTOMER ? $pdo->prepare("UPDATE ".CUST." SET ".ACTIVATION_FIELD."=$VALUE WHERE iD=$TOBLOCK"): $pdo->prepare("UPDATE ".EMP." SET ".ACTIVATION_FIELD."=$VALUE WHERE iD=$TOBLOCK");
		$stmt->execute();
		if($ISCUSTOMER){
			if($VALUE===0 || $VALUE==0){
				doNotification($TOBLOCK,null,FB_LOG_OUT);
			}
		}
		return 1;
	} catch(PDOException $e) {return -1;}
}
function getUserPermission(){
		return $GLOBALS["RequestSecurity"]["userlevelid"];
}
function authFunction($authcode){
	require_once  ("cryptor.php");	
//	echo "S".$authcode;
	$mcrypt = new MCrypt();
	$decrypted = $mcrypt->decrypt($authcode);
//	echo ($decrypted);
	if($decrypted===DB_ANDROID){return true;}
	else{return false;}
}
function getAuthHeader(){
	$headers = apache_request_headers();
//	print_r($headers);
	if(isset($headers['Auth'])){
	  return $headers['Auth'];
	}else{
	  return null;
	}
}
function getUserHeader(){
	$headers = apache_request_headers();
	if(isset($headers['X-Authorization'])){
	   // echo " SET ";
	  return $headers['X-Authorization'];
	}else{
	  return null;
	}
}
function encrypt($data){
    require_once  ("cryptor.php");
	$mcrypt = new MCrypt();
	$encrypted = $mcrypt->encrypt("$data");
	echo $encrypted;
}
function decryptData($data,$json){
//	return  $json ? jsonDecode($data):$data;
	require_once  ("cryptor.php");
	$mcrypt = new MCrypt();
	$decrypt=$mcrypt->decrypt($data);
	return $json ? jsonDecode($decrypt):$decrypt;
}
function decrypt($field,$json){
//		return  $json ? jsonDecode(getRequestValue($field)):getRequestValue($field);
	require_once  ("cryptor.php");
	$mcrypt = new MCrypt();
	$decrypt=$mcrypt->decrypt(getRequestValue($field));
	
	return $json?jsonDecode($decrypt):$decrypt;
}


?>