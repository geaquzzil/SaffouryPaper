<?php
// (A) HTML HEADER & STYLES
setHeaderA4($this);

if(!is_null($this->headerSVG)){
    $this->data .="<div id='header'><img   src='" . $this->headerSVG . "'/></div>";
}
// $this->data .="<hr>";
$this->data .= "<div id='bigi'>" . $this->invoiceTitle . "</div>";

// $this->data .="<hr>";
setBillToHeader($this);

$this->data .="<div id='tableMargin'>";
setTableHeader($this);

setTableContent($this);

setTableTotals($this);

$this->data .= "</div></div>";
$this->data.="<footer></footer>";
//  $this->data .= "</body></html>";

