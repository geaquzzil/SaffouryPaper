<?php
	function getWareHouses($wareHousesIDs){
	    $response=array();
	    foreach($wareHousesIDs as $item){
	        $iD=$item["WarehouseID"];
	        array_push($response,getProductTable($iD,WAREHOUSE));
	    }
	    return $response;
	}
	function getSearchViewAll(){
		    return getFetshALLTableWithQuery("SELECT * FROM woocommerce");
    }
	function moveToWarehouse($products){
	    try{
	        $mainWareHouse=getFetshTableWithQuery("SELECT * FROM warehouse WHERE main='1'");
	     foreach($products as $product){
	            $wareHouseID=$mainWareHouse["iD"];
	            
	         //WarehouseID
	         addProudctInTraditionalWay($product);
	     }
	     return 1;
	    }catch(Exception $e){
	        return -1;
	    }
	}
	function insertPurchasesHeader(&$purchases){
		     insertCustomerFromObject($purchases);
			 $purchases["iD"]=searchForPurchasesAndInsert($purchases);
			 return $purchases;
			
	}
	function searchForPurchasesAndInsert($purchases){
	    try{
	        $billNo=$purchases["bill_No"];
	        $supplierID=$purchases["CustomerID"];
	        $EDate = date("Y-m-d",strtotime($purchases["date"]));
	        $purchasesID;
	        if(empty($billNo)){
	            $purchasesID=searchInsertGetIDWhereQuery($purchases,DB_TABLE_PURCHASES,"Date(date)='$EDate' AND  ( bill_No='' OR bill_No IS NULL) AND CustomerID='$supplierID'");  
	        }else{
	            $purchasesID=searchInsertGetIDWhereQuery($purchases,DB_TABLE_PURCHASES,"Date(date)='$EDate' AND bill_No='$billNo' AND CustomerID='$supplierID'");
	        }
	        return $purchasesID;
	    }catch(Exception $e){
	           echo 'Caught exception: ',  $e->getMessage(), "\n";
	    }
	}
	
	
	
	function searchInsertGetIDWhereQuery($object,$tableName,$WhereQuery)
		{
		    try{
				$result=getFetshTableWithQuery("SELECT * FROM $tableName WHERE "."$WhereQuery");
			//	print_r($result);
				if(empty($result))
				{
					if(isset($object["iD"])){
						unset($object["iD"]);
					}
					insert($object,$tableName);
					$iD=getFetshTableWithQuery(getQueryMaxID($tableName));
					//print_r($iD);
					return $iD["iD"];
				}else
				{	
					return $result["iD"];
				}
		    }catch(Exception $e){
	           echo 'Caught exception: ',  $e->getMessage(), "\n";
	    }
		}
		
		function SetProductDetails(&$prodcut){
		    SetProductManufactureCompany($prodcut);
		    SetProductGSM($prodcut);
		    SetGetProductType($prodcut);
		    SetProductSize($prodcut);
		    SetCustomsFromProduct($prodcut);
		}
		function SetProductGSM(&$product){
		    $GSM=$product["GSM"];
		    $product[DB_FORGIN_PRODUCT_GSM]=searchInsertGetID($GSM,DB_TABLE_PRODUCT_GSM,"gsm",$GSM["gsm"]);
		    $GSM["iD"]=$product[DB_FORGIN_PRODUCT_GSM];
		    $product['GSM']=$GSM;
		}
		function SetProductManufactureCompany(&$product){
		    $CMC=$jsonValue["country_Manufacture_Company"];
			$Manufacture=$CMC["manufacture_Company"];
			$Country=$CMC["country"];
			
			$product[DB_FORGIN_PRODUCT_CMC]=insertMTM($Country,$Manufacture,DB_TABLE_PRODUCT_COUNTRY,DB_TABLE_PRODUCT_MANUFACTURE_COMPANY,DB_TABLE_PRODUCT_MANUFACTURE_COMPANY_COUNTRY);
			$CMC["iD"]=$productToAdd[DB_FORGIN_PRODUCT_CMC];
			$product['country_Manufacture_Company']=$CMC;
		}
		function SetProductSize(&$product){
		    $Size=$product["PSize"];
		    $product[DB_FORGIN_PRODUCT_SIZE]=insertSize($Size);
		    $Size["iD"]=$product[DB_FORGIN_PRODUCT_SIZE];
		    $product['PSize']=$Size;
		}
		//this sets where object is set size as (size) parameter
		function SetSizeFromObject(&$product){
		    $Size=$product["size"];
		    $product[DB_FORGIN_PRODUCT_SIZE]=insertSize($Size);
		    $Size["iD"]=$product[DB_FORGIN_PRODUCT_SIZE];
		    $product['size']=$Size;
		}
		
	function AddEdit($jsonValue,$tableName)
	{
		switch($tableName)
		{
			case DB_TABLE_PRODUCT:
				
				$productToAdd=$jsonValue;
				SetProductDetails($productToAdd);
				$purchases=insertPurchasesHeader($productToAdd["purchases"]);
			    unset($productToAdd["purchases"]);
				$productToAdd["PurchasesID"]=$purchases["iD"];
				
			    return addProduct($productToAdd);
			    
				case DB_TABLE_DEBTS:
				case DB_TABLE_PAYMENTS:
				case DB_TABLE_SPENDING:
				case  DB_TABLE_INCOMES:
			    $FBValue=getFBEdit($jsonValue);
			    insertCustomerAndEqualityFromObject($jsonValue,$tableName);
				$isJournal=checkToAddRemoveJournal($jsonValue,$tableName);
				$ID=updateGetIDAllowZeroInJSON($jsonValue,$tableName);
				$jsonValue["iD"]=$ID;
				$jsonValue["fb_edit"]=$FBValue;
				send_notificationProcess($jsonValue,$tableName);
				return $ID;
				
				case DB_TABLE_PRODUCT_TYPE:
					try{
						
					updateGetID($jsonValue["price"],DB_TABLE_PRODUCT_PRICE);
				    return	updateGetID($jsonValue,DB_TABLE_PRODUCT_TYPE);
					}catch(Exception $e)
					{
						return -2;
					}
				case DB_TABLE_PRODUCT_MANUFACTURE_COMPANY:
					try{
				        return	updateGetID($jsonValue,DB_TABLE_PRODUCT_MANUFACTURE_COMPANY);
					}catch(Exception $e)
					{
						return -2;
					}
				case DB_TABLE_PERMISSION:
					insert($jsonValue,DB_TABLE_PERMISSION);
					return 1;
					case DB_TABLE_SPENDING_NAME:
					try{
			        	return updateGetID($jsonValue,DB_TABLE_SPENDING_NAME);
					}catch(Exception $e)
					{
						return -2;
					}
					case DB_TABLE_INCOMES_NAME:
					try{
				    	return updateGetID($jsonValue,DB_TABLE_INCOMES_NAME);
					}catch(Exception $e)
					{
						return -2;
					}
					case DB_TABLE_ORDER:
					return 1;
					case DB_TABLE_ORDER_DETAILS:
					try{
					    $orderDetails=$jsonValue['orderDetails'];
					    $returnedProduct=$jsonValue['returnedProduct'];
					    updateProduct($orderDetails['product']);
					    $productReturn=false;
					    if(!is_null($returnedProduct)){
					        updateProduct($returnedProduct);
					        $productReturn=true;
					    }
					    unsetOrderDetails($orderDetails);
					    unset($orderDetails['removeChildes']);
					    unset($orderDetails['hasParent']);
				        unset($orderDetails['productList']);
				        unset($orderDetails['product']);
				        $result=updateGetID($orderDetails,DB_TABLE_ORDER_DETAILS);
				        $customer['iD']=$jsonValue['CustomerID'];
				        if($productReturn){
				            send_return($customer,$jsonValue);
				        }else{
				            send_recall($customer,$jsonValue);
				        }
					    return $result;
					}catch(Exception $E){return -1;}
			
				
		}
	}
	function insertMoneyFundWithoutCheckingJournal($jsonValue,$tableName){
	            $FBValue=getFBEdit($jsonValue);
			    insertCustomerAndEqualityFromObject($jsonValue,$tableName);
				$ID=updateGetID($jsonValue,$tableName);
				$jsonValue["iD"]=$ID;
				$jsonValue["fb_edit"]=$FBValue;
				return $ID;
	}
	function getFBEdit($object){
	    if($object["iD"]==-1){
	        return "";
	    }else{
	        return "EDIT";
	    }
	}
	function insertEqualityFromObject(&$object){
	   $equality=$object["equalities"];
	   $result=getFetshTableWithQuery(getQueryValue(DB_TABLE_EQUALITY,"equality_To_dollar",$equality["equality_To_dollar"]));
	   if(empty($result))
		{
			$eqResult=getProductTableAndInsert($equality,DB_TABLE_EQUALITY);
			$object["EqualitiesID"]=$eqResult;
		}
		else
		{
			$object["EqualitiesID"]=$result["iD"];
		}
	}
	function insertCustomerFromObject(&$object){
	   
	    $iD;
	   	if($object['customer']["iD"]!=-1 or $object['customer']["iD"]!==-1){
	   	   $iD=$object['customer']["iD"];
	   	}
	    else{
	       $iD= searchInsertGetID($object['customer'],DB_TABLE_CUSTOMER,"name",$object['customer']['name']);
	    }
		$object["CustomerID"]=$iD;
		$object["customer"]=getFetshTableWithQuery("SELECT iD,name FROM customer WHERE iD='$iD'");
	}
	function insertAccountNameFromObject(&$object){
	    $spendingName=$object["accountName"];
		$object["NameID"]=searchInsertGetID($spendingName,DB_TABLE_INCOMES_NAME,"name",$spendingName["name"]);
	}
	function insertCustomerAndEqualityFromObject(&$object,$tableName){
	    insertEqualityFromObject($object);
	    switch($tableName){
	        case DB_TABLE_PAYMENTS:
	        case DB_TABLE_DEBTS:
	            insertCustomerFromObject($object);
	            break;
	        case DB_TABLE_SPENDING:
	        case DB_TABLE_INCOMES:
	            insertAccountNameFromObject($object);
	            break;
	                
	    }
	}
	function checkToAddRemoveJournal(&$object,$tableName){
	    try{
	    //new record with journal
	    if($object["iD"]==-1 AND $object["isDirect"]==-1){
	        $journalObject=$object["journal"];
	        $journalTable=getJournalTableName($object);
	        $journalID=getLastIncrementID("Journal_Voucher");
	        $object["isDirect"]=$journalID;
	        $journalObject["isDirect"]=$journalID;
	        $currentObjectID=getLastIncrementID($tableName);
	        
	        $currentObjectJournalID=getLastIncrementID($journalTable);
	        
	        insertMoneyFundWithoutCheckingJournal($journalObject,$journalTable);
	        
	        
	        $journal["iD"]=$journalID;
	        $journal["fromAccount"]=$currentObjectID;
	        $journal["toAccount"]=$currentObjectJournalID;
	        $journal["transaction"]=$object["transaction"];
	        
	        updateGetID($journal,"Journal_Voucher");
	        
	    }
	    //editing record
	    if($object["iD"]!=-1){
	        $origianlDirect=$object["isDirect"];
	        $originalID=$object["iD"];
	        $originalObject=getFetshTableWithQuery("SELECT * FROM $tableName Where iD='$originalID' ");
	        //editing object and removing journal
	        if($originalObject["isDirect"]!=0 && ($object["isDirect"]==0 || $object["isDirect"]==-1)){
	            checkToSetJournal($originalObject);
	            $journalTable=getJournalTableNameFromExisting($originalObject);
	            deleteObject($originalObject["journal"],$journalTable,false);
	            $journal["iD"]=$originalObject["isDirect"];
	            deleteObject($journal,"Journal_Voucher",false);
	            if($origianlDirect==0){
	                $object["isDirect"]=0;
	            }
	            
	           //already setted from android $object["isDirect"]=0;
	        }
	        //editing object but it was journal
	        if($originalObject["isDirect"]!=0 && $object["isDirect"]==-1){
	            $journalObject=$object["journal"];
	            $journalTable=getJournalTableName($object);
	            $journalID=getLastIncrementID("Journal_Voucher");
	            $object["isDirect"]=$journalID;
	            $journalObject["isDirect"]=$journalID;
	            $currentObjectID=$originalObject["iD"];
	        
	            $currentObjectJournalID=getLastIncrementID($journalTable);
	        
	            insertMoneyFundWithoutCheckingJournal($journalObject,$journalTable);
	        
	        
	            $journal["iD"]=$journalID;
	            $journal["fromAccount"]=$currentObjectID;
	            $journal["toAccount"]=$currentObjectJournalID;
	            $journal["transaction"]=$object["transaction"];
	        
	            updateGetID($journal,"Journal_Voucher");
	        }
	        //editing object but it was not journal
	        if($originalObject["isDirect"]==0 && $object["isDirect"]==-1){
	            $journalObject=$object["journal"];
	            $journalTable=getJournalTableName($object);
	            $journalID=getLastIncrementID("Journal_Voucher");
	            $object["isDirect"]=$journalID;
	            $journalObject["isDirect"]=$journalID;
	            $currentObjectID=$originalObject["iD"];
	        
	            $currentObjectJournalID=getLastIncrementID($journalTable);
	        
	            insertMoneyFundWithoutCheckingJournal($journalObject,$journalTable);
	        
	        
	            $journal["iD"]=$journalID;
	            $journal["fromAccount"]=$currentObjectID;
	            $journal["toAccount"]=$currentObjectJournalID;
	            $journal["transaction"]=$object["transaction"];
	        
	            updateGetID($journal,"Journal_Voucher");
	        }

	    }
	    
	    }catch(Exception $E){
	          print_r($E);
	          die;
	    }
	}
	function deleteJournaledObject($object){
	    // most be checked is journal before;
	    $journalTable=getJournalTableNameFromExisting($object);
	    deleteObject($object["journal"],$journalTable,false);
	    $journal["iD"]=$object["isDirect"];
	    deleteObject($journal,"Journal_Voucher",false);
	}
	function updateSetting($setting){
	    try{
	        send_setting_changed($setting);
	        return 	updateGetIDAllowZeroInJSON($setting,"setting");
	    }catch(Exception $E){
	        print_r($E);
	        return -1;}
	}
	function addPaymentWithoutDebit($jsonValue){
	            $payback=$jsonValue;
				$equality=$jsonValue["equalities"];
				$payback["CustomerID"]=getProductTableAndInsert($payback['customer'],DB_TABLE_CUSTOMER);
				$iD=$payback["CustomerID"];
				$payback["customer"]=getFetshTableWithQuery("SELECT iD,name FROM customer WHERE iD='$iD'");
				$result=getFetshTableWithQuery(getQueryValue(DB_TABLE_EQUALITY,"equality_To_dollar",$equality["equality_To_dollar"]));
				if(empty($result))	{	$eqResult=getProductTableAndInsert($equality,DB_TABLE_EQUALITY);	$payback["EqualitiesID"]=$eqResult;}
				else	{	$payback["EqualitiesID"]=$result["iD"];	}
				unset($payback["directCustomer"]);
				unset($payback["DirectID"]);
				$paybackID=updateGetID($payback,DB_TABLE_PAYMENTS);
				$payback["iD"]=$paybackID;
				if($jsonValue["iD"]!=-1)
				{
					$payback["fb_edit"]="EDIT";
				}else{
				    	$payback["fb_edit"]="";
				}
				send_payment($payback,$payback["customer"]);
	}
	function addProduct($productToAdd)	{
		$multiple=$productToAdd['multiple'];
		$multipleSeparated=$productToAdd['multipleSeparated'];
		$weights=$productToAdd['multipleWeights'];
		$quantity=$productToAdd['quantity'];
		unset($productToAdd['multiple']);
		unset($productToAdd['multipleSeparated']);
		unset($productToAdd['quantity']);
		unset($productToAdd['multipleWeights']);
		$sendNotification=$productToAdd["sendNotification"];
		unset($productToAdd['sendNotification']);
		if($sendNotification){
		    send_new_product(null);
		}
		else{
		    if(!is_null($weights)){return addSeperatedWeights($productToAdd,$weights);}
			if($multiple AND $multipleSeparated){return addMultipleProducts($productToAdd,$quantity);}
			else{return addProudctInTraditionalWay($productToAdd);}
		}
			
		
	}
	function AddEditProduct($jsonValue)	{
				$productToAdd=$jsonValue;
				$productToAdd["PurchasesID"]=$jsonValue["PurchasesID"];
				$productToAdd[DB_FORGIN_PRODUCT_SUPPLIER]=$jsonValue["purchases"][DB_FORGIN_PRODUCT_SUPPLIER];
				$productToAdd[DB_FORGIN_PRODUCT_GSM]=$jsonValue[DB_FORGIN_PRODUCT_GSM];
				$productToAdd[DB_FORGIN_PRODUCT_SIZE]=$jsonValue[DB_FORGIN_PRODUCT_SIZE];
				$productToAdd[DB_FORGIN_PRODUCT_TYPE]=$jsonValue[DB_FORGIN_PRODUCT_TYPE];
				$productToAdd[DB_FORGIN_PRODUCT_CMC]=$jsonValue[DB_FORGIN_PRODUCT_CMC];
				$Size["iD"]=$productToAdd[DB_FORGIN_PRODUCT_SIZE];
				$GSM["iD"]=$productToAdd[DB_FORGIN_PRODUCT_GSM];
				$CMC["iD"]=$productToAdd[DB_FORGIN_PRODUCT_CMC];
				$productToAdd['PSize']=$Size;
				$productToAdd['country_Manufacture_Company']=$CMC;
				$productToAdd['GSM']=$GSM;
                SetGetProductType($productToAdd);
				return addProudctInTraditionalWay($productToAdd);
	}
	function addSeperatedWeights($productToAdd,$weights){
	    		$productToAdd["productsAddedIDS"]=array();
	                foreach($weights as $weight){
	                        $productToAdd["net_weight"] =$weight["net"];
	                        $productToAdd["gross_weight"] =$weight["gross"];
	                          $productToAdd["num_Of_Sheets"] =$weight["sheet"];
	                        array_push($productToAdd["productsAddedIDS"],addProudctInTraditionalWay($productToAdd)["iD"]);
	                }
	            $productToAdd['iD']=$productToAdd["productsAddedIDS"][0];
	            return $productToAdd;
	}
	function addMultipleProducts($productToAdd,$quantity)	{
		        $productToAdd["productsAddedIDS"]=array();
		        for($i=0;$i<$quantity;$i++){array_push($productToAdd["productsAddedIDS"],addProudctInTraditionalWay($productToAdd)["iD"]);}
	            $productToAdd['iD']=$productToAdd["productsAddedIDS"][0];
	            return $productToAdd;
	}
	function addProudctInTraditionalWay($productToAdd)	{
		$productToAdd['iD']=updateGetID($productToAdd,DB_TABLE_PRODUCT);
		return getProductsByID($productToAdd['iD']);
	}
	function editCustomer($object)	{
		try{
			unset($object["dealers"]);
			unset($object["userlevel"]);
			 updateGetID($object,DB_TABLE_CUSTOMER);
			 return 1;
		}catch(Exception $e)
			{
			return -2;
		}
	}
	function editEmployee($object)	{
		try{
			unset($object["dealers"]);
			unset($object["userlevel"]);
			 updateGetID($object,DB_TABLE_EMPLOYEE);
			 return 1;
		}catch(Exception $e)
			{
			return -2;
		}
	}
	function insertSize($jsonValue)	{
				$tableName=DB_TABLE_PRODUCT_SIZE;
				$Length=$jsonValue["length"];
				$Width=$jsonValue["width"];
				$result=getFetshTableWithQuery("SELECT iD from $tableName WHERE length=$Length AND width=$Width");
				if(empty($result))
				{
					$jsonValue["iD"]=-1;
					return getProductTableAndInsert($jsonValue,$tableName);
				}else
				{
					return $result["iD"];
				}
	}
	function insertMTM($jsonValue1,$jsonValue2,$tableName1,$tableName2,$MTMtable)	{
			
			$CID=searchInsertGetID($jsonValue1,$tableName1,"name",$jsonValue1["name"]);
			$MID=searchInsertGetID($jsonValue2,$tableName2,"name",$jsonValue2["name"]);
			$result =getFetshTableWithQuery(getQueryMTM($MTMtable,$CID,$MID));
			if(empty($result))
			{
				$toUploadMCM["iD"]=-1;
				$toUploadMCM["CountryID"]=$CID;
				$toUploadMCM["Manufacture_CompanyID"]=$MID;
				return getProductTableAndInsert($toUploadMCM,DB_TABLE_PRODUCT_MANUFACTURE_COMPANY_COUNTRY);
			}
			else
			{
				return $result["iD"];
			}
			
	}
	

	function getUserLevels(){
			$levels=getFetshALLTableWithQuery("SELECT * FROM userlevels");
			$response["productSet"] = array();
			if(is_array($levels))
				{
				foreach($levels as $OD)
				{
					$ODID=$OD["userlevelid"];
					$res=$OD;
					$sad=getFetshTableWithQuery("SELECT * FROM permissions WHERE iD=$ODID");
					$res["userPermission"]=$sad==false?null:$sad;
					array_push($response["productSet"],$res);
				}
				}
				return $response["productSet"];
				
		}
	
		function getServerData()		{
			$pdo = setupDatabase();
			if (!is_a($pdo, "PDO")) {
				return $pdo;
				}
				try{
				$response["GSMList"]=getProductTables(DB_TABLE_PRODUCT_GSM);
				$response["manufactureCompanies"]=getProductTables(DB_TABLE_PRODUCT_MANUFACTURE_COMPANY);
				$response["countries"]=getProductTables(DB_TABLE_PRODUCT_COUNTRY);
				$response["pSizeList"]=getProductTables(DB_TABLE_PRODUCT_SIZE);
				$response["accountNameList"]=getProductTables(DB_TABLE_ACCOUNT_NAME);
				$response["warehouses"]=getProductTables(DB_TABLE_WAREHOUSE);
				$response["grads"]=getStrings("SELECT DISTINCT grade from product WHERE grade IS NOT NULL AND grade <> '' ORDER BY `product`.`grade` ASC","grade");
				$response["qualities"]=getStrings("SELECT DISTINCT quality from product WHERE quality IS NOT NULL AND quality <> '' ORDER BY `product`.`quality` ASC","quality");
				$response["mills"]=getStrings("SELECT DISTINCT mill from product WHERE mill IS NOT NULL AND mill <> '' ORDER BY `product`.`mill` ASC","mill");
				$response["setting"]=getServerSetting();
				$response["productTypeList"]=array();
				$response["customsDeclaration"]=array();
				$result=getProductTables(DB_TABLE_PRODUCT_TYPE);
				foreach($result as $row)
				{
					$iD=$row["PricesID"];
					$row["price"]=getProductTable($iD,DB_TABLE_PRODUCT_PRICE);
					array_push($response["productTypeList"],$row);
				}
				$customsResult=getProductTables("customs_declaration");
				foreach($customsResult as $row)
				                    {
					                    $iD=$row["iD"];
					                    $row["cdi"]=getFetshAllTableWithQuery("SELECT * FROM customs_declaration_images WHERE CustomsDeclarationID='$iD'");
					                    array_push($response["customsDeclaration"],$row);
				                    }
				return $response;
				}
				catch(PDOException $e)
		{
			return 'ERROR: ' . $e->getMessage();
		}
		}
		function getServerDataGuest(){
		    $pdo = setupDatabase();
			        if (!is_a($pdo, "PDO")) {
				        return $pdo;
				        }
			    		try{
			    		        $response["pSizeList"]=getProductTables(DB_TABLE_PRODUCT_SIZE);
			    		        $response["GSMList"]=getProductTables(DB_TABLE_PRODUCT_GSM);
			    		        $response["countries"]=getProductTables(DB_TABLE_PRODUCT_COUNTRY);
			    		        $response["manufactureCompanies"]=getProductTables(DB_TABLE_PRODUCT_MANUFACTURE_COMPANY);;
				                $response["productTypeList"]=getProductTables(DB_TABLE_PRODUCT_TYPE);
				                $response["supplierList"]=getEmployess();
				               	$response["qualities"]=getStrings("SELECT DISTINCT quality from product WHERE quality IS NOT NULL AND quality <> '' ORDER BY `product`.`quality` ASC","quality");
				                $response["setting"]=getServerSetting();
			                	return $response;
			    		    }
				catch(PDOException $e)
		                {
		                	return 'ERROR: ' . $e->getMessage();
		                }
		}
			function getServerDataServer()		{
			    	$pdo = setupDatabase();
			        if (!is_a($pdo, "PDO")) {
				        return $pdo;
				        }
			    		try{
			    		        $response["supplierList"]=getSuppliers();
			    		        $response["customerList"]=getCustomerServer();
			    		        $response["GSMList"]=getProductTables(DB_TABLE_PRODUCT_GSM);
			    		        $response["countries"]=getProductTables(DB_TABLE_PRODUCT_COUNTRY);
			    		        $response["manufactureCompanies"]=getProductTables(DB_TABLE_PRODUCT_MANUFACTURE_COMPANY);;
			    		    	$response["productTypeList"]=array();
			    		        $response["accountNameList"]=getProductTables(DB_TABLE_ACCOUNT_NAME);
			    		    	$response["setting"]=getServerSetting();
			    		    	$response["warehouses"]=getProductTables(DB_TABLE_WAREHOUSE);
			    		    	$response["customsDeclaration"]=array();
			    		    	$response["qualities"]=getStrings("SELECT DISTINCT quality from product WHERE quality IS NOT NULL AND quality <> '' ORDER BY `product`.`quality` ASC","quality");
				                $response["barcodes"]=getStrings("SELECT DISTINCT barcode from product WHERE barcode IS NOT NULL AND barcode <> ''","barcode");
				                $result=getProductTables(DB_TABLE_PRODUCT_TYPE);
				                foreach($result as $row)
				                    {
					                    $iD=$row["PricesID"];
					                    $row["price"]=getProductTable($iD,DB_TABLE_PRODUCT_PRICE);
					                    array_push($response["productTypeList"],$row);
				                    }
				                $customsResult=getProductTables("customs_declaration");
				                 foreach($customsResult as $row)
				                    {
					                    $iD=$row["iD"];
					                    $row["cdi"]=getFetshAllTableWithQuery("SELECT * FROM customs_declaration_images WHERE CustomsDeclarationID='$iD'");
					                    array_push($response["customsDeclaration"],$row);
				                    }
				                    
			                	return $response;
			    		    }
				catch(PDOException $e)
		                {
		                	return 'ERROR: ' . $e->getMessage();
		                }
		}
		function getCutRequestsSize($iD){
		    $response=array();
		    $res=getFetshAllTableWithQuery("SELECT * FROM product_cut_request_sizes WHERE PCRID='$iD'");
		    foreach($res as $r){
		        $r["size"]=getProductTable($r["PSizeID"],DB_TABLE_PRODUCT_SIZE);
		        	  array_push($response,$r);
		    }
		    return $response;
		}
		function getCutRequestDetails(&$object){
		      $iD=$object["iD"];
			  $object["product"]=getProductsByID($object["ProductID"]);
			  $CustomerID=$object['CustomerID'];
			  $object['customer']=getFetshTableWithQuery("SELECT iD,name FROM customer WHERE iD='$CustomerID'");
			  $object["prs"]=getCutRequestsSize($iD);
		}
		function getCutRequstByID($iD){
		    		   try{
		    		    $object=getFetshTableWithQuery("SELECT * FROM product_cut_request WHERE iD='$iD'");
		    		    getCutRequestDetails($object);
		    		    return $object;
		    		   }catch(Exception $e){return null;}
		}
		function getCutRequsts($DATE){
		   try{
		       $result;
		       if(is_null($DATE)){
		           $result=getProductTables("product_cut_request");
		       }else{
		          // print_r($DATE);
		           $FROM = date("Y-m-d",strtotime($DATE["from"]));
		           $TO = date("Y-m-d",strtotime($DATE["to"]));
		         //  print_r($FROM);
		          // print_r($TO);
		           $result=getFetshAllTableWithQuery("SELECT * FROM product_cut_request WHERE Date(date)>='$FROM' AND Date(date)<='$TO' ORDER BY date DESC ");
		       }
		         
		        $response=array();
		       	
				foreach($result as $row)
				    {
					  getCutRequestDetails($row);
					  array_push($response,$row);
	                }
			return $response;
		    }catch(Exception $e){return null;}
		}
		function getCustomsByID($iD){
		    try{
		    $customs=getFetshTableWithQuery("SELECT * FROM customs_declaration WHERE iD='$iD'");
		    $customs["cdi"]=getFetshAllTableWithQuery("SELECT * FROM customs_declaration_images WHERE CustomsDeclarationID='$iD'");
		    return $customs;
		    }catch(Exception $e){return null;}
		    
		}
		function getServerSetting(){
		    
		    return getFetshTableWithQuery("SELECT * FROM setting LIMIT 1");
		}

	function getStrings($query,$value)	{
			$result=getFetshALLTableWithQuery($query);
			$response=array();
			if($result==null)return $response;
			if(!is_null($result))
			{
				if(is_array($result))
				{
					foreach($result as $r)
					{
					    
						array_push($response,$r[$value]);
					}
					
				}
			}
			return $response;
		}
		function getWastedProducts($FROM,$TO){
		    try{
		        $query;
		        if(is_null($FROM) || is_null($TO)){
		            $query="SELECT * FROM product WHERE OrderStatus='WASTED' ORDER BY date";
		        }else{
		             $query="SELECT * FROM product WHERE OrderStatus='WASTED' ORDER BY date DESC LIMIT $FROM,$TO";
		        }
		        $result=getFetshAllTableWithQuery($query);
				$response["productSet"] = array();
				if(is_array($result)){foreach($result as $row) {array_push($response["productSet"],getProductDetails($row,true,true));}}
				return $response["productSet"];			
		}catch(PDOException $e)
		{
			return 'ERROR: ' . $e->getMessage();
		}
		}
	function getProducts($FROM,$TO)	{
	    	try{
			$pdo = setupDatabase();
			if (!is_a($pdo, "PDO")) {
			    echo" SDADA";
				return $pdo;
				}
			
					
				$stmt = $pdo->prepare("SELECT * FROM product WHERE isTakenAll!=1 ORDER BY date DESC LIMIT $FROM,$TO");
				$stmt->execute();
				$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
				$response["productSet"] = array();
				if(is_array($result))
				{
				foreach($result as $row) {
					array_push($response["productSet"],getProductDetails($row,true,true));
					}
				}
				return $response["productSet"];			
		}catch(PDOException $e)
		{
			return 'ERROR: ' . $e->getMessage();
		}
		}
		function getPurchasesFromProduct($row){
		    
		    $purchases=getProductTable($row["PurchasesID"],DB_TABLE_PURCHASES);
		    
		    $purchases["customer"]=getProductTable($purchases["CustomerID"],DB_TABLE_SUPPLIER);
		    
		    $purchases["employee"]=getProductTable($purchases["EmployeeID"],DB_TABLE_EMPLOYEE);
		    $purchases["warehouse"]=getProductTable($purchases["WarehouseID"],DB_TABLE_WAREHOUSE);
		    
		    return $purchases;
		    
		    
		}
	function getSupplierProducts($supplierID)	{
			$pdo = setupDatabase();
			if (!is_a($pdo, "PDO")) {
				return $pdo;
				}
				try{
					
				$stmt = $pdo->prepare("SELECT * FROM products WHERE CustomerID='$supplierID' ORDER BY date DESC");
				$stmt->execute();
				$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
				return getPurchasesRow($result);
		}catch(PDOException $e)
		{
			return 'ERROR: ' . $e->getMessage();
		}
		}
		function getPurchasesRow($result){
		    $response["purchasesSet"]=array();
				if(is_array($result))
				{
				foreach($result as $row) {
				    $purchases=$row;
				    $purchases["products"]=array();
				    $purchases["products"]=getProductsByPurchasesID($row["iD"]);
				    $purchases["employee"]=getProductTable($purchases["EmployeeID"],DB_TABLE_EMPLOYEE);
				    $purchases["warehouse"]=getProductTable($purchases["WarehouseID"],DB_TABLE_WAREHOUSE);
					array_push($response["purchasesSet"],$purchases);
					}
				}
				return $response["purchasesSet"];
		}
		function getProductsByIDWithOutPurchases($productID){
		    $pdo = setupDatabase();
			if (!is_a($pdo, "PDO")) {
				return $pdo;
				}
				try{
				$stmt = $pdo->prepare("SELECT * FROM product WHERE iD=$productID");	
				$stmt->execute();
				$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
				return getProductDetails($result,false,true);				
		}catch(PDOException $e)
		{
			return 'ERROR: ' . $e->getMessage();
		}
		}
		function getPurchasesDetailsByID($purchasesID){
		     $pdo = setupDatabase();
			if (!is_a($pdo, "PDO")) {
				return $pdo;
				}
				try{
				$stmt = $pdo->prepare("SELECT * FROM products WHERE PurchasesID='$purchasesID'");	
				$stmt->execute();
				$result = $stmt->fetch(PDO::FETCH_ASSOC);
				if(empty($result))return null;
				$CCID=$result["CustomerID"];
				$result["customer"]=getFetshTableWithQuery("SELECT iD,name,phone,address FROM customer WHERE iD='$CCID'");
			    $result["customer"]["balance"]=getBalanceDue($CCID)["balance"];
				$CCID=$result["EmployeeID"];
				$result["employee"]=getFetshTableWithQuery("SELECT iD,name,phone FROM employee WHERE iD=$CCID");
				$CCID=$result["WarehouseID"];
				$result["warehouse"]=getFetshTableWithQuery("SELECT * FROM warehouse WHERE iD=$CCID");
				
				return $result;
		}catch(PDOException $e)
		{
			return 'ERROR: ' . $e->getMessage();
		}
				
		}
		function getProductsByPurchasesHeadID($purchasesID){
		 try{
			$response=getPurchasesDetailsByID($purchasesID);
			if(empty($response))return null;
			$response["products"]=getProductsByPurchasesID($purchasesID);
			return $response;
		    }catch(PDOException $e){return 'ERROR: ' . $e->getMessage();}
		}
		function getProductsByPurchasesID($purchasesID){
			$pdo = setupDatabase();
			if (!is_a($pdo, "PDO")) {
				return $pdo;
				}
				try{
				    
				$stmt = $pdo->prepare("SELECT * FROM product WHERE PurchasesID='$purchasesID'");	
				$stmt->execute();
				$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
				$response["productSet"] = array();
		
				if(is_array($result))
				{
				    foreach($result as $row) {
				        array_push($response["productSet"],getProductDetails($row,false,true));}
				}	
				return $response["productSet"];			
		}catch(PDOException $e)
		{
			return 'ERROR: ' . $e->getMessage();
		}
		}
		function getProductDetails($row,$withPurchases,$withParents){
		            $productResult = array();
		            $parents=array();
					$productResult=$row;
					$productResult["productType"]=getProductType($row);
					$productResult["PSize"]=getProductTable($row["PSizeID"],DB_TABLE_PRODUCT_SIZE);
					$productResult["GSM"]=getProductTable($row["GramagID"],DB_TABLE_PRODUCT_GSM);
					
					$iD=$productResult["iD"];
					$productResult["canDelete"]= canDeleteProduct($productResult);
					$productResult["country_Manufacture_Company"]=getCountryManufacture($row);
					if($withPurchases){$productResult["purchases"]=getPurchasesFromProduct($row);}
					if($productResult["CustomsDeclarationID"]!=-1){
					    $productResult["customsDeclaration"]=getCustomsByID($productResult["CustomsDeclarationID"]);
					}
					if($withParents){
				    	if($productResult["ParentID"]!=0){
				    	  //  echo $row["iD"]. " has pare t";
					       $productResult["parents"]= getParents($row,$parents);
					       $productResult["parentChildes"]=getParentAnotherChildes($row,$productResult["ParentID"],false);
				    	}else{
				    	$productResult["parentChildes"]=getParentAnotherChildes($row,$row["iD"],true);
				    	}
					}
					return $productResult;
		}
		function canDeleteProduct($product){
		    $iD=$product["iD"];
		    return   getFetshTableWithQuery("SELECT count(*) as result FROM orderdetails WHERE ProductID='$iD'")["result"]==0;
		}
		function getParents($product,$parents){
		    if($product["ParentID"]==0){return $parents;}
		    $parentProduct=getProductsByIDWithOutParents($product["ParentID"]);
		    array_push($parents,$parentProduct);
		    return getParents($parentProduct,$parents);
		}
		///
		///
		///
		///
		function getParentAnotherChildes($row,$ParentID,$allChildes){
		    $parentChildes=array();
		    $iD=$row['iD'];

		    if($allChildes){
		            $childes=getFetshAllTableWithQuery("SELECT iD FROM product WHERE ParentID='$iD'");
		            if(empty($childes))return null;
		            foreach($childes as $child){
		                $childProduct=getProductsByIDWithOutParents($child["iD"]);
		                array_push($parentChildes,$childProduct);
		            }
		             return $parentChildes;
		    }else{
		    $anotherChildID=getFetshTableWithQuery("SELECT iD FROM product WHERE ParentID='$ParentID' AND iD!= '$iD'")["iD"];
		    $childProduct=getProductsByIDWithOutParents($anotherChildID);
		    array_push($parentChildes,$childProduct);
		    return $parentChildes;
		    }
		    
		}
		function getProductsByIDWithOutParents($productID){
		    	$pdo = setupDatabase();
			if (!is_a($pdo, "PDO")) {
				return $pdo;
			}
				try{
				$stmt = $pdo->prepare("SELECT * FROM product WHERE iD=$productID");	
				$stmt->execute();
				$result = $stmt->fetch(PDO::FETCH_ASSOC);
				
				return getProductDetails($result,true,false);				
		      }catch(PDOException $e)
		            {
			            return 'ERROR: ' . $e->getMessage();
		            }
		}
		function validateProductsByBarCode($productID){
		     $pdo = setupDatabase();
			if (!is_a($pdo, "PDO")) {
				return $pdo;
			}
				try{
				$sProductID="0".$productID;    
				$stmt = $pdo->prepare("SELECT * FROM product WHERE barcode='$productID' OR barcode='$sProductID' ");	
				$stmt->execute();
				$result = $stmt->fetch(PDO::FETCH_ASSOC);
				$res["barCode"]=$productID;
				$res["validate"]=!empty($result);
				return $res;
		      }catch(PDOException $e)
		            {
			            return 'ERROR: ' . $e->getMessage();
		            }
		}
		function getProductsByBarCode($productID){
		    $pdo = setupDatabase();
			if (!is_a($pdo, "PDO")) {
				return $pdo;
			}
				try{
				    	$sProductID="0".$productID;    
				$stmt = $pdo->prepare("SELECT * FROM product WHERE barcode='$productID' OR barcode='$sProductID'");	
				$stmt->execute();
				$result = $stmt->fetch(PDO::FETCH_ASSOC);
				if(empty($result)){
				    $result["iD"]=-1;
				    $result["barcode"]=$productID;
				    $result["similarBarcode"]=false;
				    try
				    {
				        $stmt = $pdo->prepare("SELECT * FROM product WHERE SUBSTRING(barcode,1,CHAR_LENGTH(barcode)-4)=SUBSTRING('$productID',1,CHAR_LENGTH('$productID')-4)
				        
				        OR SUBSTRING(barcode,1,CHAR_LENGTH(barcode)-4)=SUBSTRING('$sProductID',1,CHAR_LENGTH('$sProductID')-4)");	
			    	    $stmt->execute();
				        $secoundResult = $stmt->fetch(PDO::FETCH_ASSOC);
				        if(!empty($secoundResult)){
				            $secoundResult= getProductDetails($secoundResult,true,true);
				            $secoundResult["iD"]=-1;
				            $secoundResult["barcode"]=$productID;
				            $secoundResult["similarBarcode"]=true;
				            $result=$secoundResult;
				            
				        }
				    }
				    catch(PDOException $e){}
				    return $result;
				}
				return getProductDetails($result,true,true);				
		      }catch(PDOException $e)
		            {
			            return 'ERROR: ' . $e->getMessage();
		            }
		}
	function getProductsByID($productID){
			$pdo = setupDatabase();
			if (!is_a($pdo, "PDO")) {
				return $pdo;
			}
				try{
				$stmt = $pdo->prepare("SELECT * FROM product WHERE iD=$productID");	
				$stmt->execute();
				$result = $stmt->fetch(PDO::FETCH_ASSOC);
				return getProductDetails($result,true,true);				
		      }catch(PDOException $e)
		            {
			            return 'ERROR: ' . $e->getMessage();
		            }
		}
    	function getProductsByListID($productID){
				$pdo = setupDatabase();
			if (!is_a($pdo, "PDO")) {
				return $pdo;
				}
				try{
					$pIDS=implode(json_decode($productID,true),"','");
					//echo "SELECT * FROM product WHERE iD IN ( '" . $pIDS . "' )";
					$stmt = $pdo->prepare("SELECT * FROM product WHERE iD IN ( '" . $pIDS . "' )");
					$stmt->execute();
					$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
				    $response["productSet"] = array();
				    if(is_array($result))
				        {
			            	foreach($result as $row) {array_push($response["productSet"],getProductDetails($row,true,true));}
				        }	
					return $response["productSet"];
		}catch(PDOException $e)
		{
			return 'ERROR: ' . $e->getMessage();
		}
		}
		function getSearchSizeAnalyizer($object){
		    try{
		    $width=$object["Width"];
		    $length=$object["Length"];
		    
		    $mlength=$length*10;
		    $mwidth=$width*10;
		    
		    $type=$object["Type"];
		    $gSM=$object["GSM"];
		    if($type=="All"){
			    $type="";
			}else{
			    $type="AND Type = '$type' ";
			}
			if($gSM=="All"){
			    $gSM="";
			}else{
			    $gSM="AND GSMR = '$gSM' ";
			}
			$sizeStatement="AND (Length BETWEEN '$length' AND '$mlength'  AND Width BETWEEN '$width' AND '$mwidth'  
			                OR Length BETWEEN '$width' AND '$mwidth' AND Width BETWEEN '$length' AND '$mlength'
			                OR Length ='0' AND Width BETWEEN '$length' AND '$mlength'
			                OR Length ='0' AND Width BETWEEN '$length' AND '$mlength')";
			$sql="SELECT iD FROM searchview WHERE isTakenAll <> '1' $type $gSM $sizeStatement";
			$result = getFetshALLTableWithQuery($sql);
			$response["productSet"] = array();
		    if(!is_null($result)){if(is_array($result)){foreach($result as $row) {array_push($response["productSet"],getProductsByID($row['iD']));}}}
			return $response["productSet"];	
		    }catch(PDOException $e)
		    {
			    return 'ERROR: ' . $e;
		    }
		}
		function getSearchAdvancedProducts($object){
		    try{
			$type=$object["Type"];
			$country=$object["country"];
			$gSM=$object["GSM"];
			$unit=$object["unit"];
			$date=$object["date"];
			$quality=$object["quality"];
			if($type=="All" OR $type=="الكل"){
			    $type="";
			}else{
			    $type="AND Type = '$type' ";
			}
			if($gSM=="All"){
			    $gSM="";
			}else{
			    $gSM="AND GSMR = '$gSM' ";
			}
			//Unit statement
			if($unit=="Roll" OR $unit=="رول"){
			    $unit="AND Length='0' ";
			}else if($unit=="Pallet" OR $unit=="بالة"){
			    $unit="AND Length <>'0' ";
			}else{
			    $unit="";
			}
			//Date statement
			if($date=="All" OR $date=="الكل"){
			    $date="";
			}else if($date=="Today" OR $date=="اليوم"){
			    $date="AND DATE(date) = CURDATE() ";//DATE(`timestamp`) = CURDATE()
			}else if ($date=="This week" OR $date=="هذا الأسبوع"){
			    $date="AND YEARWEEK(date, 1) = YEARWEEK(CURDATE(), 1) ";//YEARWEEK(`date`, 1) = YEARWEEK(CURDATE(), 1)
			}else if ($date=="This month" OR  $date=="هذا الشهر"){
			    $date="AND month(date) = month(CURDATE()) ";
			}else{
			    $date="AND YEAR(date) = YEAR(CURDATE()) ";
			    //this year
			}
		
			if($country=="All" OR $country=="الكل"){
			     $country="";
			}else{
			    $country="AND Country = '$country' ";
			}
		
			if($quality=="All" OR $quality=="الكل"){
			     $quality="";
			}else{
			    $quality="AND Quality = '$quality' ";
			}
			$sql="SELECT iD FROM searchview WHERE isTakenAll <> '1' $type $gSM $unit $date $country $quality";
			$result = getFetshALLTableWithQuery($sql);
			$response["productSet"] = array();
		    if(!is_null($result)){if(is_array($result)){foreach($result as $row) {array_push($response["productSet"],getProductsByID($row['iD']));}}}
			return $response["productSet"];	
		    }catch(PDOException $e)
		    {
			    return 'ERROR: ' . $e;
		    }
		}
		function getSearchSimilarProducts($object){
			try{
			$type=$object["Type"];
			$width=$object["Width"];
			$length=$object["Length"];
			$gSM=$object["GSM"];
			$result = getFetshALLTableWithQuery("SELECT iD FROM searchview WHERE isTakenAll <> '1' AND Length = '$length' AND Width = '$width' AND Type = '$type' AND GSMR = '$gSM'");
			$response["productSet"] = array();
		    if(!is_null($result)){if(is_array($result)){foreach($result as $row) {array_push($response["productSet"],getProductsByID($row['iD']));}}}
			return $response["productSet"];	
		    }catch(PDOException $e)
		    {
			    return 'ERROR: ' . $e;
		    }
		}
	function getSearchProducts($SEARCH_QUERY,$SEARCH_ID,$TO){
			$pdo = setupDatabase();
			if (!is_a($pdo, "PDO")) {
				return $pdo;
				}
			try{
			$searchcirtinia='%'.$SEARCH_QUERY.'%';
			$pIDS=implode(json_decode($SEARCH_ID,true),"','");
			$stmt;
			if(is_numeric($SEARCH_QUERY)){
			$stmt = $pdo->prepare("SELECT * FROM searchview WHERE iD NOT IN ( '" . $pIDS . "' )"." AND isTakenAll <> '1' AND ( (Length = :search_query_num) OR ( ABS(Length - :search_query_num) <=30)OR (Width = :search_query_num ) OR ( ABS(Width - :search_query_num) <=30) OR (GSMR = :search_query_num)) 
			ORDER BY Length ASC ,Width ASC LIMIT $TO");
			$stmt->bindParam(':search_query_num', $SEARCH_QUERY,PDO::PARAM_INT);
			}
			else{
			$stmt = $pdo->prepare("SELECT * FROM searchview WHERE iD NOT IN ( '" . $pIDS . "' )"." AND isTakenAll <> '1' 
			AND 
			(
			
			Type LIKE :search_query
			OR Manufacture LIKE :search_query 
			OR Country LIKE :search_query
			
			) LIMIT $TO");
			$stmt->bindParam(':search_query', $SEARCH_QUERY,PDO::PARAM_STR);
			}
			$stmt->execute();
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$response["productSet"] = array();
			if(is_array($result)){foreach($result as $row) {array_push($response["productSet"],getProductsByID($row['iD']));}}
			return $response["productSet"];	
		    }catch(PDOException $e)
		        {
			return 'ERROR: ' . $e->getMessage();
		        }
		}
	function getCustomers($EMPLOYEE)		{
			//$DEALER=$EMPLOYEE["dealers"];
			//$DID=$DEALER["iD"];
			return getFetshALLTableWithQuery("SELECT * FROM customer");
		}	
		function getCustomerServer(){
		    		return getFetshALLTableWithQuery("SELECT iD,name,first FROM customer");
		}
			function getSuppliers()		{
			return getFetshALLTableWithQuery("SELECT iD,name,first FROM customer WHERE isSupplier='1'");
		}	
			function getEmployess()		{
			return getFetshALLTableWithQuery("SELECT iD,name,phone FROM employee WHERE publish='1'");
		}
			function getEmployeeList($EMPLOYEE,$FROM,$TO,$ID)
		{
			$customerList;
			if($ID==-1)
			{
			$customerList=getFetshALLTableWithQuery("SELECT * FROM employee ORDER BY date DESC LIMIT $FROM,$TO ");
			}else
			{
			$customerList=getFetshALLTableWithQuery("SELECT * FROM employee WHERE iD=$ID ");
			}
			$response["customers"] = array();
			if(is_array($customerList))
				{
					foreach($customerList as $emp)
					{
							$emp["dealers"]=getProductTable($emp["DealersID"],DB_TABLE_DEALER);
							$emp["userlevel"]=getProductTable($emp["userlevelid"],DB_TABLE_USER_LEVEL);
							array_push($response["customers"],$emp);
					}
				}
			return $response["customers"];
		}
		
			function getBlockinList()		{
			$response["customers"]=getFetshALLTableWithQuery("SELECT iD,name,activated FROM customer WHERE isSupplier='0' OR isSupplier IS NULL");
			$response["employees"]=getFetshALLTableWithQuery("SELECT iD,name,activated FROM employee WHERE userlevelid <> '-1'");
			$response["suppliers"]=getFetshALLTableWithQuery("SELECT iD,name,activated FROM customer WHERE isSupplier='1'");
			return $response;
		}
		function getCustomerStatement($ID,$FROM,$TO){
		    $customer=getFetshTableWithQuery("SELECT * FROM customer WHERE iD='$ID'");
		    if(is_null($customer))return null;
		    $FROM = date("Y-m-d",strtotime($FROM));
		    $TO = date("Y-m-d",strtotime($TO));
		    $E_FROM= date('Y-m-d',(strtotime ( '-1 day' , strtotime ( $FROM) ) ));
		    $SUPPLIERS=$customer['isSupplier']==1;
		    $orders=getFetshALLTableWithQuery("SELECT * FROM extendedorder WHERE CustomerID='$ID' AND Date(date)>='$FROM' AND Date(date)<='$TO' ORDER BY date DESC");
			$cust=$customer;
			$cust["orders"]=array();
			if(is_array($orders))
			    {
				  foreach($orders as $OD)
				    {
					   $ODID=$OD["iD"];    
					   $res=getFetshALLTableWithQuery("SELECT * FROM orderdetails WHERE OrdersID=$ODID");
					   $OD["orderDetails"]=array();
					   foreach($res as $ODD)
					     {
						   $iD=$ODD["iD"];
						   $ODD["product"]=getProductsByID($ODD["ProductID"]);
						   array_push($OD["orderDetails"],$ODD);
					     }
					   array_push($cust["orders"],$OD);
				      }               
				 }
			$cust["debts"]=getDebtsPaysArrayFromTo("equalities","debts","equalities","CustomerID",$ID
					,"currency","CurrencyID","currency",0,$FROM,$TO);
			$cust["payback"]=getDebtsPaysArrayFromTo("equalities","payback","equalities","CustomerID",$ID
					,"currency","CurrencyID","currency",0,$FROM,$TO);  
			
			
			$cust["previousBalance"]=getBalanceDueTo($ID,$E_FROM)["balance"];
			$balanceResult=getBalanceDueFromTo($ID,$FROM,$TO);
			$cust["balance"]=$balanceResult["balance"];
			$cust["totalCredits"]=$balanceResult["sumPay"];
			$cust["totalDebits"]=$balanceResult["Sum_eq"];
			$cust["totalOrders"]=$balanceResult["Sum_ExtendedPrice"];
			$cust["totalPurchases"]=$balanceResult["Sum_sumPurchuses"];
			$cust["pruchasedProducts"]=array();
			if($SUPPLIERS)
			{
			$cust["pruchasedProducts"]=getSupplierProducts($ID);
			}		
			return $cust;		
					                    
		    }
		function getCustomerByID($EMPLOYEE,$ID){
		    	$customerList=getFetshALLTableWithQuery("SELECT * FROM customer WHERE iD=$ID ");
		    	$SUPPLIERS;
		    	$response["customers"] = array();
			    if(is_array($customerList))
				    {	
				        foreach($customerList as $row)
			            {
				            $SUPPLIERS=$row['isSupplier']==1;
				            $cust = array();
				            $cust=$row;
				            $customerID=$row["iD"];
				            $orders=getFetshALLTableWithQuery("SELECT * FROM orders WHERE CustomerID=$customerID ORDER BY date DESC");
			        	    $cust["orders"]=array();
			        	    if(is_array($orders))
				                {
				                    foreach($orders as $OD)
				                        {
					                        $ODID=$OD["iD"];    
					                        $res=getFetshALLTableWithQuery("SELECT * FROM orderdetails WHERE OrdersID=$ODID");
					                        $OD["orderDetails"]=array();
					                            foreach($res as $ODD)
					                                {
						                                $iD=$ODD["iD"];
						                                $ODD["product"]=getProductsByID($ODD["ProductID"]);
						                              array_push($OD["orderDetails"],$ODD);
					                                }
					                        array_push($cust["orders"],$OD);
				                        }               
				                }
				            $cust["debts"]=getDebtsPaysArray("equalities","debts","equalities","CustomerID",$customerID
					                    ,"currency","CurrencyID","currency",0,null);
				            $cust["payback"]=getDebtsPaysArray("equalities","payback","equalities","CustomerID",$customerID
					                    ,"currency","CurrencyID","currency",0,null);
					        $balanceResult=getBalanceDue($customerID);
					        $cust["balance"]=$balanceResult["balance"];
					        $cust["totalCredits"]=$balanceResult["sumPay"];
					        $cust["totalDebits"]=$balanceResult["Sum_eq"];
					        $cust["totalOrders"]=$balanceResult["Sum_ExtendedPrice"];
					        $cust["totalPurchases"]=$balanceResult["Sum_sumPurchuses"];
					        
				            if($SUPPLIERS)
				                {
				                  	$cust["pruchasedProducts"]=getSupplierProducts($customerID);
				  	               
				                }
				            array_push($response["customers"],$cust);
			            }
				    }
				    
				return $response["customers"];
		    	
		}
		function getCustomerBalances(){
		    $response["customers"] = array();
		    $customerList=getFetshALLTableWithQuery("SELECT * FROM customer WHERE first != 1");
		    if(is_array($customerList))
				{
				    foreach($customerList as $row)
			            {
			                    $customerID=$row["iD"];
			                    $cust["iD"]=$customerID;
			                    $cust["name"]=$row["name"];
			                    $cust["phone"]=$row["phone"];
			                    $cust["isSupplier"]=$row["isSupplier"];
			                	$balanceResult=getBalanceDue($customerID);
			                    $cust["balance"]=$balanceResult["balance"];
				                $cust["totalCredits"]=$balanceResult["sumPay"];
			                    $cust["totalDebits"]=$balanceResult["Sum_eq"];
				                $cust["totalOrders"]=$balanceResult["Sum_ExtendedPrice"];
			                    $cust["totalPurchases"]=$balanceResult["Sum_sumPurchuses"];
			                   	$cust["payback"]=getDebtsPaysArray("equalities","payback","equalities","CustomerID",$customerID
					                    ,"currency","CurrencyID","currency",0,null);
			                    $productMovement=array();
			                    $recallProduct=getFetshTableWithQuery("SELECT * from productmovement WHERE customerID='$customerID' AND recall='1' ORDER BY date ASC");
						        if(is_array($recallProduct))
						            {
							            $productMovement[]=$recallProduct;
						            }
						        $cust["productMovementList"]=$productMovement;
						        array_push($response["customers"],$cust);
						        
			            }
				}
			return $response["customers"];
		}
    	function getCustomerList($EMPLOYEE,$FROM,$TO,$ID,$SUPPLIERS)
		{
			
			$customerList;
			if($ID==-1 && !$SUPPLIERS)
			{
				$customerList=getFetshALLTableWithQuery("SELECT * FROM customer WHERE isSupplier='0' OR isSupplier IS NULL ORDER BY date DESC LIMIT $FROM,$TO ");
			}if($ID!=-1 &&!$SUPPLIERS)
			{
				$customerList=getFetshALLTableWithQuery("SELECT * FROM customer WHERE iD=$ID ");
			}
			if($ID==-1 && $SUPPLIERS)
			{
				$customerList=getFetshALLTableWithQuery("SELECT * FROM customer WHERE isSupplier='1' ORDER BY date DESC LIMIT $FROM,$TO ");
			}if($ID!=-1 &&$SUPPLIERS)
			{
			   
				$customerList=getFetshALLTableWithQuery("SELECT * FROM customer WHERE iD=$ID AND isSupplier='1' ");
			//	 print_r($customerList);
			}
			
			$response["customers"] = array();
			if(is_array($customerList))
				{
			foreach($customerList as $row)
			{
			    $SUPPLIERS=$row['isSupplier']==1;
				$cust = array();
				$cust=$row;
				$customerID=$row["iD"];
				$orders=getFetshALLTableWithQuery("SELECT * FROM orders WHERE CustomerID=$customerID ORDER BY date DESC");
				$cust["orders"]=array();
				$balanceResult=getBalanceDue($customerID);
			    $cust["balance"]=$balanceResult["balance"];
				$cust["totalCredits"]=$balanceResult["sumPay"];
			    $cust["totalDebits"]=$balanceResult["Sum_eq"];
				$cust["totalOrders"]=$balanceResult["Sum_ExtendedPrice"];
			    $cust["totalPurchases"]=$balanceResult["Sum_sumPurchuses"];
				$cust["productMovementList"]=array();
				$productMovement=array();
				if(is_array($orders))
				{
				foreach($orders as $OD)
				{
				//	print_r($OD);
					$ODID=$OD["iD"];
					//$OD["orderDetails"]=array();
					$res=getFetshALLTableWithQuery("SELECT * FROM orderdetails WHERE OrdersID=$ODID");
					$OD["orderDetails"]=array();
					foreach($res as $ODD)
					{
						$iD=$ODD["iD"];
						$ODD["product"]=getProductsByID($ODD["ProductID"]);
						array_push($OD["orderDetails"],$ODD);
						$recallProduct=getFetshTableWithQuery("SELECT * from productmovement WHERE iD=$iD AND recall='1' ORDER BY date ASC");
						if(is_array($recallProduct))
						{
							$productMovement[]=$recallProduct;
						}
						
						
					//	array_push($cust["productMovementList"],
					//	getFetshALLTableWithQuery("SELECT * from productmovement WHERE ProductID=$iD AND recall='1' ORDER BY date ASC"));
					}
					
					array_push($cust["orders"],$OD);
				}
				}
				$cust["productMovementList"]=$productMovement;
				$cust["debts"]=getDebtsPaysArray("equalities","debts","equalities","CustomerID",$customerID
					,"currency","CurrencyID","currency",0,null);
				
				$cust["payback"]=getDebtsPaysArray("equalities","payback","equalities","CustomerID",$customerID
					,"currency","CurrencyID","currency",0,null);
				if($SUPPLIERS)
				{
				  //  	$cust["pruchasedProducts"]=getSupplierProducts($customerID);
				  	$cust["pruchasedProducts"]=array();
				}
				array_push($response["customers"],$cust);
			}
				}
			return $response["customers"];
		}
	
		
		function getSpending()
		{
			$response["spendingNameList"]=array();
			$spendingName=getFetshALLTableWithQuery("SELECT * FROM account_names");
			if(is_array($spendingName))
			{
				foreach($spendingName as $SN)
				{
					$SN["spending"]=getDebtsPaysArrayALL("equalities","spending","equalities","NameID",$SN["iD"]
					,"currency","CurrencyID","currency");
					array_push($response["spendingNameList"],$SN);
				}
			}
			return $response["spendingNameList"];
		}
		function getIncomes()
		{
			$response["incomesNameList"]=array();
			$spendingName=getFetshALLTableWithQuery("SELECT * FROM account_names");
			if(is_array($spendingName))
			{
				foreach($spendingName as $SN)
				{
					$SN["incomes"]=getDebtsPaysArrayALL("equalities","incomes","equalities","NameID",$SN["iD"]
					,"currency","CurrencyID","currency");
					array_push($response["incomesNameList"],$SN);
				}
			}
			return $response["incomesNameList"];
		}
		function getListDashboardProducts(){
				$response=getMinMaxMonth();
				$response["dashboardList"]=array();
				$min = $response["minMonth"];
				$max=$response["maxMonth"];
				do {
					array_push($response["dashboardList"],getDashboard(null,$min));
					$min++;
				} while ($min <= $max);
				//	print_r( $response);
				return $response;
				
		}
		function getFund($FROM,$TO){
		        $FROM = date("Y-m-d",strtotime($FROM));
		        $TO = date("Y-m-d",strtotime($TO));
		        
		        $response["paybackList"]=array();
				$response["spendingList"]=array();
				$response["incomesList"]=array();
				$response["debtsList"]=array();
				
				$response["debtsDue"]=balanceDueFromTo(DB_TABLE_DEBTS,$FROM,$TO);
				$response["paybacksDue"]=balanceDueFromTo(DB_TABLE_PAYMENTS,$FROM,$TO);
				$response["incomesDue"]=balanceDueFromTo(DB_TABLE_INCOMES,$FROM,$TO);
				$response["spendingDue"]=balanceDueFromTo(DB_TABLE_SPENDING,$FROM,$TO);
				
				$response["PdebtsDue"]=balanceDuePrevious(DB_TABLE_DEBTS,$FROM);
				$response["PpaybacksDue"]=balanceDuePrevious(DB_TABLE_PAYMENTS,$FROM);
				$response["PincomesDue"]=balanceDuePrevious(DB_TABLE_INCOMES,$FROM);
				$response["PspendingDue"]=balanceDuePrevious(DB_TABLE_SPENDING,$FROM);
				
				$response["paybackList"]=getDebtsPaysArrayFromTo("equalities","payback","equalities","CustomerID",1
					,"currency","CurrencyID","currency",1,$FROM,$TO);
				$response["debtsList"]=getDebtsPaysArrayFromTo("equalities","debts","equalities","CustomerID",1
					,"currency","CurrencyID","currency",1,$FROM,$TO);
					
				$spend=getDebtsPaysArrayFromTo("equalities","spending","equalities","CustomerID",2
					,"currency","CurrencyID","currency",1,$FROM,$TO);
				if(is_array($spend))
				{
					foreach($spend as $SP)
						{
							$SPID=$SP["NameID"];
							$SP["accountName"]=getProductTable($SPID,DB_TABLE_ACCOUNT_NAME);
							array_push($response["spendingList"],$SP);
						}
					
				}
				$incomes=getDebtsPaysArrayFromTo("equalities","incomes","equalities","CustomerID",2
					,"currency","CurrencyID","currency",1,$FROM,$TO);
				if(is_array($incomes))
				{
					foreach($incomes as $SP)
						{
							$SPID=$SP["NameID"];
							$SP["accountName"]=getProductTable($SPID,DB_TABLE_ACCOUNT_NAME);
							array_push($response["incomesList"],$SP);
						}
					
				}
				return $response;
		}
		function getDashboard($EMPLOYEE,$BYMONTH)
		{
				$EDate;
				if($BYMONTH==-2){
				$EDate=$EMPLOYEE["date"];
				//echo $EDate;
				$EDate = date("Y-m-d",strtotime($EDate));
				//echo $EDate;
				//$EDate=DATE_FORMAT(($EDate), '%Y-%m-%d');
				$orders=getFetshALLTableWithQuery("SELECT * FROM orders WHERE Date(date) = '$EDate' ORDER BY date DESC");
				}else{
				$orders=getFetshALLTableWithQuery("SELECT * FROM orders WHERE month(date) = '$BYMONTH' ORDER BY date DESC");
				}
			//	$orders=getFetshALLTableWithQuery("SELECT * FROM orders ORDER BY date DESC");
				$response["ordersList"]=array();
				$response["paybackList"]=array();
				$response["spendingList"]=array();
				$response["incomesList"]=array();
				$response["debtsList"]=array();
				$response["purchasesList"]=array();
				$response["notPayedCustomers"]=notPayCustomers();
			//	echo $EDate;
			//	echo $BYMONTH;
				if($BYMONTH==-2){
				$response["debtsDue"]=balanceDue(DB_TABLE_DEBTS,$EDate);
				$response["paybacksDue"]=balanceDue(DB_TABLE_PAYMENTS,$EDate);
				$response["incomesDue"]=balanceDue(DB_TABLE_INCOMES,$EDate);
				$response["spendingDue"]=balanceDue(DB_TABLE_SPENDING,$EDate);
				$response["purchasesList"]=getProductsByDate($EDate);
				
				$response["PdebtsDue"]=balanceDuePrevious(DB_TABLE_DEBTS,$EMPLOYEE["date"]);
				$response["PpaybacksDue"]=balanceDuePrevious(DB_TABLE_PAYMENTS,$EMPLOYEE["date"]);
				$response["PincomesDue"]=balanceDuePrevious(DB_TABLE_INCOMES,$EMPLOYEE["date"]);
				$response["PspendingDue"]=balanceDuePrevious(DB_TABLE_SPENDING,$EMPLOYEE["date"]);
				}else{
					$EDate=$BYMONTH;
				$response["debtsDue"]=balanceDue(DB_TABLE_DEBTS,$BYMONTH);
				$response["paybacksDue"]=balanceDue(DB_TABLE_PAYMENTS,$BYMONTH);
				$response["incomesDue"]=balanceDue(DB_TABLE_INCOMES,$BYMONTH);
				$response["spendingDue"]=balanceDue(DB_TABLE_SPENDING,$BYMONTH);
				$response["purchasesList"]=getProductsByDate($BYMONTH);
				}
				if(is_array($orders))
				{
				
				foreach($orders as $OD)
				{
					if(!isset($response["date"])){
					
					$response["date"]=$OD["date"];
					}
					$ODID=$OD["iD"];
					$CCID=$OD["CustomerID"];
					$OD["customer"]=getFetshTableWithQuery("SELECT iD,name,phone FROM customer WHERE iD=$CCID");
					//$OD["orderDetails"]=array();
					$res=getFetshALLTableWithQuery("SELECT * FROM orderdetails WHERE OrdersID=$ODID");
					$OD["orderDetails"]=array();
					foreach($res as $ODD)
					{
						$ODD["product"]=getProductsByID($ODD["ProductID"]);
						array_push($OD["orderDetails"],$ODD);
					
					}
					
					array_push($response["ordersList"],$OD);
				}
			
				}
	
				$response["paybackList"]=getDebtsPaysArray("equalities","payback","equalities","CustomerID",1
					,"currency","CurrencyID","currency",1,$EDate);
				$response["debtsList"]=getDebtsPaysArray("equalities","debts","equalities","CustomerID",1
					,"currency","CurrencyID","currency",1,$EDate);
					
				$spend=getDebtsPaysArray("equalities","spending","equalities","CustomerID",2
					,"currency","CurrencyID","currency",1,$EDate);
				if(is_array($spend))
				{
					foreach($spend as $SP)
						{
							$SPID=$SP["NameID"];
							$SP["accountName"]=getProductTable($SPID,DB_TABLE_ACCOUNT_NAME);
							array_push($response["spendingList"],$SP);
						}
					
				}
				$incomes=getDebtsPaysArray("equalities","incomes","equalities","CustomerID",2
					,"currency","CurrencyID","currency",1,$EDate);
				if(is_array($incomes))
				{
					foreach($incomes as $SP)
						{
							$SPID=$SP["NameID"];
							$SP["accountName"]=getProductTable($SPID,DB_TABLE_ACCOUNT_NAME);
							array_push($response["incomesList"],$SP);
						}
					
				}
				return $response;
		}
		function getProductsByDate($date)
		{
			$pdo = setupDatabase();
			if (!is_a($pdo, "PDO")) {
				return $pdo;
				}
				try{
					$stmt;
				if(!is_numeric($date)){
				$stmt = $pdo->prepare("SELECT * FROM products WHERE Date(date) = '$date' ORDER BY date DESC ");
				}else{
				$stmt = $pdo->prepare("SELECT * FROM products WHERE month(date) = '$date' ORDER BY date DESC ");
				}
				$stmt->execute();
				$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
				return getPurchasesRow($result);		
		}catch(PDOException $e)
		{
			return 'ERROR: ' . $e->getMessage();
		}
		}
		function getIncomesByIDJourney($ID){
		    $order=getFetshTableWithQuery("SELECT * FROM incomes WHERE iD = $ID");	
            setEmployeeCustomerNameToObject($order);
			return $order;
		}
		function getIncomesByID($ID){
		    $order=getFetshTableWithQuery("SELECT * FROM incomes WHERE iD = $ID");
		    checkToSetJournal($order);
            setEmployeeCustomerNameToObject($order);
			return $order;
		}
		function getSpendingByIDJourney($ID){
		    $order=getFetshTableWithQuery("SELECT * FROM spending WHERE iD = $ID");	
            setEmployeeCustomerNameToObject($order);
			return $order;
		}
		function getSpendingByID($ID){
		    $order=getFetshTableWithQuery("SELECT * FROM spending WHERE iD = $ID");
		    checkToSetJournal($order);
            setEmployeeCustomerNameToObject($order);
			return $order;
		}
		function getDebitByID($ID){
		    $order=getFetshTableWithQuery("SELECT * FROM debts WHERE iD = $ID");
		    checkToSetJournal($order);
            setEmployeeCustomerNameToObject($order);
			$order["customer"]["balance"]=getBalanceDue($order["CustomerID"])["balance"];
			return $order;
		}
		function getDebitByIDJourney($ID){
		    $order=getFetshTableWithQuery("SELECT * FROM debts WHERE iD = $ID");	
            setEmployeeCustomerNameToObject($order);
		//	$order['customer']['balance']=getBalanceDue($order['CustomerID'])['balance'];
			return $order;
		}
		function getPaymentByID($ID){
            $order=getFetshTableWithQuery("SELECT * FROM payback WHERE iD = $ID");
            checkToSetJournal($order);
            setEmployeeCustomerNameToObject($order);
			$order["customer"]["balance"]=getBalanceDue($order["CustomerID"])["balance"];
			return $order;
		}
		function getPaymentByIDJourney($ID){
		    $order=getFetshTableWithQuery("SELECT * FROM payback WHERE iD = $ID");	
            setEmployeeCustomerNameToObject($order);
		//	$order["customer"]["balance"]=getBalanceDue($order["CustomerID"])["balance"];
			return $order;
		}
		function checkToSetJournal(&$item){
		    if(!isset($item["isDirect"]))return;
		    $journalID=$item["isDirect"];
		    if($journalID!=0){
				$journalRecord=getFetshTableWithQuery("SELECT * FROM Journal_Voucher WHERE iD='$journalID'");
				$journalTable=$journalRecord["transaction"];
				$str_arr = explode ("_", $journalTable); 
				$secoundID;
				if($item["iD"]==$journalRecord["fromAccount"]){
				$secoundID=$journalRecord["toAccount"];
				$journalTable=$str_arr[1];
				}else{
				$secoundID=$journalRecord["fromAccount"];
				$journalTable=$str_arr[0];
				}
				$item["transaction"]=$journalTable;
				$item["journal"]=getJourney($journalTable,$secoundID);
				
			}
		}
		function getJournalTableNameFromExisting($object){
		    $journalID=$object["isDirect"];
		    if($journalID!=0){
				$journalRecord=getFetshTableWithQuery("SELECT * FROM Journal_Voucher WHERE iD='$journalID'");
				$journalTable=$journalRecord["transaction"];
				$str_arr = explode ("_", $journalTable); 
				if($object["iD"]==$journalRecord["fromAccount"]){
				return $str_arr[1];
				}else{
				$secoundID=$journalRecord["fromAccount"];
				return $str_arr[0];
				}
			}
		}
		function getJournalTableName($object){
		    	$journalTable=$object["transaction"];
				$str_arr = explode ("_", $journalTable); 
				return $str_arr[1];
		}
		function getJourney($tableName,$ID){
		    if($tableName==DB_TABLE_SPENDING){
		        return getSpendingByIDJourney($ID);
		    }
		    if($tableName==DB_TABLE_INCOMES){
		        return getIncomesByIDJourney($ID);
		    }
		    if($tableName==DB_TABLE_PAYMENTS){
		        return getPaymentByIDJourney($ID);
		    }
		     if($tableName==DB_TABLE_DEBTS){
		        return getDebitByIDJourney($ID);
		    }
		    
		}
		function getOrderByID($ID)
		{
			$order=getFetshTableWithQuery("SELECT * FROM extendedorder WHERE iD = $ID");	
			if(empty($order))return null;
			$CCID=$order["CustomerID"];			
			$order["customer"]=getFetshTableWithQuery("SELECT iD,name,phone,address,token FROM customer WHERE iD='$CCID'");
			$order["customer"]["balance"]='';
			$order["customer"]["balance"]=getBalanceDue($CCID)["balance"];
			$order["customer"]["customerTerms"]=customerTerms($CCID);
			$CCID=$order["EmployeID"];			
			$order["employee"]=getFetshTableWithQuery("SELECT iD,name,phone FROM employee WHERE iD=$CCID");
			$res=getFetshALLTableWithQuery("SELECT * FROM orderdetails WHERE OrdersID=$ID");
			$order["orderDetails"]=array();
			foreach($res as $ODD)
			{
				$ODD["product"]=getProductsByID($ODD["ProductID"]);
				array_push($order["orderDetails"],$ODD);
			}
				return $order;
		}
		function getBalanceDue($CCID){
		    return getFetshTableWithQuery("SELECT customer.iD,
  customer.name,
  payments.sumPay,
  debts.Sum_eq,
  orders.Sum_ExtendedPrice,
  products.Sum_sumPurchuses,
  ((COALESCE(orders.Sum_ExtendedPrice, 0) + COALESCE(debts.Sum_eq, 0)) -
  (COALESCE(payments.sumPay, 0) + COALESCE(products.Sum_sumPurchuses, 0))) AS balance
FROM customer
  LEFT JOIN (SELECT payequality.CustomerID,
    Sum(payequality.eq) AS sumPay
  FROM payequality
  GROUP BY payequality.CustomerID) payments ON customer.iD = payments.CustomerID
  LEFT JOIN (SELECT debtequality.CustomerID,
    Sum(debtequality.eq) AS Sum_eq
  FROM debtequality
  GROUP BY debtequality.CustomerID) debts ON customer.iD = debts.CustomerID
  LEFT JOIN (SELECT extendedorder.CustomerID,
    Sum(extendedorder.ExtendedPrice) AS Sum_ExtendedPrice
  FROM extendedorder
  GROUP BY extendedorder.CustomerID) orders ON customer.iD = orders.CustomerID
  LEFT JOIN (SELECT products.SupplierID AS SupplierID,
    Sum(products.sumPurchuses) AS Sum_sumPurchuses
  FROM products
  GROUP BY products.SupplierID) products ON customer.iD = products.SupplierID WHERE iD=$CCID");
		}
		function getBalanceDueTo($CCID,$date){
		    return getFetshTableWithQuery("SELECT customer.iD,
  customer.name,
  SUM(  ((Coalesce(orders.Sum_ExtendedPrice, 0) + Coalesce(debts.Sum_eq, 0)) -
  (Coalesce(payments.sumPay, 0) + Coalesce(products.Sum_sumPurchuses,
  0))) ) AS balance,
  payments.sumPay,
  debts.Sum_eq,
  orders.Sum_ExtendedPrice,
  products.Sum_sumPurchuses
FROM customer
  LEFT JOIN (SELECT payequality.CustomerID,
    Sum(payequality.eq) AS sumPay,
    payequality.date
  FROM payequality WHERE Date(payequality.date)<= '$date'
  GROUP BY payequality.CustomerID) payments ON customer.iD = payments.CustomerID
  LEFT JOIN (SELECT debtequality.CustomerID,
    Sum(debtequality.eq) AS Sum_eq,
    debtequality.date
  FROM debtequality WHERE  Date(debtequality.date)<= '$date'
  GROUP BY debtequality.CustomerID) debts ON customer.iD = debts.CustomerID
  LEFT JOIN (SELECT extendedorder.CustomerID,
    Sum(extendedorder.ExtendedPrice) AS Sum_ExtendedPrice,
    extendedorder.date
  FROM extendedorder WHERE Date(extendedorder.date)<= '$date' 
  GROUP BY extendedorder.CustomerID) orders ON customer.iD = orders.CustomerID
  LEFT JOIN (SELECT products.SupplierID AS SupplierID,
    Sum(products.sumPurchuses) AS Sum_sumPurchuses,
    products.date 
  FROM products WHERE Date(products.date)<= '$date' 
  GROUP BY products.SupplierID) products ON customer.iD = products.SupplierID
 WHERE iD=$CCID");
		}
	function getBalanceDueFromTo($CCID,$date,$to){
		    return getFetshTableWithQuery("SELECT customer.iD,
            customer.name,
            SUM(  ((Coalesce(orders.Sum_ExtendedPrice, 0) + Coalesce(debts.Sum_eq, 0)) -
  (Coalesce(payments.sumPay, 0) + Coalesce(products.Sum_sumPurchuses,
  0))) ) AS balance,
  payments.sumPay,
  debts.Sum_eq,
  orders.Sum_ExtendedPrice,
  products.Sum_sumPurchuses
FROM customer
  LEFT JOIN (SELECT payequality.CustomerID,
    Sum(payequality.eq) AS sumPay,
    payequality.date
  FROM payequality WHERE Date(payequality.date)>= '$date' AND Date(payequality.date)<= '$to'
  GROUP BY payequality.CustomerID) payments ON customer.iD = payments.CustomerID
  LEFT JOIN (SELECT debtequality.CustomerID,
    Sum(debtequality.eq) AS Sum_eq,
    debtequality.date
  FROM debtequality WHERE  Date(debtequality.date)>= '$date' AND Date(debtequality.date)<= '$to'
  GROUP BY debtequality.CustomerID) debts ON customer.iD = debts.CustomerID
  LEFT JOIN (SELECT extendedorder.CustomerID,
    Sum(extendedorder.ExtendedPrice) AS Sum_ExtendedPrice,
    extendedorder.date
  FROM extendedorder WHERE Date(extendedorder.date)>= '$date' AND Date(extendedorder.date)<= '$to'
  GROUP BY extendedorder.CustomerID) orders ON customer.iD = orders.CustomerID
  LEFT JOIN (SELECT products.SupplierID AS SupplierID,
    Sum(products.sumPurchuses) AS Sum_sumPurchuses,
    products.date 
  FROM products WHERE Date(products.date)>= '$date' AND Date(products.date)<= '$to'
  GROUP BY products.SupplierID) products ON customer.iD = products.SupplierID
 WHERE iD=$CCID");
		}
		function getSearchCustomerList($EMPLOYEE,$SEARCH_QUERY,$SEARCH_ID,$TO,$ACTION)
		{
			$pdo = setupDatabase();
			if (!is_a($pdo, "PDO")) {
				return $pdo;
				}
				try{
			$searchcirtinia='%'.$SEARCH_QUERY.'%';
			$pIDS=implode(json_decode($SEARCH_ID,true),"','");
			$stmt;
			if($ACTION=="CUSTOMER")
			{
			$stmt = $pdo->prepare("SELECT * FROM customer WHERE iD NOT IN ( '" . $pIDS . "' )"." AND name LIKE :search_query AND isSupplier='0' OR isSupplier IS NULL ORDER BY date DESC LIMIT $TO");
			}
			if($ACTION=="EMPLOYEE")
			{
			$stmt = $pdo->prepare("SELECT * FROM employee WHERE iD NOT IN ( '" . $pIDS . "' )"." AND name LIKE :search_query ORDER BY date DESC LIMIT $TO");
			}
			if($ACTION=="SUPPLIER")
			{
			$stmt = $pdo->prepare("SELECT * FROM customer WHERE iD NOT IN ( '" . $pIDS . "' )"." AND name LIKE :search_query AND isSupplier='1' ORDER BY date DESC LIMIT $TO");
			}
			$stmt->bindParam(':search_query', $searchcirtinia,PDO::PARAM_STR);
			$stmt->execute();
			$customerList=$stmt->fetchAll(PDO::FETCH_ASSOC);
			$response["customers"] = array();
			if($ACTION=="EMPLOYEE")
			{
				if(is_array($customerList))
				{
					foreach($customerList as $emp)
					{
							$emp["dealers"]=getProductTable($emp["DealersID"],DB_TABLE_DEALER);
							$emp["userlevel"]=getProductTable($emp["userlevelid"],DB_TABLE_USER_LEVEL);
							array_push($response["customers"],$emp);
					}
				}
			}
			else
			{
			if(is_array($customerList))
			{
				foreach($customerList as $row)
				{
				$cust = array();
				$cust=$row;
				$customerID=$row["iD"];
				$orders=getFetshALLTableWithQuery("SELECT * FROM orders WHERE CustomerID=$customerID ORDER BY date DESC");
				$cust["orders"]=array();
				foreach($orders as $OD)
				{
					$ODID=$OD["iD"];
					//$OD["orderDetails"]=array();
					$res=getFetshALLTableWithQuery("SELECT * FROM orderdetails WHERE OrdersID=$ODID");
					$OD["orderDetails"]=array();
					foreach($res as $ODD)
					{
						
						$ODD["product"]=getProductTable($ODD["ProductID"],"searchview");
						array_push($OD["orderDetails"],$ODD);
					}
					
					array_push($cust["orders"],$OD);
				}
				$cust["debts"]=getDebtsPaysArray("equalities","debts","equalities","CustomerID",$customerID
					,"currency","CurrencyID","currency",0,null);
				
				$cust["payback"]=getDebtsPaysArray("equalities","payback","equalities","CustomerID",$customerID
					,"currency","CurrencyID","currency",0,null);
				
				array_push($response["customers"],$cust);
				}
			}
			}
			return $response["customers"];
		}catch(PDOException $e)
		{
			return 'ERROR: ' . $e->getMessage();
		}
		}
		function getDebtsPaysArray($OBJECT,$PARENT_TABLE,$CHILD_TABLE,$WHERENAME,$ID,
				$SECOUND_OBJECT,$FORGIN_KEY,$SECOUND_TABLE,$DASHBOARD,$DATE)
		{
			$data;
			if($DASHBOARD==1){
				if(!is_numeric($DATE)){$data=getFetshALLTableWithQuery("SELECT * FROM $PARENT_TABLE WHERE Date(date) = '$DATE' ORDER BY date DESC");}
				else{$data=getFetshALLTableWithQuery("SELECT * FROM $PARENT_TABLE WHERE month(date) = '$DATE' ORDER BY date DESC");}
			}else{$data=getFetshALLTableWithQuery("SELECT * FROM $PARENT_TABLE WHERE $WHERENAME=$ID ORDER BY date DESC ");}
			$result["response"]=array();
			if(is_array($data))
				{
					foreach($data as $item)
						{
						    checkToSetJournal($item);
							setEmployeeCustomerNameToObject($item);
							array_push($result["response"],$item);
						}
				}
			return $result["response"];
		}
			function getDebtsPaysArrayFromTo($OBJECT,$PARENT_TABLE,$CHILD_TABLE,$WHERENAME,$ID,
				$SECOUND_OBJECT,$FORGIN_KEY,$SECOUND_TABLE,$DASHBOARD,$DATE,$TO)
		{
			$data;
			if($DASHBOARD==1){
				if(!is_numeric($DATE)){$data=getFetshALLTableWithQuery("SELECT * FROM $PARENT_TABLE WHERE Date(date) >= '$DATE' AND Date(date) <= '$TO' ORDER BY date DESC");}
				else{$data=getFetshALLTableWithQuery("SELECT * FROM $PARENT_TABLE WHERE month(date) >= '$DATE' AND month(date) <= '$TO' ORDER BY date DESC");}
			}else{$data=getFetshALLTableWithQuery("SELECT * FROM $PARENT_TABLE WHERE $WHERENAME=$ID AND Date(date) >= '$DATE' AND Date(date) <= '$TO' ORDER BY date DESC ");}
			$result["response"]=array();
			if(is_array($data))
				{
					foreach($data as $item)
						{
						    checkToSetJournal($item);
							setEmployeeCustomerNameToObject($item);
							array_push($result["response"],$item);
						}
				}
			return $result["response"];
		}
		function getDebtsPaysArrayALL($OBJECT,$PARENT_TABLE,$CHILD_TABLE,$WHERENAME,$ID,
				$SECOUND_OBJECT,$FORGIN_KEY,$SECOUND_TABLE)
		{
			$data=getFetshALLTableWithQuery("SELECT * FROM $PARENT_TABLE WHERE $WHERENAME=$ID ORDER BY date DESC");
			$result["response"]=array();
		if(is_array($data))
		{
			foreach($data as $item)
			{
			    checkToSetJournal($item);
			    setEmployeeCustomerNameToObject($item);
				array_push($result["response"],$item);
			}
		}
			return $result["response"];
		}
		function setEmployeeCustomerNameToObject(&$item){
		    
		    if(isset($item["EqualitiesID"])){
		        $CCID=$item["EqualitiesID"];	
                $item["equalities"]=getFetshTableWithQuery("SELECT * FROM equalities WHERE iD = $CCID");
                $CCID=$item["equalities"]["CurrencyID"];	
                $item["equalities"]["currency"]=getFetshTableWithQuery("SELECT * FROM currency WHERE iD = $CCID");
		    }
		    if(isset($item["EmployeID"])){
				$CCID=$item["EmployeID"];
				$item["employee"]=getFetshTableWithQuery("SELECT iD,name,phone FROM employee WHERE iD=$CCID");
			}
			if(isset($item["CustomerID"])){
				$CCID=$item["CustomerID"];				
				$item["customer"]=getFetshTableWithQuery("SELECT iD,name,phone,address FROM customer WHERE iD=$CCID");
			}
			if(isset($item["NameID"])){
			    $CCID=$item["NameID"];			
			    $item["accountName"]=getFetshTableWithQuery("SELECT * FROM account_names WHERE iD=$CCID");
			}
		}
		function getCountryManufacture($row)
		{
			$valueTable=getProductTable($row["Country_Manufacture_CompanyID"],DB_TABLE_PRODUCT_MANUFACTURE_COMPANY_COUNTRY);
			$valueTable["country"]=getProductTable($valueTable["CountryID"],DB_TABLE_PRODUCT_COUNTRY);
			$valueTable["manufacture_Company"]=getProductTable($valueTable["Manufacture_CompanyID"],DB_TABLE_PRODUCT_MANUFACTURE_COMPANY);
			unset($valueTable["manufacture_Company"]["webSite"]);
			unset($valueTable["manufacture_Company"]["email"]);
			return $valueTable;
		}
		function getProductType($row)
		{
			
					$value=getProductTable($row["ProductTypeID"],DB_TABLE_PRODUCT_TYPE);
					$value["price"]=getProductTable($value["PricesID"],DB_TABLE_PRODUCT_PRICE);
					return $value;
		}
		
		

		function getProductTableAndInsert($jsonValue,$tableName)
		{
			if($jsonValue["iD"]==-1)
			{
			    if(isset($jsonValue['fb_edit'])){
			        unset($jsonValue['fb_edit']);
			    }
				unset($jsonValue["iD"]);
				insert($jsonValue,$tableName);
				$result=getFetshTableWithQuery(getQueryMaxID($tableName));
				return $result["iD"];
			}else
			{
				return $jsonValue["iD"];
			}
		}
		
		

		

		
		
		function cleanOrder(&$order){
		            if(!empty($order["orderDetails"])){
		                  foreach($order["orderDetails"] as $orderToCheck){
		                      if(orderDetailsHasRemoveChildes($orderToCheck)){
		                          $product=$orderToCheck["product"];
		                          deleteObjectCustom($product,DB_TABLE_PRODUCT,"ParentID");
		                      }
		                      if($orderToCheck["delete"]){if($orderToCheck['iD']!=-1){deleteOrderDetails($orderToCheck);}}
		                    }
		              }else{
		                $res["iD"]= deleteObject($order,DB_TABLE_ORDER,true);
			            $res["orderDetailsIDS"]=array();
			            return $res;
		              }
		              if($order["delete"]){
	                   $res["iD"]= deleteObject($order,DB_TABLE_ORDER,true);
			           $res["orderDetailsIDS"]=array();
			           return $res;
		              }
		        unset($order['delete']);
		        return 100;
		}
		function checkCutIfDelete($object){
		     if($object["delete"]){
		        $res= deleteObject($object,"product_cut_request",false);
		        return $res;
		    }
		   unset($object['delete']);
		   return 100;
		}
		function returnToDefualts($object,$tableName){
		    
		}
		function checkCustomesIfDelete($object){
		    if($object["delete"]){
		        try {
		            $pdo = setupDatabase();
		            $params['iD']=$object['iD'];
					$stmt = $pdo->prepare("UPDATE product SET CustomsDeclarationID='-1' WHERE CustomsDeclarationID=:iD");
					$stmt->execute($params);}catch(PDOException $e){return -1;	}
		        $res= deleteObject($object,DB_TABLE_CUSTOMS,false);
		        return $res;
		    }
		   unset($object['delete']);
		   return 100;
		}
		
	    function checkPurchasesIfDelete($object){
	        if($object["delete"]){
		        $res= deleteObject($object,DB_TABLE_PURCHASES,true);
		        return $res;
		    }
		   unset($object['delete']);
		   return 100;
	    }
		
        function cleanPurchases($object){
            if(!empty($object["products"])){
                //checking for id if is -1 then ignore it else delete it 
		          foreach($object["products"] as $c){
		              if($c["delete"]===true){if($c['iD']!=-1){deleteObject($c,DB_TABLE_PRODUCT,false);}}
		          }
            }  
        }
		function cleanCustomes($object){
		    if(!empty($object["cdi"])){
		          foreach($object["cdi"] as $c){
		              if($c["delete"]===true){if($c['iD']!=-1){unlinkFile(substr($c["path"], 38));deleteObject($c,"customs_declaration_images",false);}}
		          }
		    }
		}
		function cleanCut($object){
		    if(!empty($object["prs"])){
		          foreach($object["prs"] as $c){
		              if($c["delete"]===true){if($c['iD']!=-1){deleteObject($c,"product_cut_request_sizes",false);}}
		          }
		    }
		}
		function getGeneratedProductsFromCutRequest($object){
		      if($object['iD']==-1 || $object['iD']===-1){ return null;die;}
		        $product['iD']=$object["ProductID"];
		       return getParentAnotherChildes($product,null,true);
		      
		}
		function insertProductsFromCutRequest($object){
		    if($object['iD']==-1 || $object['iD']===-1){ return null;die;}
		      foreach($object["generatedProducts"] as $c){
		          updateProduct($c);
		      }
		}
	    function insertCut($object){
	         cleanCut($object);
	         $res=checkCutIfDelete($object);
	         if($res['iD']==1 || $res==-1 || $res==1){ return $res;die;}
	         insertCustomerFromObject($object);
	         $object["iD"]=updateGetID($object,"product_cut_request");
	            if(!empty($object["prs"])){
	              foreach($object["prs"] as $c){
	                  if($c["delete"]===false){
	                      SetSizeFromObject($c);
	                      $c["PCRID"]= $object["iD"];
	                      updateGetID($c,"product_cut_request_sizes");
	                  }
	              }
	                
	            }
	            return $object["iD"];
	    }
		function insertCustomes($customes){
		    try{
		            cleanCustomes($customes);
		            $res=checkCustomesIfDelete($customes);
		            if($res['iD']==1 || $res==-1 || $res==1){ return $res;die;}
		            $customes["iD"]=updateGetID($customes,"customs_declaration");
		            if(!empty($customes["cdi"])){
		            foreach($customes["cdi"] as $c){
		               if($c["delete"]===false AND $c['iD']==-1){
		                unset($c['delete']);
		                
		                $filename_path = md5(time().uniqid()).".jpg";
                        $base64_string = str_replace('data:image/png;base64,', '', $c["path"]);
                        $base64_string = str_replace(' ', '+', $c["path"]);
                        $decoded = base64_decode($base64_string);
                        file_put_contents("Images/".$filename_path,$decoded);
                        $c["path"]="http://www.saffoury.com/SaffouryPaper/Images/".$filename_path;
                        $c["CustomsDeclarationID"]=$customes["iD"];
                        updateGetID($c,"customs_declaration_images");
		            }
		            }
		            }
		            return $customes["iD"];
		    }catch(Exception $e) {
		        print_r($e);
		        return -1;}
		        
		}
			//TODO
	function AddEditList($jsonValue,$tableName,$user){
	    	switch($tableName)
		        {
			        case DB_TABLE_PRODUCT:
			           try{
			                foreach($jsonValue as $product)
			                {
			                    $product["EmployeID"]=$user["iD"];
			                    AddProductFromList($product,$user);
			                }
			                send_new_product(null);
			                return 1;
			              }catch(Exception $e){return -1;}
			            break;
		}
	}
	function SetGetProductType(&$product){
	    $productType=$product["productType"];
	    $name=$productType["name"];
	    $grade=$productType["grade"];
	    $unit=$productType["unit"];
	    $price=$productType["price"];
    	$retail_Price=$price["retail_Price"];
	    $buyer_Price=$price["buyer_Price"];
	    if(is_numeric($unit)){
	        switch($unit){
	            case 0:$unit='KG';break;
	            case 1:$unit='Ream';break;
	            case 2:$unit='Sheet';break;
	        }
	    }
	    $result=getFetshTableWithQuery("SELECT * FROM products_prices WHERE name='$name' AND grade='$grade' AND unit='$unit' AND retail_Price LIKE '$retail_Price' AND buyer_Price LIKE '$buyer_Price'");
	    if(empty($result)){
	         $result=getFetshTableWithQuery("SELECT * FROM prices WHERE retail_Price LIKE '$retail_Price' AND buyer_Price LIKE '$buyer_Price'");
				if(empty($result))
				{
				    $productType["PricesID"]=updateGetID($price,DB_TABLE_PRODUCT_PRICE);
				}else{
				    $productType["PricesID"]=$result["iD"];
				}
			$productType["iD"]=updateGetID($productType,DB_TABLE_PRODUCT_TYPE);
	    }else{
	      	$productType["iD"]=  $result["iD"];
	    }
	    $product[DB_FORGIN_PRODUCT_TYPE]=$productType["iD"];
	   	$product['productType']=$productType;
		}
	function AddProductFromList(&$product){
	    
			    SetProductGSM($product);
			    SetProductSize($product);
			    SetProductManufactureCompany($product);
			    SetGetProductType($product);
				return addProudctInTraditionalWay($product);
	}
	//TODO it's already added via fabric / here if i decide to put the feedbacks on our database
	function insertFeedback($feedback){
		    return updateGetID($feedback,"feedbacks");
	}
	function SetCustomsFromProduct(&$product){
	         $customsID;
	          //if is set that mean its uploaded before
	         if($product["CustomsDeclarationID"]!=-1){ $customsID=$product["CustomsDeclarationID"];}
		         //if is -1 then we have to check if null or its new customs
		      else{
		         if(
		             isset($product["customsDeclaration"]) 
		             && !is_null($product["customsDeclaration"]) 
		             && $product["customsDeclaration"]["iD"]==-1){
		             	      $customsID=insertCustomes($product["customsDeclaration"]);
		             }
		         }
		      $product["CustomsDeclarationID"]=$customsID;
	            return $customsID;
	    
	}
	function insertPurchases($purchases,$fileReader){
	    //check if there is deleted products
	     cleanPurchases($purchases);
	     //check if the hole purchases is deleted
		 $res=checkPurchasesIfDelete($purchases);
		 if($res['iD']==1 || $res==-1 || $res==1){ return $res;die;}
		 // add purchases
		 $purchases['iD']=insertPurchasesHeader($purchases)['iD'];
		 $iD=$purchases['iD'];
		 $iDList=array();
		 $firstRow=true;
		 $customsID=-1;
		 // this is only for fileReader 
		 foreach($purchases["products"] as $product){
		     
		     //if its from filereader then we have to upload the first product
		     //if its not from filereeader we need to check it all
		     if($firstRow or !$fileReader){
		         $firstRow=false;
		         $customsID=SetCustomsFromProduct($product);
		     }
		     if($product["delete"]===false){
		         $product["PurchasesID"]=$iD;
		         $product["CustomsDeclarationID"]=$customsID;
		         array_push($iDList,AddProductFromList($product)["iD"]);
		     }
		 }
		 send_new_product(null);
		 $purchases["iDList"]=$iDList;
		 $purchases["RESPONSE"]=$purchases['iD'];
		 return $purchases;
		 
	}

	function insertOrder($order)
		{
		    $res=cleanOrder($order);
		    if($res['iD']==1 || $res==-1 || $res==1){ return $res;die;}
		    
		    insertCustomerFromObject($order);
			$fb_customer=$order["customer"];
			$orderDetails=$order['orderDetails'];
			$payDollar=$order["paybackListDollar"];
			$paySYP=$order["paybackListSYP"];
			$isCash=$order["isPayCash"];
			
			unset($order["customer"]);
			unset($order["isPayCash"]);
			unset($order["paybackListDollar"]);
			unset($order["paybackListSYP"]);
			
			if($order["iD"]!=-1){
			    $FB_ACTION="EDIT";
			}else{
			    $FB_ACTION="";
			}
			$order["iD"]=getProductTableAndInsert($order,DB_TABLE_ORDER);
			$toUploadProducts=insertOrderDetails($order);
			
			if($isCash)
			{
				if(!is_null($paySYP))
				{
				    $paySYP["customer"]=$fb_customer;
					AddEdit($paySYP,DB_TABLE_PAYMENTS);
				}
				if(!is_null($payDollar))
				{
				    $payDollar["customer"]=$fb_customer;
					AddEdit($payDollar,DB_TABLE_PAYMENTS);
				}
			}
			$order["fb_edit"]=$FB_ACTION;
			send_order(getFirebaseOrder($order),$fb_customer);
			if($direct){
			        return $toUploadProducts;
			}else{
					$order["orderDetailsIDS"]=$toUploadProducts;
					return $order;
			}
		}
	function deleteOrderDetails($orderDetails){
		    if(orderDetailsHasParent($orderDetails))
		    {
		        $product=$orderDetails['product'];
		        deleteObjectCustom($product,DB_TABLE_PRODUCT,"ParentID");
		    }
		    
		    if($orderDetails['product'])
		    updateProduct($orderDetails['product']);
		    unset($orderDetails["product"]);
		    deleteObject($orderDetails,DB_TABLE_ORDER_DETAILS,true);
		    
		}
	function orderDetailsHasParent($orderDetails){
		    return $orderDetails["hasParent"] or $orderDetails["removeChildes"]==1;
		}
	function orderDetailsHasRemoveChildes($orderDetails){
		    return $orderDetails["removeChildes"] or $orderDetails["removeChildes"]==1;
		}
	function unsetOrderDetails($orderDetails){
		      unset($orderDetails['removeChildes']);
		      unset($orderDetails['hasParent']);
		}
	function insertOrderDetails($oD)
	{
			$iDs=array();
			$orderID=$oD["iD"];
			foreach($oD["orderDetails"] as $row)
			{
			    $direct=!is_null($row["product"]["purchases"]);
				$row['OrdersID']=$orderID;
				
				if(!$direct)
				{
				    //if order is having custom products
				    
				    if(orderDetailsHasRemoveChildes($row)){
				        
				        $firstProduct=$row['productList'][1];
				        $firstProduct['ODID']=(int)$orderID;
				        updateProduct($firstProduct);
				        updateProduct($row['product']);
				        $row["ProductID"]=updateProduct($row["productList"][0]);
				    }else{
				        $row["ProductID"]=updateProduct($row['product']);
				    }
				}
				//this for adding product as purchases
				else
				{
				    $product=$row["product"];
					$product=AddEdit($product,DB_TABLE_PRODUCT);	
					$row["ProductID"]=$product["iD"];
			
				}
				unsetOrderDetails($row);
				unset($row['removeChildes']);
			    unset($row['hasParent']);
				unset($row['productList']);
				unset($row['product']);
				$iD=updateGetID($row,DB_TABLE_ORDER_DETAILS);
				array_push($iDs,$row["ProductID"]);
			}
			return $iDs;
		}
	function updatePermission($permission){
		    return updateGetIDAllowZeroInJSON($permission,DB_TABLE_PERMISSION);
		}
	function getTheFirstProduct($productID){
		    
		}
	function returnProduct($productID){
		    
		}
	function checkCustomerReturnID($customer)
	{
			return getProductTableAndInsert($customer,"customer");
		}
	function updateProductWithOutUnSetSize($product)
	{
			$product['PSizeID']=insertSize($product['PSize']);
			$size=$product['PSize'];
			unset($product['PSize']);
	        unset($product["multipleWeights"]);
	        $product["iD"]=updateGetID($product,DB_TABLE_PRODUCT);
	        $product["size"]=$size;
			return $product["iD"];
		}
	function updateProduct($product)
	{
			$product['PSizeID']=insertSize($product['PSize']);
			unset($product['PSize']);
	        unset($product["multipleWeights"]);
			return updateGetIDAllowZeroInJSON($product,DB_TABLE_PRODUCT);
		}
	
		function getProductMovement($iD,$SHOWALL)
		{
			return getFetshALLTableWithQuery("SELECT * from productmovement WHERE ProductID=$iD ORDER BY date ASC,recall ASC");
		    
		}
		function insertToken($userID,$Token,$tableName)
		{
			$pdo = setupDatabase();
			if (!is_a($pdo, 'PDO')) {
				return $pdo;
			}
			$params["userID"]=$userID;
			$params["token"]=$Token;
			try {
					$stmt = $pdo->prepare("UPDATE $tableName SET token=:token WHERE iD=:userID");
					$stmt->execute($params);
					return 1;
		}catch(PDOException $e)
		{
			return -1;
		}
			
		}
		function deleteObjectCustom($object,$tableName,$whereName){
		    $pdo = setupDatabase();
			if (!is_a($pdo, 'PDO')) {
				return $pdo;
			}
				try {
					$ID=$object["iD"];
				    $stmt = $pdo->prepare("DELETE FROM $tableName WHERE $whereName='$ID'");
				    $stmt->execute();
				  //  sendDelete($object,$tableName);
				    return 1;
			        }catch(PDOException $e) {return -1;}
		}
		
	function transfer($OBJECT){
	    	$pdo = setupDatabase();
			if (!is_a($pdo, 'PDO')) {
				return $pdo;
			}
				try {
                $from=$OBJECT["customerFrom"]["iD"];
                $to=$OBJECT["customerTo"]["iD"];
                
				$stmt = $pdo->prepare("UPDATE payback SET CustomerID=$to WHERE CustomerID=$from");
				$stmt->execute();
                $stmt = $pdo->prepare("UPDATE debts SET CustomerID=$to WHERE CustomerID=$from");
				$stmt->execute();
				$stmt = $pdo->prepare("UPDATE orders SET CustomerID=$to WHERE CustomerID=$from");
				$stmt->execute();
				$stmt = $pdo->prepare("UPDATE purchases SET CustomerID=$to WHERE CustomerID=$from");
				$stmt->execute();
				return 1;
			} catch(PDOException $e) {
				return 'ERROR: ' . $e->getMessage();}
				
	}
	
	
	function balanceDuePrevious($tableName,$date){
	    	switch($tableName)
		{
			default:
			if(!is_numeric($date)){
			return getFetshALLTableWithQuery("SELECT currency.name AS currency,
			Sum($tableName.value) AS sum
			FROM currency
			JOIN equalities ON currency.iD = equalities.CurrencyID
			LEFT JOIN $tableName ON equalities.iD = $tableName.EqualitiesID
			WHERE (Date($tableName.date) < '$date') GROUP BY currency.name  ");}
			return getFetshALLTableWithQuery("SELECT currency.name AS currency,
			Sum($tableName.value) AS sum
			FROM currency
			JOIN equalities ON currency.iD = equalities.CurrencyID
			LEFT JOIN $tableName ON equalities.iD = $tableName.EqualitiesID
			WHERE (month($tableName.date) < '$date') GROUP BY currency.name  ");
			case DB_TABLE_DEBTS:case DB_TABLE_PAYMENTS:case DB_TABLE_SPENDING:case DB_TABLE_INCOMES:
				if(!is_numeric($date)){
			return getFetshALLTableWithQuery("SELECT currency.name AS currency,
			Sum($tableName.value) AS sum
			FROM currency
			JOIN equalities ON currency.iD = equalities.CurrencyID
			LEFT JOIN $tableName ON equalities.iD = $tableName.EqualitiesID
			WHERE (Date($tableName.date) < '$date') AND (isDirect is NULL OR isDirect=0) AND (FromBox is NULL OR FromBox=0) GROUP BY currency.name  ");
				}
			return getFetshALLTableWithQuery("SELECT currency.name AS currency,
			Sum($tableName.value) AS sum
			FROM currency
			JOIN equalities ON currency.iD = equalities.CurrencyID
			LEFT JOIN $tableName ON equalities.iD = $tableName.EqualitiesID
			WHERE (month($tableName.date) < '$date') AND (isDirect is NULL OR isDirect=0) AND (FromBox is NULL OR FromBox=0) GROUP BY currency.name  ");
			
		}
	}
	function balanceDueFromTo($tableName,$date,$to){
	    switch($tableName)
		{
			default:
			if(!is_numeric($date)){
			return getFetshALLTableWithQuery("SELECT currency.name AS currency,
			Sum($tableName.value) AS sum
			FROM currency
			JOIN equalities ON currency.iD = equalities.CurrencyID
			LEFT JOIN $tableName ON equalities.iD = $tableName.EqualitiesID
			WHERE (Date($tableName.date) >= '$date') AND (Date($tableName.date) <= '$to') GROUP BY currency.name  ");}
			return getFetshALLTableWithQuery("SELECT currency.name AS currency,
			Sum($tableName.value) AS sum
			FROM currency
			JOIN equalities ON currency.iD = equalities.CurrencyID
			LEFT JOIN $tableName ON equalities.iD = $tableName.EqualitiesID
			WHERE (month($tableName.date) >= '$date') AND (month($tableName.date) <= '$to') GROUP BY currency.name  ");
			case DB_TABLE_DEBTS:case DB_TABLE_PAYMENTS:case DB_TABLE_SPENDING:case DB_TABLE_INCOMES:
				if(!is_numeric($date)){
			return getFetshALLTableWithQuery("SELECT currency.name AS currency,
			Sum($tableName.value) AS sum
			FROM currency
			JOIN equalities ON currency.iD = equalities.CurrencyID
			LEFT JOIN $tableName ON equalities.iD = $tableName.EqualitiesID
			WHERE (Date($tableName.date) >= '$date') AND (Date($tableName.date) <= '$to') AND (isDirect is NULL OR isDirect=0) AND (FromBox is NULL OR FromBox=0) GROUP BY currency.name  ");
				}
			return getFetshALLTableWithQuery("SELECT currency.name AS currency,
			Sum($tableName.value) AS sum
			FROM currency
			JOIN equalities ON currency.iD = equalities.CurrencyID
			LEFT JOIN $tableName ON equalities.iD = $tableName.EqualitiesID
			WHERE (month($tableName.date) >= '$date') AND (month($tableName.date) <= '$to') AND (isDirect is NULL OR isDirect=0) AND (FromBox is NULL OR FromBox=0) GROUP BY currency.name  ");
			
		}
	}
	function balanceDue($tableName,$date)
	{
	  
		switch($tableName)
		{
		   
			default:
			if(!is_numeric($date)){
			return getFetshALLTableWithQuery("SELECT currency.name AS currency,
			Sum($tableName.value) AS sum
			FROM currency
			JOIN equalities ON currency.iD = equalities.CurrencyID
			LEFT JOIN $tableName ON equalities.iD = $tableName.EqualitiesID
			WHERE (Date($tableName.date) <= '$date') GROUP BY currency.name  ");}
			return getFetshALLTableWithQuery("SELECT currency.name AS currency,
			Sum($tableName.value) AS sum
			FROM currency
			JOIN equalities ON currency.iD = equalities.CurrencyID
			LEFT JOIN $tableName ON equalities.iD = $tableName.EqualitiesID
			WHERE (month($tableName.date) <= '$date') GROUP BY currency.name  ");
			case DB_TABLE_DEBTS:case DB_TABLE_PAYMENTS:case DB_TABLE_SPENDING:case DB_TABLE_INCOMES:
				if(!is_numeric($date)){
			return getFetshALLTableWithQuery("SELECT currency.name AS currency,
			Sum($tableName.value) AS sum
			FROM currency
			JOIN equalities ON currency.iD = equalities.CurrencyID
			LEFT JOIN $tableName ON equalities.iD = $tableName.EqualitiesID
			WHERE (Date($tableName.date) <= '$date') AND (isDirect is NULL OR isDirect=0) AND (FromBox is NULL OR FromBox=0) GROUP BY currency.name  ");
				}
			return getFetshALLTableWithQuery("SELECT currency.name AS currency,
			Sum($tableName.value) AS sum
			FROM currency
			JOIN equalities ON currency.iD = equalities.CurrencyID
			LEFT JOIN $tableName ON equalities.iD = $tableName.EqualitiesID
			WHERE (month($tableName.date) <= '$date') AND (isDirect is NULL OR isDirect=0) AND (FromBox is NULL OR FromBox=0) GROUP BY currency.name  ");
			
		}
	
	}
	
	//TODO it's an error result
	function OrderReminder(){
	
	    $result=getFetshALLTableWithQuery("SELECT customer.token,
            customer.name,
            customer.iD,
            customer.phone,
            extendedorder.TermsDate,
            extendedorder.OrderID
            FROM customer
            INNER JOIN extendedorder ON customer.iD = extendedorder.CustomerID
            WHERE (Date(extendedorder.TermsDate)) = (CurDate())");
    if(is_array($result)){
        for($x=0;$x<count($result);$x++){
            $balance=getBalanceDueTo($result[$x]['iD'],CurDate())['balance'];
            if($balance<=0){
                unset($result[$x]);
            }else{
                $result[$x]['balance']='';
                $result[$x]['balance']=$balance;
            }
        }
    }
  //  print_r($result);
    return $result;
	    
	}
	function customerTerms($iD){
	    $result=array();
	    $response=array();
	    if(empty($iD)){
	        	    $result=getFetshALLTableWithQuery("SELECT customer.token,
  customer.name,
  customer.iD,
  customer.phone,
  extendedorder.TermsDate AS TermsDate,
  extendedorder.OrderID,
  Query1.date1,
  (To_Days(Query1.date1) - To_Days(CurDate())) AS daysbetween,
  Query1.value
FROM customer
  INNER JOIN extendedorder ON customer.iD = extendedorder.CustomerID
  LEFT JOIN (SELECT Max(payback.date) AS date1,
    payback.CustomerID,
    payback.value
  FROM payback
  GROUP BY payback.CustomerID,
    payback.value) Query1 ON customer.iD = Query1.CustomerID");
	    }else{
	        $result=getFetshALLTableWithQuery("SELECT customer.token,
  customer.name,
  customer.iD,
  customer.phone,
  extendedorder.TermsDate AS TermsDate,
  extendedorder.OrderID,
  Query1.date1,
  (To_Days(Query1.date1) - To_Days(CurDate())) AS daysbetween,
  Query1.value
FROM customer
  INNER JOIN extendedorder ON customer.iD = extendedorder.CustomerID
  LEFT JOIN (SELECT Max(payback.date) AS date1,
    payback.CustomerID,
    payback.value
  FROM payback
  GROUP BY payback.CustomerID,
    payback.value) Query1 ON customer.iD = Query1.CustomerID
    WHERE  customer.iD='$iD'");
	    }
    if(is_array($result)){
        for($x=0;$x<count($result);$x++){
            $balance=getBalanceDueTo($result[$x]['iD'],$result[$x]['TermsDate'])['balance'];
            if($balance<=0){
                unset($result[$x]);
            }else{
                $result[$x]['balance']='';
                $result[$x]['balance']=$balance;
                array_push($response,$result[$x]);
            }
        }
    }
    return $response;
		
	}
	function notPayCustomers()
	{
	    $result=array();
	    $response=array();
	    $result=getFetshALLTableWithQuery("SELECT customer.token,
  customer.name,
  customer.iD,
  customer.phone,
  extendedorder.TermsDate AS TermsDate,
  extendedorder.OrderID,
  Query1.date1,
  (To_Days(Query1.date1) - To_Days(CurDate())) AS daysbetween,
  Query1.value
FROM customer
  INNER JOIN extendedorder ON customer.iD = extendedorder.CustomerID
  LEFT JOIN (SELECT Max(payback.date) AS date1,
    payback.CustomerID,
    payback.value
  FROM payback
  GROUP BY payback.CustomerID,
    payback.value) Query1 ON customer.iD = Query1.CustomerID
WHERE (Date(extendedorder.TermsDate)) <= (CurDate())");
    if(is_array($result)){
        for($x=0;$x<count($result);$x++){
            $balance=getBalanceDueTo($result[$x]['iD'],$result[$x]['TermsDate'])['balance'];
            if($balance<=0){
                unset($result[$x]);
            }else{
                $result[$x]['balance']='';
                $result[$x]['balance']=$balance;
                array_push($response,$result[$x]);
            }
        }
    }
    return $response;
		
	}
	function getMinMaxMonth(){
		return getFetshTableWithQuery("SELECT month(( Max(product.date))) AS maxMonth,
		Max(product.date) as maxDate,Min(product.date) as minDate,
  month(Min(product.date)) AS minMonth
FROM product");
	}
	
	///object to SalesYear SalesMonth, TotalSales it's total sale only
	function getTotalSalesPerMonth(){
	    return getFetshAllTableWithQuery("SELECT Year(orders.date) AS SalesYear,
  Month(orders.date) AS SalesMonth,
  round(Sum(orderdetails.weight),3) AS TotalSales
FROM orderdetails
  INNER JOIN orders ON orders.iD = orderdetails.OrdersID
GROUP BY Year(orders.date),
  Month(orders.date)
ORDER BY SalesYear,
  SalesMonth");
	}
	//the same but totalReturns
	function getTotalReturnPerMonth(){
	        return getFetshAllTableWithQuery("SELECT Year(orders.date) AS SalesYear,
  Month(orders.date) AS SalesMonth,
  round(Sum(orderdetails.recallWeight),3) AS TotalSales
FROM orderdetails
  INNER JOIN orders ON orders.iD = orderdetails.OrdersID
GROUP BY Year(orders.date),
  Month(orders.date)
ORDER BY SalesYear,SalesMonth");
	}
		function getTotalSalesPerMonthAll(){
	        return getFetshAllTableWithQuery("SELECT Year(orders.date) AS SalesYear,
  Month(orders.date) AS SalesMonth,
  round(Sum(orderdetails.weight)-Sum(orderdetails.recallWeight),3) AS TotalSales
FROM orderdetails
  INNER JOIN orders ON orders.iD = orderdetails.OrdersID
GROUP BY Year(orders.date),
  Month(orders.date)
ORDER BY SalesYear,SalesMonth");
	}
		///object to Width Length, TotalSales it's total
	function getBestSellingSize(){
	   return getFetshAllTableWithQuery("SELECT Sum(orderdetails.weight - orderdetails.recallWeight) AS TotalSales,
  searchview.Width AS Width,
  searchview.Length AS Length
FROM orderdetails
  INNER JOIN searchview ON searchview.iD = orderdetails.ProductID
GROUP BY searchview.Width,
  searchview.Length ORDER  BY TotalSales DESC LIMIT 5"); 
	}
	function getBestSellingGSM(){
	    return getFetshAllTableWithQuery(" SELECT Sum(orderdetails.weight - orderdetails.recallWeight) AS TotalSales,
  gsm.gsm AS GSM
FROM orderdetails
  INNER JOIN orders ON orders.iD = orderdetails.OrdersID
  INNER JOIN product ON product.iD = orderdetails.ProductID
  INNER JOIN gsm ON gsm.iD = product.GramagID
GROUP BY gsm.gsm ORDER  BY TotalSales DESC LIMIT 5");
	}
		function getBestSellingType(){
	    return getFetshAllTableWithQuery("SELECT Sum(orderdetails.weight - orderdetails.recallWeight) AS TotalSales,
  producttype.name
FROM orderdetails
  INNER JOIN orders ON orders.iD = orderdetails.OrdersID
  INNER JOIN product ON product.iD = orderdetails.ProductID
  INNER JOIN producttype ON producttype.iD = product.ProductTypeID
GROUP BY producttype.name ORDER  BY TotalSales DESC LIMIT 5");
	}
	function getBestProfitableType(){
	     return getFetshAllTableWithQuery("SELECT producttype.name AS name,
  Sum(round((orderdetails.price - (orderdetails.recallWeight *
  orderdetails.unitPrice)), 2)) AS sellingPrice,
  Sum(round(((orderdetails.weight * prices.buyer_Price) -
  (orderdetails.recallWeight * prices.buyer_Price)), 2)) AS buyPrice,
  Count(orderdetails.ProductID) AS Count_ProductID
FROM ((orderdetails
  JOIN product ON product.iD = orderdetails.ProductID)
  JOIN producttype ON producttype.iD = product.ProductTypeID)
  JOIN prices ON prices.iD = producttype.PricesID
GROUP BY producttype.name");
	}
	// * -1 i forgent to 
	function getProfitsByMonths(){
	    return getFetshAllTableWithQuery("SELECT Year(orders.date) AS SalesYear,
  Month(orders.date) AS SalesMonth,
  CASE  WHEN customer.first='1' THEN Sum(round( (orderdetails.price - (orderdetails.recallWeight * orderdetails.unitPrice) ), 3))   ELSE
  ((Sum(round(((orderdetails.weight * prices.buyer_Price) -
  (orderdetails.recallWeight * prices.buyer_Price)), 3))) -
  (Sum(round((orderdetails.price - (orderdetails.recallWeight *
  orderdetails.unitPrice)), 3)))) * -1 END AS TotalSales,
  Count(orderdetails.ProductID) AS Count_ProductID
FROM ((orderdetails
  JOIN product ON product.iD = orderdetails.ProductID)
  JOIN producttype ON producttype.iD = product.ProductTypeID)
  JOIN prices ON prices.iD = producttype.PricesID
  JOIN purchases ON purchases.iD = product.PurchasesID
  JOIN customer ON customer.iD = purchases.CustomerID
  INNER JOIN orders ON orders.iD = orderdetails.OrdersID

GROUP BY SalesYear,
  SalesMonth");
	}
	function getWastsByMonths(){
	    return getFetshAllTableWithQuery("SELECT Year(product.date) AS SalesYear,
  Month(product.date) AS SalesMonth,
  Sum(round(product.orginalWeight * prices.buyer_Price)) AS TotalSales,
  Sum(product.orginalWeight) AS TotalQuantity,
  Count(product.iD) AS Count_ProductID
FROM (product
  JOIN producttype ON producttype.iD = product.ProductTypeID)
  JOIN prices ON prices.iD = producttype.PricesID
      WHERE product.OrderStatus='WASTED'
GROUP BY SalesYear,
  SalesMonth");
	}
	function getSalesAnalysis(){
	    $response;
	    $response["bestSellingSize"]=getBestSellingSize();
	    $response["bestSellingGSM"]=getBestSellingGSM();
	    $response["bestSellingTYPE"]=getBestSellingType();
	    $response["bestProfitableType"]=getBestProfitableType();
	    $response["total"]=getTotalSalesPerMonthAll();
	    $response["totalSales"]=getTotalSalesPerMonth();
	    $response["totalReturns"]=getTotalReturnPerMonth();
	    return $response;
	    //to add on android unprofible and مخسر
	    
	    
	}

	//extends find way to remove employee customer not needed
	function getProfitAnalysis(){
	       $response;
	        $tomorrow = new DateTime('tomorrow');
            $EDate=$tomorrow->format('Y-m-d');
            $response["profits"]=getProfitsByMonths();
            $response["wastes"]=getWastsByMonths();
        //$response["bestProfitableType"]=getBestProfitableType();
	       $response["debtsDue"]=balanceDue(DB_TABLE_DEBTS,$EDate);
		   $response["paybacksDue"]=balanceDue(DB_TABLE_PAYMENTS,$EDate);
		   $response["incomesDue"]=balanceDue(DB_TABLE_INCOMES,$EDate);
		   $response["spendingDue"]=balanceDue(DB_TABLE_SPENDING,$EDate);

		    $response["spendingList"]=getSpending();
		    		   $response["incomesList"]=getIncomes();
	         return $response;
	}
		
		
?>