<?php
define('IMAGES_PATH', "saffoury.com/SaffouryPaper2/Images");
define('AC_NAME', "account_names");
define('AC_NAME_TYPE', "account_names_types");
define('COUNTRY', "countries");
define('CMC', "countries_manufactures");
define('CRED', "credits");
define('CUR', "currency");
define('CUST', "customers");
define('CUSTOMS', "customs_declarations");
define('CUSTOMS_IMAGES', "customs_declarations_images");
define('CUT', "cut_requests");
define('CUT_RESULT', "cut_request_results");
define('DEAL', "dealers");
define('DEBT', "debits");
define('EMP', "employees");
define('EQ', "equalities");
define('GD', "grades");
define('GSM', "gsms");
define('INC', "incomes");
define('JO', "journal_voucher");
define('MAN', "manufactures");

define('ORDR', "orders");
define('ORDR_D', "orders_details");
define('ORDR_R', "orders_refunds");
define('ORDR_R_D', "order_refunds_order_details");

define('RI', "reservation_invoice");
define('RI_D', "reservation_invoice_details");

define('CRS', "customers_request_sizes");
define('CRS_D', "customers_request_sizes_details");

define('PH', "phones");
define('PR', "products");
define('PR_SEARCH', "products_search_view");
define('PR_INV', "inventory_products");

define('PR_INPUT', "products_inputs");
define('PR_INPUT_D', "products_inputs_details");
define('PR_OUTPUT', "products_outputs");
define('PR_OUTPUT_D', "products_outputs_details");

define('TYPE', "products_types");

define('PURCH', "purchases");
define('PURCH_D', "purchases_details");
define('PURCH_R', "purchases_refunds");
define('PURCH_R_D', "purchases_refunds_purchases_details");

define('QUA', "qualities");
define('SETTING', "setting");

define('SIZE', "sizes");
define('SIZE_CUT', "sizes_cut_requests");
define('SP', "spendings");
define('SP_O', "spendings_orders");
define('SP_T', "spendings_transfers");

define('TR', "transfers");
define('TR_D', "transfers_details");

define('GOV', "governorates");
define('CARGO', "cargo_transporters");

define('HOME_IMAGE', "home_image_list");
define('HOME_IMAGE_D', "home_image_list_action");
define('HOME_ADS', "home_ads_image_list");
define('HOME_ADS_D', "home_ads_image_list_action");

define('WARE', "warehouse");
define('WARE_E', "warehouse_employees");
class TCPCommand
{
    
    public $requestIDs;
    public $isList=false;
    public  $actionMessage;

    //sorting
    public $fieldSortBy = "";
    public $fieldSortByAscDesc = "";
    public $fieldSortByMaster="";
    
    
    //invoicing
    public $hideBalanceDue;
    public $hideUnitAndTotalPrice;
    public $hideTerms;
    public $hideAddressAndPhone;
    public $hideCargoInfo;
    public $hideGeneralInvoiceTerms=false;
    public $hideGeneralInvoiceNotes=false;
    public $invoiceProductTypeOptions;
    public $invoiceProductTypeOptionsName = "ReelCut";
    
    
    //invoice and funds
    public $threeZeroPrice;
     
    
    //cut request
    public $hideCustomerName;
    
    
    //funds
    public $hidePaymentAmount;
    public $hideCurrency;
    public $hideEmployeeName;
    
    
    //product
    public $printProductAsLabel=false;
    
    
    
    public $reportOptions;
    public $printerOptions;
    public $shipmentDetails;
    public  $invoiceOptions;
    


    public  $sortBy;
    public  $cutRequestSort;
    public  $cutRequestPrintOption;
    public  $moneyFundSort;
    public  $printProductOptions;
    public $customQuantity;

    
    public $dontPrintWasteProductLabel = true;
    public $printAsCatalog;
    public $hideQuantity;
    public $collectSimilarProducts;
    // *
    //     * Displays the product current status
    //     * ( DES)
    public $showProductStatus;
    // *
    //     * Fills product that changes its status to another color
    public $fillChangedProductsToAnotherColor;
    // *
    //     * Show the total number of Pallets and Rolls
    public $showTotalCounts;
    // *
    //     * show SubTotals Like Reams / Sheets / KG Sheets
    public $showSubTotals;
   
