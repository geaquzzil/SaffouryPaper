<?php

function getJournalTableNameFromExisting($object)
{
	$journalID = getKeyValueFromObj($object, "isDirect");
	if ($journalID != 0) {
		$journalRecord = getFetshTableWithQuery("SELECT * FROM " . JO . " WHERE iD='$journalID'");
		$journalTable = getKeyValueFromObj($journalRecord, "transaction");
		$str_arr = explode("_", $journalTable);
		if (getKeyValueFromObj($object, "iD") == getKeyValueFromObj($journalRecord, "fromAccount")) {
			return $str_arr[1];
		} else {
			$secoundID = getKeyValueFromObj($journalRecord, "fromAccount");
			return $str_arr[0];
		}
	}
}
function checkToDeleteJournal(&$object)
{
	if (!isJournalRecord($object)) return;
	$journalTable = getJournalTableNameFromExisting($object);
	deleteObject(getKeyValueFromObj($object, JO), $journalTable, false);
	setKeyValueFromObj($journal, "iD", getKeyValueFromObj($object, "isDirect"));
	deleteObject($journal, JO, false);
}
function isJournalRecord($object)
{
	return isSetKeyAndNotNullFromObj($object, 'isDirect');
}
function checkToSetJournal(&$item)
{
	if (!isJournalRecord($item)) return;
	$journalID = getKeyValueFromObj($item, "isDirect");
	if ($journalID != 0) {
		$journalRecord = getFetshTableWithQuery("SELECT * FROM " . JO . " WHERE iD='$journalID'");
		$journalTable = getKeyValueFromObj($journalRecord, "transaction");
		$str_arr = explode("_", $journalTable);
		$secoundID;
		if (getKeyValueFromObj($item, "iD") == $journalRecord["fromAccount"]) {
			$secoundID = getKeyValueFromObj($journalRecord, "toAccount");
			$journalTable = $str_arr[1];
		} else {
			$secoundID = getKeyValueFromObj($journalRecord, "fromAccount");
			$journalTable = $str_arr[0];
		}
		setKeyValueFromObj($item, "transaction", $journalTable);
		setKeyValueFromObj($item, JO, depthSearch($secoundID, $journalTable, -1, [], [CUST, EMP], null));
	}
}
function getJournalTableNameFirst($object)
{
	$journalTable = getKeyValueFromObj($object, 'transaction');
	$str_arr = explode("_", $journalTable);
	return $str_arr[0];
}
function getJournalTableName($object)
{
	$journalTable = getKeyValueFromObj($object, 'transaction');
	$str_arr = explode("_", $journalTable);
	return $str_arr[1];
}
function addJournalFromObject(&$object, $tableName)
{

	$journalObject = getKeyValueFromObj($object, JO);
	$journalObject = json_decode($journalObject, FALSE);
	$journalTable = getJournalTableName($object);

	$currentObjectID = !isNewRecord($object) ? getKeyValueFromObj($object, ID) : getLastIncrementID($tableName);
	$currentObjectJournalID = getLastIncrementID($journalTable);

	$journal = array();
	$journal["iD"] = -1;
	$journal["fromAccount"] = $currentObjectID;
	$journal["toAccount"] = $currentObjectJournalID;
	$journal["transaction"] = getKeyValueFromObj($object, 'transaction');
	$journal =	addEditObject($journal, JO, null);
	$journalID = getKeyValueFromObj($journal, ID);

	setKeyValueFromObj($object, 'isDirect', $journalID);
	setKeyValueFromObj($journalObject, 'isDirect', $journalID);


	unSetKeyFromObj($journalObject, JO);
	//	print_r($journalObject);
	//	die();
	addEditObject($journalObject, $journalTable, getDefaultAddOptions());
	unSetKeyFromObj($object, JO);
}
function checkToAddRemoveJournal(&$object, $tableName)
{
	//   echo "\n checkToAddRemoveJournal $tableName   ";
	//new record with journal
	if (isNewRecord($object) && isSetKeyAndNotNullFromObj($object, JO)) {
		// echo "  OBJECT IS JOURNAL $tableName  ";

		addJournalFromObject($object, $tableName);
		return;
	}
	if (!isNewRecord($object)) {
		// 	    echo "  OBJECT IS JOURNAL isNewRecord $tableName  ";
		$OriginalRecord = depthSearch(
			getKeyValueFromObj($object, 'iD'),
			$tableName,
			0,
			null,
			null,
			null
		);
		setKeyValueFromObj($object, 'isDirect', null);
		if (isSetKeyAndNotNullFromObj($OriginalRecord, JO)) {
			deleteObjectThatIsJournal($OriginalRecord, $tableName);
		}
		if (isSetKeyAndNotNullFromObj($object, JO)) {
			//	deleteObjectThatIsJournal($OriginalRecord,$tableName);	
			addJournalFromObject($object, $tableName);
		}
	}
}
function deleteObjectThatIsJournal($object, $tableName)
{

	$JornalObject = getKeyValueFromObj($object, JO);

	$AnotherTableName = getJournalTableName($JornalObject);

	$AnotherTableRecord = getKeyValueFromObj($JornalObject, $AnotherTableName)[0];

	setKeyValueFromObj($AnotherTableRecord, "isDirect", null);

	doDelete($AnotherTableRecord, $AnotherTableName, false);
	doDelete($JornalObject, JO, false);
}


