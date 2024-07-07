<?php  
function setupDatabase(){		
	$username = DB_USER;
	$password = DB_PASSWORD;
	$host = DB_SERVER;
	$dbname = DB_DATABASE;
	try {
		$pdo = new PDO("mysql:host=$host;dbname=$dbname",$username,$password);
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$pdo -> exec("set names utf8");
	}catch(PDOException $e) {
	return returnServerError($e->getMessage()." qurey ");}
	return $pdo;
}
function executeMultiQuery($Query){
    $pdo=setupDatabase();
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, 0);
    try{
        	$pdo -> exec($Query);
    }catch(PDOException $e) {
        return returnServerError($e->getMessage()." qurey $Query");}
}

function getFetshTableWithQueryOptions($iD,$tableName,$option){
    
	$newQuery="SELECT * FROM `".$tableName."` WHERE `iD`='$iD'";
	if(is_null($option) || empty($option)){
	//	echo $newQuery;
		return getFetshTableWithQuery($newQuery);
	}
	if(isset($option["WHERE_EXTENSION"])){
		$newQuery=has_word($newQuery,"WHERE")?
			($newQuery." AND ".$option["WHERE_EXTENSION"]):
			($newQuery." WHERE ".$option["WHERE_EXTENSION"]);
			
			//echo$newQuery;
	}
	if(isset($option["SEARCH_QUERY"])){
	    $newQuery=has_word($newQuery,"WHERE")?
			($newQuery." AND ".$option["SEARCH_QUERY"]):
			($newQuery." WHERE ".$option["SEARCH_QUERY"]);
	}
	if(isset($option["ORDER_BY_EXTENSTION"])){
		$newQuery=$newQuery.$option["ORDER_BY_EXTENSTION"];
	}
	if(isset($option["LIMIT"])){
		$newQuery=$newQuery." ".$option["LIMIT"];
	}
// 	echo $newQuery;
// 	die;
	return getFetshTableWithQuery($newQuery);
}
function getFetshALLTableWithQueryOptions($tableName,$option){
    
    $newQuery='';
    if(checkRequestValue('searchByFieldName') && getRequestValue('table')==$tableName){
        $fieldName=getRequestValue('searchByFieldName');
        $newQuery="SELECT DISTINCT(`$tableName`.`$fieldName`) FROM `".$tableName."`";
    }else{
        $newQuery="SELECT * FROM `".$tableName."`";
    }
	
	if(is_null($option) || empty($option)){
		return getFetshALLTableWithQuery($newQuery);
	}
	if(isset($option["CUSTOM_JOIN"])){
		$newQuery=$newQuery." ".$option["CUSTOM_JOIN"];
	}
	if(isset($option["WHERE_EXTENSION"])){
		$newQuery=has_word($newQuery,"WHERE")?
			($newQuery." AND ( ".$option["WHERE_EXTENSION"]." )"):
			($newQuery." WHERE ".$option["WHERE_EXTENSION"]);
	}
	if(isset($option["SEARCH_QUERY"])){
	$newQuery=has_word($newQuery,"WHERE")?
			($newQuery." AND ( ".$option["SEARCH_QUERY"]." )"):
			($newQuery." WHERE ".$option["SEARCH_QUERY"]);
	}
	if(isset($option["ORDER_BY_EXTENSTION"])){
		$newQuery=$newQuery.$option["ORDER_BY_EXTENSTION"];
	}
	if(isset($option["LIMIT"])){
		$newQuery=$newQuery." ".$option["LIMIT"];
	}
// 		if(isset($option["SEARCH_QUERY"])){
// 		    	echo $newQuery;
// 	die;

// 		}
// 			echo $newQuery;
// 	die;
	return getFetshALLTableWithQuery($newQuery);
}
function getFetshTableWithQuery($query){
	$pdo = setupDatabase();
	try{
		$stmt = $pdo->prepare($query);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		return $result;
	}catch(PDOException $e){
	    //print_r($e);
	    	http_response_code(401);
	    return returnResponseErrorMessage($e->getMessage()." Query  =>$query " );}
}
function getFetshTableWithQueryWithoutEx($query){
    $pdo = setupDatabase();
	try{
		$stmt = $pdo->prepare($query);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		return $result;
	}catch(PDOException $e){return null;}
}
function getUpdateTableWithQuery($query){
	$pdo = setupDatabase();
	try{
		$stmt = $pdo->prepare($query);
		$stmt->execute();
		return $stmt->rowCount();
	}catch(PDOException $e){
	    
	    //print_r($e);
	    	http_response_code(401);
	    return returnResponseErrorMessage($e->getMessage()." Query  =>$query ");}
}
function getInsertTableWithQuery($query){
	$pdo = setupDatabase();
	try{
		$stmt = $pdo->prepare($query);
		$stmt->execute();
		return $pdo->lastInsertId();
	}catch(PDOException $e){
	    if ($e->errorInfo[1] == 1062) {
	        	http_response_code(401);
	        return returnResponseErrorMessage($e->getMessage()." Query  =>$query ");
        } else {
            	http_response_code(401);
            return returnResponseErrorMessage($e->getMessage()." Query  =>$query ");
            }
	    }
}
function getExecuteTableWithQuery($query){
	$pdo = setupDatabase();
	try{
		$stmt = $pdo->prepare($query);
		$stmt->execute();
	}catch(PDOException $e){
	    
	  //  print_r($e);
		http_response_code(401);
	return returnResponseErrorMessage($e->getMessage()." Query  =>$query ");}
}
function getDeleteTableWithQuery($query){
	$pdo = setupDatabase();
	try{
		$stmt = $pdo->prepare($query);
		$stmt->execute();
		return $stmt->rowCount()>0 ? true : false;
	}catch(PDOException $e){
	    //echo "$e";
	    return false;}
}
function getFetshALLTableWithQuery($query){
    	try{
	$pdo = setupDatabase();

		$stmt = $pdo->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $result;
	}catch(PDOException $e){
	    	http_response_code(401);
	//	print_r($e);
		return returnResponseErrorMessage($e->getMessage()." Query  =>$query ");}
}
function getFetshALLTableWithQueryWithoutEx($query){
	$pdo = setupDatabase();
	try{
		$stmt = $pdo->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $result;
	}catch(PDOException $e){return null;}
}
function getProductTables($tableName){
	return getFetshALLTableWithQuery(getQueryName($tableName));
}
function getProductTable($ID,$tableName){
	return getFetshTableWithQuery(getQuery($tableName,$ID));	
}


