<?php
// (A) LOAD INVOICR
require "invlib/invoicr.php";

require_once("../languages/en_ar.php");
require_once("../db_config.php");



require_once("../extension_api.php");
include("../number-to-words-master/autoload.php");
include("../qrcode/qrCodeID.php");
include("jsonMapper/vendor/autoload.php");
include("helpers/printOptions.php");


require_once("helpers/print_config.php");
require_once("helpers/product.php");
require_once("helpers/invoice.php");
require_once("helpers/cut_request.php");
require_once("helpers/print_a5_helper.php");
require_once("helpers/print_a4_helper.php");
$mapper = new JsonMapper();
// if(!isset($_POST['data'])){
//     returnServerError("no request data");
// }
// $printOption = $mapper->map(
//     json_decode($_POST['data']),
//     new TCPCommand()
// );



//testing
$printOption = $mapper->map(
    json_decode(file_get_contents('test.json')),
    new TCPCommand()
);

$printOption->validateRequest();
$User;
setUser();
$lang = $printOption->getLanguage();
$tableName = $printOption->actionMessage;
$iDs = $printOption->requestIDs;

$result = depthSearch($iDs, $tableName, 1, getDepthSearchDetails($tableName), true, $printOption->getSQLOption());
$isPDF=false;
if (empty($result)) {
    returnServerError("no results");
    die;
}
if (is_array($iDs)) {
    foreach ($result as $r) {
        $isGeneratedQrCode = false;
        generateInvoiceHTML($r);
    }
    global $invoicr;
    $invoicr->data .= "</body></html>";
} else {
    generateInvoiceHTML($result);
    global $invoicr;
    $invoicr->data .= "</body></html>";
    if($isPDF){
        $invoicr->outputPDFContentUsingWKPDF();
    }
    
    if(false){
        $FileHandle = fopen('result.pdf', 'w+');
        $curl = curl_init();
        $instructions = '{
        "parts": [
        {
            "html": "invoice.html"
        }
        ]
        }';

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.pspdfkit.com/build',
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_POSTFIELDS => array(
        'instructions' => $instructions,
        'invoice.html' => new CURLFILE('invoice.html')
        ),
        CURLOPT_HTTPHEADER => array(
        'Authorization: Bearer pdf_live_635vytDm4B4zAhghpQ8DvIx6jo1eVUISxqwTM53XeCx'
        ),
        CURLOPT_FILE => $FileHandle,));

        $response = curl_exec($curl);

        curl_close($curl);

        fclose($FileHandle);
    }
    
    
    
   
}
function generateInvoiceHTML($result)
{
    global $lang, $tableName, $invoicr,$isPDF;

    $reportTamplate = getReportTemplate($tableName);
    $details = getDepthSearchDetails($tableName)[0];

    $invoicr->set('headerSVG', getReportHeaderSVG($tableName));
    $invoicr->set('lang', $lang);
    $invoicr->set('appQrcode', "../qr.php?text=https://play.google.com/store/apps/details?id=com.saffoury.saffourypaper&backColor='F9F9F9'&foreColor='00F9F9'");
    $qrCodeContent = getQrCode($tableName, $result);
    $invoicr->set('invoiceQrcode', "../qr.php?compress=true&text=$qrCodeContent");
    $invoicr->set("company", [
        getReportLogo($tableName),
        getReportLogo($tableName),
        getTransValue($lang, "companyInfo"),
        getTransValue($lang, "companyWeb"),
        getTransValue($lang, "companyContact")
        //add scan RIGHT NOW
    ]);
    $invoicr->set("invoiceTitle", getUpperString(getTransValue($lang, $tableName)));
    $invoicr->set("billto", getInvoiceGetBillTo($lang, $tableName, $result));
    $invoicr->set("head", getInvoiceInfo($lang, $tableName, $result));
    $invoicr->set("invoiceTotals",  getInvoiceHeadTotals($lang, $tableName, $result));
    $invoicr->set("invoiceTotalsInWords",getInvoiceTotalsInWords($lang,$tableName,$result));
    $invoicr->set("tableHeaders", getReportTableHeaders($lang, $tableName));
    $invoicr->set("items", getDetailsItems($tableName, $result));
    $invoicr->set("totals", getInvoiceBottomTotals($lang, $tableName, $result));
    $invoicr->set("notes", getInvoiceNotes($lang, $tableName, $result));
    $invoicr->set('masterInvoice',$result);
    $invoicr->template($reportTamplate);
    if(!$isPDF){
     $invoicr->outputHTML();
     $invoicr->reset();
    }
    
}
// 1 : DISPLAY IN BROWSER
// 2 : FORCE DOWNLOAD
// 3 : SAVE ON SERVER
// $invoicr->outputHTML(1);//
// $invoicr->outputHTML(2, "invoice.html");
// $invoicr->outputHTML(3, __DIR__ . DIRECTORY_SEPARATOR . "invoice.html");

// (C3) OUTPUT IN PDF
// DEFAULT : DISPLAY IN BROWSER
// 1 : DISPLAY IN BROWSER
// 2 : FORCE DOWNLOAD
// 3 : SAVE ON SERVER
// $invoicr->outputPDF();
// $invoicr->outputPDF(1);
// $invoicr->outputPDF(2, "invoice.pdf");
// $invoicr->outputPDF(3, __DIR__ . DIRECTORY_SEPARATOR . "invoice.pdf");

// (C4) OUTPUT IN DOCX
// DEFAULT : FORCE DOWNLOAD
// 1 : FORCE DOWNLOAD
// 2 : SAVE ON SERVER
// $invoicr->outputDOCX();
// $invoicr->outputDOCX(1, "invoice.docx");
// $invoicr->outputDOCX(2, __DIR__ . DIRECTORY_SEPARATOR . "invoice.docx");

// (D) USE RESET() IF YOU WANT TO CREATE ANOTHER ONE AFFTER THIS
//  $invoicr->reset();

