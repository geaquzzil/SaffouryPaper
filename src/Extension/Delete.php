<?php

use Etq\Restful\Helpers;

$container[DELETE . BEFORE][SP] = function (&$object, $container) {
    $container['fund_repository']->checkToDeleteJournal($object);
    checkToDeleteJournal($object);
};
$container[DELETE . BEFORE][INC] = function (&$object, $container) {
    $container['fund_repository']->checkToDeleteJournal($object);
    // checkToDeleteJournal($object);
};
$container[DELETE . BEFORE][CRED] = function (&$object, $container) {
    $container['fund_repository']->checkToDeleteJournal($object);
    // checkToDeleteJournal($object);
};
$container[DELETE . BEFORE][DEBT] = function (&$object, $container) {
    $container['fund_repository']->checkToDeleteJournal($object);
    // checkToDeleteJournal($object);
};
$container[DELETE . BEFORE][TYPE] = function ($object, $container) {
    if (!Helpers::isEmptyString($object["image"])) {
        Helpers::unlinkFile(getKeyValueFromObj($object, "image"));
    }
};
$container[DELETE . BEFORE][CUSTOMS] = function (&$object, $container) {
    $result = depthSearch($object['iD'], CUSTOMS, -1, [CUSTOMS_IMAGES], [], null);
    if (!empty($result[CUSTOMS_IMAGES])) {
        foreach ($result[CUSTOMS_IMAGES] as $img) {
            if (!Helpers::isEmptyString($img["image"])) {
                Helpers::unlinkFile($img["image"]);
            }
        }
    }
};
//TODO check strrpos last index of /
$container[DELETE . BEFORE][CUSTOMS_IMAGES] = function (&$object, $container) {
    if (!Helpers::isEmptyString(getKeyValueFromObj($object, 'image'))) {
        Helpers::unlinkFile(getKeyValueFromObj($object, "image"));
    }
};


$DELETE_OBJ[HOME_ADS] = function (&$object, $container) {
    if (!Helpers::isEmptyString(getKeyValueFromObj($object, 'image'))) {
        Helpers::unlinkFile(getKeyValueFromObj($object, "image"));
    }
};
$DELETE_OBJ[CUT_RESULT] = function (&$object, $container) {
    $iD = getKeyValueFromObj($object, 'ProductInputID');
    deleteObject($iD, PR_INPUT, false);

    $iD = getKeyValueFromObj($object, 'ProductOutputID');
    deleteObject($iD, PR_OUTPUT, false);

    try {
        $iD = getKeyValueFromObj($object, 'CutRequestID');
        $query =  " UPDATE `" . CUT . "` Set `cut_requests`.`cut_status` = 'PROCESSING' WHERE `iD` = '$iD'";
        getUpdateTableWithQuery($query);
    } catch (Exception $e) {
    }
};

$FIX_RESPONSE_OBJECT_DELETE[TR] = function (&$object, $container) {
    $object["fromWarehouse"] = depthSearch($object["fromWarehouse"], WARE, 1, [], [], null);
    $object["toWarehouse"] = depthSearch($object["toWarehouse"], WARE, 1, [], [], null);
};
