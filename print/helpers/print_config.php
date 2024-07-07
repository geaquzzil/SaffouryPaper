<?php
define("IDF", "2022");

define("RED", "eb1f3c");
define("RED_", "bc202d");

define("GRAY", "819ba9");
define("GRAY_", "60808d");

define("DGRAY", "8d8988");
define("DGRAY_", "8d8988");

define("GREEN", "7db642");
define("GREEN_", "7db642");

define("DARK_GREEN", "0d8040");
define("DARK_GREEN_", "37684c");

define("ORANGE", "f89c1e");
define("ORANGE_", "f57e20");

define("PURBLE", "92278f");
define("PURBLE_", "92278f");

use NumberToWords\NumberToWords;
function getReportHeaderSVG($tableName){
     switch($tableName){
        case CUT:case CRED:case DEBT:case SP:case INC:
            return "headers/headerA5.php?color=".getReportColor($tableName);
        default : 
            $color=getReportColor($tableName);
            $darkColor=getReportColorDark($tableName);
            return "headers/headerA4.php?color=$color&darkColor=$darkColor";
    }
    return null;
}
function getQRCodeLabel(){
    global $invoicr;
    global $tableName;
    $obj = $invoicr->items;
    if(!is_null($invoicr->masterInvoice)){
         $obj = $invoicr->masterInvoice;
    }
    $id=getIDFormat($tableName,$obj);
    return  "<div><small>$id</small></div>";
}
function getRowSpanForQR(){
    global $tableName;
    switch($tableName){
        default : return 2;
        case INC:case CRED: case SP:case DEBT:
            return 4;
    }
}
function getReportLogo($tableName)
{
	$color = getReportColor($tableName);
	$darkColor = getReportColorDark($tableName);
	$darkColor = $color;
	return "logo/logoSVG.php?color=$color&darkColor=$darkColor"; //todo
}
function getReportLogoCustom($color,$darkColor){
    $url= "logo/logoSVG.php?color=$color";
    if($stroke){
        $url.="&stroke=yes";
    }
    if($new){
        $url.="&newModel=yes";
    }
    return $url;
}
function getReportBackground($color,$stroke=true,$new=true){
    $url= "logo/logoSVG.php?color=$color";
    if($stroke){
        $url.="&stroke=yes";
    }
    if($new){
        $url.="&newModel=yes";
    }
    return $url;
}
function getReportColor($tableName)
{
	switch ($tableName) {

		case ORDR:
			return GREEN;
		case PURCH:
			return RED;
		case CRED:case INC:
			return DARK_GREEN;
		case DEBT:
			return RED;
		case SP:
			return RED;
		case CUT:
		case PR_INPUT:
		case PR_OUTPUT:
		case PR:
		    global $printOption;
		    if($printOption->isPrintProductAsLabel()){
		        	return DGRAY;
		    }else{
		        	return GRAY;
		    }
		case CUSTOMS:
		case RI:
			return PURBLE;
		case TR:
			return ORANGE;
	}
	return '#007fff'; 
}
function getDir(){
    global $lang;
    if($lang=='ar'){
        return 'rtl';
    }else{
        return 'ltr';
    }
}
function getReportColorDark($tableName)
{
	switch ($tableName) {

		case ORDR:
			return GREEN_;
		case PURCH:
			return RED_;
		case CRED:case INC:
			return DARK_GREEN_;
		case DEBT:
			return RED_;
		case SP:
			return RED_;
		case CUT:
		case PR_INPUT:
		case PR_OUTPUT:
	        global $printOption;
		    if($printOption->isPrintProductAsLabel()){
		        	return DGRAY_;
		    }else{
		        	return GRAY_;
		    }
		case CUSTOMS:
		case RI:
			return PURBLE_;
		case TR:
			return ORANGE_;
	}
	return '#007fff'; //todo
}
function getNumberFromPrintOption($value){
    return $value;
}
function getCurrency(){
    return 'SY';
}
function getNumberToWords($lang, $value, $currency = 'USD')
{
	$numberToWords = new NumberToWords;
	// build a new number transformer using the RFC 3066 language identifier
	$numberTransformer = $numberToWords->getNumberTransformer($lang);
	$currencyTransformer = $numberToWords->getCurrencyTransformer($lang);
	return $currencyTransformer->toWords($value*100, $currency);
}
function getReportTemplate($tableName){
    switch($tableName){
        case PR:
            //todo add option to print as label or list
            return 'product-label';
        case CUT:case CRED:case DEBT:case SP:case INC:
            return 'cut-invoice';
        default:
            return 'invoices';
    }
}
function isHideBalance()
{
	global $printOption;
	return $printOption->isHideBalanceDue();
}
function isHideUnitAndPrice()
{
	global $printOption;
	return  $printOption->isHideUnitAndPrice();
}
function isOrderOrPurchasesRefund()
{
	global $printOption;
	return $printOption->isOrderOrPurchasesRefund();
}
function isHasReportFooter()
{
	global $printOption;
	return $printOption->isHasReportFooter();
}
function getReportFooter()
{
	global $printOption;
	return $printOption->reportOptions->reportFooter;
}
function getOptionalTableHeaderArray($array)
{
	if (isHideUnitAndPrice()) {
		$array = [
			'des', 'gsms', 'warehouse', 'quantity'
		];
	}
	return $array;
}

