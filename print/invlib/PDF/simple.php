<?php
// (A) HTML HEADER & STYLES
$this->data = "<!DOCTYPE html><html><head><style>".
"html,body{font-family:DejaVuSans}#bigi{margin-bottom:20px;font-size:28px;font-weight:bold;color:#ad132f;padding:10px}#company,#billship{margin-bottom:30px}#company img{max-width:180px;height:auto}#billship,#company,#items{width:100%;border-collapse:collapse}#billship td{width:33%}#billship td,#items td,#items th{padding:10px}#items th{text-align:left;border-top:2px solid #000;border-bottom:2px solid #000}#items td{border-bottom:1px solid #ccc}.idesc{color:#999}.ttl{background:#fafafa;font-weight:700}.right{text-align:right}#notes{background:#efefef;padding:10px;margin-top:30px}".
"</style></head><body><div id='invoice'>";



// $this->data = "<!DOCTYPE html><html><head>".
// "<link  rel=\"stylesheet\" href=\"http://localhost:80/print/style.css\">".
// "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">".
// "<link rel='stylesheet' href='https://fonts.googleapis.com/icon?family=Material+Icons'>".

// "</head><body><div id='invoice'>";

// (B) COMPANY
$this->data .= "<div id='header'>";
$this->data .= "<table id='company'><tr><td><img src='" . $this->company[0] . "'/></td><td class='right'>";
for ($i = 2; $i < count($this->company); $i++) {
	$this->data .= "<div id ='companyInfo'>" . $this->company[$i] . "</div>";
}
$this->data .= "</td></tr></table>";
$this->data .= "<div id='bigi'>" . $this->invoiceTitle . "</div>";
$this->data .= "</div>";


// (C) BILL TO
$this->data .= "<table id='billship'><tr><td>";
foreach ($this->billto as $b) {
	$this->data .=
	"<span class='material-icons md-dark md-inactive'>".$b[0]."</span>".	$b[1] . "<br>" . "<strong >" . $b[2] . "</strong><br>";
}

// (D) SHIP TO
$this->data .= "</td><td><strong>SHIP TO</strong><br>";
foreach ($this->shipto as $s) { $this->data .= $s."<br>"; }

// (E) INVOICE INFO
$this->data .= "</td><td>";
foreach ($this->head as $i) {
	$this->data .= "<strong>$i[0]:</strong> $i[1]<br>";
}
$this->data .= "</td></tr></table>";

// (F) ITEMS
$this->data .= "<table id='items'><tr><th>Item</th><th>Quantity</th><th>Unit Price</th><th>Amount</th></tr>";
foreach ($this->items as $i) {
	$this->data .= "<tr><td><div>".$i[0]."</div>".($i[1]==""?"":"<small class='idesc'>$i[1]</small>")."</td><td>".$i[2]."</td><td>".$i[3]."</td><td>".$i[4]."</td></tr>";
}

// (G) TOTALS
if (count($this->totals)>0) { foreach ($this->totals as $t) {
	$this->data .= "<tr class='ttl'><td class='right' colspan='3'>$t[0]</td><td>$t[1]</td></tr>";
}}
$this->data .= "</table>";

// (H) NOTES
if (count($this->notes)>0) {
	$this->data .= "<div id='notes'>";
	foreach ($this->notes as $n) {
		$this->data .= $n."<br>";
	}
	$this->data .= "</div>";
}

// (I) CLOSE
$this->data .= "</div></body></html>";
$mpdf->WriteHTML($this->data);