<?php



namespace Etq\Restful\Repository;

use Etq\Restful\Helpers;
use Etq\Restful\Repository\BaseRepository;
use Etq\Restful\Repository\Options;
use Illuminate\Support\Arr;

class CustomerRepository extends SharedDashboardAndCustomerRepo
{

    public function transfer(int $from, int $to, ?Options $option = null)
    {
        $count = 0;
        $results = $this->getTransferKeys(KCUST);
        if (!empty($results)) {
            foreach ($results as $res) {
                $updateQuery = "UPDATE `" . $res["TABLE_NAME"] .
                    "` SET `" . KCUST . "` ='$to' WHERE `" . KCUST . "` ='$from'";
                //echo $updateQuery;
                $count = $count + $this->getUpdateTableWithQuery($updateQuery);
            }
        }
        $response = array();
        $response["count"] = $count;
        $response["serverStatus"] = true;
        return $response;
    }

    public function getStatement(int $iD, ?Options $option = null, bool $withAnalsis = false, bool $mobileVersion = false)
    {
        $customer = $this->view(CUST, $iD, null, Options::getInstance()->requireObjects());
        $option = Options::getInstance($option)->withStaticWhereQuery("CustomerID ='$iD'")
            ->requireObjects()
            ->requireDetails([ORDR_D, ORDR_R_D, PURCH_D, PURCH_R_D, RI_D, CRS_D, CUT_RESULT]);

        $this->setListsWithAnalysis($customer, CRED, $option, CUST, $withAnalsis);
        $this->setListsWithAnalysis($customer, DEBT,  $option, CUST, $withAnalsis);

        $this->setLists($customer, ORDR, "OrderID", ORDR_R, ORDR_R_D, $option, CUST, $withAnalsis);
        $this->setLists($customer, PURCH, "PurchaseID", PURCH_R, PURCH_R_D, $option, CUST, $withAnalsis);

        $this->setListsWithAnalysis($customer, RI,  $option, CUST, $withAnalsis);
        $this->setListsWithAnalysis($customer, CRS,  $option, CUST, false);
        $this->setListsWithAnalysis($customer, CUT,  $option, CUST, $withAnalsis);

        Helpers::setKeyValueFromObj(
            $customer,
            "previousBalance",
            $option->date ? $this->getBalance($iD, Date::getInstance()->getPreviousTo($option->date?->from)) : null
        );
        Helpers::setKeyValueFromObj(
            $customer,
            "dateObject",
            $option->date
        );

        //TODO Balance

        return $customer;
    }
}