function getImgTagSmall($content){
    return "<img id='smallImage' style='width:50px;height:50px;' src='$content'/>";
}
function getImgTag($content){
    return "<img src='$content'>";
}
function getQrCode($tableName, $obj)
{

	$qrCode = new QRCodeID;
	$qrCodeContent;
	switch ($tableName) {
		default:
			$qrCodeContent = $qrCode->getQrCode($tableName, $obj);
		case  PR:
			$qrCodeContent = $qrCode->getQrCodeWithQuantity($tableName, $obj, 1000);
	}

	return $qrCodeContent;
}
function getIDFormat($tableName, $obj)
{
	$iD = $obj[ID];
	switch ($tableName) {
		case ORDR:
			return "INV-$iD-" . IDF;
		case PURCH:
			return "PURCH-$iD-" . IDF;
		case PR_OUTPUT:
			return "PRO-$iD-" . IDF;
		case PR_INPUT:
			return "PRI-$iD-" . IDF;
		case TR:
			return "TR-$iD-" . IDF;
		case ORDR_R:
			return "INV-REF-$iD-" . IDF;
		case PURCH_R:
			return "PRCH-REF-$iD-" . IDF;
		case PR:
			if (isset($obj["barcode"])) {
				$barcode = $obj["barcode"];
				return "PR-$iD-BARCODE-$barcode" . IDF;
			}
			return "PR-$iD-" . IDF;
		case CUT:
			return  "CUT-$iD-" . IDF;
		case CRED:
		    return "CRED-$iD-".IDF;	    
		case DEBT:
		    return "DEBT-$iD-".IDF;	   
		case INC:
		    return "INC-$iD-".IDF;
		case SP:
		    return "SP-$iD-".IDF;
	}
}
function getWarehouse($parent)
{
	if (isset($parent[WARE]))
		return $parent[WARE]["name"];

	return "NOT SET";
}
function getIfSet($obj, $key, $nextKey)
{
	if (isset($obj[$key])) {
		return $obj[$key][$nextKey];
	}
	return "-";
}





function getBig($value)
{
	return "<big>$value</big>";
}
function getStrong($value)
{
	return "<strong>$value</strong>";
}
function getBold($value)
{
	return "<b>$value</b>";
}
function getSmall($value)
{
	return "<small>$value</small>";
}
function getUpperString($value)
{
	return strtoupper($value);
}





