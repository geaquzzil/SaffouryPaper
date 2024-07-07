<?php 
$OBJECT_ACTIONS[ORDR_R]=function (&$object){
  //  print_r($object);
    $option=array();
    $iD=$object["OrderID"];
	$option["WHERE_EXTENSION"]= "`OrderID` = '$iD'";
	
	$details=depthSearch(null,ORDR_D,1,[],[PR,WARE],$option);
	if(count((is_countable($object[ORDR_R_D])?$object[ORDR_R_D]:[]))>0)
	foreach($object[ORDR_R_D] as &$ord){
	    $ord["orders_orders_details"]=	$details;
	} 
    
};
$OBJECT_ACTIONS[CUT_RESULT]=function(&$object){
   // return;
    
  
    $option=array();
    $iD=$object["ProductInputID"];
	$option["WHERE_EXTENSION"]= "`ProductInputID` = '$iD'";
	$details=depthSearch(null,PR_INPUT_D,1,[],true,$option);
    $object[PR_INPUT][PR_INPUT_D]=$details;
    
    
    $iD=$object["ProductOutputID"];
    $option["WHERE_EXTENSION"]= "`ProductOutputID` = '$iD'";
	$details=depthSearch(null,PR_OUTPUT_D,1,[],true,$option);
    $object[PR_OUTPUT][PR_OUTPUT_D]=$details;
    
    
    if(isActionTableIs(CUT_RESULT)){
    $iD=$object[CUT]['iD'];
    $option["WHERE_EXTENSION"]= "`PCRID` = '$iD'";
    $object[CUT][SIZE_CUT]=depthSearch(null,SIZE_CUT,1,[],[SIZE],$option);
    
    $iD=$object[CUT][PR]['iD'];
    $object[CUT][PR]=depthSearch($iD,PR,1,[PR],true,null);
    }
    
//    $object["products_outputs"]=depthSearch($object["ProductOutputID"],PR_OUTPUT,0,true,true,null);
    if(isActionTableIs(CUT)){
      //  depth()
    }
};
$OBJECT_ACTIONS[TYPE]=function(&$object){
	if(isCustomer()|| isGuest()){
		if(!checkPermissionActionWAR('text_prices_for_customer'))
		{
			unset($object['sellPrice']);
		}
		unset($object["purchasePrice"]);
	}else{
		if(!checkPermissionActionWAR('text_purchase_price')){
			unset($object["purchasePrice"]);
		}
	}
	 if(!isEmptyString($object['image'])){
        $object['image']=IMAGES_PATH.substr($object["image"], strripos($object["image"], "/"));
    }
    $iD=$object['iD'];
    $results=getFetshTableWithQuery("SELECT COUNT(  `ProductTypeID`) AS availability FROM `".PR_SEARCH."` WHERE `ProductTypeID`='$iD'");
    if(!empty($results) || !is_null($results)){
        $object['availability']=$results['availability'];
    }else{
        $object['availability']=0;
    }
    
};
$OBJECT_ACTIONS[HOME_ADS]=function(&$object){
	if(!isEmptyString($object['image'])){
        $object['image']=IMAGES_PATH.substr($object["image"], strripos($object["image"], "/"));
    }
};
$OBJECT_ACTIONS[ORDR]=function (&$object){
	if(isActionIsView()){
		
	}
};
$OBJECT_ACTIONS[SP]=function(&$object){
	if(isSetKeyAndNotNullFromObj($object,'isDirect')&&isTableIs(SP)){
		setKeyValueFromObj($object,JO,
		depthSearch(getKeyValueFromObj($object,"isDirect"),JO,0,[INC,CRED],null,null));
	}else{
		setKeyValueFromObj($object,JO,null);
	}
};
$OBJECT_ACTIONS[INC]=function(&$object){
      //  echo "  hi  ".INC;
       //  echo "  isSetKeyAndNotNullFromObj  ".isSetKeyAndNotNullFromObj($object,'isDirect')? " TRUE ":"FALSE ";
	if(isSetKeyAndNotNullFromObj($object,'isDirect')  and (isTableIs(INC))){
	//     echo "  isSetKeyAndNotNullFromObj  ";
		setKeyValueFromObj($object,JO,
		depthSearch(getKeyValueFromObj($object,"isDirect"),JO,0,[SP,DEBT],null,null));
		
		//checkToSetJournal($object);
	}else{
	//    echo " null journal  ";
		setKeyValueFromObj($object,JO,null);
	}
};
$OBJECT_ACTIONS[CRED]=function(&$object){
	if(isSetKeyAndNotNullFromObj($object,'isDirect')&&isTableIs(CRED)){
		setKeyValueFromObj($object,JO,
		depthSearch(getKeyValueFromObj($object,"isDirect"),JO,0,[SP,DEBT],null,null));
	}else{
		setKeyValueFromObj($object,JO,null);
	}
	//$object[JO]=depthSearch
	//checkToSetJournal($object);
	if(isActionIsView()&& checkPermissionActionWAR("list_customers_balances")){
		setKeyValueFromObj($object,'balance',getBalanceDue($object[KCUST])["balance"]);
	}
};
$OBJECT_ACTIONS[DEBT]=function(&$object){
	if(isSetKeyAndNotNullFromObj($object,'isDirect')&&isTableIs(DEBT)){
	  //  echo "TSASD";
		setKeyValueFromObj($object,JO,
		depthSearch(getKeyValueFromObj($object,"isDirect"),JO,0,[INC,CRED],null,null));
	}else{
		setKeyValueFromObj($object,JO,null);
	}
	if(isActionIsView() && checkPermissionActionWAR("list_customers_balances")){
		setKeyValueFromObj($object,'balance',getBalanceDue($object[KCUST])["balance"]);
	}
};
$OBJECT_ACTIONS[CUSTOMS_IMAGES]=function ( &$object){
    if(!isEmptyString($object['image'])){
        $object['image']=IMAGES_PATH.substr($object["image"], strripos($object["image"], "/"));
    }
};
$OBJECT_ACTIONS[CUST]=function(&$object){
   // return;
//	$object["isSupplier"]=$object[PURCH."_count"]>0;
	if(!isCustomer() && !checkPermissionActionWAR('text_customers_password')){
		unset($object['password']);
	}
	$balanceResult=isCustomer()|| checkPermissionActionWAR('list_customers_balances')?getBalanceDue($object['iD']):null;
	if(empty($balanceResult)||is_null($balanceResult)){
		$object["balance"]=0;
		$object["totalCredits"]=0;
		$object["totalDebits"]=0;
		$object["totalOrders"]=0;
		$object["totalPurchases"]=0;
	}else{
		$object["balance"]=$balanceResult["balance"];
		$object["totalCredits"]=$balanceResult["sumPay"];
		$object["totalDebits"]=$balanceResult["Sum_eq"];
		$object["totalOrders"]=$balanceResult["Sum_ExtendedPrice"];
		$object["totalPurchases"]=$balanceResult["Sum_sumPurchuses"];
	}
	if(isActionIsView() && (isCustomer() || checkPermissionActionWAR('list_customers') )  ){
		$object["customerTerms"]=customersTerms($object['iD']);
	}
};
$OBJECT_ACTIONS[PR_INV]=function(&$object){
	$object[WARE]=depthSearch($object['WarehouseID'],WARE,0,[],[],null);
	unset($object['iD']);
	unset($object['ProductID']);
};
$OBJECT_ACTIONS[PR]=function(&$object){
    
    if(checkRequestValue("<width>")){
        $size=array();
        $size["width"]=jsonDecode(getRequestValue("<width>"),true)[0];
        $size['length']=jsonDecode(getRequestValue("<length>"),true)[0];
        $object["requiredSize"]=$size;
        $waste=jsonDecode(getRequestValue("<maxWaste>"),true)[0];
        switch($waste){
            case "10 (mm)":
                $waste="M1";
                break;
                  case "20 (mm)":
                $waste="M2";
                break;
                  case "30 (mm)":
                $waste="M3";
                break;
                  case "40 (mm)":
                $waste="M4";
                break;
                  case "50 (mm)":
                $waste="M5";
                break;
                  case "60 (mm)":
                $waste="M6";
                break;
                  case "70 (mm)":
                $waste="M7";
                break;
                  case "80 (mm)":
                $waste="M8";
                break;
                  case "90 (mm)":
                $waste="M9";
                break;
                      case "100 (mm)":
                $waste="M10";
                break;
                
        }
        $object["requiredMaxWaste"]=$waste;
     }
	$iD=$object['iD'];
	$option=array();
	 if(checkPermissionActionWAR('text_products_quantity')){
	     	$option["WHERE_EXTENSION"]= "`ProductID` = '$iD' AND quantity <>0";
	        $result= depthSearch(null,PR_INV,0,[],[],$option);
	        if(empty($result) || is_null($result)){
		        $object['inStock']=null;
	        }else{
		        $object['inStock']=$result;
	       }
	    }else{
	        $object['inStock']=null;
	    }

	
	///Get Parents
	
	if(!is_null($object[PARENTID])){
	//	$parentID=$object[PARENTID];
	//	$iD=$object['iD'];
	//	$option=array();
	//	$option["WHERE_EXTENSION"]= " `iD` = '$parentID'";
	//	$object["parents"]=depthSearch(null,PR,1,[],true,$option);
	//	$option["WHERE_EXTENSION"]= " `ParentID` = '$parentID' ";
	//	$object["parentChildes"]=depthSearch(null,PR,1,[],true,$option);
	}
    // print_r($object);
    // die;
    if(isset($object["cut_requests_count"])){
    if($object["cut_requests_count"]>0){
        $option["WHERE_EXTENSION"]= "`ProductID` = '$iD'";
        $osbj=depthSearch(null,"pending_cut_requests",0,[],[],$option);
        
        if(!is_null($osbj)){
            if(!empty($osbj)){
                $object["pending_cut_requests"]=$osbj[0]["quantity"];
            }
        }
        
    }
        
    }
    if(isset($object["reservation_invoice_details_count"])){
    if(isEmployee()){
        $curDate=curdate();
        $option["WHERE_EXTENSION"]= "`ProductID` = '$iD' ";
        $osbj=depthSearch(null,"pending_reservation_invoice",0,[],[],$option);
      
        if(!is_null($osbj)){
            if(!empty($osbj)){
                $object["pending_reservation_invoice"]=$osbj[0]["quantity"];
            }
        }
   
    }
    }
     $option["WHERE_EXTENSION"]= "`ProductID` = '$iD'";
	if(!checkPermissionActionWAR('text_products_notes')){
		unset($object['comments']);
	}
	
};
$OBJECT_ACTIONS[EMP]=function(&$object){
	if(!isAdmin()){
		unset($object['password']);
		unset($object['profile']);
// 		unset($object['password']);
	}
	if(isEmployee()){
	 //   if(!is_null($object[PARENTID])){
	//    	$parentID=$object[PARENTID];
	//        $option=array();
	 //       $option["WHERE_EXTENSION"]= "`iD` = '$parentID'";
	 //       if(isSetKeyFromObj($object,"parents")){
	         //   echo "IS SET PARENT";
	 //           array_push($object["parents"],depthSearch(null,EMP,1,[],[],$option));
	 //       }else{
	          //  echo "SETTING PARENT";
	//	        $object["parents"]=depthSearch(null,EMP,1,[],[],$option);
	  //      }
	  //  }
	}
};
 $OBJECT_ACTIONS[TR]=function(&$object){
  //   echo " im in object exten \n ";
     unSetKeyFromObj($object,"warehouse");
     $object["fromWarehouse"]=depthSearch($object["fromWarehouse"],WARE,1,[],[],null);
     $object["toWarehouse"]=depthSearch($object["toWarehouse"],WARE,1,[],[],null);
 };
 
