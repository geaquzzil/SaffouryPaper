
<?php
/*
 * All database connection variables
 */
define("ROOT", dirname(__FILE__) . "/");
define("JSONTOMYSQL_LOCKED", false);
define("ROOT_LOCAL", dirname(__FILE__) . "\\");

//define("KEY",md5(DB_ANDROID));
//define("SALT",md5(DB_ANDROID));

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

define('USR', "userlevels");

define('ID', "iD");
define('KCUST', "CustomerID");
define('KEMP', "EmployeeID");

define('KP', "ProductID");
define('PARENTID', "ParentID");
define('KPURCH', "PurchasesID");
define('KORDER', "OrderID");
define('KTYPE', "ProductTypeID");

define('KCMC', "Country_Manufacture_CompanyID");
define('KGSM', "GSMID");
define('KSIZE', "SizeID");
define('KCOUNTRY', "CountryID");
define('KMANUFACTURE', "ManufactureID");
define('KLVL', "userlevelid");
