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
            ->requireDetails([ORDR_D, ORDR_R_D, PURCH_D, PURCH_R_D]);

        $this->setListsWithAnalysis($customer, CRED, CUST, $withAnalsis, $option);
        $this->setListsWithAnalysis($customer, DEBT, CUST, $withAnalsis, $option);

        $this->setLists($customer, ORDR, "OrderID", ORDR_R, ORDR_R_D, $withAnalsis, $option);
        $this->setLists($customer, PURCH, "PurchaseID", PURCH_R, PURCH_R_D, $withAnalsis, $option);

        $this->setListsWithAnalysis($customer, RI, CUST, $withAnalsis, $option);
        $this->setListsWithAnalysis($customer, CRS, CUST, false, $option);
        $this->setListsWithAnalysis($customer, CUT, CUST, $withAnalsis, $option);

        Helpers::setKeyValueFromObj(
            $customer,
            "previousBalance",
            $this->getBalance($iD, Date::getInstance()->getPreviousTo($date?->from))
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
