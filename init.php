<?php
//statics setup

$permssionTableName=PER;
//list as menu - print as print - view as view id 
$permissionActions=array("print_","list_","view_","add_","edit_","delete_","notification_");
$permissionUserLevel=array("");


$currencyExtenstions=array();
$warehouseExtenstions=array();
function setup(){
	//check if admin
	checkPermissionTable();
}
function addExtentionsPermissionCol($extenstionCol){
	
	
}
function checkPermissionTable(){
	global $permssionTableName ,$permissionActions,$permissionExtentions;
//	if(tableExists($permssionTableName))return;
	$query="ALTER TABLE `" .$permssionTableName ."`  "; 
	$colomns="";
	$tables=getAllTablesWithoutViewString();
	$views=getAllTablesViewString();
	foreach($tables as $t){
		$colomn;
		foreach($permissionActions as $p){
			$colomn="ADD  `".$p.$t."` tinyint(1) NOT NULL DEFAULT 0,";
			$colomns=$colomns.$colomn;
		}
	
	}
	foreach($views as $v){
		$colomn="ADD  `view_".$v."` tinyint(1) NOT NULL DEFAULT 0,";
		$colomns=$colomns.$colomn;
	}
	foreach($permissionExtentions as $e){
		$colomn="ADD `".$e."` tinyint(1) NOT NULL DEFAULT 0,";
		$colomns=$colomns.$colomn;
	}
	$query=$query.$colomns;
	$query=substr($query,0,-1);
	//$query=$query."  ENGINE=InnoDB DEFAULT CHARSET=utf8";
	getFetshTableWithQuery($query);
	
	//echo $query;
	// get only tables not views
	// add the permissionExtentions like allow send notifications
	// add permissionActions befor table name
	// add it as colomns tinyint(1);
	// iD is primary key should has value as same as userlevel iD
	
	
	
}
function checkCurrenyTable(){
	
}
function checkWarehouseTable(){
	
}
?>