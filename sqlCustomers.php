<?php

function invoicesOverduesAndDesposited($From,$To){
    $result=array();
	$response=array();
	$response["notDueYet"]=0;
	$response["overDue"]=0;
	$response["notDisposited"]=0;
	$response["desposited"]=0;
	$response["paid"]=0;
	$response["unpaid"]=0;
	//not due yet
	$Query="SELECT 
	    extended_order_refund.iD AS OrderID,
	    customers.iD,
	    customers.name,
		extended_order_refund.termsDate AS termsDate,
		extended_order_refund.extendedNetPrice
		
		FROM customers
		INNER JOIN extended_order_refund ON customers.iD = extended_order_refund.CustomerID  
	    WHERE (Date(extended_order_refund.termsDate)) >= (CurDate())";
	$result=getFetshALLTableWithQuery($Query);
	$resultID = array_map(function($tmp) { return $tmp['iD']; }, $result);
    if(is_array($result)){
        for($x=0;$x<count($result);$x++){
            $balance=getBalanceDueTo($result[$x]['iD'],curdate())['balance'];
            if($balance<=0){
                unset($result[$x]);
            }else{
                
                $response["notDueYet"]+=$result[$x]["extendedNetPrice"];
                
            }
        }
        
    }
    
    
    //over due
    $Query="SELECT 
	    customers.iD,
	    customers.name,
		extended_order_refund.termsDate AS termsDate	,
		extended_order_refund.extendedNetPrice
		
		FROM customers
		INNER JOIN extended_order_refund ON customers.iD = extended_order_refund.CustomerID
		 WHERE (Date(extended_order_refund.termsDate)) <= (CurDate())";
	$result=getFetshALLTableWithQuery($Query);
	$resultID = array_map(function($tmp) { return $tmp['iD']; }, $result);
    if(is_array($result)){
        for($x=0;$x<count($result);$x++){
                $toDate= date("Y-m-d",strtotime($result[$x]['termsDate']));
            $balance=getBalanceDueTo($result[$x]['iD'],$toDate)["balance"];
            if($balance<=0){
                unset($result[$x]);
            }else{
               $response["overDue"]+=$result[$x]["extendedNetPrice"];
            }
        }
        
    }
    $response["unpaid"]=$response ["overDue"]+ $response["notDueYet"];
    $orders=getBalanceAllByTable(ORDR)['balance'];
    $purchases=getBalanceAllByTable(PURCH)['balance'];
    $debits=getBalanceAllByTable(DEBT)['balance'];
    $credits=getBalanceAllByTable(CRED)['balance'];
   // print_r($allBalances);
    $response["paid"]=$purchases+$credits;
    $response["desposited"]=$credits;
    $response["notDisposited"]=($credits+$purchases)-($debits+$orders);
    return $response;
}
function customersTerms($iD){
	$result=array();
	$response=array();
	$Query="SELECT 
	    customers.iD,
	    extended_order_refund.iD AS OrderID,
		extended_order_refund.termsDate AS termsDate
		FROM customers
		INNER JOIN extended_order_refund ON customers.iD = extended_order_refund.CustomerID
		";
		
	$Query=is_null($iD) ?  $Query : $Query." WHERE customers.iD='$iD'";
	//echo $Query;
	
	$result=getFetshALLTableWithQuery($Query);
    if(is_array($result)){
        for($x=0;$x<count($result);$x++){
            $balance=getBalanceDueTo($result[$x]['iD'],$result[$x]['termsDate'])['balance'];
            if($balance<=0){
                unset($result[$x]);
            }else{
                array_push($response,$result[$x]);
            }
        }
    }
    return $response;		
}	
function notPayedCustomers(){
	$result=array();
	$response=array();
	$Query="SELECT 
	    customers.iD,
	    customers.name,
		extended_order_refund.termsDate AS termsDate
		FROM customers
		INNER JOIN extended_order_refund ON customers.iD = extended_order_refund.CustomerID
		 WHERE (Date(extended_order_refund.termsDate)) <= (CurDate())";
	$result=getFetshALLTableWithQuery($Query);
	$resultID = array_map(function($tmp) { return $tmp['iD']; }, $result);
    if(is_array($result)){
        for($x=0;$x<count($result);$x++){
                $toDate= date("Y-m-d",strtotime($result[$x]['termsDate']));
            $balance=getBalanceDueTo($result[$x]['iD'],$toDate)["balance"];
            if($balance<=0){
                unset($result[$x]);
            }else{
                $customer=depthSearch($result[$x]['iD'],CUST,1,null,true,null);
                $customer['termsDate']=$result[$x]['termsDate'];
                array_push($response,$customer);
            }
        }
        
    }
    return $response;	
}

function customerToPayNextByID($iD){
    $result=array();
	$response=array();
	$Query="SELECT 
	    extended_order_refund.iD AS OrderID,
	    customers.iD,
	    customers.name,
		extended_order_refund.termsDate AS termsDate
		FROM customers
		INNER JOIN extended_order_refund ON customers.iD = extended_order_refund.CustomerID  
	    WHERE (Date(extended_order_refund.termsDate)) >= (CurDate())";
	    
	$Query=is_null($iD) ?  $Query : $Query." AND customers.iD='$iD'";
	$result=getFetshALLTableWithQuery($Query);
	$resultID = array_map(function($tmp) { return $tmp['iD']; }, $result);
    if(is_array($result)){
        for($x=0;$x<count($result);$x++){
            $toDate= curdate();
            $balance=getBalanceDueTo($result[$x]['iD'],$toDate)['balance'];
            if($balance<=0){
                unset($result[$x]);
            }else{
          
              //  $customer=depthSearch($result[$x]['iD'],CUST,1,null,true,null);
              //  $customer['termsDate']=$result[$x]['termsDate'];
              //  $myArray[]=depthSearch($result[$x]['OrderID'],ORDR,1,null,null,null);
             //   $customer[ORDR]=$myArray;
               // $result[$x]['OrderID'];
            //    $customer['order']=
                array_push($response,$result[$x]);
            }
        }
        
    }
       return $response;	
}
function customerToPayNext(){
    $result=array();
	$response=array();
	$Query="SELECT 
	    extended_order_refund.iD AS OrderID,
	    customers.iD,
	    customers.name,
		extended_order_refund.termsDate AS termsDate
		FROM customers
		INNER JOIN extended_order_refund ON customers.iD = extended_order_refund.CustomerID  
	    WHERE (Date(extended_order_refund.termsDate)) >= (CurDate())";
	$result=getFetshALLTableWithQuery($Query);
	$resultID = array_map(function($tmp) { return $tmp['iD']; }, $result);
    if(is_array($result)){
        for($x=0;$x<count($result);$x++){
            $toDate= curdate();
            $balance=getBalanceDueTo($result[$x]['iD'],$toDate)['balance'];
            if($balance<=0){
                unset($result[$x]);
            }else{
          
                $customer=depthSearch($result[$x]['iD'],CUST,1,null,true,null);
                $customer['termsDate']=$result[$x]['termsDate'];
                $myArray[]=depthSearch($result[$x]['OrderID'],ORDR,1,null,null,null);
                $customer[ORDR]=$myArray;
               // $result[$x]['OrderID'];
            //    $customer['order']=
                array_push($response,$customer);
            }
        }
        
    }
    return $response;
}
?>