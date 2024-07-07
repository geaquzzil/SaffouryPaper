<?php 
function getCommentsPayment($lang,$obj){
    $comments=$obj['comments'];
    if(isJournalRecord($obj)){
        $journal=$obj[JO];
        $table= getJournalTableNameFirst($journal);
        $content='';
        switch($table)
        {
            case CRED:case DEBT:
                $content=getTransValue($lang,"to").": ".$journal[$table][0][CUST]['name']. " #".getIDFormat($table,$journal[$table][0]);
                break;
            case INC:case SP:
                $content=getTransValue($lang,"to").": ".$journal[$table][0][AC_NAME]['name']. " #".getIDFormat($table,$journal[$table][0]);
                  break;
        }
        $comments.="<br>";
        $comments.=$content;
    }
    return isEmptyString($comments)?"-":$comments;
}
function getPayemntAmount($obj){
    $equality=$obj[EQ]['value'];
    $value=$obj['value'];
    return round($value * $equality);
}
function getCutResult($lang,$obj){
    $comments="-";
    if($obj['cut_status']=='COMPLETED' && $obj[CUT_RESULT."_count"]>0){
    $productInputID=$obj[CUT_RESULT][0][PR_INPUT][ID];
    $query="WHERE `ProductInputID`= '$productInputID'";
    $results=getFetshALLTableWithQuery("SELECT *  FROM `products_inputs_details`  $query");
        if(!empty($results) && !is_null($results)){	
		    $results = array_map(function($tmp) { return getNumberFormat($tmp['quantity']); }, $results);
		    $results=" ( ".implode(" - ",$results) . " ) ";
		    $comments= " <b> $results </b>";
	    }else{
	        return null;
	    }
    }
    return $comments;
}
function getComments($lang,$obj){
    $comments="";
    if($obj['cut_status']!='COMPLETED'){
    $iD=$obj[ID];
    $productID=$obj[PR][ID];
    $query="WHERE `CutRequestID`<> '$iD' AND `ProductID`='$productID'";
    $results=getFetshALLTableWithQuery("SELECT *  FROM `pending_cut_requests`  $query");
        if(!empty($results) && !is_null($results)){	
            
		    $results = array_map(function($tmp) { return $tmp['CutRequestID']; }, $results);
		    $results=" ( ".implode("'",$results) . " ) ";
		    $comments=getTransValue($lang,'pendingBefore'). " <b> $results </b>";
	    }
    }
	$comments.=$obj['comments'];        
    return isEmptyString($comments)?null:$comments;
}
function getCutRequestSizes($obj){
    $cutRequestSizes=$obj[SIZE_CUT];
    $content="";
    foreach($cutRequestSizes as $item){
        $content.=getSize($item)."<br>";
    }
    return $content;
}
function getStatusColor($status){
    switch($status){
        case 'PENDING':return 'Orange';
        case 'PROCESSING':return 'MediumSeaGreen';
        case 'COMPLETED':return 'Green';
        default: return 'Gray';
    }
}
function getWaste($obj){
    if($obj['cut_status']=='COMPLETED'){
        $iD=$obj[ID];
        $productID=$obj[PR][ID];
        $query="WHERE `CutRequestID`= '$iD'";
        $results=getFetshTableWithQuery("SELECT *  FROM `extended_cut_requests`  $query");
            if(!empty($results) || !is_null($results)){	
		        return $results['resultQuantity'] ;
	        }
    }
    return "-";
}
