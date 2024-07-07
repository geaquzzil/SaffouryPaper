<?php
define('TABLE_BACKGROUND', 'logo/logoSVG.php?color=999&stroke=');
function setHeaderA5(&$invoicr){
    global $tableName;
    if(!$invoicr->get("headerAdded")){
        $invoicr->data .= "<html><head>";
    $cssContent=file_get_contents('style-cut.css');
   // echo $cssContent;
    
    $patterns = array();
    $patterns[0] = '7BB744';
    
    $replacements = array();
    $replacements[0] = '7BB700';
    
    $cssContent=str_replace('7BB744', getReportColor($tableName), $cssContent);
    
    $invoicr->data .=  
    "<link rel='stylesheet' href='https://fonts.googleapis.com/icon?family=Material+Icons'/>" .
    "<link href='https://fonts.googleapis.com/css?family=Roboto' rel='stylesheet' type='text/css'/>" .
    "<link rel='stylesheet' type='text/css' href='print-a5.css' media='print' />".
    "<style media='all' > $cssContent</style>".
    "</head><body>";
    $invoicr->set("headerAdded",true);
    }
    $invoicr->data.="<div id='invoice'>";
    
}
function setTable(&$invoicr)
{
	global $tableName;
	global $lang;

	$background = getReportBackground('F0F0F0');
	$invoicr->data .= "<table cellpadding='10px' cellspacing='5px' id='items' style=
        'background-image: url($background);
        background-position: center;background-repeat:no-repeat;
        background-position: center top;
        background-attachment: inherit;' ".($lang=='ar'?"dir='rtl' lang='ar'>":">");

	$obj = $invoicr->items;
	$requestedCols = getReportTableContent($tableName);
	$count = 0;
	$content = '';

	foreach ($requestedCols as $row) {
		$rowColSpan = getColRowSpan($row);
		$col = $rowColSpan[0];
		$label = getUpperString(getTransValue($lang, $row));
		$contents = getReportTableContentA5($lang, $obj, $row);
		if(is_null($content))continue;
		if ($col > 1) {
			$content = getNewLineOneRow([$label, $contents], $invoicr->invoiceQrcode);
			$invoicr->data .= $content;
		} else {
			if ($count == 0) {
				$content = "<tr>";
			}
			$count++;

			$content .= getNewLine(
				[$label, $contents],
				$rowColSpan
			);

			if ($count == 2) {
				$count = 0;
				$content .= "</tr>";
				$invoicr->data .= $content;
			}
		}
	}
	setTotals($invoicr);
	$invoicr->data .= "</table>";
}