$BEFORE_SEND_RESPONSE[PR]=function(&$object){
   //todo check inventory  echo "\n im in \n ";
   return $object;  
     
 };
 
 $CUSTOM_SEARCH_QUERY[SIZE]=function($object){
    
    // die;
    if(is_numeric($object)){
        $s=$object;
        $ps=$object+90;
		$ms=$object-90;
		$results;
		if(checkRequestValue("<unit>")){
		    
		    $requestUnit=json_decode(getRequestValue("<unit>"),true);
		    if(isRequestValueIsRoll($requestUnit[0])){
		      $results=getFetshALLTableWithQuery("SELECT `sizes`.`iD` FROM ".SIZE. " WHERE `width` BETWEEN $ms AND $ps AND (`length`='0' OR `Length` IS NULL)");
		    }else{
		         $results=getFetshALLTableWithQuery("SELECT `sizes`.`iD` FROM ".SIZE. " WHERE `width` BETWEEN $ms AND $ps OR `length`  BETWEEN $ms AND $ps  AND (`Length` <>'0')");
		    }
		}else{
		    $results=getFetshALLTableWithQuery("SELECT `sizes`.`iD` FROM ".SIZE. " WHERE `width` BETWEEN $ms AND $ps OR `length`  BETWEEN $ms AND $ps"); 
		}
       
        
        if(empty($results)){
            return null;
        }
        $results = array_map(function($tmp) { return $tmp['iD']; }, $results);
        return $results;
    }
 
     return null;
 };
 $CUSTOM_JOIN[PR]=function(){
     if(checkRequestValue("requireInventory")){
         return " JOIN `products_search_view` ON `products_search_view`.`iD` =`products`.`iD`  ";
     }
     return null;
 };
 $CAN_SEARCH_IN_STRING_QUERY[CRED]=function($searchQuery,$tableName){
         if(is_numeric($searchQuery)){
            // echo ("   ".($tableName===SIZE)."  ".($tableName===GSM)." ".($tableName===CUSTOMS));
            return false;
         }else{
              return $tableName===EMP || $tableName===CUST;
         }
     
   return true;  
 };
  $CAN_SEARCH_IN_STRING_QUERY[ORDR]=function($searchQuery,$tableName){
         if(is_numeric($searchQuery)){
            // echo ("   ".($tableName===SIZE)."  ".($tableName===GSM)." ".($tableName===CUSTOMS));
            return false;
         }else{
              return $tableName===EMP || $tableName===CUST;
         }
     
   return true;  
 };
 $CAN_SEARCH_IN_STRING_QUERY[PR]=function($searchQuery,$tableName){
         if(is_numeric($searchQuery)){
            //  if(checkRequestValue("<unit>")){
            //      return  $tableName==GSM || $tableName===GSM;
            //  }
            // echo ("   ".($tableName===SIZE)."  ".($tableName===GSM)." ".($tableName===CUSTOMS));
            return $tableName===SIZE || $tableName===GSM ||$tableName==SIZE || $tableName==GSM;
         }else{
              return $tableName===TYPE ||$tableName==TYPE ;
         }
     
   return true;  
 };
 $CUSTOM_SEARCH_COL[PR]=function(){
   $columns=array();
   $columns[]="dateEnum";
   $columns[]="unit";
   $columns[]="width";
   $columns[]="length";
   $columns[]="maxWaste";
   return $columns;
 };
 function isRequestValueIsRoll($value){
     return $value=="Roll" OR $value=="رول" OR $value=="Reel" OR $value=="Reel(s)";
 }
 $CUSTOM_SEARCH_COL_GET[PR]=function($key,$value){
    // $whereQuery=array();
     $query="";
     switch($key){
         case "unit":
            // $stringSearchQuery=null;
            // if(checkRequestValue(getRequestValue("searchStringQuery"))){
            //     if(is_numeric(getRequestValue("searchStringQuery"))){
                    
            //     }
            // }
           //  echo " is unit $value \n";
              $value=jsonDecode($value,true);
              $value=$value[0];
             $results=array();
             if(isRequestValueIsRoll($value)){
                 //   echo " is Roll \n";
                 $results=getFetshALLTableWithQuery("SELECT `sizes`.`iD` FROM ".SIZE. " WHERE  `length`='0' OR `Length` IS NULL");
             }else if($value=="Pallet" OR $value=="بالة"){
               //     echo " is Pallet \n";
                 $results=getFetshALLTableWithQuery("SELECT `sizes`.`iD` FROM ".SIZE. " WHERE `Length` <>'0'");
             }
             if(empty($results)){
                    return "";
            }
            $results = array_map(function($tmp) { return $tmp['iD']; }, $results);
            $whereIds=implode("','",$results);
            return PR.".`SizeID` IN ('$whereIds')";
            
            
            
         case "dateEnum":
                $value=jsonDecode($value,true);
                $value=$value[0];
             	if($value=="All" OR $value=="الكل"){$value="";}
	            else if($value=="Today" OR $value=="اليوم"){
		        $query=" DATE(".PR.".date) = CURDATE() ";
	            }else if ($value=="This week" OR $value=="هذا الأسبوع"){
	            $query=" YEARWEEK(".PR.".date, 1) = YEARWEEK(CURDATE(), 1) ";
	            }else if ($value=="This month" OR  $value=="هذا الشهر"){
	            $query=" month(".PR.".date) = month(CURDATE()) ";
	            }else{
	            $query=" YEAR(".PR.".date) = YEAR(CURDATE()) ";
	            }	
                return $query;
        case"width":
                $Width=jsonDecode($value,true);
                $Width=$Width[0];
                $Length=jsonDecode(getRequestValue("<length>"),true);
		        $Length=$Length[0];
		        
                $MWidth=((int)$Width)*10; 
                $MLength=((int)$Length)*10;
                $results=array();
                
		        $query=" (`Length` BETWEEN '$Length' AND '$MLength' 
		        AND `Width` BETWEEN '$Width' AND '$MWidth'  
				OR 
				`Length` BETWEEN '$Width' AND '$MWidth' AND `Width` BETWEEN '$Length' AND '$MLength'
			    OR 
			    `Length` ='0' AND `Width` BETWEEN '$Length' AND '$MLength'
			    OR 
			    `Length` ='0' AND `Width` BETWEEN '$Length' AND '$MLength')";
			    
			    $results=getFetshALLTableWithQuery("SELECT `sizes`.`iD` FROM ".SIZE. " WHERE  $query");
			    if(empty($results)){
                    return "";
                }
                
                $results = array_map(function($tmp) { return $tmp['iD']; }, $results);
                $whereIds=implode("','",$results);
                return PR.".`SizeID` IN ('$whereIds')";
     }
   return $query;  
 };
?>