function getDepthSearchDetails($tableName)
{
	switch ($tableName) {
		case ORDR:
			return [ORDR_D];
		case PURCH:
			return [PURCH_D];
		case PR_INPUT:
			return [PR_INPUT_D];
		case RI:
			return [RI_D];
		case PR_OUTPUT:
			return [PR_OUTPUT_D];
		case TR:
			return [TR_D];
		case ORDR_R:
			return [ORDR_R_D];
		case PURCH_R:
			return [PURCH_R_D];
		case PR:
			return [];
		case CUT:
			return [CUT_RESULT, SIZE_CUT];
			case CRED:case SP:case DEBT:case INC:
			    return [];
	}
}
function getNumberFormatZero($value)
{
    if(is_string($value)){
        if(is_numeric($value)){
            return number_format($value);
        }
        return $value;
    }
	return number_format($value);
}
function getNumberFormat($value)
{
    if(is_string($value))return $value;
	return round($value,2);
}
function getCurrencyFormat($value,$dec=1){
    return  formatMoney($value,$dec);
}
function formatMoney($number, $cents = 1) { // cents: 0=never, 1=if needed, 2=always
  if (is_numeric($number)) { // a number
    if (!$number) { // zero
      $money = ($cents == 2 ? '0.00' : '0'); // output zero
    } else { // value
      if (floor($number) == $number) { // whole number
        $money = number_format($number, ($cents == 2 ? 2 : 0)); // format
      } else { // cents
        $money = number_format(round($number, 2), ($cents == 0 ? 0 : 2)); // format
      } // integer or decimal
    } // value
    return $money;
  } // numeric
} // formatMoney
  function bd_nice_number($n) {
        // first strip any formatting;
        $n = (0+str_replace(",","",$n));
       
        // is this a number?
        if(!is_numeric($n)) return false;
       
        // now filter it;
        if($n>1000000000000) return round(($n/1000000000000),1).' trillion';
        else if($n>1000000000) return round(($n/1000000000),1).' billion';
        else if($n>1000000) return round(($n/1000000),1).' million';
        else if($n>1000) return round(($n/1000),1).' thousand';
       
        return number_format($n);
    }
function arabicDate($time)
{
	$months = ["Jan" => "يناير", "Feb" => "فبراير", "Mar" => "مارس", "Apr" => "أبريل", "May" => "مايو", "Jun" => "يونيو", "Jul" => "يوليو", "Aug" => "أغسطس", "Sep" => "سبتمبر", "Oct" => "أكتوبر", "Nov" => "نوفمبر", "Dec" => "ديسمبر"];
	$days = ["Sat" => "السبت", "Sun" => "الأحد", "Mon" => "الإثنين", "Tue" => "الثلاثاء", "Wed" => "الأربعاء", "Thu" => "الخميس", "Fri" => "الجمعة"];
	$am_pm = ['AM' => 'صباحاً', 'PM' => 'مساءً'];

	$day = $days[date('D', $time)];
	$month = $months[date('M', $time)];
	$am_pm = $am_pm[date('A', $time)];
	//$date = $day . ' ' . date('d', $time) . '-' . date('m', $time) . '-' . date('Y', $time) .' '. date('h:i', $time) . ' <small>' . $am_pm.'</small>';
	$date =date('d', $time) . '-' . date('m', $time) . '-' . date('Y', $time) .' '. date('h:i', $time) . ' <small>' . $am_pm.'</small>';
	//$numbers_ar = ["٠", "١", "٢", "٣", "٤", "٥", "٦", "٧", "٨", "٩"];
	// $numbers_en = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

	return str_replace($numbers_en, $numbers_ar, $date);
}
function getDateFromString($lang, $value)
{
	if ($lang == 'ar') {
		$reset = date_default_timezone_get();
		date_default_timezone_set('Asia/Damascus');
		$stamp = strtotime($value);
		date_default_timezone_set($reset);
		return  arabicDate($stamp);
	}
	return date("Y-m-d h:i a", strtotime($value));
}
