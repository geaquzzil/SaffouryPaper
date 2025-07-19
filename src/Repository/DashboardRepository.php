<?php



namespace Etq\Restful\Repository;

use Etq\Restful\Helpers;
use Etq\Restful\Middleware\Auth;
use Etq\Restful\Repository\BaseRepository;
use Etq\Restful\Repository\Options;
use Illuminate\Support\Facades\Response;

class DashboardRepository extends SharedDashboardAndCustomerRepo
{



    private function setEmployeeOrCustomer(Options &$option, bool $requireOnlyEmployeeRecords = false)
    {
        $iD = $this->getUserID($option);
        $isCustomer = $this->isCustomer($option);
        $hasKey = $isCustomer ? "CustomerID ='$iD'" : ($requireOnlyEmployeeRecords ? "EmployeeID ='$iD'" : null);
        $option = $hasKey ? $option->withStaticWhereQuery($hasKey) : $option;
        return $hasKey ? true : false;
    }
    public function dashIT(string $tableName, Options $option)
    {
        $response = array();
        $withInterval = $option->hasNotFoundedColumn("interval") ? true : false;
        $isSet = $this->setEmployeeOrCustomer($option);

        Helpers::setKeyValueFromObj(
            $response,
            "responseListAnalysis",
            $this->getGrowthRate($tableName, null, $option, false, $withInterval)
        );
        $response['date'] = $option->date;
        if ($isSet) {
            $response['userID'] = $this->getUserID($option);
        }
        return $response;
    }

    public function getSalesDashboard(Options $options, $requireOnlyEmployeeRecords = false)
    {
        $response = array();
        $orderOption = $options->getClone($options);
        Helpers::setKeyValueFromObj(
            $response,
            ORDR . "_offline_count",
            $this->getGrowthRate(ORDR, null, $orderOption->addStaticQuery("status = 'NONE'"), true)
        );
        Helpers::setKeyValueFromObj(
            $response,
            ORDR . "_online_count",
            $this->getGrowthRate(ORDR, null, $orderOption->addStaticQuery("status != 'NONE'"), true)
        );
        Helpers::setKeyValueFromObj(
            $response,
            CUST . "_count",
            $this->getGrowthRate(CUST, null, $options, true)
        );
        Helpers::setKeyValueFromObj(
            $response,
            PR . "_count",
            $this->getGrowthRate(PR, null, $options, true)
        );


        Helpers::setKeyValueFromObj(
            $response,
            "totalSalesQuantity",
            $this->getGrowthRate(ORDR, "quantity", $options)
        );
        Helpers::setKeyValueFromObj(
            $response,
            "totalReturnsQuantity",
            $this->getGrowthRate(ORDR, "refundQuantity", $options)
        );
        Helpers::setKeyValueFromObj(
            $response,
            "totalNetSalesQuantity",
            $this->getGrowthRate(ORDR, "extendedNetQuantity", $options)
        );

        Helpers::setKeyValueFromObj(
            $response,
            "totalSalesPrice",
            $this->getGrowthRate(ORDR, "extendedPrice", $options)
        );
        Helpers::setKeyValueFromObj(
            $response,
            "totalReturnsPrice",
            $this->getGrowthRate(ORDR, "extendedRefundPrice", $options)
        );
        Helpers::setKeyValueFromObj(
            $response,
            "totalNetSalesPrice",
            $this->getGrowthRate(ORDR, "extendedNetPrice", $options)
        );


        Helpers::setKeyValueFromObj(
            $response,
            "profitsByOrder",
            $this->getGrowthRate("profits_orders", "total", $options)
        );
        Helpers::setKeyValueFromObj(
            $response,
            "profitsByCutRequests",
            $this->getGrowthRate("profits_cut_requests_products", "totalPrice", $options)
        );
        Helpers::setKeyValueFromObj(
            $response,
            "wastesByCutRequests",
            $this->getGrowthRate("wasted_cut_requests_products", "total", $options)
        );
        $this->setListsWithAnalysis($response, INC, $options, null, true);
        $this->setListsWithAnalysis($response, SP, $options, null, true);
        // $isSet = $this->setPreviousAndTodayDate($response, null, true, $options, false);

        Helpers::setKeyValueFromObj(
            $user,
            "dateObject",
            $options->date
        );

        return $response;
    }

