<?php 

function getProductSheet($pro)
{
	if (isRoll($pro)) return "-";
	$quantity = getProductQuantityInStock($pro, null);
	if(isReams($pro))return getSheetCountByReams($pro,$quantity);
	$weightBySheets = getWeightBySheets($pro, 1);
	if ($quantity == 0 || $weightBySheets == 0) return "-";
	return (int) ($quantity / $weightBySheets);
}
function isReams($pro){
    //echo "   ".($pro[TYPE]['unit']!='KG')? "REAM":"KG";
    return $pro[TYPE]['unit']!='KG';
}
function getProductQuality($product)
{
	return getIfSet($product, QUA, 'name');
}
function getProductType($product)
{
	return getIfSet($product, TYPE, 'name');
}
function getProductUnit($lang, $product)
{
	$Type = $product[TYPE];
	return getTransValue($lang, $Type["unit"]);
}
function getGSM($product)
{
	return $product[GSM]["gsm"];
}
function getSize($product)
{
	$size = $product[SIZE];
	$length = $size['length'];
	$width = $size['width'];
	if ($size['length'] == 0) {
		return sprintf("<b> %s </b>", $width);
	}
	if (isset($product['fiberLines'])) {
		switch ($product['fiberLines']) {
			case "None":
				return sprintf("<big><b> %s </b></big>", $width);
			case "Length":
				return sprintf("%s X <big><b> %s </b></big>", $width, $length);
				// return $size["width"] . " X " . "<b>" . $size["length"] . "</b>";
			case "Width":
				return sprintf(" <big><b> %s </b></big> X %s", $width, $length);
				//  return "<b>" . $size["width"] . "</b> X " . $size["length"];
		}
	}else{
	    return sprintf("%s X <big><b> %s </b></big>", $width, $length);
	}
}
function getProductQuantityInStock($obj, $warehouse)
{
	if (isset($obj['inStock'])) {
		//   print_r($obj['inStock']);
		$inStock = $obj['inStock'];
		$quantity = 0;
		foreach ($inStock as $in) {
			if (is_null($warehouse)) {
				$quantity = $quantity + $in['quantity'];
			} else {
				if ($warehouse['iD'] == ($in['warehouse']['iD'])) {
					return getNumberFormat($in['quantity']);
				}
			}
		}
		return getNumberFormat($quantity);
	}
	return "-";
}
function getReams($pro){
    if($pro[TYPE]['unit']=="KG"){
        return "-";
    }
    return getNumberFormatZero(getProductQuantityInStock($pro,null));
}
function getSheetsPerReam($pro){
    if($pro[TYPE]['unit']=="KG"){
        return "-";
    }
   if($pro['sheets']==0){
        return "-";
    }
    return $pro['sheets'];
}
function getProductCustomerCutRequest($pro){
    if(isset($pro[CUST])){
         $qrCode = new QRCodeID;
         $qrCodeContent=$qrCode->getQrCode(CUST, $pro[CUST]);
        return getImgTagSmall("../qr.php?text='$qrCodeContent'")."<br>".getSmall($pro[CUST]['name']);
    }
    
    return "-";
}
function getProductCutRequest($pro){
    if(isset($pro[CUT]) && is_object($pro[CUT])){
         $qrCode = new QRCodeID;
         $qrCodeContent=$qrCode->getQrCode(CUT, $pro[CUT]);
        return getImgTagSmall("../qr.php?text='$qrCodeContent'")."<br>".getSmall(getIfSet($pro[CUT],CUST,'name'));
    }
    
    return "-";
}
function getCustomsProductQR($pro){
    $customs=getIfSet($pro,CUSTOMS,ID);
    if($customs!='-'){
        $qrCode = new QRCodeID;
	    return getImgTag("../qr.php?text=".$qrCode->getQrCode(CUSTOMS, $customs));
    }
    return "-";
}
function isRoll($pro)
{
   // echo "  ".($pro[SIZE]['length'] == 0)?"YES" : "NO";
	return $pro[SIZE]['length'] == 0;
}
function hasGSM($pro)
{
	if (!isset($pro[GSM])) return false;
	if (is_null($pro[GSM])) return false;
	return $pro[GSM]["gsm"] != 0;
}

 function getSheetCountByReams($pro,$reams) {
        if ($reams == 0) return 0;
        
        $sheets=$pro['sheets'];
        if($sheets==0){
            return "dsadas";
        }
        return getNumberFormat($sheets * $reams);
    }
function getWeightBySheets($pro, $sheet)
{
	if ($sheet == 0) return 0;
	if (!hasGSM($pro)) return 0;
	$gsm = $pro[GSM]["gsm"];
	$width = $pro[SIZE]["width"];
	$length = $pro[SIZE]["length"];
	return $sheet * ((($length / 100) * ($width / 100) * $gsm) / 100000);
}
function getGrain($pro)
{
	if (isRoll($pro)) {
		return "-";
	}
	$size = $pro[SIZE];
	$length = $size['length'];
	$width = $size['width'];
	switch ($pro['fiberLines']) {
		case "None":
			return $width;
		case "Length":
			return $length;
			// return $size["width"] . " X " . "<b>" . $size["length"] . "</b>";
		case "Width":
			return $width;
			//  return "<b>" . $size["width"] . "</b> X " . $size["length"];
	}
}
function getProductNotes($pro){
    if(isset($pro['comments'])){
        return $pro['comments'];
    }
    return "-";
}
function getReportTableContentFormatProductLabel($lang,$obj,$column){
    
    switch($column){
        case ID:return getIDFormat(PR,$obj);
        case MAN:
        case COUNTRY:
        case TYPE:
        case GD:
        case QUA:
            return getIfSet($obj,$column,"name"); 
        case GSM:return getIfSet($obj,GSM,"gsm"); 
        case SIZE:return getSize($obj); 
        case 'quantity':return getProductQuantityInStock($obj,null).getSmall(" ".getProductUnit($lang,$obj));
        case 'sheets':return getProductSheet($obj);
        case 'ream':return getReams($obj);
        case 'sheetsPerReam':return getSheetsPerReam($obj);
        case CUST:
        case 'customer':return getProductCustomerCutRequest($obj);
        case 'grain':return getGrain($obj);
        case 'cut': return getProductCutRequest($obj);
        case 'OneSheet':return (getWeightBySheets($obj,1)*1000). " ".getSmall(getTransValue($lang,'g'));
        case CUSTOMS:
            return getCustomsProductQR($obj);
            
    
    }
    
    return $column;
}
