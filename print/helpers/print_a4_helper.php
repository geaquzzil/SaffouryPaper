<?php 
function setHeaderA4Label(&$invoicr){
    global $tableName,$isPDF;
    if(!$invoicr->get("headerAdded")){
     //PDF SUPPORT 
    if($isPDF){
        // $cssContent=file_get_contents('style-product-label-pdf.css');
        // $invoicr->data .= "<html><head>";
    
        // $invoicr->data .=  
        // "<link rel='stylesheet' href='https://fonts.googleapis.com/icon?family=Material+Icons'/>" .
        // "<link href='https://fonts.googleapis.com/css?family=Roboto' rel='stylesheet' type='text/css'/>" .
        // "<style > $cssContent</style>".
        // "</head><body>";
        $invoicr->data .= "<html><body>";
    
    }else{
        $invoicr->data .= "<html><head>";
        $invoicr->data .=    "<link  rel=\"stylesheet\" href='style-product-label.css' media='all'>" .
        "<link href='https://fonts.googleapis.com/css?family=Roboto' rel='stylesheet' type='text/css'>" .
        "<link rel='stylesheet' type='text/css' href='print-a4.css' media='print' />".
        "</head><body>";
    }
    
    $invoicr->set("headerAdded",true);
    }
    $invoicr->data.="<div id='invoice'>";
    
} 
function setHeaderA4(&$invoicr){
    global $tableName,$lang,$isPDF;
    if(!$invoicr->get("headerAdded")){
        if($lang=='ar'){
             $invoicr->data .= "<html dir='rtl' lang='ar'><head>" ;
        }else{
           $invoicr->data .= "<html><head>";  
        }
       
    $cssContent=file_get_contents('style-invoices.css');
    
    $patterns = array();
    $patterns[0] = '7BB744';
    
    $replacements = array();
    $replacements[0] = '7BB700';
    
    $cssContent=str_replace('7BB744', getReportColor($tableName), $cssContent);
    
    $invoicr->data .=  
    "<link rel='stylesheet' href='https://fonts.googleapis.com/icon?family=Material+Icons'/>" .
    "<link href='https://fonts.googleapis.com/css?family=Roboto' rel='stylesheet' type='text/css'/>" .
    ($isPDF?"": "<link rel='stylesheet' type='text/css' href='print-a4.css' media='print' />").
    
    "<style media='all' > $cssContent</style>".
    "</head><body>";
    $invoicr->set("headerAdded",true);
    }
    $invoicr->data.="<div id='invoice'>";
    
}   
function setBillsHelpers(&$invoicr,$list,$class){
    if(count($list)>0){
        $invoicr->data .="<td id='$class'>";
        foreach ($list as $key=>$b) {
            $spaces="";
            if($key!=count($invoicr->head)-1){
                $spaces="<br><br>";
            }else{
                $spaces="<br>";
            }
	       $invoicr->data .=
		    "<span class='material-icons md-dark md-inactive'>" . $b[0] . "</span>" .	$b[1] . "<br>" . "<strong >" . $b[2] . "</strong>$spaces";
        }
        $invoicr->data .="</td>";
    }
    
}
function setBillToHeader(&$invoicr){
    $invoicr->data .= "<table id='billship' cellpadding='10px' cellspacing='5px'><tr height:'50px'>";
    setBillsHelpers($invoicr,$invoicr->billto,'billInfoFirst');
    setBillsHelpers($invoicr,$invoicr->head,'billInfoCenter');
    setBillsHelpers($invoicr,$invoicr->invoiceTotals,'billInfoLast');
    $invoicr->data .= "</tr></table>";
}

