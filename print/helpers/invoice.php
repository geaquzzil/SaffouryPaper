<?php 
function getInvoiceInfo($lang, $tableName, $obj)
{
	$timezone = 'America/New_York';
	global $printOption;
	$isInvoiceWithTerms = $printOption->isInvoiceWithTerms();
	switch ($tableName) {
		case ORDR:
		case PURCH:
		case PR_OUTPUT:
		case PR_INPUT:
		case TR:
		case RI:
		case ORDR_R:
		case PURCH_R:
			if ($isInvoiceWithTerms && !$printOption->hideTerms) {
				return [
					["info_outline", getTransValue($lang, "invoiceNum"), getIDFormat($tableName, $obj)],
					["schedule", getTransValue($lang, "dop"),  getDateFromString($lang, $obj["date"])],
					["date_range", getTransValue($lang, "dueDate"), getDateFromString($lang, $obj["termsDate"])]
				];
			} else {
				return [
					["info_outline", getTransValue($lang, "invoiceNum"), getIDFormat($tableName, $obj)],
					["schedule", getTransValue($lang, "dop"),  getDateFromString($lang, $obj["date"])]
				];
			}
	}
}
function getDetailsItems($tableName,$result){
    switch($tableName){
        case ORDR_R:
        case PURCH_R:
        case ORDR:
        case PURCH:
        case PR_OUTPUT:
        case PR_INPUT:
        case TR:
        case RI:
            return $result[getDepthSearchDetails($tableName)[0]];
        case PR:case CUT:case DEBT:case CRED:case SP:case INC:
            //todo check or set option from printOption even label or list;;;
            if(count($result)==1){
                return $result[0];
            }
            return $result;
            
            
            //return json_decode(json_encode($result), true);
    }
}
function getTotalQuantity($tableName, $obj)
{
	switch ($tableName) {
		case ORDR:
		case PURCH:
		case PR_OUTPUT:
		case PR_INPUT:
		case TR:
		case RI:
			return ($obj['quantity']);
		case ORDR_R:
		case PURCH_R:
			return ($obj['refundQuantity']);
	}
	return "";
}
function getExtendedPrice($obj)
{
	if (isOrderOrPurchasesRefund()) {
		return getCurrencyFormat($obj["extendedRefundPrice"]);
	} else {
		return getCurrencyFormat($obj["extendedPrice"]);
	}
}
function getTotalForHeader($lang, $tableName, $obj)
{
	if (isHideUnitAndPrice()) {
		return getTotalQuantity($tableName, $obj);
	} else {
		if (isOrderOrPurchasesRefund()) {
			return getExtendedPrice($obj);
		} else {
			$subTotal = getNumberFormat($obj["extendedPrice"]);
			$discount = getNumberFormat($obj["extendedDiscount"]);
			$grandTotal = getNumberFormat($obj["extendedPrice"] - $obj["extendedDiscount"]);
			return $grandTotal;
		}
	}
}
function getInvoiceTotalsInWordSymbol(){
    if(isHideUnitAndPrice()){
        return 'KG';
    }else{
        return getCurrency();
    }
}
function getTotalForInvoiceInWords($lang,$tableName,$obj){
    if (isHideUnitAndPrice()) {
		return getTotalQuantity($tableName, $obj);
		 
	} else {
		if (isOrderOrPurchasesRefund()) {
		    return getExtendedPrice($obj);
		} else {
			$subTotal = getCurrencyFormat($obj["extendedPrice"]);
			$discount = getCurrencyFormat($obj["extendedDiscount"]);
			$grandTotal = ($obj["extendedPrice"] - $obj["extendedDiscount"]);
			return getNumberFromPrintOption($grandTotal);
		}
	}
}
function getInvoiceTotalsInWords($lang,$tableName,$obj){
    switch($tableName){
    	case ORDR_R:
		case PURCH_R:
		case ORDR:
		case PURCH:
		case PR_OUTPUT:
		case PR_INPUT:
		case TR:
		case RI: 
		    $isHidePrices=isHideUnitAndPrice();
		    $invoiceTotal='';
		    if(!$isHidePrices){
		        $quantity = getTotalQuantity($tableName, $obj);
		        $invoiceTotal .="<small><b>".getTransValue($lang,'totalQuantityLabel'). "  $quantity </b></small><br><br>";
		        $invoiceTotal .="<small>".getTransValue($lang,'invoiceTotal')."</small>";
		        $invoiceTotal .="<br><br>";
		        $invoiceTotal .="<div id ='ttlLastLastLeft'>".getNumberToWords($lang,getTotalForInvoiceInWords($lang,$tableName,$obj),getInvoiceTotalsInWordSymbol())."</div>";
		        return $invoiceTotal ;
		    }		
		    $invoiceTotal="<small>".getTransValue($lang,'invoiceTotal')."</small>";
		    $invoiceTotal .="<br><br>";
		    $invoiceTotal .="<div id ='ttlLastLastLeft'>".getNumberToWords($lang,getTotalForInvoiceInWords($lang,$tableName,$obj),getInvoiceTotalsInWordSymbol())."</div>";
		    return $invoiceTotal ;
		default:return null;
    }
}
function getAccountInfoTableContent($obj){
    global $tableName,$lang;
    switch($tableName){
    	case ORDR_R:
    	    return [[getTransValue($lang,'name'),$obj[ORDR][CUST]['name']],[getTransValue($lang,'num'),$obj[ORDR][CUST]['iD']."IDE"]];
		case PURCH_R:
		    //if(isset($obj[ORDR][CUST]['name'])){
		        return [[getTransValue($lang,'name'),$obj[PURCH][CUST]['name']],[getTransValue($lang,'num'),$obj[PURCH][CUST]['iD']."IDE"]];
		   // }
		    //return null;
		    
		case ORDR:
		    return [[getTransValue($lang,'name'),$obj[CUST]['name']],[getTransValue($lang,'num'),$obj[CUST]['iD']."IDE"]];
		case PURCH:
		   // if(isset($obj[CUST]['name'])){
		        return [[getTransValue($lang,'name'),$obj[CUST]['name']],[getTransValue($lang,'num'),$obj[CUST]['iD']."IDE"]];
		  //  }
		  //  return null;
		case PR_OUTPUT:
		case PR_INPUT:
		  //  if(isset($obj[WARE]['name'])){
		        return [[getTransValue($lang,'name'),$obj[WARE]['name']],[getTransValue($lang,'num'),$obj[WARE]['iD']."IDE"]];
		  //  }
		  //  return null;
		    
		case TR:
		     return [[getTransValue($lang,'name'),$obj['toWarehouse']['name']],[getTransValue($lang,'num'),$obj['toWarehouse']['iD']."IDE"]];
		case RI: 
		  //  if(isset($obj[CUST]['name'])){
		        return [[getTransValue($lang,'name'),$obj[CUST]['name']],[getTransValue($lang,'num'),$obj[CUST]['iD']."IDE"]];
		   // }
		   // return null;
		default:return null;
    }
}
function getTotal($lang, $tableName, $obj)
{
	if (isHideUnitAndPrice()) {
		$quantity = getTotalQuantity($tableName, $obj);
		return [
			[getUpperString(getTransValue($lang, "grandTotal")), getCurrencyFormat($quantity)]
		];
	} else {
		if (isOrderOrPurchasesRefund()) {
			return [
				[getUpperString(getTransValue($lang, "grandTotal")), getCurrencyFormat(getExtendedPrice($obj))]
			];
		} else {
			$subTotal = getCurrencyFormat($obj["extendedPrice"]);
			$discount = getCurrencyFormat($obj["extendedDiscount"]);
			$grandTotal = ($obj["extendedPrice"] - $obj["extendedDiscount"]);
			return [
				[getUpperString(getTransValue($lang, "subTotal")), $subTotal],
				[getUpperString(getTransValue($lang, "discount")), $discount],
				[getUpperString(getTransValue($lang, "grandTotal")), getCurrencyFormat($grandTotal)]
			];
		}
	}
}
function getCargoInf($cargo)
{
    global $lang;
	$name = $cargo['name'];
	$carNumber = $cargo['carNumber'];
	$carGovernorate = $cargo[GOV];
	if ($carGovernorate) {
		return sprintf("%s : <b>%s</b><br>%s : <b>%s</b>", getTransValue($lang,CARGO),$name, getTransValue($lang,'carInfo'), $carNumber." ".$carGovernorate['name']);
	} else {
	return sprintf("%s : <b>%s</b><br>%s : <b>%s</b>", getTransValue($lang,CARGO),$name, getTransValue($lang,'carInfo'), $carNumber);
	}
}
function getInvoiceBottomTotals($lang, $tableName, $obj)
{
	$array = array();
	switch ($tableName) {
		case ORDR_R:
		case PURCH_R:
		case ORDR:
		case PURCH:
		case PR_OUTPUT:
		case PR_INPUT:
		case TR:
		case RI:
			$array = getTotal($lang, $tableName, $obj);
			break;
	}
	
	return $array;
}
function getInvoiceGetBillTo($lang, $tableName, $obj)
{
	global $printOption;
	$isHideAddressAndPhone = $printOption->isHideAddressAndPhone();
	$array = array();
	switch ($tableName) {
		case ORDR:
		case PURCH:
		case RI:
		case ORDR_R:
		case PURCH_R:
			if ($tableName == ORDR_R) {
				$obj = $obj[ORDR];
			}
			if ($tableName == PURCH_R) {
				$obj = $obj[PURCH];
			}
			$city = $obj[CUST]["city"];
			$address = $obj[CUST]["address"];
			$phone = $obj[CUST]['phone'];
			$name = $obj[CUST]["name"];
			$array = [["account_circle", getTransValue($lang, "mr"), $name]];
			if (!$isHideAddressAndPhone) {
				if (isset($phone)) {
					$array[] = ["phone", getTransValue($lang, "phone"), $phone];
				}
				if (isset($city) || isset($address)) {
					$array[] =  ["map",  getTransValue($lang, "address"), ($city == '' ? '' : $city . " ") . ($address == '' ? '' : $address)];
				}
			}
			break;

		case PR_INPUT:
		case PR_OUTPUT:
			$name = $obj[WARE]["name"];
			$array = [["warehouse", getTransValue($lang, "warehouse"), $name]];
			break;
		case TR:
			$from = $obj["fromWarehouse"]["name"];
			$to = $obj["toWarehouse"]["name"];
			$array = [
				["north_east", getTransValue($lang, "from"), $from],
				["north_west", getTransValue($lang, "to"), $to]

			];
			break;
	}
	return $array;
}
function getReportTableContent($tableName)
{
	$array = array();
	switch ($tableName) {
		case ORDR:
		case PURCH:
		case ORDR_R:
		case PURCH_R:
			$array = ['des', 'gsms', 'warehouse', 'quantity', 'unitPrice', 'discount', 'price'];
			return  getOptionalTableHeaderArray($array);
		case PR_OUTPUT:
		case PR_INPUT:
		case TR:
		case RI:
			return ['des',  'gsms', 'quantity'];
		case PR:
			return ['des',  'gsms', 'warehouse', 'quantity'];
		case CUT:
			return [CUST, ID, PR, QUA, SIZE_CUT, 'quantity','status', 'waste',CUT_RESULT,'comments' ];
		case CRED:case DEBT:
		  //  echo "DSA";
		    return [ID,'date',CUST,'amount','amountWords','comments'];
		case INC:case SP:
		    return [ID,'date',AC_NAME,'amount','amountWords','comments'];
	}

	return $array;
}

