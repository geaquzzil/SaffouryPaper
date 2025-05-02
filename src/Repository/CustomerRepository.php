<?php



namespace Etq\Restful\Repository;

use Etq\Restful\Helpers;
use Etq\Restful\Repository\BaseRepository;
use Etq\Restful\Repository\Options;
use Illuminate\Support\Arr;

class CustomerRepository extends SharedDashboardAndCustomerRepo
{

    public function getStatement(int $iD, ?Date $date = null, bool $withAnalsis = false, bool $mobileVersion = false)
    {
        $customer = $this->view(CUST, $iD, null, Options::getInstance()->requireObjects());
        $option = Options::withStaticWhereQuery("CustomerID ='$iD'")->withDate($date)
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
            $date ? $this->getBalance($iD, Date::getInstance()->getPreviousTo($date?->from)) : null
        );
        Helpers::setKeyValueFromObj(
            $customer,
            "dateObject",
            $date
        );

        //TODO Balance

        return $customer;
    }
}