function getQuery($tableName,$ID){
	switch($tableName)
	{
		case CUST:return "SELECT name,iD,phone FROM  `".addslashes($tableName)."` WHERE iD=$ID";
		case USR_L:return "SELECT * FROM `".addslashes($tableName)."` WHERE iD=$ID";
		case DEAL:return "SELECT name,iD,phone FROM `".addslashes($tableName)."` WHERE iD=$ID";
		case EMP:return "SELECT name,iD,userlevelid FROM  `".addslashes($tableName)."` WHERE iD=$ID";
		default :return "SELECT * FROM  `".addslashes($tableName)."` WHERE iD=$ID";
	}
}
function getQueryValue($tableName,$columnName,$valueToSearch){
	if(ctype_digit($valueToSearch)){return "SELECT * FROM  ".addslashes($tableName)." WHERE $columnName=$valueToSearch";}
	else{return "SELECT * FROM  ".addslashes($tableName)." WHERE $columnName = '$valueToSearch'";}
}
function getQueryName($tableName){
	switch($tableName)
	{   case EMP:return "SELECT iD,name,userlevelid,CONVERT(phone,char) AS phone FROM  ".addslashes($tableName)." ORDER BY name ASC";
		case CUST:return "SELECT name,iD,phone,token FROM  ".addslashes($tableName)." ORDER BY name ASC";
		case COUNTRY:return "SELECT * FROM  ".addslashes($tableName)." ORDER BY name ASC";
		case AC_NAME:return "SELECT * FROM  ".addslashes($tableName)." ORDER BY name ASC";
		case TYPE:return "SELECT * FROM  ".addslashes($tableName)." ORDER BY name ASC";
		case GSM:return "SELECT * FROM  ".addslashes($tableName)." ORDER BY gsm ASC";
		default :return "SELECT * FROM  ".addslashes($tableName)." ";
	}
}


?>