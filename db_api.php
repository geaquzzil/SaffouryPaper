<?php
function recursiveSearch(&$result, $recursiveLevel, $i)
{
	if ($i === $recursiveLevel) return;
}
function removeParentTableObjectIfAllTableIsTrue($option)
{

	//when we call a details object with true to all object we dont want to duplicate the parent table object;
	$res = isSetKeyFromObjReturnValue($option, "REM_P_T");
	return is_null($res) ? null : $res;
}
function isRequireParent($option)
{
	///here we need to disable parents and childes on childes depthsearch 
	$res = isSetKeyFromObjReturnValue($option, "REQ_P");
	return is_null($res) ? true : $res;
}
function isRequireParentTable($option)
{
	///here we need to disable parents and childes on childes depthsearch 
	$res = isSetKeyFromObjReturnValue($option, "REQ_T_P");
	return is_null($res) ? true : $res;
}
function isDetailArrayRequire($ParentKey, $key, $detailArrayTable)
{
	if ($ParentKey === $key || $ParentKey == $key) {
		return false;
	}
	if (is_bool($detailArrayTable)) {
		return ((bool)$detailArrayTable);
	}
	if (($i = array_search(getJsonKeyFromForginArray($key), $detailArrayTable)) !== FALSE) {
		return true;
	}
	return false;
}
function isDetailObjectRequire($ParentKey, $key, $detailObjectTable)
{
	if ($ParentKey === $key || $ParentKey == $key) {
		return false;
	}
	if (is_bool($detailObjectTable)) {
		return ((bool)$detailObjectTable);
	}
	//print_r($detailObjectTable);
	if (($i = array_search(getJsonKeyFromForginObject($key), $detailObjectTable)) !== FALSE) {
		return true;
	}

	return false;
}
function deAllowedRecursiveObject($tableName)
{
	$deAllowed = array(EMP);
	return in_array($tableName, $deAllowed) || isActionTableIs($tableName);
}
function allowDetailsOnRecursiveObject($tableName)
{
	// echo "\n allowDetailsOnRecursiveObject ".$tableName;
	$allow = array(PR_INPUT, PR_OUTPUT);
	return in_array($tableName, $allow);
}
function getNotUsedRecords($tableName)
{
	$forginsDetails = getArrayForginKeys($tableName);
	$results = array();
	if (is_null($forginsDetails) || empty($forginsDetails)) {
		return array();
	}
	$i = 0;
	$query = "SELECT iD FROM $tableName a
            WHERE NOT EXISTS";
	$subQuery = "";

	foreach ($forginsDetails as $forgin) {
		$forginKeyColName = $forgin["COLUMN_NAME"];
		$forginTableName = $forgin["TABLE_NAME"];
		$subQuery = $subQuery . " " . (($i === 0) ? " " : "AND NOT EXISTS") . " (SELECT NULL FROM $forginTableName r WHERE  r.$forginKeyColName = a.iD) ";
		$i++;
	}
	//  echo "\n $query $subQuery \n";
	$results = getFetshAllTableWithQuery($query . $subQuery);
	$results = array_map(function ($tmp) {
		return $tmp['iD'];
	}, $results);
	return $results;
}
function recursiveSerachObject($recursiveObj, $parentKey, $object, $tableName, $recursiveLevel)
{
	$recursiveObj = $object;
	if ($recursiveLevel === 0) {
		return $recursiveObj;
	}
	if (deAllowedRecursiveObject($tableName)) return $recursiveObj;
	$forgins = getObjectForginKeys($tableName);
	if (!empty($forgins)) {
		foreach ($forgins as $forgin) {
			$tt = getJsonKeyFromForginObject($forgin);
			if (!isCurrentObjectIDEmpty($object, $forgin)) {
				$theResult = getFetshTableWithQuery(getQueryFromForginCurrent($object, $forgin));
				$theResult = addObjectExtenstion($theResult, $tt);
				$recursiveObj[$tt] = $theResult;
				$recursiveLevel--;
				recursiveSerachObject($recursiveObj, $tt, $theResult, $tt, $recursiveLevel);
			} else {
				$recursiveObj[$tt] = null;
			}
		}
	}
	return $recursiveObj;
}
function getSearchQueryMasterStringValue($object, $tableName)
{
	$res = getSearchObjectStringValue($object, $tableName);
	return ($res);
}
function searchObjectDetailStringValue($object, $tableName)
{
	$hasCustomFunctionFounded = false;
	$searchQuery = hasCustomSearchQueryReturnListOfID($object, $tableName, $hasCustomFunctionFounded);
	if (!is_null($searchQuery)) {
		return $searchQuery;
	}
	if ($hasCustomFunctionFounded) {
		return null;
	}

	$searchQuery = getSearchObjectStringValue($object, $tableName);

	if (isEmptyString($searchQuery)) {
		return null;
	}
	$query = "SELECT " . addslashes($tableName) . ".`iD` FROM "
		. addslashes($tableName) . " WHERE " . $searchQuery;

	$result = getFetshTableWithQuery($query);
	return empty($result) ? null : $result['iD'];
}
//json key as table name // object as json value
function searchObject($object, $tableName)
{
	beforeSearchObjectExtenstion($object, $tableName);
	$searchQueryAttr = customSearchQueryBeforeAddExtenstion($object, $tableName);
	$searchQuery;
	if (!is_null($searchQueryAttr)) {
		$searchQuery = $searchQueryAttr;
	} else {
		$searchQuery = getSearchQueryAttributes($object, $tableName);
	}
	$query = "SELECT iD FROM "
		. addslashes($tableName) . " WHERE " . $searchQuery;

	//echo ("\n $query \n"  );
	$result = getFetshTableWithQuery($query);
	return empty($result) ? null : $result['iD'];
}
function checkToDeleteDetails($object, $tableName)
{
	if (property_exists($object, 'delete') && isset($object->delete)) {
		if ($object->delete) {
			deleteObject($object->iD, $tableName, false);
			return true;
		} else {
			return false;
		}
	} else {
		return false;
	}
}
function getFBEdit($object)
{
	return isNewRecord($object) ? "new" : "edit";
}
function insertWithCheckForDublicates($ParentTableName, $object, $tableName, $option, &$isDuplicate)
{
	$result = getInsertTableWithQuery(getInsertQuery($iD, $arr, $tableName, true));
	$isDuplicate = false;
	if (!$result) {
		$isDuplicate = true;
		$arr = (array)$object;
		return searchObject($arr, $tableName);
	}
	return $result;
}
function insertFromObject($ParentTableName, &$object, $tableName, $option)
{
	$fbAction = getFBEdit($object);
	$arr = (array)$object;
	$search = $option["SEARCH_BEFORE_ADD"];
	$dontSearchOnTable = $option["DONT_SEARCH_BEFORE_ADD"];
	$allowUpdate = $option["ALLOW_UPDATE"];
	$iD = null;
	//	echo "insertFromObject $ParentTableName $tableName  \n";
	//	print_r($object);
	if ($search) {
		if (is_null($dontSearchOnTable)) {
			$iD = searchObject($arr, $tableName);
			if (!is_null($iD)) {
				$object->iD = $iD;
				return $iD;
			}
		} else {
			if ((($i = array_search((string)$tableName, $dontSearchOnTable)) === FALSE)) {
				$iD = searchObject($arr, $tableName);
				if (!is_null($iD)) {
					$object->iD = $iD;
					return $iD;
				}
			}
		}
	}
	//	echo "done search \n";
	if (isset($arr['iD'])) {
		$iD = $arr['iD'];
		unset($arr['iD']);
	}
	if (is_null($iD) || $iD == -1) {
		//	echo "insert \n";
		//SET FB EDIT
		// insert
		$isDuplicate = false;
		$insertID = getInsertTableWithQuery(getInsertQuery($iD, $arr, $tableName, true));
		$object->iD = $insertID;
		$object->fb_edit = $fbAction;
		//	doNotification($object,$tableName,$fbAction);
		return $object->iD;
	} else {
		//allow update for details not the parent table 
		// if its parent then force uppdate;
		if ((!$allowUpdate) && $ParentTableName !== $tableName) {
			//	echo "not allowUpdate \n";
			$object->iD = $iD;
			return $iD;
		} else {
			//	echo "update is  allowUpdate \n";
			getUpdateTableWithQuery(getInsertQuery($iD, $arr, $tableName, false));
			$object->iD = $iD;
			$object->fb_edit = $fbAction;
			//	doNotification($object,$tableName,$fbAction);
			return $object->iD;
		}
	}
}
function validateJsonArrayAndAdd($parentObject, $parentTableName, $array, $tableName, $option)
{
	try {
		unSetKeyFromObj($array, "currentTableName");
		foreach ($array as &$object) {
			if (!checkToDeleteDetails($object, $tableName)) {
				//    print_r($object);
				if (isObject($object)) {
					//  echo "Is OBject \n";
					$object->{$parentTableName} = $parentObject;
					//  print_r($object);
					validateJsonColsAndAdd($tableName, true, $object, $tableName, $option);
				} else {
					//  echo " is array $parentTableName \n ";

					//   print_r($parentObject);
					$object[$parentTableName] = $parentObject;
					//    print_r($object);
					validateJsonColsAndAdd($tableName, true, $object, $tableName, $option);
				}
			}
		}
	} catch (Exception $e) {
		return $e->getMessage();
		die;
	}
}
function validateJsonColsAndAdd($Parent, $isParent, &$object, $tableName, $option)
{
	// echo " validateJsonColsAndAdd ".$Parent. "\n ";
	//  convertToObject($object);

	// echo "\n adding \n";
	$origianlObject = cloneByJson($object);
	if (empty($object) || is_null($object)) {
		return null;
	}
	$forginsDetails = getArrayForginKeys($tableName);
	$forginsDetails = array_map(function ($tmp) {
		return getJsonKeyFromForginArray($tmp);
	}, $forginsDetails);
	//	print_r($object);
	//	print_r($forginsDetails);
	$forgins = getObjectForginKeys($tableName);
	$forgins = array_map(function ($tmp) {
		return  getJsonKeyFromForginObject($tmp);
	}, $forgins);
	//	print_r($forgins);
	$allowUpdateObject = isSetKeyFromObjReturnValue($origianlObject, "allowUpdateObject");
	if (!$isParent && !isNewRecord($object)) {
		if (is_null($allowUpdateObject) || !$allowUpdateObject)
			return $object->iD;
	}
	fixObjectExtenstion($object, $tableName);
	$depth = $option["DEPTH_EDIT_ADD"];
	$allowUpdateDetails = $option["ALLOW_UPDATE_DETAILS"];
	$removeZeroValue = $option["REMOVE_ZERO_VALUE"];
	$ForginsToAdd = array();
	$DetailsToAdd = array();
	$tableColumns = getTableColumns($tableName);
	foreach ($object as $key => $value) {
		// echo "\n FOR $key ";
		if (isObject($value) && $depth) {
			if (empty($value) || (($i = array_search((string)$key, $forgins)) === FALSE)) {
				//    print_r($forgins);
				//   echo " \n Remove isObject $key";

				unSetKeyFromObjWithoutChecking($object, $key);
			} else {
				//   echo "\n isObject =>validateJsonColsAndAdd  $key \n";

				$ForginsToAdd[$key] = validateJsonColsAndAdd($Parent, false, $value, $key, $option);
				//	 echo " ended isObject =>validateJsonColsAndAdd  $key \n";
			}
		}
		if (isArray($value) && $depth && $allowUpdateDetails) {
			if (empty($value) || (($i = array_search((string)$key, $forginsDetails)) === FALSE)) {
				//     print_r($forginsDetails);
				//   echo " \n Remove $key";
				unSetKeyFromObjWithoutChecking($object, $key);
			} else {
				$value['currentTableName'] = $key;
				$DetailsToAdd[] = $value;
				//	 echo " \n Remove isArray $key";
			}
		}
		if ($key === PARENTID && $value === -1) {
			//	     echo " \n   $key===PARENTID && $value===-1 Remove $key  \n";
			unSetKeyFromObjWithoutChecking($object, $key);
		}
		if (
			($i = array_search((string)$key, $tableColumns)) === FALSE
		) {
			unSetKeyFromObjWithoutChecking($object, $key);
		}
		if (empty($value)) {
			if ($value == 0 && $removeZeroValue) {
				//    echo " \n  removed $key  is zero and remove zero  \n";
				unSetKeyFromObjWithoutChecking($object, $key);
			}
			//  else{
			//      echo " \n  empty removed $key  is zero and not remove zero  \n";
			//   }
		}
		if (is_null($value)) {
			unSetKeyFromObjWithoutChecking($object, $key);
		}
	}

	if (!empty($ForginsToAdd)) {
		$forgins = getObjectForginKeys($tableName);
		foreach ($forgins as $fs) {
			if (
				isset($ForginsToAdd[$fs["REFERENCED_TABLE_NAME"]])
			) {
				$object->{$fs["COLUMN_NAME"]} = $ForginsToAdd[$fs["REFERENCED_TABLE_NAME"]];
			}
		}
	}
	fixOnBeforeAddObjectExtenstion($origianlObject, $object, $tableName);

	$object->iD = insertFromObject($Parent, $object, $tableName, $option);
	if (!empty($DetailsToAdd)) {
		foreach ($DetailsToAdd as $detailsArray) {
			//echo "\n Detail to add \n"; 
			validateJsonArrayAndAdd(
				$object,
				$tableName,
				($detailsArray),
				$detailsArray['currentTableName'],
				$option
			);
		}
	}
	afterAddObjectExtenstion($origianlObject, $object, $tableName);
	//	echo "SOS $tableName \n";
	return $object->iD;
}
function addEditObject($object, $tableName, $options)
{
	convertToObject($object);
	$fbAction = getFBEdit($object);
	$iD = validateJsonColsAndAdd($tableName, true, $object, $tableName, $options);
	doNotification($object, $tableName, $fbAction);
	//return the first index
	return depthSearch($iD, $tableName, getRecursiveLevel(), getRequireArrayTables(), getRequireObjectTable(), getOptions());  //getFetshTableWithQuery("SELECT * FROM `$tableName` Where `iD`='$iD'");
}
function addEditObjectWithoutNoti($object, $tableName, $options)
{
	convertToObject($object);
	$fbAction = getFBEdit($object);
	$iD = validateJsonColsAndAdd($tableName, true, $object, $tableName, $options);
	//doNotification($object,$tableName,$fbAction);
	//return the first index
	return depthSearch($iD, $tableName, getRecursiveLevel(), getRequireArrayTables(), getRequireObjectTable(), getOptions());  //getFetshTableWithQuery("SELECT * FROM `$tableName` Where `iD`='$iD'");
}
function searchByID($ParentTableName, $forginsDetails, $forgins, $obj, $tableName, $recursiveLevel, $detailArrayTable, $detailObjectTable, $options)
{
	$starttime = microtime(true);
	$result = $obj;
	$RequireParent = isRequireParent($options);
	if (is_null($result) || empty($result)) return null;
	if (!empty($forgins)) {
		$skipedObject = removeParentTableObjectIfAllTableIsTrue($options);
		foreach ($forgins as $forgin) {
			$pp = getJsonKeyFromForginObject($forgin);
			if (!is_null($skipedObject)) {
				if ($pp == $skipedObject) {
					continue;
				}
			}
			if (isParent($forgin)) {
				if ($RequireParent) {
					$Where = PARENTID;
					$iD = getKeyValueFromObj($result, "ParentID");
					$options["WHERE_EXTENSION"] = $tableName . ".`iD` = '$iD'";
					removeFromArray($detailArrayTable, $tableName);
					removeFromArray($detailObjectTable, $tableName);
					$options["REQ_P"] = false;
					$result["parents"] = depthSearch(null, getJsonKeyFromForginArray($forgin), $recursiveLevel, $detailArrayTable, $detailObjectTable, $options);
				}
			} else if (
				!isCurrentObjectIDEmpty($result, $forgin) && isDetailObjectRequire($ParentTableName, $forgin, $detailObjectTable)
				&& !isActionTableIs($pp)
			) {
				$theResult =	getFetshTableWithQuery(
					getQueryFromForginCurrent($result, $forgin)
				);

				$theResult = addObjectExtenstion($theResult, $pp);
				$result[$pp] = $theResult;
				$result[$pp] = recursiveSerachObject(null, $pp, $result[$pp], $pp, $recursiveLevel);
			} else {
				$result[$pp] = null;
			}
		}
	}
	if (!empty($forginsDetails)) {
		foreach ($forginsDetails as $forgin) {
			$t = getJsonKeyFromForginArray($forgin);
			$Where = $forgin["COLUMN_NAME"];
			$iD = getKeyValueFromObj($result, "iD");
			$options = array();
			$options["WHERE_EXTENSION"] = "`$Where` = '$iD'";
			if (isParent($forgin)) {
				if ($RequireParent) {
					removeFromArray($detailArrayTable, $tableName);
					removeFromArray($detailObjectTable, $tableName);

					$options["REQ_P"] = false;
					$result["childs"] = depthSearch(null, $t, $recursiveLevel, $detailArrayTable, $detailObjectTable, $options);
				}
			} else if (!isDetailedIDEmpty($result, $forgin) && isDetailArrayRequire($ParentTableName, $forgin, $detailArrayTable)) {
				$options["REM_P_T"] = $ParentTableName;
				$result[$t] = depthSearch(null, $t, $recursiveLevel, true, true, $options);
			} else {
				$result[isParent($forgin) ? "childs" : $t] = array();
			}
			$result[isParent($forgin) ? "childs_count" :  $t . "_count"] = isDetailedIDEmpty($result, $forgin) ? 0 :
				getFetshTableWithQuery(getCountQuery($result, $forgin))["result"];
		}
	}
	$result = addObjectExtenstion($result, $tableName);
	$endtime = microtime(true);
	$duration = $endtime - $starttime;
	echo  " searchByID -->->->->-->->->->---->-> $tableName-->->->->-->->->->---->-> \n$duration \n ";
	return $result;
}
function changeToExtended($tableName)
{
	switch ($tableName) {
		default:
			return $tableName;
		case RI:
		case PR_INPUT:
		case PR_OUTPUT:
		case TR:
			return "extended_" . $tableName;
		case PURCH:
			return "extended_purchases_refund";
		case ORDR:
			return "extended_order_refund";
	}
}
///Search for specific Column on the detail table and return the master with the list of details
function depthSearchByDetailTable($masterTableName, $detailTableName, $Column, $ColumnValue, $returnAllDetails)
{
	$forginsDetails = getArrayForginKeys($masterTableName);
	//  print_r($forginsDetails);

	$results = array_map(function ($tmp) {
		return $tmp['TABLE_NAME'];
	}, $forginsDetails);

	if (($i = array_search($detailTableName, $results)) === FALSE) {
		return (array());
	}
	$ColumnNameInMaster;
	foreach ($forginsDetails as $f) {
		if ($f["TABLE_NAME"] === $detailTableName) {
			$ColumnNameInMaster = $f["COLUMN_NAME"];
		}
	}
	$forgins = getObjectForginKeys($detailTableName);
	$results = array_map(function ($tmp) {
		return $tmp['COLUMN_NAME'];
	}, $forgins);
	if (($i = array_search($Column, $results)) === FALSE) {
		return (array());
	}
	$option = array();
	$option["WHERE_EXTENSION"] = " `$Column`= '$ColumnValue'";
	$option["REM_P_T"] = $masterTableName;
	$detailsResult = depthSearch(null, $detailTableName, 1, true, getRequireObjectTable(), $option);
	if (empty($detailsResult)) return (array());
	$response = array();
	foreach ($detailsResult as $detailObject) {
		$iD = $detailObject[$ColumnNameInMaster];
		//    $option=array();
		//   $option["REM_P_T"]=$masterTableName;
		$res = depthSearch($iD, $masterTableName, 1, [], getRequireObjectTable(), null);
		$res[$detailTableName] = array();
		$res[$detailTableName][] = $detailObject;
		array_push($response, $res);
	}
	return $response;

	//return $forginsDetails;
}
function isPrintOptions($options)
{
	return isset($options['PRINT_OPTION']) && $options['PRINT_OPTION'];
}
function depthSearch($iD, $tableName, $recursiveLevel, $detailArrayTable, $detailObjectTable, $options)
{
	if (is_null($iD)) {
		$starttime = microtime(true);


		$results = getFetshALLTableWithQueryOptions(changeToExtended($tableName), $options);
		if (is_null($results) || empty($results)) {
			return array();
		}
		$forginsDetails = array();
		$forgins = array();
		if (!empty($detailArrayTable) || !is_null($detailArrayTable)) {
			$forginsDetails = getArrayForginKeys($tableName);
		}
		if (!empty($detailObjectTable) || !is_null($detailObjectTable)) {
			$forgins = getObjectForginKeys($tableName);
		}
		$response = array();
		$starttime2 = microtime(true);
		foreach ($results as $item) {

			array_push(
				$response,
				searchByID($tableName, $forginsDetails, $forgins, $item, (($tableName)), $recursiveLevel, $detailArrayTable, $detailObjectTable, $options)
			);
		}
		$endtime2 = microtime(true);
		$duration2 = $endtime2 - $starttime2;
		echo  "forloop-->->->->-->->->->---->-> $tableName-->->->->-->->->->---->-> \n$duration2 \n ";

		$endtime = microtime(true);
		$duration = $endtime - $starttime;
		echo  "\ndepthSearch-->->->->-->->->->---->-> $tableName-->->->->-->->->->---->-> \n$duration \n ";
		return $response;
	}
	//one row
	else if (is_array($iD)) {
		if (!is_null($options) &&  isset($options["WHERE_EXTENSION"])) {
			$whereRequest = $options["WHERE_EXTENSION"];
			$Key = is_null($options["KEY"]) ? "`$tableName`.`iD`" : $tableName . $options["KEY"];
			$whereIds = implode("','", $iD);
			$allQuery = " $whereRequest AND `$tableName`.`$Key` IN ('$whereIds')";
			//  echo "$allQuery";
			$options["WHERE_EXTENSION"] = $allQuery;
		} else {
			if (isPrintOptions($options)) {
				$tableNameEx = changeToExtended($tableName);
			} else {
				$tableNameEx = $tableName;
			}
			if (isset($options["KEY"])) {
				$key = $options["KEY"];
				$ids = implode("','", $iD);
				$options["WHERE_EXTENSION"] = "`$tableNameEx`.`$key` IN ( '" . $ids . "' )";
			} else {
				$ids = implode("','", $iD);
				$options["WHERE_EXTENSION"] = " `$tableNameEx`.`iD` IN ( '" . $ids  . "' )";
			}
		}

		return depthSearch(null, $tableName, $recursiveLevel, $detailArrayTable, $detailObjectTable, $options);
	} else {

		$result = getFetshTableWithQueryOptions($iD, changeToExtended($tableName), $options);
		$forginsDetails = array();
		$forgins = array();
		if (!empty($detailArrayTable) || !is_null($detailArrayTable)) {
			$forginsDetails = getArrayForginKeys($tableName);
		}
		if (!empty($detailObjectTable) || !is_null($detailObjectTable)) {
			$forgins = getObjectForginKeys($tableName);
		}
		return searchByID($tableName, $forginsDetails, $forgins, $result, ($tableName), $recursiveLevel, $detailArrayTable, $detailObjectTable, $options);
	}
}