function setTotals(&$invoicr){
    $bottomTotals=getInvoiceBottomTotalsA5($invoicr->items);
    if(count($bottomTotals)==0)return;
    foreach($bottomTotals as $hd){
        $invoicr->data .=getLastLine($hd);
    }
}
function getFundsBottomTotals($obj){
    global $printOption;
    global $lang;
	$showCargoInfo = !$printOption->isHideCargoInfo();
	$isHidePaymentAmount=$printOption->isHidePaymentAmount();
	$isHideBalanceDue=$printOption->isHideBalanceDue();
	$isHideEmployee=$printOption->isHideEmployee();
	$array=array();
	if(!$isHidePaymentAmount){
	    $array[]=[getUpperString(getTransValue($lang,'payAmount')),getNumberFormat(getPayemntAmount($obj))];
	}
	if(!$isHideBalanceDue){
	    $array[]= [getUpperString(getTransValue($lang, "balance")), getNumberFormat($obj[CUST]["balance"])];
	}
	if(!$isHideEmployee){
	    $array[]= [getUpperString(getTransValue($lang, EMP)), ($obj[EMP]['name'])];
	}
	return $array;
	
}
function getInvoiceBottomTotalsA5($obj)
{
    global $tableName;
	$array = array();
	switch ($tableName) {
	    default:break;
		case CRED:
		case DEBT:
		case SP:
		case INC:
			$array = getFundsBottomTotals( $obj);
			break;
	}
	return $array;
}
function getColRowSpan($row){
    global $tableName;
    switch($tableName){
        case CUT:
            return getColRowSpanCut($row);
        case CRED:case DEBT:case SP:case INC:
            return getColRowSpanCreditAndDebit($row);
    }
    
}
function getColRowSpanCreditAndDebit($row){
    switch($row){
        case ID:
        case 'date':
            return [1,1];
        case CUST:
        case AC_NAME:
        case 'amount':
        case 'amountWords':
        case 'comments':
            return [3,1];
    }
}
function getColRowSpanCut($row){
    switch($row){
        case PR:
        case QUA:
            return [1,2];
        case ID:
        case 'quantity':
        case 'waste':
        case CUST:
            return [1,1];
        case 'comments':
        case CUT_RESULT:
            return [3,1];
            case 'qr':
                return [2,2];
    }
}
function getReportTableContentA5($lang,$obj,$column){
    global $tableName;
    switch($tableName){
        case CUT:
            return getReportTableContentFormatCut($lang,$obj,$column);
        case CRED:case DEBT:case SP:case INC:
            return getReportTableContentFormatCreditDebit($lang,$obj,$column);
           // case 
    }
}
function getReportTableContentFormatCreditDebit($lang,$obj,$column){
     global $tableName;
    switch($column){
        default :return  $column;
        case ID:return getIDFormat($tableName,$obj);
        case 'date':return getDateFromString($lang, $obj["date"]);
        case CUST:return getIfSet($obj,CUST,"name");
        case 'amount':return getNumberFormat($obj['value']);
        case 'amountWords':return getNumberToWords($lang,$obj['value'],getCurrency());
        case 'comments':return getCommentsPayment($lang,$obj);
        case AC_NAME:return getIfSet($obj,AC_NAME,"name");
    }
}
function getReportTableContentFormatCut($lang,$obj,$column){
    
    switch($column){
        default :return  $column;
        case ID:return getIDFormat(CUT,$obj)."<br>".getSmall(getDateFromString($lang, $obj["date"]));
        case CUST:return getIfSet($obj,CUST,"name");
        case PR:return getIDFormat(PR,$obj[PR])."<br>".getProductType($obj[PR]).getBig(getSize($obj[PR])).getSmall(" X ". getGSM($obj[PR]));
        case QUA:return getProductQuality($obj[PR]);
        case SIZE_CUT:return getCutRequestSizes($obj);
        case 'quantity':return getNumberFormat($obj['quantity'])." (".getSmall(getProductUnit($lang, $obj[PR])).")";
        case 'status':
            $status=$obj['cut_status'];
            $color=getStatusColor($status);
            $status=getUpperString(getTransValue($lang,$status));
            return "<p style='color:$color;'>$status</p>";
        case 'comments':return getComments($lang,$obj);
        case 'waste':return getNumberFormat(getWaste($obj))." (".getSmall(getProductUnit($lang, $obj[PR])).")";
        case CUT_RESULT:return getCutResult($lang,$obj);
    }
    
    return "TEST";
}
function getNewLine($headerDescription,$colSpan){
    $ht="";
    $label=$headerDescription[0];
    $des=$headerDescription[1];
    $td="<td id='tableHeadFirst' >$label</td>"."<th class='idesc' colspan='$col' rowspan='$row'>$des</th>";
    $ht.=$td;
    return $ht;
}
function getLastLine($headerDescription){
    $ht="<tr>";
    $label=$headerDescription[0];
    $des=$headerDescription[1];
    $emptyTd="<td id='ttlLastEmpty'></td><th id='ttlLastEmpty'></th>";
    $td=$emptyTd."<td id='tableHeadFirst' >$label</td>"."<th class='idescTotals' colspan='$col' rowspan='$row'>$des</th>";
    $ht.=$td;
    $ht.="</tr>";
    return $ht;
}

function getNewLineOneRow($headerDescription,$qr){
    global $isGeneratedQrCode;
    $ht="<tr>";
    $label=$headerDescription[0];
    $des=$headerDescription[1];
    if($isGeneratedQrCode){
            
            $ht.="<td id='tableHeadFirst' >$label</td>".
            "<th class='idesc' colspan='2' rowspan='1'>$des</th>";
            $ht.="</tr>";
    }else{
        $qrRowSpan=getRowSpanForQR();
        $qrLabel=getQRCodeLabel();
         $ht.="<td id='tableHeadFirst' >$label</td>".
            "<th class='idesc' colspan='2' rowspan='1'>$des</th>".
            "<th class='idescQr' colspan='1' rowspan='$qrRowSpan'><img  id='contentQrCode' alt='qrcode'  src='" . $qr . "'/>$qrLabel</th>"
            ;
            $isGeneratedQrCode=true;
            $ht.="</tr>";
    }

    return $ht;
    
}
