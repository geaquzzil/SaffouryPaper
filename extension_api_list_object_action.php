<?php
$OBJECT_ACTIONS[TYPE] = function (&$object) {
  if (isCustomer() || isGuest()) {
    if (!checkPermissionActionWAR('text_prices_for_customer')) {
      unset($object['sellPrice']);
    }
    unset($object["purchasePrice"]);
  } else {
    if (!checkPermissionActionWAR('text_purchase_price')) {
      unset($object["purchasePrice"]);
    }
  }
  if (!isEmptyString($object['image'])) {
    $object['image'] = IMAGES_PATH . substr($object["image"], strripos($object["image"], "/"));
  }
  $iD = $object['iD'];
  //todo fixing bad performance
  // $results=getFetshTableWithQuery("SELECT COUNT(`ProductTypeID`) AS availability FROM `".PR_SEARCH."` WHERE `ProductTypeID`='$iD'");
  // if(!empty($results) || !is_null($results)){
  //     $object['availability']=$results['availability'];
  // }else{
  //     $object['availability']=0;
  // }

};

$OBJECT_ACTIONS[SP] = function (&$object) {
  if (isSetKeyAndNotNullFromObj($object, 'isDirect') && isTableIs(SP)) {
    setKeyValueFromObj(
      $object,
      JO,
      depthSearch(getKeyValueFromObj($object, "isDirect"), JO, 0, [INC, CRED], null, null)
    );
  } else {
    setKeyValueFromObj($object, JO, null);
  }
};
$OBJECT_ACTIONS[INC] = function (&$object) {
  //  echo "  hi  ".INC;
  //  echo "  isSetKeyAndNotNullFromObj  ".isSetKeyAndNotNullFromObj($object,'isDirect')? " TRUE ":"FALSE ";
  if (isSetKeyAndNotNullFromObj($object, 'isDirect')  and (isTableIs(INC))) {
    //     echo "  isSetKeyAndNotNullFromObj  ";
    setKeyValueFromObj(
      $object,
      JO,
      depthSearch(getKeyValueFromObj($object, "isDirect"), JO, 0, [SP, DEBT], null, null)
    );

    //checkToSetJournal($object);
  } else {
    //    echo " null journal  ";
    setKeyValueFromObj($object, JO, null);
  }
};
$OBJECT_ACTIONS[CRED] = function (&$object) {
  if (isSetKeyAndNotNullFromObj($object, 'isDirect') && isTableIs(CRED)) {
    setKeyValueFromObj(
      $object,
      JO,
      depthSearch(getKeyValueFromObj($object, "isDirect"), JO, 0, [SP, DEBT], null, null)
    );
  } else {
    setKeyValueFromObj($object, JO, null);
  }
  //$object[JO]=depthSearch
  //checkToSetJournal($object);
  if (isActionIsView() && checkPermissionActionWAR("list_customers_balances")) {
    setKeyValueFromObj($object, 'balance', getBalanceDue($object[KCUST])["balance"]);
  }
};
$OBJECT_ACTIONS[DEBT] = function (&$object) {
  if (isSetKeyAndNotNullFromObj($object, 'isDirect') && isTableIs(DEBT)) {
    //  echo "TSASD";
    setKeyValueFromObj(
      $object,
      JO,
      depthSearch(getKeyValueFromObj($object, "isDirect"), JO, 0, [INC, CRED], null, null)
    );
  } else {
    setKeyValueFromObj($object, JO, null);
  }
  if (isActionIsView() && checkPermissionActionWAR("list_customers_balances")) {
    setKeyValueFromObj($object, 'balance', getBalanceDue($object[KCUST])["balance"]);
  }
};
$OBJECT_ACTIONS[PR] = function (&$object) {

  if (checkRequestValue("<width>")) {
    $size = array();
    $size["width"] = jsonDecode(getRequestValue("<width>"), true)[0];
    $size['length'] = jsonDecode(getRequestValue("<length>"), true)[0];
    $object["requiredSize"] = $size;
    $waste = jsonDecode(getRequestValue("<maxWaste>"), true)[0];
    switch ($waste) {
      case "10 (mm)":
        $waste = "M1";
        break;
      case "20 (mm)":
        $waste = "M2";
        break;
      case "30 (mm)":
        $waste = "M3";
        break;
      case "40 (mm)":
        $waste = "M4";
        break;
      case "50 (mm)":
        $waste = "M5";
        break;
      case "60 (mm)":
        $waste = "M6";
        break;
      case "70 (mm)":
        $waste = "M7";
        break;
      case "80 (mm)":
        $waste = "M8";
        break;
      case "90 (mm)":
        $waste = "M9";
        break;
      case "100 (mm)":
        $waste = "M10";
        break;
    }
    $object["requiredMaxWaste"] = $waste;
  }
  $iD = $object['iD'];
  $option = array();
  if (checkPermissionActionWAR('text_products_quantity')) {
    $option["WHERE_EXTENSION"] = "`ProductID` = '$iD' AND quantity <>0 AND WarehouseID IS NOT NULL";
    // $result = depthSearch(null, PR_INV, 0, [], [], $option);
    $result = null;
    if (empty($result) || is_null($result)) {
      $object['inStock'] = null;
    } else {
      $object['inStock'] = $result;
    }
  } else {
    $object['inStock'] = null;
  }


  ///Get Parents

  if (!is_null($object[PARENTID])) {
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
  if (isset($object["cut_requests_count"])) {
    if ($object["cut_requests_count"] > 0) {
      $option["WHERE_EXTENSION"] = "`ProductID` = '$iD'";
      // $osbj = depthSearch(null, "pending_cut_requests", 0, [], [], $option);
      $osbj = null;
      if (!is_null($osbj)) {
        if (!empty($osbj)) {
          $object["pending_cut_requests"] = $osbj[0]["quantity"];
        }
      }
    }
  }
  if (isset($object["reservation_invoice_details_count"])) {
    if (isEmployee()) {
      $curDate = curdate();
      $option["WHERE_EXTENSION"] = "`ProductID` = '$iD' ";
      $osbj = depthSearch(null, "pending_reservation_invoice", 0, [], [], $option);

      if (!is_null($osbj)) {
        if (!empty($osbj)) {
          $object["pending_reservation_invoice"] = $osbj[0]["quantity"];
        }
      }
    }
  }
  $option["WHERE_EXTENSION"] = "`ProductID` = '$iD'";
  if (!checkPermissionActionWAR('text_products_notes')) {
    unset($object['comments']);
  }
};

$CUSTOM_SEARCH_QUERY[SIZE] = function ($object) {

  // die;
  if (is_numeric($object)) {
    $s = $object;
    $ps = $object + 90;
    $ms = $object - 90;
    $results;
    if (checkRequestValue("<unit>")) {

      $requestUnit = json_decode(getRequestValue("<unit>"), true);
      if (isRequestValueIsRoll($requestUnit[0])) {
        $results = getFetshALLTableWithQuery("SELECT `sizes`.`iD` FROM " . SIZE . " WHERE `width` BETWEEN $ms AND $ps AND (`length`='0' OR `Length` IS NULL)");
      } else {
        $results = getFetshALLTableWithQuery("SELECT `sizes`.`iD` FROM " . SIZE . " WHERE `width` BETWEEN $ms AND $ps OR `length`  BETWEEN $ms AND $ps  AND (`Length` <>'0')");
      }
    } else {
      $results = getFetshALLTableWithQuery("SELECT `sizes`.`iD` FROM " . SIZE . " WHERE `width` BETWEEN $ms AND $ps OR `length`  BETWEEN $ms AND $ps");
    }


    if (empty($results)) {
      return null;
    }
    $results = array_map(function ($tmp) {
      return $tmp['iD'];
    }, $results);
    return $results;
  }

  return null;
};
$CAN_SEARCH_IN_STRING_QUERY[CRED] = function ($searchQuery, $tableName) {
  if (is_numeric($searchQuery)) {
    // echo ("   ".($tableName===SIZE)."  ".($tableName===GSM)." ".($tableName===CUSTOMS));
    return false;
  } else {
    return $tableName === EMP || $tableName === CUST;
  }

  return true;
};
$CUSTOM_SEARCH_COL[PR] = function () {
  $columns = array();
  $columns[] = "dateEnum";
  $columns[] = "unit";
  $columns[] = "width";
  $columns[] = "length";
  $columns[] = "maxWaste";
  return $columns;
};
function isRequestValueIsRoll($value)
{
  return $value == "Roll" or $value == "رول" or $value == "Reel" or $value == "Reel(s)";
}
$CUSTOM_SEARCH_COL_GET[PR] = function ($key, $value) {
  // $whereQuery=array();
  $query = "";
  switch ($key) {
    case "unit":
      // $stringSearchQuery=null;
      // if(checkRequestValue(getRequestValue("searchStringQuery"))){
      //     if(is_numeric(getRequestValue("searchStringQuery"))){

      //     }
      // }
      //  echo " is unit $value \n";
      $value = jsonDecode($value, true);
      $value = $value[0];
      $results = array();
      if (isRequestValueIsRoll($value)) {
        //   echo " is Roll \n";
        $results = getFetshALLTableWithQuery("SELECT `sizes`.`iD` FROM " . SIZE . " WHERE  `length`='0' OR `Length` IS NULL");
      } else if ($value == "Pallet" or $value == "بالة") {
        //     echo " is Pallet \n";
        $results = getFetshALLTableWithQuery("SELECT `sizes`.`iD` FROM " . SIZE . " WHERE `Length` <>'0'");
      }
      if (empty($results)) {
        return "";
      }
      $results = array_map(function ($tmp) {
        return $tmp['iD'];
      }, $results);
      $whereIds = implode("','", $results);
      return PR . ".`SizeID` IN ('$whereIds')";



    case "dateEnum":
      $value = jsonDecode($value, true);
      $value = $value[0];
      if ($value == "All" or $value == "الكل") {
        $value = "";
      } else if ($value == "Today" or $value == "اليوم") {
        $query = " DATE(" . PR . ".date) = CURDATE() ";
      } else if ($value == "This week" or $value == "هذا الأسبوع") {
        $query = " YEARWEEK(" . PR . ".date, 1) = YEARWEEK(CURDATE(), 1) ";
      } else if ($value == "This month" or  $value == "هذا الشهر") {
        $query = " month(" . PR . ".date) = month(CURDATE()) ";
      } else {
        $query = " YEAR(" . PR . ".date) = YEAR(CURDATE()) ";
      }
      return $query;
    case "width":
      $Width = jsonDecode($value, true);
      $Width = $Width[0];
      $Length = jsonDecode(getRequestValue("<length>"), true);
      $Length = $Length[0];

      $MWidth = ((int)$Width) * 10;
      $MLength = ((int)$Length) * 10;
      $results = array();

      $query = " (`Length` BETWEEN '$Length' AND '$MLength' 
		        AND `Width` BETWEEN '$Width' AND '$MWidth'  
				OR 
				`Length` BETWEEN '$Width' AND '$MWidth' AND `Width` BETWEEN '$Length' AND '$MLength'
			    OR 
			    `Length` ='0' AND `Width` BETWEEN '$Length' AND '$MLength'
			    OR 
			    `Length` ='0' AND `Width` BETWEEN '$Length' AND '$MLength')";

      $results = getFetshALLTableWithQuery("SELECT `sizes`.`iD` FROM " . SIZE . " WHERE  $query");
      if (empty($results)) {
        return "";
      }

      $results = array_map(function ($tmp) {
        return $tmp['iD'];
      }, $results);
      $whereIds = implode("','", $results);
      return PR . ".`SizeID` IN ('$whereIds')";
  }
  return $query;
};
