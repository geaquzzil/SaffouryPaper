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
        $this->setEmployeeOrCustomer($option);
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
    }
    //todo customer
    //todo or employee that see every thing 
    //todo employee only sees his crdits
    public function getDashboard(Options $options, $requireOnlyEmployeeRecords = false)
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
        $this->setEmployeeOrCustomer($option);

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

        $dateDue = $option?->date?->unsetFrom();
        $previousDate = Date::getInstance()->getPreviousTo($date?->from);
        $preString = "previous";
        $date ? $this->setDue($user, DEBT, $hasKey, $previousDate, $preString) : null;
        $date ? $this->setDue($user, CRED, $hasKey, $previousDate, $preString) : null;

        $this->setDue($user, CRED, $hasKey, $date,  null, "BalanceToday");
        $this->setDue($user, DEBT, $hasKey, $date,  null, "BalanceToday");

        $this->setDue($user, CRED, $hasKey, $date ? $dateDue : null);
        $this->setDue($user, DEBT, $hasKey, $date ? $dateDue : null);



        if ($isEmployee) {
            $date ? $this->setDue($user, SP, $hasKey, $previousDate, $preString) : null;
            $date ? $this->setDue($user, INC, $hasKey, $previousDate, $preString) : null;

            $this->setDue($user, SP, $hasKey, $date,  null, "BalanceToday");
            $this->setDue($user, INC, $hasKey, $date,  null, "BalanceToday");

            $this->setDue($user, SP, $hasKey, $date ? $dateDue : null);
            $this->setDue($user, INC, $hasKey, $date ? $dateDue : null);
        }


        // if ($date) {
        //     $this->setDue($user, DEBT, $hasKey, $previousDate, $preString);
        //     $this->setDue($user, CRED, $hasKey, $previousDate, $preString);
        //     if (!$isCustomer) {
        //     }
        // }

        // if ($isCustomer) {


        //     $this->setDue($user, DEBT, $hasKey, $dateDue);
        //     $this->setDue($user, CRED, $hasKey, $dateDue);
        //     if ($date) {
        //     }
        // } else {

        //     $this->setDue($user, DEBT, $hasKey, $dateDue);
        //     $this->setDue($user, CRED, $hasKey, $dateDue);
        //     $this->setDue($user, INC, $hasKey, $dateDue);
        //     $this->setDue($user, SP, $hasKey, $dateDue);

        //     if ($date) {



        //         $this->setDue($user, DEBT, $hasKey, $previousDate, $preString);
        //         $this->setDue($user, CRED, $hasKey, $previousDate, $preString);
        //         $this->setDue($user, INC, $hasKey, $previousDate, $preString);
        //         $this->setDue($user, SP, $hasKey, $previousDate, $preString);


        //         $this->setDue($user, DEBT, $hasKey, $date, null, "BalanceToday");
        //         $this->setDue($user, CRED, $hasKey, $date,  null, "BalanceToday");
        //         $this->setDue($user, INC, $hasKey, $date,  null, "BalanceToday");
        //         $this->setDue($user, SP, $hasKey, $date,  null, "BalanceToday");
        //     }
        // }



        // $response[DEBT . "Due"] = balanceDue(DEBT, $TO);
        // 	$response[CRED . "Due"] = balanceDue(CRED, $TO);
        // 	$response[INC . "Due"] = balanceDue(INC, $TO);
        // 	$response[SP . "Due"] = balanceDue(SP, $TO);


        // 	$response[DEBT . "BalanceToday"] = balanceDueFromTo(DEBT, $FROM, $TO);
        // 	$response[CRED . "BalanceToday"] = balanceDueFromTo(CRED, $FROM, $TO);
        // 	$response[INC . "BalanceToday"] = balanceDueFromTo(INC, $FROM, $TO);
        // 	$response[SP . "BalanceToday"] = balanceDueFromTo(SP, $FROM, $TO);


        // 	if ($IsDate) {
        // 		$response["previous" . DEBT . "Due"] = balanceDuePrevious(DEBT, $FROM);
        // 		$response["previous" . CRED . "Due"] = balanceDuePrevious(CRED, $FROM);
        // 		$response["previous" . INC . "Due"] = balanceDuePrevious(INC, $FROM);
        // 		$response["previous" . SP . "Due"] = balanceDuePrevious(SP, $FROM);
        // 	}
        // $this->setListsWithAnalysis($user, DEBT, $parentTableName, $withAnalsis, $option);

        // $this->setLists($user, ORDR, "OrderID", ORDR_R, ORDR_R_D, $withAnalsis, $option);
        // $this->setLists($user, PURCH, "PurchaseID", PURCH_R, PURCH_R_D, $withAnalsis, $option);

        // $this->setListsWithAnalysis($user, RI, $parentTableName, $withAnalsis, $option);
        // $this->setListsWithAnalysis($user, CRS, $parentTableName, false, $option);
        // $this->setListsWithAnalysis($user, CUT, $parentTableName, $withAnalsis, $option);

        Helpers::setKeyValueFromObj(
            $customer,
            "dateObject",
            $user
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
        echo $query;
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

        $results = $this->getFetshAllTableWithQuery($this->getNotUsedRecordsQuery($tableName));
        $results = array_map(function ($tmp) {
            return $tmp['iD'];
        }, $results);
        return $results;
    }
}
