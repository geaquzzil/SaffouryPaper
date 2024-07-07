<?php 
 // Original (Object is Array) ($object is Object->)
$AFTER_ADD_OBJ[CRED]=function ($origianlObject,$object){
	//print_r($origianlObject);
//	print_r($object);
};
$AFTER_ADD_OBJ[CUT_RESULT]=function ($origianlObject,$object){
    	try{
	        $iD=getKeyValueFromObj($object,'CutRequestID');
	        $query=  " UPDATE `".CUT."` Set `cut_requests`.`cut_status` = 'COMPLETED' WHERE `iD` = '$iD'";
            getUpdateTableWithQuery($query);
	   }catch(Exception $e){}
	
};
function paymentAdds($origianlObject,$object,$tableNameToAddOn){
	$payDollar=isSetKeyFromObjReturnValue($origianlObject,CRED.'Dollar');
	$paySYP=isSetKeyFromObjReturnValue($origianlObject,CRED.'SYP');

	if(!is_null($paySYP)){
		$paySYP[KCUST]=$object[KCUST];
		addEditObject($paySYP,$tableNameToAddOn,getDefaultAddOptions());
	}
	if(!is_null($payDollar)){
		$payDollar[KCUST]=$object[KCUST];
		addEditObject($payDollar,$tableNameToAddOn,getDefaultAddOptions());
	}
}
$AFTER_ADD_OBJ[PURCH]=function ($origianlObject,$object){
	paymentAdds($origianlObject,$object,DEBT);
};
$AFTER_ADD_OBJ[ORDR]=function($origianlObject,$object){
	paymentAdds($origianlObject,$object,CRED);
	
	///If there are any cut request then add it 		
	$cutRequest=isSetKeyFromObjReturnValue($origianlObject,CUT);
};
?>
