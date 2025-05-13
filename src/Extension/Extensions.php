<?php

use Etq\Restful\Helpers;
use Etq\Restful\Repository\BaseRepository;
use Etq\Restful\Repository\Joins;
use Etq\Restful\Repository\JoinType;
use Etq\Restful\Repository\Options;


function setImagePath(&$object)
{
    $image = Helpers::isSetKeyFromObjReturnValue($object, "image");
    if ($image) {
        Helpers::setKeyValueFromObj(
            $object,
            "image",
            IMAGES_PATH . substr($image, strripos($image, "/"))
        );
    }
}

$container[JO] = [
    AFTER . VIEW => function (&$object, ?Options &$option, BaseRepository $rep) {
        
    }
];

$container[EMP] = [
    AFTER . VIEW => function (&$object, ?Options &$option, BaseRepository $rep) {
        Helpers::unSetKeyFromObj($object, "password");
    }
];
$container[CUST] = [
    AFTER . VIEW => function (&$object, ?Options &$option, BaseRepository $rep) {
        Helpers::unSetKeyFromObj($object, "password");
        // if ($option->tableName == ORDR) {
        //     $iD = Helpers::getKeyValueFromObj($object, ID);
        //     Helpers::setKeyValueFromObj(
        //         $object,
        //         "balance",
        //         $rep->getDashbaordRepository()->getBalance($iD)[0]
        //     );
        // }
    }
];

$container[CUT_RESULT] = [
    AFTER . VIEW => function (&$object, ?Options &$option, BaseRepository $rep) {
        $bool = is_null(($option->auth)) ? "is sauth null" : "not auth null";
        echo "\n" . $bool . " \n";
        echo "\npermission" . $option->auth->checkForPermissionBoolean(CUT) . "\n";
        $iD = Helpers::getKeyValueFromObj($object, "ProductInputID");
        $prInput = Helpers::getKeyValueFromObj($object, PR_INPUT);

        Helpers::setKeyValueFromObj(
            $prInput,
            PR_INPUT_D,
            $rep->list(
                PR_INPUT_D,
                PR_INPUT,
                Options::getInstance($option)->addStaticQuery("ProductInputID='$iD'")->requireObjects()
            )
        );

        $iD = Helpers::getKeyValueFromObj($object, "ProductOutputID");
        $prInput = Helpers::getKeyValueFromObj($object, PR_OUTPUT);
        Helpers::setKeyValueFromObj(
            $prInput,
            PR_OUTPUT_D,
            $rep->list(
                PR_OUTPUT_D,
                PR_OUTPUT,
                Options::getInstance($option)->addStaticQuery("ProductOutputID='$iD'")->requireObjects()
            )
        );
        //   if (isActionTableIs(CUT_RESULT)) {
        //     $iD = $object[CUT]['iD'];
        //     $option["WHERE_EXTENSION"] = "`PCRID` = '$iD'";
        //     $object[CUT][SIZE_CUT] = depthSearch(null, SIZE_CUT, 1, [], [SIZE], $option);

        //     $iD = $object[CUT][PR]['iD'];
        //     $object[CUT][PR] = depthSearch($iD, PR, 1, [PR], true, null);
        //   }
    }
];
$container[TR] = [
    AFTER . VIEW => function (&$object, ?Options &$option, BaseRepository $rep) {
        Helpers::unSetKeyFromObj($object, "warehouse");
        $fromID = Helpers::getKeyValueFromObj($object, "fromWarehouse");
        $toID = Helpers::getKeyValueFromObj($object, "toWarehouse");
        Helpers::setKeyValueFromObj($object, "fromWarehouse", $rep->view(WARE, $fromID));
        Helpers::setKeyValueFromObj($object, "toWarehouse", $rep->view(WARE, $toID));
    }
];

$container[CUSTOMS_IMAGES] = [
    AFTER . VIEW => function (&$object, ?Options &$option, BaseRepository $reo) {
        setImagePath($object);
    }
];
$container[HOME_ADS] = $container[CUSTOMS_IMAGES];
$container[TYPE] = $container[CUSTOMS_IMAGES];
$container[PURCH] = [
    BEFORE . VIEW => function (&$object, ?Options &$option, BaseRepository $reo) {
        echo "\n inside container  before VIEW";
    },
    AFTER . VIEW => function (&$object, ?Options &$option, BaseRepository $reo) {
        echo "\n inside container after VIEW";
    },
];
// $container[PR] = [
//     BEFORE . LISTO => function (&$object, ?Options &$option, BaseRepository $reo) {
//         if ($option->notFoundedColumns->get("requiresInventory", null)) {
//             $option->addJoin(new Joins(PR_INV_NEW, PR, ID, JoinType::RIGHT));
//             $option->addStaticQuery("quantity <> 0");
//         }
//     },
//     //TODO takes 2 sec for each 20 rows
//     ONFEH => function (&$arr, ?Options &$option, BaseRepository $reo) {
//         if ($option->notFoundedColumns->get("requiresInventory", null)) {
//             $arr = array_values($arr);
//             foreach (($arr) as &$a) {
//                 // print_r($a);
//                 $obj = array();
//                 $obj['ProductID'] = $a['ProductID'];
//                 $obj['WarehouseID'] = $a['WarehouseID'];
//                 $obj['quantity'] = $a['quantity'];
//                 $obj[WARE] = $reo->view(WARE, $obj['WarehouseID']);
//                 unset($a['ProductID'], $a['WarehouseID'], $a['quantity']);
//                 $a["inStock"] = array();
//                 $a["inStock"][] = $obj;
//             }
//             // print_r($arr);
//             $arr = array_values(Helpers::removeDuplicatesAndAdd($arr, ID, "inStock"));
//         }
//     },
//     AFTER . VIEW => function (&$object, ?Options &$option, BaseRepository $reo) {

//         if (!Helpers::isSetKeyFromObj($object, "inStock")) {
//             $iD = Helpers::getKeyValueFromObj($object, ID);
//             Helpers::setKeyValueFromObj(
//                 $object,
//                 "inStock",
//                 $reo->list(
//                     PR_INV_NEW,
//                     PR,
//                     Options::getInstance()->addStaticQuery("ProductID='$iD'")->requireObjects()
//                 )
//             );
//         }
//     },
// ];
$container[PR_INV_NEW] = [
    AFTER . VIEW => function (&$object, ?Options &$option, BaseRepository $reo) {
        Helpers::setKeyValueFromObj(
            $object,
            WARE,
            $reo->view(WARE, $object['WarehouseID'])
        );
    },
];
$container[AT] = [
    PR => ["requiresInventory"]
];


// SELECT 
// p.iD,
// IFNULL(qin.quantity, 0) - IFNULL(qout.quantity, 0) AS quantity
// FROM products as p
// LEFT JOIN (
//     SELECT ProductID, WarehouseID,SUM(quantity) AS quantity
//     FROM inventory_total_input
//     GROUP BY ProductID,WarehouseID
// ) qin ON p.Id = qin.ProductID  
// LEFT JOIN (
//     SELECT ProductID, WarehouseID,SUM(quantity) AS quantity
//     FROM inventory_total_output
//     GROUP BY ProductID,WarehouseID
// ) qout ON p.Id = qout.ProductID
