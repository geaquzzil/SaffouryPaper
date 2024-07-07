<?php 


$DELETE_OBJ[SP]=function(&$object){checkToDeleteJournal($object);};
$DELETE_OBJ[INC]=function(&$object){checkToDeleteJournal($object);};
$DELETE_OBJ[CRED]=function(&$object){checkToDeleteJournal($object);};
$DELETE_OBJ[DEBT]=function(&$object){checkToDeleteJournal($object);};
$DELETE_OBJ[TYPE]=function ($object){
	if(!isEmptyString($object["image"])){
	    unlinkFile(getKeyValueFromObj($object,"image"));
	}
};
$DELETE_OBJ[CUSTOMS]=function(&$object){
	$result=depthSearch($object['iD'],CUSTOMS,-1,[CUSTOMS_IMAGES],[],null);		
		if(!empty($result[CUSTOMS_IMAGES])){				
			foreach($result[CUSTOMS_IMAGES] as $img){
				if(!isEmptyString($img["image"])){
				    unlinkFile($img["image"]);
				}
			}
		}
};
//TODO check strrpos last index of /
$DELETE_OBJ[CUSTOMS_IMAGES]=function(&$object){
	if(!isEmptyString(getKeyValueFromObj($object,'image'))){
		unlinkFile(getKeyValueFromObj($object,"image"));
	}
};


$DELETE_OBJ[HOME_ADS]=function(&$object){
	if(!isEmptyString(getKeyValueFromObj($object,'image'))){
		unlinkFile(getKeyValueFromObj($object,"image"));
	}
};
$DELETE_OBJ[CUT_RESULT]=function(&$object){
    $iD=getKeyValueFromObj($object,'ProductInputID');
	deleteObject($iD,PR_INPUT,false);
	
	$iD=getKeyValueFromObj($object,'ProductOutputID');
	deleteObject($iD,PR_OUTPUT,false);
	
	try{
	$iD=getKeyValueFromObj($object,'CutRequestID');
	$query=  " UPDATE `".CUT."` Set `cut_requests`.`cut_status` = 'PROCESSING' WHERE `iD` = '$iD'";
    getUpdateTableWithQuery($query);
	}catch(Exception $e){
		
	}
};

$FIX_RESPONSE_OBJECT_DELETE[TR]=function(&$object){
	$object["fromWarehouse"]=depthSearch($object["fromWarehouse"],WARE,1,[],[],null);
    $object["toWarehouse"]=depthSearch($object["toWarehouse"],WARE,1,[],[],null);
};
?>