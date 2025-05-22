<?php


$DELETE_OBJ[SP] = function (&$object) {
	checkToDeleteJournal($object);
};
$DELETE_OBJ[INC] = function (&$object) {
	checkToDeleteJournal($object);
};
$DELETE_OBJ[CRED] = function (&$object) {
	checkToDeleteJournal($object);
};
$DELETE_OBJ[DEBT] = function (&$object) {
	checkToDeleteJournal($object);
};
$DELETE_OBJ[TYPE] = function ($object) {
	if (!isEmptyString($object["image"])) {
		unlinkFile(getKeyValueFromObj($object, "image"));
	}
};
$DELETE_OBJ[CUSTOMS] = function (&$object) {
	$result = depthSearch($object['iD'], CUSTOMS, -1, [CUSTOMS_IMAGES], [], null);
	if (!empty($result[CUSTOMS_IMAGES])) {
		foreach ($result[CUSTOMS_IMAGES] as $img) {
			if (!isEmptyString($img["image"])) {
				unlinkFile($img["image"]);
			}
		}
	}
};
//TODO check strrpos last index of /
$DELETE_OBJ[CUSTOMS_IMAGES] = function (&$object) {
	if (!isEmptyString(getKeyValueFromObj($object, 'image'))) {
		unlinkFile(getKeyValueFromObj($object, "image"));
	}
};


$DELETE_OBJ[HOME_ADS] = function (&$object) {
	if (!isEmptyString(getKeyValueFromObj($object, 'image'))) {
		unlinkFile(getKeyValueFromObj($object, "image"));
	}
};
