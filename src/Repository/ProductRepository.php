<?php



namespace Etq\Restful\Repository;

use Etq\Restful\Helpers;
use Etq\Restful\Repository\BaseRepository;
use Etq\Restful\Repository\Options;



class ProductRepository extends SharedDashboardAndCustomerRepo
{
    public function getProductTypeAvailability(?int $iD = null, Options $option)
    {

        $results = $this->getFetshALLTableWithQuery("SELECT DISTINCT  `ProductTypeID` AS iD FROM `" . PR_SEARCH . "`");
        if (!empty($results) || !is_null($results)) {
            return $this->list(TYPE, null, $option->addStaticQuery(Helpers::getIDSImpolde($results, "iD", true)));
        } else {
            return array();
            // no content
        }
    }
    public function setCustomsDeclaration()
    {
        // $API_ACTIONS["set_customs_declarations"] = function () {
        //     // checkEditAddRequest();
        //     //  checkPermissionAction("list_customers");
        //     $data = json_decode(getRequestValue('data'), false);
        //     $customsDeclaration = isSetKeyFromObjReturnValue($data, CUSTOMS);
        //     $productType = isSetKeyFromObjReturnValue($data, TYPE);

        //     $CDID = isSetKeyFromObjReturnValue($customsDeclaration, 'iD');
        //     $PRID = isSetKeyFromObjReturnValue($productType, 'iD');

        //     if ($CDID > 0 and $PRID > 0) {
        //         $query =  " UPDATE `products` Set `CustomsDeclarationID` = '$CDID' WHERE ProductTypeID = '$PRID'";
        //         getUpdateTableWithQuery($query);
        //         returnResponse($data);
        //     } else {
        //         returnResponse(-1);
        //     }



        //     //  $countryManufacture=isSetKeyFromObjReturnValue($data,CMC);
        //     if (!is_null($customsDeclaration)) {
        //         if (isNewRecord($customsDeclaration)) {
        //             $customsDeclaration = addEditObject($customsDeclaration, CUSTOMS, getDefaultAddOptions());
        //         }
        //     }
        //     if (!is_null($purchases)) {
        //         foreach (getKeyValueFromObj($purchases, PURCH_D) as &$purch) {
        //             $purch->{PR}->{CUSTOMS} = $customsDeclaration;
        //             $purch->{PR}->{CMC} = $countryManufacture;
        //         }
        //         returnResponse($data);
        //     }


        //     // echo " NO WE ADDING PURCHASIG\n ";



        //     //json_decode(getRequestValue('data'),false)
        // };
    }

    public function getMovement(int $iD, Options $options)
    {
        $object = array();
        Helpers::setKeyValueFromObj($object, PR, $this->view(PR, $iD, null, $options->getClone($options)->removeDate()));

        $options =  $options->addStaticQuery("ProductID='$iD'");

        $this->setListsWithAnalysisByListByDetail($object, ORDR, ORDR_D, $options, null, true);
        $this->setListsWithAnalysisByListByDetail($object, ORDR_R, ORDR_R_D, $options, null, true);

        $this->setListsWithAnalysisByListByDetail($object, PURCH, PURCH_D, $options, null, true);
        $this->setListsWithAnalysisByListByDetail($object, PURCH_R, PURCH_R_D, $options, null, true);

        $this->setListsWithAnalysisByListByDetail($object, RI, RI_D, $options, null, true);

        $this->setListsWithAnalysisByListByDetail($object, PR_INPUT, PR_INPUT_D, $options, null, true);
        $this->setListsWithAnalysisByListByDetail($object, PR_OUTPUT, PR_OUTPUT_D, $options, null, true);

        $this->setListsWithAnalysisByListByDetail($object, TR, TR_D, $options, null, true);

        $this->setListsWithAnalysis(
            $object,
            CUT,
            $options->getClone($options)->requireDetails()->requireObjects(),
            null,
            true
        );

        return $object;
    }
    public function getMostPopular(Options $options)
    {
        $options = $options
            ->withGroupByArray(["products.iD"])
            ->withDESCArray(["extendedNetQuantity"])
            ->withLimit(20);

        $optionQuery = $options->getQuery("extended_order_refund");
        $query = "
        SELECT 
	            count(products.iD) AS extendedNetQuantity,
			    (products.iD) AS iD
			FROM
                extended_order_refund
			INNER JOIN
                orders_details ON orders_details.OrderID = extended_order_refund.iD
			INNER JOIN
                products ON products.iD = orders_details.ProductID
			$optionQuery";

        $result = $this->getFetshALLTableWithQuery($query);
        $response = array();
        // print_r($result);
        // die;
        foreach ($result as $res) {

            $product = $this->view(PR, $res['iD'], null, Options::getInstance($options)->requireObjects());
            Helpers::setKeyValueFromObj($product, "orders_details", [$res]);
            array_push($response, $product);
        }
        return $response;
    }
    public function getBestSellingProducts(Options $options)
    {
        $options = $options
            ->withGroupByArray(["products.iD"])
            ->withDESCArray(["extendedNetQuantity"])
            ->withLimit(10);

        $optionQuery = $options->getQuery("extended_order_refund");
        $query = "
        SELECT 
	            Sum(extendedNetQuantity) AS extendedNetQuantity,
			    products.iD AS iD
			FROM
                extended_order_refund
			INNER JOIN
                orders_details ON orders_details.OrderID = extended_order_refund.iD
			INNER JOIN
                products ON products.iD = orders_details.ProductID
			$optionQuery";

        $result = $this->getFetshALLTableWithQuery($query);
        $response = array();

        foreach ($result as $res) {

            $product = $this->view(PR, $res['iD'], null, Options::getInstance($options)->requireObjects());
            Helpers::setKeyValueFromObj($product, "orders_details", [$res]);
            array_push($response, $product);
        }
        return $response;
    }
    public function getExpectedProductsToBuy(Options $options)
    {
        $date = $options->date;
        $options = $options->getClone()->unsetDate()
            ->withGroupByArray(["products.iD", "month"], "count(products.iD)>7")
            ->withDESCArray(["extendedNetQuantity"]);
        // ->withLimit(40);

        $optionQuery = $options->getQuery("extended_order_refund");
        $query = "
        SELECT 
            count(products.iD) AS extendedNetQuantity,
            sum(extendedNetQuantity) as quantity,
            Month(extended_order_refund.date) as month,
            (products.iD) AS iD
        FROM
            extended_order_refund
        INNER JOIN
            orders_details ON orders_details.OrderID = extended_order_refund.iD
        INNER JOIN
            products ON products.iD = orders_details.ProductID
        $optionQuery";
        // echo $query;
        $result = $this->getFetshALLTableWithQuery($query);
        // print_r($result);
        $response = array();
        $monthNumber = $date?->getMonthNumber(true);
        // echo "\nmonthNumber $monthNumber";
        if ($monthNumber) {
            foreach ($result as $res) {
                if ($res['month'] == (int)$monthNumber) {
                    $pr = $this->view(PR, $res['iD'], null, Options::getInstance($options)->requireObjects());

                    Helpers::setKeyValueFromObj($pr, 'orders_details_count', $res['extendedNetQuantity']);
                    Helpers::setKeyValueFromObj($pr, 'inventory_count', $res['quantity']);
                    array_push($response, $pr);
                    // echo "\n" . $res['iD'];
                }
            }
        }



        return $response;
    }
}
