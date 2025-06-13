<?php

use Etq\Restful\Helpers;
use Etq\Restful\Repository\BaseRepository;
use Etq\Restful\Repository\FundRepository;
use Etq\Restful\Repository\Joins;
use Etq\Restful\Repository\JoinType;
use Etq\Restful\Repository\Options;


function setImage(&$object)
{
    $image = Helpers::isSetKeyFromObjReturnValue($object, "image");
    if (Helpers::isBase64($image)) {
        Helpers::unSetKeyFromObj($object, "delete");
        $filename_path = md5(time() . uniqid()) . ".jpg";
        $base64_string = str_replace('data:image/png;base64,', '', $image);
        $base64_string = str_replace(' ', '+', $image);
        $decoded = base64_decode($base64_string);
        file_put_contents("Images/" . $filename_path, $decoded);
        Helpers::setKeyValueFromObj($object, "image", $filename_path);
    }
}
function unlinkImage($object)
{
    $image = Helpers::isSetKeyFromObjReturnValue($object, "image");
    if ($image) {
        $image =  ROOT . "Images/" . $image;
        echo "unlinking $image";
        Helpers::unlinkFile($image);
    }
}
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
    },

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
    BEFORE . EDIT => function (&$object, ?Options &$option, BaseRepository $reo) {
        setImage($object);
    },
    BEFORE . ADD => function (&$object, ?Options &$option, BaseRepository $reo) {
        setImage($object);
    },
    BEFORE . DELETE => function (&$object, ?Options &$option, BaseRepository $reo) {
        unlinkImage($object);
    }
];
$container[CUSTOMS] = [
    BEFORE . DELETE => function (&$object, ?Options &$option, BaseRepository $reo) {
        $images = Helpers::isSetKeyFromObjReturnValue($object, CUSTOMS_IMAGES);
        if (!$images || is_null($images) || empty($images)) {
            $iD = Helpers::getKeyValueFromObj($object, "iD");
            $images = $reo->list(CUSTOMS_IMAGES, null, Options::getInstance()->withStaticSelect("CustomsDeclarationID=''$iD"));
        }
        if (!empty($images)) {
            foreach ($images as $ob) {
                unlinkImage($ob);
            }
        }
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
$container[PR] = [
    BEFORE . LISTO => function (&$object, ?Options &$option, BaseRepository $reo) {
        if ($option->notFoundedColumns->get("requiresInventory", null)) {
            $option
                ->replaceTableName(INV)
                ->addJoin(new Joins(INV, PR, "ProductID", JoinType::RIGHT, ID))
                ->addStaticQuery("quantity <> 0")
                ->addGroupBy("ProductID");
        }
    },

];
$container[TYPE] = [
    AFTER . VIEW => function (&$object, ?Options &$option, BaseRepository $reo) {
        $text_purchase_price = $option?->auth?->checkForPermissionBoolean("text_purchase_price");
        $text_prices_for_customer = $option?->auth?->checkForPermissionBoolean("text_prices_for_customer");
        if (!$text_prices_for_customer) {
            Helpers::unSetKeyFromObj($object, "sellPrice");
        }
        if (!$text_purchase_price) {
            Helpers::unSetKeyFromObj($object, "purchasePrice");
        }
        $iD = Helpers::getKeyValueFromObj($object, ID);
        $res = $reo->getFetshCountQueryForTable(PR_SEARCH, Options::getInstance($option)->addStaticQuery("ProductTypeID='$iD'"));
        Helpers::setKeyValueFromObj(
            $object,
            "availability",
            $res
        );
    },
];
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




// <?php
// $OBJECT_ACTIONS[PR] = function (&$object) {

//   if (checkRequestValue("<width>")) {
//     $size = array();
//     $size["width"] = jsonDecode(getRequestValue("<width>"), true)[0];
//     $size['length'] = jsonDecode(getRequestValue("<length>"), true)[0];
//     $object["requiredSize"] = $size;
//     $waste = jsonDecode(getRequestValue("<maxWaste>"), true)[0];
//     switch ($waste) {
//       case "10 (mm)":
//         $waste = "M1";
//         break;
//       case "20 (mm)":
//         $waste = "M2";
//         break;
//       case "30 (mm)":
//         $waste = "M3";
//         break;
//       case "40 (mm)":
//         $waste = "M4";
//         break;
//       case "50 (mm)":
//         $waste = "M5";
//         break;
//       case "60 (mm)":
//         $waste = "M6";
//         break;
//       case "70 (mm)":
//         $waste = "M7";
//         break;
//       case "80 (mm)":
//         $waste = "M8";
//         break;
//       case "90 (mm)":
//         $waste = "M9";
//         break;
//       case "100 (mm)":
//         $waste = "M10";
//         break;
//     }
//     $object["requiredMaxWaste"] = $waste;
//   }
//   $iD = $object['iD'];
//   $option = array();
//   if (checkPermissionActionWAR('text_products_quantity')) {
//     $option["WHERE_EXTENSION"] = "`ProductID` = '$iD' AND quantity <>0 AND WarehouseID IS NOT NULL";
//     // $result = depthSearch(null, PR_INV, 0, [], [], $option);
//     $result = null;
//     if (empty($result) || is_null($result)) {
//       $object['inStock'] = null;
//     } else {
//       $object['inStock'] = $result;
//     }
//   } else {
//     $object['inStock'] = null;
//   }


//   ///Get Parents

//   if (!is_null($object[PARENTID])) {
//     //	$parentID=$object[PARENTID];
//     //	$iD=$object['iD'];
//     //	$option=array();
//     //	$option["WHERE_EXTENSION"]= " `iD` = '$parentID'";
//     //	$object["parents"]=depthSearch(null,PR,1,[],true,$option);
//     //	$option["WHERE_EXTENSION"]= " `ParentID` = '$parentID' ";
//     //	$object["parentChildes"]=depthSearch(null,PR,1,[],true,$option);
//   }
//   // print_r($object);
//   // die;
//   if (isset($object["cut_requests_count"])) {
//     if ($object["cut_requests_count"] > 0) {
//       $option["WHERE_EXTENSION"] = "`ProductID` = '$iD'";
//       // $osbj = depthSearch(null, "pending_cut_requests", 0, [], [], $option);
//       $osbj = null;
//       if (!is_null($osbj)) {
//         if (!empty($osbj)) {
//           $object["pending_cut_requests"] = $osbj[0]["quantity"];
//         }
//       }
//     }
//   }
//   if (isset($object["reservation_invoice_details_count"])) {
//     if (isEmployee()) {
//       $curDate = curdate();
//       $option["WHERE_EXTENSION"] = "`ProductID` = '$iD' ";
//       $osbj = depthSearch(null, "pending_reservation_invoice", 0, [], [], $option);

//       if (!is_null($osbj)) {
//         if (!empty($osbj)) {
//           $object["pending_reservation_invoice"] = $osbj[0]["quantity"];
//         }
//       }
//     }
//   }
//   $option["WHERE_EXTENSION"] = "`ProductID` = '$iD'";
//   if (!checkPermissionActionWAR('text_products_notes')) {
//     unset($object['comments']);
//   }
// };

// $CUSTOM_SEARCH_QUERY[SIZE] = function ($object) {

//   // die;
//   if (is_numeric($object)) {
//     $s = $object;
//     $ps = $object + 90;
//     $ms = $object - 90;
//     $results;
//     if (checkRequestValue("<unit>")) {

//       $requestUnit = json_decode(getRequestValue("<unit>"), true);
//       if (isRequestValueIsRoll($requestUnit[0])) {
//         $results = getFetshALLTableWithQuery("SELECT `sizes`.`iD` FROM " . SIZE . " WHERE `width` BETWEEN $ms AND $ps AND (`length`='0' OR `Length` IS NULL)");
//       } else {
//         $results = getFetshALLTableWithQuery("SELECT `sizes`.`iD` FROM " . SIZE . " WHERE `width` BETWEEN $ms AND $ps OR `length`  BETWEEN $ms AND $ps  AND (`Length` <>'0')");
//       }
//     } else {
//       $results = getFetshALLTableWithQuery("SELECT `sizes`.`iD` FROM " . SIZE . " WHERE `width` BETWEEN $ms AND $ps OR `length`  BETWEEN $ms AND $ps");
//     }


//     if (empty($results)) {
//       return null;
//     }
//     $results = array_map(function ($tmp) {
//       return $tmp['iD'];
//     }, $results);
//     return $results;
//   }

//   return null;
// };
// $CAN_SEARCH_IN_STRING_QUERY[CRED] = function ($searchQuery, $tableName) {
//   if (is_numeric($searchQuery)) {
//     // echo ("   ".($tableName===SIZE)."  ".($tableName===GSM)." ".($tableName===CUSTOMS));
//     return false;
//   } else {
//     return $tableName === EMP || $tableName === CUST;
//   }

//   return true;
// };
// $CUSTOM_SEARCH_COL[PR] = function () {
//   $columns = array();
//   $columns[] = "dateEnum";
//   $columns[] = "unit";
//   $columns[] = "width";
//   $columns[] = "length";
//   $columns[] = "maxWaste";
//   return $columns;
// };
// function isRequestValueIsRoll($value)
// {
//   return $value == "Roll" or $value == "رول" or $value == "Reel" or $value == "Reel(s)";
// }
// $CUSTOM_SEARCH_COL_GET[PR] = function ($key, $value) {
//   // $whereQuery=array();
//   $query = "";
//   switch ($key) {
//     case "unit":
//       // $stringSearchQuery=null;
//       // if(checkRequestValue(getRequestValue("searchStringQuery"))){
//       //     if(is_numeric(getRequestValue("searchStringQuery"))){

//       //     }
//       // }
//       //  echo " is unit $value \n";
//       $value = jsonDecode($value, true);
//       $value = $value[0];
//       $results = array();
//       if (isRequestValueIsRoll($value)) {
//         //   echo " is Roll \n";
//         $results = getFetshALLTableWithQuery("SELECT `sizes`.`iD` FROM " . SIZE . " WHERE  `length`='0' OR `Length` IS NULL");
//       } else if ($value == "Pallet" or $value == "بالة") {
//         //     echo " is Pallet \n";
//         $results = getFetshALLTableWithQuery("SELECT `sizes`.`iD` FROM " . SIZE . " WHERE `Length` <>'0'");
//       }
//       if (empty($results)) {
//         return "";
//       }
//       $results = array_map(function ($tmp) {
//         return $tmp['iD'];
//       }, $results);
//       $whereIds = implode("','", $results);
//       return PR . ".`SizeID` IN ('$whereIds')";



//     case "dateEnum":
//       $value = jsonDecode($value, true);
//       $value = $value[0];
//       if ($value == "All" or $value == "الكل") {
//         $value = "";
//       } else if ($value == "Today" or $value == "اليوم") {
//         $query = " DATE(" . PR . ".date) = CURDATE() ";
//       } else if ($value == "This week" or $value == "هذا الأسبوع") {
//         $query = " YEARWEEK(" . PR . ".date, 1) = YEARWEEK(CURDATE(), 1) ";
//       } else if ($value == "This month" or  $value == "هذا الشهر") {
//         $query = " month(" . PR . ".date) = month(CURDATE()) ";
//       } else {
//         $query = " YEAR(" . PR . ".date) = YEAR(CURDATE()) ";
//       }
//       return $query;
//     case "width":
//       $Width = jsonDecode($value, true);
//       $Width = $Width[0];
//       $Length = jsonDecode(getRequestValue("<length>"), true);
//       $Length = $Length[0];

//       $MWidth = ((int)$Width) * 10;
//       $MLength = ((int)$Length) * 10;
//       $results = array();

//       $query = " (`Length` BETWEEN '$Length' AND '$MLength' 
// 		        AND `Width` BETWEEN '$Width' AND '$MWidth'  
// 				OR 
// 				`Length` BETWEEN '$Width' AND '$MWidth' AND `Width` BETWEEN '$Length' AND '$MLength'
// 			    OR 
// 			    `Length` ='0' AND `Width` BETWEEN '$Length' AND '$MLength'
// 			    OR 
// 			    `Length` ='0' AND `Width` BETWEEN '$Length' AND '$MLength')";

//       $results = getFetshALLTableWithQuery("SELECT `sizes`.`iD` FROM " . SIZE . " WHERE  $query");
//       if (empty($results)) {
//         return "";
//       }

//       $results = array_map(function ($tmp) {
//         return $tmp['iD'];
//       }, $results);
//       $whereIds = implode("','", $results);
//       return PR . ".`SizeID` IN ('$whereIds')";
//   }
//   return $query;
// };