    // *
    //     * Hide Unit Price And Total Price Report ONLY
    
    // *
    //     * Hide account balance on the report ONLY
    
   

    // *
    //     * Hide payment Amount For Credit And Debit only
    
    // *
    //     * View summary reports in detailed mode
    public $showReporting;
    public $showInvoiceRefunds;
    public $viewAsLedger;
    // todo test this fixme
    public $date;
    public $currency;
    public $warehouse;
    public $showPreviousBalance;
    public $printLabel;
    public $showBarcodes;
    public  $imgLinkAndroidQRCode;
    public $customClassName;
    // public List<String> generatedFileNameToDownload;
    // public List<String> filesNameToDownload;
    // *
    //     * this is useful for letting the server know what type of object to serializing
    //     * table === "products" in RestOptions then get the Product.class to deserialize
    private  $table;
    // *
    //     * This change the actionMessage class name to a specific class name to find it on c# if the restOption not presented on c#
    //     *
    //     * @param actionMessage
    //     * @return
    function  __ruct($Obj, $actionMessage)
    {
    }
    function validateRequest(){
        if($this->isList){
            $this->requestIDs=json_decode( $this->requestIDs);
        }
    }
    function isOrderOrPurchases()
    {
        return $this->actionMessage == ORDR || $this->actionMessage == PURCH;
    }
    function isOrderOrPurchasesRefund()
    {
        return $this->actionMessage == ORDR_R || $this->actionMessage == PURCH_R;
    }
    function isProduct()
    {
        return $this->actionMessage == PR;
    }
    function isList()
    {
        return is_array($this->requestIDs);
    }

    function isPrintProductAsLabel(){
        if(!is_array($this->$requestIDs)){
            return true;
        }
        return $this->printProductAsLabel;
    }
    function isHideCurrency()
    {
        return $this->hideCurrency;
    }
    function isCustomerStatement()
    {
        //todo 
        //  return "Objects.isClassOf(Obj, Customer.class)";
        return $this->actionMessage == CUST;
    }
    function isHideGeneralInvoiceNotes(){
        return $this->hideGeneralInvoiceNotes;
    }
    function isHideGeneralInvoiceTerms(){
        return $this->hideGeneralInvoiceTerms;
    }
    function isHideBalanceDue()
    {
        if( $this->isIncomes() || $this->isSpendings()){
            return true;
        }
        return $this->hideBalanceDue;
    }
    function ShowProductStatus()
    {
        return $this->showProductStatus;
    }
    function isThreeZeroPrice()
    {
        return $this->threeZeroPrice;
    }
     function isHidePaymentAmount()
    {
        return $this->hidePaymentAmount;
    }
    function isHideAddressAndPhone()
    {
        return $this->hideAddressAndPhone;
    }
    function isHideCargoInfo(){
        return $this->hideCargoInfo;
    }
    function isHideUnitAndPrice()
    {
        return $this->hideUnitAndTotalPrice || !$this->isInvoiceWithPrices();
    }
    function isInvoiceWithTerms()
    {
        return $this->actionMessage == ORDR || $this->actionMessage == RI;
    }
    function isDebits()
    {
        return $this->actionMessage == DEBT;
    }
    function isInvoiceWithPrices()
    {
        return $this->isOrderOrPurchasesRefund() || $this->isOrderOrPurchases();
    }
    function isInvoice()
    {
        return $this->actionMessage == ORDR
            || $this->actionMessage == PURCH
            || $this->actionMessage == PR_INPUT
            || $this->actionMessage == PR_OUTPUT
            || $this->actionMessage == ORDR_R
            || $this->actionMessage == PURCH_R
            || $this->actionMessage == TR
            || $this->actionMessage == RI;
    }
    function getLanguage(){
        return $this->printerOptions->language==0 ? 'ar' :'en';
    }
    function hasReportOptions()
    {
        return $this->isHasReportFooter() || $this->isHasReportHeader();
    }
    function getReportFooter(){
        return $this->reportOptions->reportFooter;
    }
    function isHasReportHeader()
    {
        return !is_null($this->reportOptions) && !is_null($this->reportOptions->reportHeader);
    }
    function isHasReportFooter()
    {
        return !is_null($this->reportOptions) && !is_null($this->reportOptions->reportFooter);
    }
    function isHideEmployee(){
        return $this->hideEmployeeName;
    }
    function isCutRequest()
    {
        return $this->actionMessage == CUT;
    }
    function isIncomes(){
      return $this->actionMessage == INC;  
    }
    function isSpendings(){
      return $this->actionMessage == SP;  
    }
    function isCredit()
    {
        return $this->actionMessage == CRED;
    }

