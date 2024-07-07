<?php
global $isPDF;
// (A) HTML HEADER & STYLES
setHeaderA4Label($this);
if(!is_null($this->headerSVG)){
    $this->data .="<div id='header'><img   src='" . $this->headerSVG . "'/></div>";
}
$this->data .= "<div id='bigi'>" . $this->invoiceTitle . "</div>";
$background=getReportBackground("F0F0F0");
if($isPDF){
    $this->data .= "<table cellpadding='0' cellspacing='0' id='items' style='background-image: url($background);background-repeat: round;background-size: contain;width:100%; height:200mm;' >";
}else{
$this->data .= "<table cellpadding='0' cellspacing='0' id='items' style='background-image: url($background);background-repeat: round;background-size: contain;' >";
}
global $tableName;
global $lang;
$obj=$this->items;
$this->data.=getNewLineA4([
    [getTransValue($lang,'des'),
    getReportTableContentFormatProductLabel($lang,$obj,TYPE)],
    
    [getTransValue($lang,'gradeLabel'),
    getReportTableContentFormatProductLabel($lang,$obj,GD)],
    
    [getTransValue($lang,'qualityLabel'),
    getReportTableContentFormatProductLabel($lang,$obj,QUA)]],
    [[3,0],[1,0],[1,0]]);

$this->data.=getNewLineA4([
    [getTransValue($lang,'sizeLabel'),
    getReportTableContentFormatProductLabel($lang,$obj,SIZE)],
    
    [getTransValue($lang,'gsmLabel'),
    getReportTableContentFormatProductLabel($lang,$obj,GSM)],
    
    [getTransValue($lang,'grainLabel'),
    getReportTableContentFormatProductLabel($lang,$obj,"grain")]],
     [[3,0],[1,0],[1,0]]);


$this->data.=getNewLineA4([
    [getTransValue($lang,'quantityLabel'),
    getReportTableContentFormatProductLabel($lang,$obj,'quantity')],
    
    [getTransValue($lang,'OneSheetLabel'),
    getReportTableContentFormatProductLabel($lang,$obj,'OneSheet')],
    
    
    ["","<img  align='center' style='width:150px;height:150px;' src='" . $this->invoiceQrcode . "'/><br>".getSmall(getIDFormat($tableName,$obj))]],
    
    
    
    [[2,0],[1,0],[2,2]]);
    
    
$this->data.=getNewLineA4([
    [getTransValue($lang,'sheetsLabel'),
    getReportTableContentFormatProductLabel($lang,$obj,'sheets')],
    
    [getTransValue($lang,'sheetsPerReamLabel'),
    getReportTableContentFormatProductLabel($lang,$obj,'sheetsPerReam')],
    
    [getTransValue($lang,'reamLabel'),
    getReportTableContentFormatProductLabel($lang,$obj,"ream")]],
    [[1,0],[1,0],[1,0]]);
    
$this->data.=getNewLineA4([
    [getTransValue($lang,'customerLabel'),
    getReportTableContentFormatProductLabel($lang,$obj,'customer')],
    
    [getTransValue($lang,'cutLabel'),
    getReportTableContentFormatProductLabel($lang,$obj,'cut')]],
    [[2,0],[3,0]]);
    

$this->data.=getNewLineA4([
    [getTransValue($lang,'countryLabel'),
    getReportTableContentFormatProductLabel($lang,$obj,COUNTRY)],
    
    [getTransValue($lang,'manufacturLabel'),
    getReportTableContentFormatProductLabel($lang,$obj,MAN)]],
    [[2,0],[3,0]]);
    
$this->data.=getNewLineA4([
    ["",getTransValue($lang,'madeinSyria')],
    
    [getTransValue($lang,'iDLabel'),
    getReportTableContentFormatProductLabel($lang,$obj,ID)]],
    [[2,0],[3,0]]);
    
$this->data.=getNewLineA4([
    ["",getProductNotes($obj)],
    ["",getCustomsProductQR($obj)]],
    [[2,0],[3,0]]);


$this->data .= "</table>";

$this->data .= "<footer><small>Saffoury<b>Paper </b> 2022-2023 ® All Rights Reserved</small>  </footer></div>";

