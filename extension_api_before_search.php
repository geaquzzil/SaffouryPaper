<?php 

$BEFORE_SEARCH_OBJECT[SIZE] = function(&$object) {
    if(!isSetKeyFromObj($object,"length")){
        setKeyValueFromObj($object,"length",0);
    }
   // print_r($object);
};
$CUSTOM_QUERY_BEFORE_ADD_SEARCH[PR]=function($object) {
	//print_r($object);
    $barcodeString= isSetKeyFromObjReturnValue($object,"barcode");
//	echo "BARCODE IS ".$barcodeString;
	$haveBarcode = !is_null($barcodeString) && !isEmptyString($barcodeString);
	if($haveBarcode){
	    $query="`barcode` LIKE $barcodeString";
	    $whereQuery[]=$query;
	//	echo "HAS BARCODE ";
	    return implode(" AND ",$whereQuery);
	}
//	echo "! HAS BARCODE ";
	return null;
};

$ADD_KEY_VALUE_TO_SEARCH_QUERY[PR]=function($key) {
//	echo "ADD TO SEARCH $key  \n";

	if($key==='date'){
	//	echo "!!ADD TO SEARCH $key  \n";
		return false;
	
	}
//	echo "ADDed TO SEARCH $key  \n";

	return true;



};

?>