    function hasSortBy()
    {
        return !is_null($this->fieldSortBy) && !is_null($this->fieldSortByAscDesc);
    }
    function getSQLOptionDetails(){
        $option=array();
        $option["PRINT_OPTION"]=true;
        return $option;
        
    }
    function getSQLOption()
    {
        $option=array();
        if ($this->hasSortBy()) {
            $ASC=$this->fieldSortByAscDesc=="ASC";
            $DESC=$this->fieldSortByAscDesc=="DESC";
            if ($ASC) {
                $option["ORDER_BY_EXTENSTION"] = " ORDER BY " . addslashes($this->actionMessage) . ".`" . $this->fieldSortBy . "` ASC ";
            }
            if ($DESC) {
                $option["ORDER_BY_EXTENSTION"] = " ORDER BY " . addslashes($this->actionMessage) . ".`" . $this->fieldSortBy . "` DESC ";
            }
        }
        $option["PRINT_OPTION"]=true;
        return $option;
    }

    function isProductMovement()
    {
        // return !isList && Objects.isClassOf(Obj, ProductMovements.class);
        return false;
    }

    function ShowProductPrintTypes()
    {
        // TODO Small Version
        // return !isList && Objects.isClassOf(Obj, Product.class);
        return false;
    }
    function ShowPrintLabel()
    {
        // / is product or invoice master
        // return ShowProductPrintTypes() || isInvoice() || (Objects.isClassOf(Obj, CustomsDeclaration.class));
        return false;
    }

    function ShowFillChangedProductsToAnothrColor()
    {
        return $this->ShowProductStatus();
    }

    function ShowCustomerStatement()
    {
        //   return Objects.isClassOf(Obj, Customer.class);
        return false;
    }


    function isMoneyFund()
    {

        // return Objects.isClassOf(Obj, MoneyFund.class);
        return false;
    }
   
    function isCutRequestAndHasResult()
    {
        //  return Objects.isClassOf(Obj, CutRequests.class) && ((CutRequests) Obj).cut_request_results_count > 0;
        return false;
    }