$FIX_ADD_OBJECT[SP] = function (&$object) {
	checkToAddRemoveJournal($object, SP);
};
$FIX_ADD_OBJECT[INC] = function (&$object) {
	checkToAddRemoveJournal($object, INC);
};
$FIX_ADD_OBJECT[CRED] = function (&$object) {
	checkToAddRemoveJournal($object, CRED);
};
$FIX_ADD_OBJECT[DEBT] = function (&$object) {
	checkToAddRemoveJournal($object, DEBT);
};
$FIX_ADD_OBJECT[CUSTOMS_IMAGES] = function (&$object) {
	if (isBase64($object->image)) {
		unset($object->delete);
		$filename_path = md5(time() . uniqid()) . ".jpg";
		$base64_string = str_replace('data:image/png;base64,', '', $object->image);
		$base64_string = str_replace(' ', '+', $object->image);
		$decoded = base64_decode($base64_string);
		file_put_contents("Images/" . $filename_path, $decoded);
		$object->image = ROOT . "Images/" . $filename_path;
	}
};

$FIX_ADD_OBJECT[HOME_ADS] = function (&$object) {
	if (isBase64($object->image)) {
		unset($object->delete);
		$filename_path = md5(time() . uniqid()) . ".jpg";
		$base64_string = str_replace('data:image/png;base64,', '', $object->image);
		$base64_string = str_replace(' ', '+', $object->image);
		$decoded = base64_decode($base64_string);
		file_put_contents("Images/" . $filename_path, $decoded);
		$object->image = ROOT . "Images/" . $filename_path;
	}
};

$FIX_ADD_OBJECT[PR] = function (&$object) {
	if (property_exists($object, 'status') && isset($object->status)) {
		$status = $object->status;
		if (is_numeric($status)) {
			switch ($status) {
				case 0:
					$status = 'NONE';
					break;
				case 1:
					$status = 'PENDING';
					break;
				case 2:
					$status = 'RETURNED';
					break;
				case 3:
					$status = 'WASTED';
					break;
			}
			$object->status = $status;
		}
	} else {
		$object->status = "NONE";
	}
};
$FIX_ADD_OBJECT[ORDR] = function (&$object) {
	if (property_exists($object, 'status') && isset($object->status)) {
		$status = $object->status;
		if (is_numeric($status)) {
			switch ($status) {
				case 0:
					$unit = 'NONE';
					break;
				case 1:
					$unit = 'PENDING';
					break;
				case 2:
					$unit = 'PROCESSING';
					break;
				case 3:
					$unit = 'CANCELED';
					break;
				case 4:
					$unit = 'APPROVED';
					break;
			}
			$object->status = $status;
		}
	} else {
		$object->status = "NONE";
	}
};
$FIX_ADD_OBJECT[TYPE] = function (&$object) {
	if (property_exists($object, 'unit') && isset($object->unit)) {
		$unit = $object->unit;
		if (is_numeric($unit)) {
			switch ($unit) {
				case 0:
					$unit = 'KG';
					break;
				case 1:
					$unit = 'Ream';
					break;
				case 2:
					$unit = 'Sheet';
					break;
			}
			$object->unit = $unit;
		}
	} else {
		$object->unit = "KG";
	}
	if (isBase64($object->image)) {
		unset($object->delete);
		$filename_path = md5(time() . uniqid()) . ".jpg";
		$base64_string = str_replace('data:image/png;base64,', '', $object->image);
		$base64_string = str_replace(' ', '+', $object->image);
		$decoded = base64_decode($base64_string);
		file_put_contents("Images/" . $filename_path, $decoded);
		$object->image = ROOT . "Images/" . $filename_path;
	}
};