    public function getFundDashboard(Options $options, $requireOnlyEmployeeRecords = false)
    {
        $date = $options->date;
        $iD = $this->getUserID($options);
        $isCustomer = $this->isCustomer($options);
        $isEmployee = $this->isEmployee($options);
        $tableName = $isCustomer ? CUST : EMP;
        $parentTableName = $isCustomer ? CUST : ($requireOnlyEmployeeRecords ? EMP : null);
        $withInterval = $options->hasNotFoundedColumn("interval") ? true : false;
        $withAnalysis = $options->hasNotFoundedColumn("withAnalysis") ? true : false;
        $hasKey = $isCustomer ? "CustomerID ='$iD'" : ($requireOnlyEmployeeRecords ? "EmployeeID ='$iD'" : null);
        $user = $this->view($tableName, $iD, null, Options::getInstance($options)->requireObjects());
        $option = Options::getInstance($options);
        $isSet = $this->setEmployeeOrCustomer($option);
        $option =
            $option
            ->withDate($date)
            ->requireObjects();

        $this->setListsWithAnalysis($user, CRED, $option, $parentTableName, $withAnalysis);
        $this->setListsWithAnalysis($user, DEBT, $option, $parentTableName, $withAnalysis);


        if ($isEmployee) {
            $this->setListsWithAnalysis($user, INC, $option, $parentTableName, $withAnalysis);
            $this->setListsWithAnalysis($user, SP, $option, $parentTableName, $withAnalysis);
        }
        $this->setPreviousAndTodayDate($user, $hasKey, $isEmployee, $option);
        if ($isSet) {
            Helpers::setKeyValueFromObj(
                $user,
                "userID",
                $this->getUserID($option)
            );
        }
        Helpers::setKeyValueFromObj(
            $user,
            "dateObject",
            $option->date
        );
        return $user;
    }
    private function setPreviousAndTodayDate(&$user, $hasKey, $isEmployee, Options $option, bool $setCreditAndDebit = true)
    {
        $date = $option->date;
        $dateDue = $option?->date?->unsetFrom();
        $previousDate = Date::getInstance()->getPreviousTo($date?->from);
        $preString = "previous";

        if ($setCreditAndDebit) {
            $date ? $this->setDue($user, DEBT, $hasKey, $previousDate, $option, $preString) : null;
            $date ? $this->setDue($user, CRED, $hasKey, $previousDate, $option, $preString) : null;

            $this->setDue($user, CRED, $hasKey, $date, $option,  null, "BalanceToday");
            $this->setDue($user, DEBT, $hasKey, $date, $option,  null, "BalanceToday");

            $this->setDue($user, CRED, $hasKey, $date ? $dateDue : null, $option);
            $this->setDue($user, DEBT, $hasKey, $date ? $dateDue : null, $option);
        }


        if ($isEmployee) {
            $date ? $this->setDue($user, SP, $hasKey, $previousDate, $option, $preString) : null;
            $date ? $this->setDue($user, INC, $hasKey, $previousDate, $option, $preString) : null;

            $this->setDue($user, SP, $hasKey, $date,  $option, null, "BalanceToday");
            $this->setDue($user, INC, $hasKey, $date,  $option, null, "BalanceToday");

            $this->setDue($user, SP, $hasKey, $date ? $dateDue : null, $option);
            $this->setDue($user, INC, $hasKey, $date ? $dateDue : null, $option);
        }
    }
    //todo customer
    //todo or employee that see every thing 
    //todo employee only sees his crdits
    //todo pending reservation and overdue and pending cut request not payed and overdue customers
    public function getDashboard(Options $options, $requireOnlyEmployeeRecords = true)
    {
        $date = $options->date;
        $iD = $this->getUserID($options);
        $isCustomer = $this->isCustomer($options);
        $isEmployee = $this->isEmployee($options);
        $tableName = $isCustomer ? CUST : EMP;
        $parentTableName = $isCustomer ? CUST : ($requireOnlyEmployeeRecords ? EMP : null);
        $withInterval = $options->hasNotFoundedColumn("interval") ? true : false;
        $withAnalysis = $options->hasNotFoundedColumn("withAnalysis") ? true : false;
        $hasKey = $isCustomer ? "CustomerID ='$iD'" : ($requireOnlyEmployeeRecords ? "EmployeeID ='$iD'" : null);

        $user = $this->view($tableName, $iD, null, Options::getInstance($options)->requireObjects());
        $option = Options::getInstance($options);

        $isSet = $this->setEmployeeOrCustomer($option, $requireOnlyEmployeeRecords);

        $option =
            $option
            ->withDate($date)
            ->requireObjects()
            ->requireDetails([
                ORDR_D,
                ORDR_R_D,
                PURCH_D,
                PURCH_R_D,
                PR_INPUT_D,
                PR_OUTPUT_D,
                TR_D,
                RI_D,
                CRS_D,
                CUT_RESULT
            ]);



        // $this->setLists($customer, ORDR, "OrderID", ORDR_R, ORDR_R_D,  $option, $parentTableName, $withAnalsis);
        // $this->setLists($customer, PURCH, "PurchaseID", PURCH_R, PURCH_R_D,  $option, $parentTableName, $withAnalsis);
        $this->setListsWithAnalysis($user, CRED, $option, $parentTableName, $withAnalysis);
        $this->setListsWithAnalysis($user, DEBT, $option, $parentTableName, $withAnalysis);


        if ($isEmployee) {
            $this->setListsWithAnalysis($user, INC, $option, $parentTableName, $withAnalysis);
            $this->setListsWithAnalysis($user, SP, $option, $parentTableName, $withAnalysis);
            $this->setListsWithAnalysis($user, PR_INPUT, $option, $parentTableName, $withAnalysis);
            $this->setListsWithAnalysis($user, PR_OUTPUT, $option, $parentTableName, $withAnalysis);
            $this->setListsWithAnalysis($user, TR, $option, $parentTableName, $withAnalysis);
        }
        $this->setListsWithAnalysis($user, RI,  $option, $parentTableName, $withAnalysis);
        $this->setListsWithAnalysis($user, CRS,  $option, $parentTableName, false);
        $this->setListsWithAnalysis($user, CUT,  $option, $parentTableName, $withAnalysis);

        $this->setPreviousAndTodayDate($user, $hasKey, $isEmployee, $option);

        if ($isSet) {
            Helpers::setKeyValueFromObj(
                $user,
                "userID",
                $this->getUserID($option)
            );
        }
        Helpers::setKeyValueFromObj(
            $user,
            "dateObject",
            $option->date
        );
        return $user;
    }
    private function getNotUsedRecordsQuery($tableName, ?Options $option = null)
    {
        $forginsDetails = $this->getCachedForginList($tableName);
        if (is_null($forginsDetails) || empty($forginsDetails)) {
            return array();
        }
        $i = 0;
        $query = "SELECT iD FROM $tableName a
            WHERE NOT EXISTS";
        $subQuery = "";

        foreach ($forginsDetails as $forgin) {
            $forginKeyColName = $forgin["COLUMN_NAME"];
            $forginTableName = $forgin["TABLE_NAME"];
            $subQuery = $subQuery . " " . (($i === 0) ? " " : "AND NOT EXISTS") . " (SELECT NULL FROM $forginTableName r WHERE  r.$forginKeyColName = a.iD) ";
            $i++;
        }
        $query = $query . $subQuery . str_replace("WHERE", " AND ", ($option?->getQuery($tableName, "a") ?? ""));
        //  echo $query;
        return $query;
    }
    public function deleteNotUsedRecords($tableName, Options $option)
    {
        $results = $this->getFetshAllTableWithQuery($this->getNotUsedRecordsQuery($tableName, $option));
        $results = array_map(function ($tmp) {
            return $tmp['iD'];
        }, $results);
        // if (($key = array_search($del_val, $messages)) !== false) {
        //     unset($messages[$key]);
        // }

        $arr = $option?->getRequestColumnValue("iD");
        if (!empty($arr)) {
            $results = Helpers::removeAllNonFoundInTowArray($arr, $results, true);
            $option = $option->setOrChangeRequestColumnValue("iD", $results);
        } else {
            $option = $option->setOrChangeRequestColumnValue("iD", $results);
        }
        return $this->delete($tableName, null, $option);
    }
    public function getNotUsedRecords($tableName, Options $option)
    {

        $id = $this->getFetshAllTableWithQuery($this->getNotUsedRecordsQuery($tableName));
        $id = array_map(function ($tmp) {
            return $tmp['iD'];
        }, $id);
        $results = array();
        if (empty($id)) {
            return $results;
        }
        $results['list'] = $id;
        if ($option->isSetRequestColumnsKeyNonFound("requireObjects")) {
            $results['listObjects'] = $this->list($tableName, null, $option->addStaticQuery(Helpers::getIDsWhereIN($id, ID, false)));
        }
        return $results;
    }
}
