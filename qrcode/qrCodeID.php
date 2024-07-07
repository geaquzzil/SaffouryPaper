<?php
class QRCodeID{
    public  $iD=-1;
    public  $action='';
    public  $quantity=0;

    public function getQrCode($tableName,$obj){
        $this->action=$tableName;
        $this->iD=$obj["iD"];
        return json_encode($this,JSON_UNESCAPED_UNICODE);
    }
    public function getQrCodeWithQuantity($tableName,$obj,$quantity){
        $this->action=$tableName;
        $this->iD=$obj["iD"];
        $this->quantity=$quantity;
        return json_encode($this,JSON_UNESCAPED_UNICODE);
    }
    public function getQrCodeCompress($tableName,$obj){
        return base64_encode(gzcompress(getQrCode($tableName,$obj)));
    }

}


?>