function getReportTableHeaders($lang, $tableName)
{
	$array = getReportTableContent($tableName);
	return $array =array_map(function ($item) {
	    global $lang;
		return strtoupper(getTransValue($lang, $item));
	}, $array);
}
function getInvoiceHeadTotals($lang, $tableName, $obj)
{
	$array = array();
	global $printOption;
	$isInvoiceWithTerms = $printOption->isInvoiceWithTerms() && !$printOption->hideTerms;

	switch ($tableName) {
		case ORDR:
		case PURCH:
		case ORDR_R:
		case PURCH_R:
		case PR_OUTPUT:
		case PR_INPUT:
		case TR:
		case RI:
			$array = [["style", getTransValue($lang, "total"), getCurrencyFormat(getTotalForHeader($lang, $tableName, $obj))]];

			if (!isHideBalance()) {
				$array[] = ["account_balance", getTransValue($lang, "balance"), getCurrencyFormat($obj[CUST]["balance"])];
			}
			if ($isInvoiceWithTerms) {
				$array[] = ["credit_card", getTransValue($lang, "TermsID"), getPaymenTermsText($obj['TermsID'])];
			}
	}
	return $array;
}
function getPaymenTermsText($TermsID){
   // echo $TermsID ;
    global $lang;
    $terms=[
         'en' => ['Payment in advance','Payment in the end of the week after invoice date','Payment seven days after invoice date','Payment ten days after invoice date',
                  'Payment ten days after invoice date','Payment 30 days after invoice date','End of month','Cash on delivery','Payment of agreed amounts at stage'
                ],
         'ar' => ['الدفع مقدما',
                'الدفع في نهاية الأسبوع الذي يلي تاريخ الفاتورة',
                'الدفع بعد سبعة أيام من تاريخ الفاتورة',
                'الدفع بعد عشرة أيام من تاريخ الفاتورة',
                'الدفع بعد 30 يوما من تاريخ الفاتورة',
                'نهاية الشهر',
                'الدفع عند الاستلام',
                'دفع المبالغ المتفق عليها في مراحل'
                ]
        ];
       $terms= $terms[$lang];
        switch($TermsID){
            case -1:return $terms[0];
            case -7:return $terms[1];
            case 7:return $terms[2];
            case 10:return $terms[3];
            case 30:return $terms[4];
            case -30:return $terms[5];
            case 1:return $terms[6];
            case 60:return $terms[7];
        }
}
function changeObjIfRefund($tableName, &$obj)
{
	if ($tableName == ORDR_R) {
		$obj = $obj[ORDR];
	}
	if ($tableName == PURCH_R) {
		$obj = $obj[PURCH];
	}
}
function getInvoiceNotes($lang, $tableName, $obj)
{
	$notes = $obj['comments'];
	$array = array();

	if ($notes == '') {
		$array = [getSmall(getTransValue($lang, 'importNoteDes')), getSmall(getTransValue($lang, 'importNoteDes2'))];
	} else {
		$array =  [$notes, getSmall(getTransValue($lang, 'importNoteDes')), getSmall(getTransValue($lang, 'importNoteDes2'))];
	}
	if (isHasReportFooter()) {
		array_unshift($array, getReportFooter());
	}
	return $array;
}
function getProductTypeForInvoice($obj){
    global $printOption;
    $product=$obj[PR];
    $type=$product[TYPE]["name"];
    
    switch($printOption->invoiceProductTypeOptions){
        case 0:
            return $type;
        case 1:
            $isRollCut=  $product['products'] != null;
            if($isRollCut){
                return $printOption->invoiceProductTypeOptionsName;
            }else{
                return $type;
            }
        case 2:
            return $printOption->invoiceProductTypeOptionsName;
            
    }
    return $type;
}
function getReportTableContentFormat($lang, $tableName, $obj, $column)
{
 
    $dir=getDir();
	switch ($tableName) {
		case ORDR_R:
		case PURCH_R:
		case ORDR:
		case PURCH:
		case PR_OUTPUT:
		case PR_INPUT:
		case TR:
		case RI:
			if ($column == 'des') {
			    $type=getProductTypeForInvoice($obj);
			    
			    $content="<div>".$type."</div>";
			    $comments = $obj["comments"];
			    $des="<big>" . getSize($obj[PR]) . "</big>" . ($comments == '' ? '' : " <small> $comments </small>");
			    $content.="<small class='idesc' dir='$dir' >$des</small>";
				return $content;
				
			}
			if ($column == 'warehouse') {
				return getWarehouse($obj);
			}
			if ($column == 'gsms') {

				return getGSM($obj[PR]);
			}
			if ($column == 'quantity') {
				return getCurrencyFormat($obj["quantity"]) . "<br><small>(" . getProductUnit($lang, $obj[PR]) . ")</small>";
			}
			if ($column == 'unitPrice') {

				return getCurrencyFormat(getNumberFromPrintOption($obj["unitPrice"]),4);
			}
			if ($column == 'discount') {
				return getCurrencyFormat(getNumberFromPrintOption($obj["discount"]));
			}
			if ($column == 'price') {
				return getCurrencyFormat(getNumberFromPrintOption($obj["price"]));
			}
			break;
		case PR:
			if ($column == 'des') {
				return $obj[TYPE]["name"];
			}
			if ($column == 'desText') {
				$comments = $obj["comments"];

				return "<big>" . getSize($obj) . "</big>" . ($comments == '' ? '' : " <small> $comments </small>");
			}
			if ($column == 'warehouse') {
				return getWarehouse($obj);
			}
			if ($column == 'gsms') {

				return getGSM($obj);
			}
			if ($column == 'quantity') {

				return getNumberFormat(123213) . "<br><small>(" . getProductUnit($lang, $obj) . ")</small>";
			}
	}
}
?>