    function isDashboard()
    {
        //   return Objects.isClassOf(Obj, Dashboard.class);
        return false;
    }
    function ShowReporting()
    {
        // return Objects.isClassOf(Obj, InvoiceMaster.class);
        return false;
    }
    function IsShipmentDetailsVisible()
    {
        // return Objects.isClassOf(Obj, Orders.class);
        return false;
    }
    function IsDateVisible()
    {
        // return Objects.isClassOf(Obj, Customer.class);
        // todo   return Obj.GetType() == typeof(Customer) || Obj.GetType() == typeof(Dashboard);
        return false;
    }
    function HasPrintOption()
    {
        // Logs.Log("HasPrintOption=> ", Obj.getClass().toString());
        // return Objects.isClassOf(Obj, InvoiceMaster.class) || Objects.isClassOf(Obj, MoneyFund.class) || Objects.isClassOf(Obj, Product.class) || Obj instanceof User
        //          || Objects.isClassOf(Obj, CustomsDeclaration.class) || Objects.isClassOf(Obj, Employee.class);
        // todo || Obj instanceof (Dashboard);
        return false;
    }
}
class CutRequestPrintOption extends Enum
{
    const  RESULT  = 0;
    const  REQUEST  = 1;
    const  REQUEST_AND_RESULT  = 2;
}
class CutRequestSort extends Enum
{
    const DATE = 0;
    const PENDING_FIRST  = 1;
}
class MoneyFundSort extends Enum
{
    const  DATE  = 0;
    const INCOMES_EXPENCES  = 1;
    const EXPENCES_INCOMES  = 2;
}
// *
//     * Product sorting type
class ProductSort extends Enum
{
    const None  = 0;
    const Description  = 1;
    const Quality  = 2;
    const GSM  = 3;
    const Quantity  = 4;
    const Size  = 5;
    const Sheets  = 6;
    const Type  = 7;
    const Unit  = 8;
}
class DateObject
{
    // TODO SET ANCHORE
    public  $dateDrop;
    public  $from;
    public  $to;
}
class ShipmentDetails
{
    public  $shipmentPrice;
}
class  PrinterOptions
{
    public  $ip;
    public $copies;
    public  $startEndPage;
    public  $language;
    public  $printOptionsEnum;
    public  $printerName;
    public  $printerLabelName;
    public $sendToClient;
    public  $printPaperSize;
    // public ServerDetails serverDetails;
    function  __construct()
    {
        $this->copies = 1;
        $this->name = "-";
        $this->ip = "-";
        $this->printPaperSize = PrintPaperSize::Default;
    }
}
class PrintProductOptions extends Enum
{
    const  constA4LABEL  = 0;
    const  A4BreifLabel  = 1;
    const  PRODUCTS_LABEL  = 2;
}
class Language extends Enum
{
    const English  = 0;
    const Arabic  = 1;
}
class PrintPaperSize extends Enum
{
    const Default  = 0;
    const A3Size  = 1;
    const A4Size  = 2;
    const A5Size  = 3;
}
class PrintInvoiceProductTypeOptions extends Enum
{
    const ORIGINAL_PRODUCT_TYPE  =  0;
    const  CHANGE_ONLY_CUT_ROLL  =  1;
    const CHANGE_ALL_PRODUCT_TYPE  =  2;
}
class PrintInvoiceOptions extends Enum
{
    const INVOICE  =  0;
    const PRODUCTS_LABEL  =  1;
    const INVOICE_LABEL  =  2;
}
class PrintOptionsEnum extends Enum
{
    const  PRINT  = 0;
    const  PRINT_AND_DOWNLOAD  = 1;
    const  DOWNLOAD  = 2;
    const  SHOW_ONLY  = 3;
    const  SAVE_ONLY  = 4;
    const  BROADCAST  = 5;
    const  SEND_TO_CLIENT  = 6;
    private $value;
    function  __construct($value)
    {
        $this->value = $value;
    }
    function getValue()
    {
        return ($this->value);
    }
}
class Currency
{
    public $iD;
    public  $name;
    public  $nameAr;
}
class Customer
{
    public $iD;
    public  $name;
}
class Warehouse
{
    public $iD;
    public  $name;
}
class ReportOptions
{
    public  $reportHeader;
    public  $reportFooter;
}
abstract class Enum
{
    private static $constCacheArray = NULL;

    private static function getConstants()
    {
        if (self::$constCacheArray == NULL) {
            self::$constCacheArray = [];
        }
        $calledClass = get_called_class();
        if (!array_key_exists($calledClass, self::$constCacheArray)) {
            $reflect = new \ReflectionClass($calledClass);
            self::$constCacheArray[$calledClass] = $reflect->getConstants();
        }
        return self::$constCacheArray[$calledClass];
    }

    public static function isValidName($name, $strict = false)
    {
        $constants = self::getConstants();

        if ($strict) {
            return array_key_exists($name, $constants);
        }

        $keys = array_map('strtolower', array_keys($constants));
        return in_array(strtolower($name), $keys);
    }

    public static function isValidValue($value, $strict = true)
    {
        $values = array_values(self::getConstants());
        return in_array($value, $values, $strict);
    }

    public static function fromString($name)
    {
        if (self::isValidName($name, $strict = true)) {
            $constants = self::getConstants();
            return $constants[$name];
        }

        return false;
    }

    public static function toString($value)
    {
        if (self::isValidValue($value, $strict = true)) {
            return array_search($value, self::getConstants());
        }

        return false;
    }
}
class DateEnum extends Enum
{
    const Custom  = 0;
    const Today  = 1;
    const This_Week  = 2;
    const This_Month  = 3;
    const This_Year  = 4;
}