function setTableHeader(&$invoicr){
    // (F) HEADER
    $invoicr->data .= "<table id='items' cellpadding='0px' cellspacing='0px' ><tr>";
    for($i=0; $i<count($invoicr->tableHeaders);$i++){
        $value=$invoicr->tableHeaders[$i];
        if($i==0){
       	    $invoicr->data .= "<th id='tableHeadFirst' colspan='2'>" . $value . "</th>" ;
        }else if($i==count($invoicr->tableHeaders)-1){
       	    $invoicr->data .= "<th id='tableHeadLast'>" . $value . "</th>" ;
        }else{
       	    $invoicr->data .= "<th id='tableHeadCenter'>" . $value . "</th>" ;
        }
    }
    $invoicr->data .="</tr>";
}
function setTableContent(&$invoicr){
    global $tableName;
    global $lang;
    $tableRows=getReportTableContent($tableName);
    $tableRowsCount=count($tableRows);
    $tableRowsCountMinusOne=count($tableRows)-2;
    foreach ($invoicr->items as $item) {
        $invoicr->data .="<tr>";
    for($i=0; $i<count($tableRows);$i++){
        $col=$tableRows[$i];
        $formatedValue=getReportTableContentFormat($lang,$tableName,$item,$col);
        
        if($i==0){
       	    $invoicr->data .=  "<td id='tableHeadFirst' colspan='2'>" . $formatedValue ."</td>";
        }else if($i==count($invoicr->tableHeaders)-1){
       	    $invoicr->data .= "<td id='tableHeadLast'>" . $formatedValue . "</td>" ;
        }else{
       	    $invoicr->data .= "<td id='tableHeadCenter'>" . $formatedValue . "</td>" ;
        }

    }
    $invoicr->data .="</tr>";
    }
}
function getAdditionalNotes($totalSpan){
    global $lang,$invoicr,$printOption;
    if($printOption->isHideGeneralInvoiceNotes())return null;
    $notes=$invoicr->masterInvoice['comments'];
    //$this->data .="<div id='thanks'>Thank you for your business!</div>";
    $width='100';
    $table="<table id='accountInfo' width='$width%' cellpadding='0px' cellspacing='0px'>";
    $table .="<tr> ";
    $table .="<th id='accountInfoTH' colspan='$totalSpan' >".getTransValue($lang,'importNote')."</th>";
    $table .="</tr> ";  
    if($printOption->isHasReportFooter()){
        $notes.=$printOption->getReportFooter();
    }
    if(!isEmptyString($notes)){
        $table .="<tr> ";
        $table .="<td id='accountInfoTD' colspan='$totalSpan'>".$notes."<br></td>";
        $table .="</tr> ";
    }
    
    $table .="<tr height='40px'> ";
    $table .="<td id='accountInfoTD' colspan='$totalSpan'>".getTransValue($lang,'thnaks')."<br></td>";
    $table .="</tr> ";
    $table .="<tr> ";
    $table .="<td id='accountInfoTD' colspan='$totalSpan'>".getTransValue($lang,'enquery')."</td>";
    $table .="</tr> ";
    $table .="</table> ";
    return $table; 
}
function getTermsAndConditionsTable(){
    global $lang,$printOption;
    if($printOption->isHideGeneralInvoiceTerms())return null;
    $width='100';
    $table="<table id='accountInfo' width='$width%' cellpadding='0px' cellspacing='0px'>";
    $table .="<tr> ";
    $table .="<th id='accountInfoTH' colspan='3' >".getTransValue($lang,'termsAndCon')."</th>";
    $table .="</tr> ";
    $table .="<tr height='60px'> ";
    $table .="<td id='accountInfoTD' colspan='3'>".getTransValue($lang,'importNoteDes')."<br></td>";
    $table .="</tr> ";
    $table .="<tr> ";
    $table .="<td id='accountInfoTD' colspan='3'>".getTransValue($lang,'importNoteDes2')."</td>";
    $table .="</tr> ";
    $table .="</table> ";
    return $table;
}
function getAccountInfoTable($invoicr,$colCount){
    global $isGeneratedQrCode,$lang,$printOption;
   // print_r($invoicr->masterInvoice);
    $width='100';
    $qrColSpan=getRowSpanForQR();
    $qrColSpan=$colCount==4?1:2;
    $qrLabel=getQRCodeLabel();
    $content=getAccountInfoTableContent($invoicr->masterInvoice);
    $qrContent= "<td id='idescQr' colspan='$qrColSpan' rowspan='2'><img  id='contentQrCode' alt='qrcode'  src='" . $invoicr->invoiceQrcode . "'/>$qrLabel</td>";
    $isGeneratedQrCode=true;
    $table="<table id='accountInfo' width='$width%' cellpadding='0px' cellspacing='0px'>";
    $table .="<tr> ";
    $table .="<th id='accountInfoTH' colspan='1' >".getTransValue($lang,'accountInfo')."</th>";
    $table .="</tr> ";
    $info='';
    $table .="<tr> ";
    foreach($content as $key=>$value){
        $info .=$value[0].": <b>".$value[1]."</b><br>";
    }
    $obj=$invoicr->masterInvoice;
    $isHideCargoInfo = $printOption->isHideCargoInfo();
    if (!$isHideCargoInfo && isset($obj[CARGO])) {
		$info .=getCargoInf($obj[CARGO]);
	}
    $table .="<td id='accountInfoTD' colspan='1'>".$info."</td>$qrContent";
    $table .="</tr> ";
    
    
    $table .="</table>";
    return $table;
}
function setAccountInformationOnBottomTotals(&$invoicr,$colCount,$minusCount,$totalsCount){
    $colSpan=$colCount-$minusCount+1;
    $totalsCount=$totalsCount+1;
    $tableAccountInfo=getAccountInfoTable($invoicr,$colCount);
    $invoicr->data .="<td id='ttlLastEmpty' colspan='$colSpan' rowspan='$totalsCount'>$tableAccountInfo</td>";
}
function setTableTotals(&$invoicr){
    global $tableName;
    $colCount=count(getReportTableContent($tableName));
    $minusCount=($colCount==4)?2:3;
    $isAccountInfoRowSet=false;
    $tableRowsCountMinusOne=count(getReportTableContent($tableName))-$minusCount;
    if (count($invoicr->totals) > 0) {
            for($i=0; $i<count($invoicr->totals);$i++){
                $invoicr->data.="<tr>";
                if(!$isAccountInfoRowSet){
                    setAccountInformationOnBottomTotals($invoicr,$colCount,$minusCount,count($invoicr->totals));
                    $isAccountInfoRowSet=true;
                }
            $t=$invoicr->totals[$i];
            if($i==count($invoicr->totals)-1){
                $colspan=$colCount==4?1:2;
                $invoicr->data .= "<td id='ttlLastLastLeft' colspan='$colspan'>$t[0]</td><td id='ttlLastLastRight'>$t[1]</td>";
             
            }else{
                $colspan=$colCount==4?1:2;
		        $invoicr->data .= "<td id='ttlLastLeft' colspan='$colspan'>$t[0]</td><td id='ttlLastRight'>$t[1]</td>";
            }
            
            $invoicr->data .="</tr>";
        }
    }
    if(!is_null($invoicr->invoiceTotalsInWords)){
        $invoicr->data.="<tr>";
        $des=$invoicr->invoiceTotalsInWords;
        $colspan=$colCount==4?2:3;
        $invoicr->data .= "<td id='tableInvoiceTotalInWords' colspan='$colspan'>$des</td>";
        $invoicr->data .="</tr>";
    }
    
    
    $colSpan=$colCount-$minusCount+1;
    $termsAndCon=getTermsAndConditionsTable();
    if(!is_null($termsAndCon)){
        $invoicr->data .="<tr><td colspan='$colSpan' id='ttlLastEmpty'>$termsAndCon</td></tr>";
    }
    $notes=getAdditionalNotes($colCount);
    if(!is_null($notes)){
        $invoicr->data .="<tr><td colspan='$colSpan' id='ttlLastEmpty'>$notes</td></tr>";
    }
    
    $invoicr->data .= "</table>";
    
    
}
function getNewLineA4($headerDescription,$colSpan){
    $ht="<tr height='95px'>";
    $dir=getDir();
    for($i=0;$i<count($headerDescription);$i++){
        $hd=$headerDescription[$i];
        $col=$colSpan[$i][0]== 0 ? 1 :$colSpan[$i][0];
        $row=$colSpan[$i][1] == 0 ? 1 :$colSpan[$i][1] ;
        
        $label=$hd[0];
        $des=$hd[1];
        $td="<td id='tableHeadFirst' colspan='$col' rowspan='$row'>$label".
            "<div class='idesc' dir='$dir'>$des</div></td>";
        $ht.=$td;
    }
    $ht.="</tr>";
    return $ht;
    
}

?>