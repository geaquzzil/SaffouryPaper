<?php



namespace Etq\Restful\Repository;

use Etq\Restful\Helpers;
use Etq\Restful\Middleware\Auth;
use Etq\Restful\Repository\BaseRepository;
use Etq\Restful\Repository\Options;

class DashboardRepository extends SharedDashboardAndCustomerRepo
{


    //todo customer
    //todo or employee that see every thing 
    //todo employee only sees his crdits
    public function getDashboard(Auth $auth, ?Date $date = null, bool $withAnalsis = false, $requireOnlyEmployeeRecords = false)
    {
        $isCustomer = $auth->isCustomer();

        $iD = $auth->getUserID();

        $parentTableName = $isCustomer ? CUST : EMP;


        $user = $this->view($parentTableName, $iD, null, Options::getInstance()->requireObjects());

        $option =
            $isCustomer ?
            Options::withStaticWhereQuery("CustomerID ='$iD'") : ($requireOnlyEmployeeRecords ? Options::withStaticWhereQuery("EmployeeID ='$iD'") :
                Options::getInstance());
        $option =
            $option
            ->withDate($date)
            ->requireObjects()
            ->requireDetails([ORDR_D, ORDR_R_D, PURCH_D, PURCH_R_D]);




        $this->setListsWithAnalysis($user, CRED, $parentTableName, $withAnalsis, $option);
        $this->setListsWithAnalysis($user, DEBT, $parentTableName, $withAnalsis, $option);

        $this->setLists($user, ORDR, "OrderID", ORDR_R, ORDR_R_D, $withAnalsis, $option);
        $this->setLists($user, PURCH, "PurchaseID", PURCH_R, PURCH_R_D, $withAnalsis, $option);

        $this->setListsWithAnalysis($user, RI, $parentTableName, $withAnalsis, $option);
        $this->setListsWithAnalysis($user, CRS, $parentTableName, false, $option);
        $this->setListsWithAnalysis($user, CUT, $parentTableName, $withAnalsis, $option);
    }
}
