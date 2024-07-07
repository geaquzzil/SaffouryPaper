<?php
include('qrcode/phpqrcode.php');
if(isset($_GET['text'])){
    $text=$_GET['text'];
    if(isset($_GET['compress'])){
        $text=base64_encode(gzencode( ($text)));
    }
    $backColor=isset($_GET['backColor'])?hexdec($_GET['backColor']): 0xF9F9F9;
    $foreColor=isset($_GET['foreColor'])?hexdec($_GET['foreColor']): 0x000000;
    Qrcode::svg($text,false,4,2,false,$backColor,$foreColor);
  //  Qrcode::png($text);
}
?>


