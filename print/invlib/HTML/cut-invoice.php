<?php

setHeaderA5($this);
if(!is_null($this->headerSVG)){
    $this->data .="<div id='header'><img   src='" . $this->headerSVG . "'/></div>";
}
$this->data.="<div id= 'bigi'>".$this->invoiceTitle."</div>";
setTable($this);
$this->data .= "</div>";
$this->data .= "<footer></footer>";