//returns array
// serverStatus could be bool or string 
function deleteObject($object, $tableName, $sendNoti)
{
	$pdo = setupDatabase();
	try {
		$query = "SELECT * FROM  `" . addslashes($tableName) . "` " . getWhereQuery($object);
		$toDeleteObjects = getFetshALLTableWithQuery($query);
		if (empty($toDeleteObjects)) {
			return null;
		}
		$responseArray = array();
		foreach ($toDeleteObjects as $deleteObject) {
			// echo "F";
			$deleteObject["serverStatus"] = doDelete($deleteObject, $tableName, $sendNoti);
			fixDeleteResponseObjectExtenstion($deleteObject, $tableName);
			$responseArray[] = $deleteObject;
			//	array_push($responseArray,$deleteObject);
		}
		return $responseArray;
	} catch (PDOException $e) {
		return null;
	}
}
//returns true or string of error
function doDelete($object, $tableName, $sendNoti)
{
	try {
		$ID = getKeyValueFromObj($object, 'iD');
		$result = getDeleteTableWithQuery("DELETE FROM  `" . addslashes($tableName) . "` WHERE iD='$ID'");
		if ($result) {
			deleteObjectExtenstion($object, $tableName);
			if ($sendNoti) {
				doNotification($object, $tableName, "delete");
			}
		}
		return $result;
	} catch (PDOException $e) {
		return $e->getMessage();
	}
}
