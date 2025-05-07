<?php



namespace Etq\Restful\Repository;

use Etq\Restful\Helpers;
use Etq\Restful\Repository\BaseRepository;
use Etq\Restful\Repository\Options;
use Illuminate\Support\Arr;

class SharedDashboardAndCustomerRepo extends BaseRepository
{
    protected $cachedCustomerBalances = [];




    ///if date is null then current date is execution
    ///if 
    public function getNextAndOverDuePayment(?int $iD = null, ?Date $date = null, $requiresEqualsSign = false, $isOverDue = false)
    {
        $result = array();
        $response = array();
        $Query = $this->getTermsQuery($iD, $date, $requiresEqualsSign, $isOverDue);
        // echo $Query;
        $result = $this->getFetshALLTableWithQuery($Query);
        if (is_array($result)) {
            for ($x = 0; $x < count($result); $x++) {

                $balance = $this->getCachedCustomerBalance(
                    $result[$x]['iD'],
                    $date ? Date::to($result[$x]['termsDate']) : Date::currentDate()->unsetFrom()
                )[0]['balance'];
                if ($balance <= 0) {
                    unset($result[$x]);
                } else {
                    $result[$x]["balane"] = $balance;
                    array_push($response, $result[$x]);
                }
            }
        }
        return $response;
    }

    public function getBalance(?int $iD = null, ?Date $date = null)
    {

        $iDQuery = "";

        if ($iD) {
            $iDQuery = "WHERE customers.iD='$iD' ";
        }
        $c = $date ? "WHERE" . $date->getQuery('equality_credits') : "";
        $d = $date ? "WHERE" . $date->getQuery('equality_debits') : "";
        $o = $date ? "WHERE" . $date->getQuery('extended_order_refund') : "";
        $p = $date ? "WHERE" . $date->getQuery('extended_purchases_refund') : "";

        // echo "popo \n$c $d $o $p";
        // die;

        return $this->getFetshALLTableWithQuery("SELECT customers.iD,
            customers.name,
            COALESCE(credits.sumPay,0) AS totalCredits,
            COALESCE(debits.Sum_eq,0) AS totalDebits,
            COALESCE(orders.Sum_ExtendedPrice,0) AS totalOrders,
            COALESCE(purchases.Sum_sumPurchuses,0) AS totalPurchases,
            (
                (COALESCE(orders.Sum_ExtendedPrice, 0) + COALESCE(debits.Sum_eq, 0))
                -
                (COALESCE(credits.sumPay, 0) + COALESCE(purchases.Sum_sumPurchuses, 0))
                
            ) AS balance FROM customers
            
            LEFT JOIN 
                (
                    SELECT equality_credits.CustomerID,
                    Sum(equality_credits.value) AS sumPay
                    FROM equality_credits $c
                    GROUP BY equality_credits.CustomerID
                ) credits ON customers.iD = credits.CustomerID
                
            LEFT JOIN 
                (
                    SELECT equality_debits.CustomerID,
                    Sum(equality_debits.value) AS Sum_eq
                    FROM equality_debits $d
                    GROUP BY equality_debits.CustomerID
                ) debits ON customers.iD = debits.CustomerID
                
            LEFT JOIN 
                (
                    SELECT extended_order_refund.CustomerID,
                    Sum(extended_order_refund.extendedNetPrice) AS Sum_ExtendedPrice
                    FROM extended_order_refund $o
                    GROUP BY extended_order_refund.CustomerID
                ) orders ON customers.iD = orders.CustomerID
                
            LEFT JOIN 
                (
                    SELECT extended_purchases_refund.CustomerID,
                    Sum(extended_purchases_refund.extendedNetPrice) AS Sum_sumPurchuses
                    FROM extended_purchases_refund $p
                    GROUP BY extended_purchases_refund.CustomerID
                ) purchases ON customers.iD = purchases.CustomerID
                
             $iDQuery
             
             ");
    }
    public function getBalanceAll()
    {
        $Query = "SELECT ((SELECT SUM(extendedNetPrice) FROM extended_order_refund)  + (SELECT SUM(value) FROM equality_debits)) -  ( (SELECT SUM(value) FROM equality_credits) -(SELECT SUM(extendedNetPrice) FROM extended_purchases_refund) ) as balance";
        return $this->getFetshTableWithQuery($Query);
    }

    public function getBalanceAllByTable($tableName)
    {
        $Query = "";
        switch ($tableName) {
            case ORDR:
                $Query = "(SELECT SUM(extendedNetPrice) as balance FROM extended_order_refund)";
                break;
            case CRED:
                $Query = "(SELECT SUM(value) as balance FROM equality_credits)";
                break;
            case DEBT:
                $Query = "(SELECT SUM(value) as balance FROM equality_debits)";
                break;
            case PURCH:
                $Query = "(SELECT SUM(extendedNetPrice)as balance FROM extended_purchases_refund) ";
                break;
        }
        return $this->getFetshTableWithQuery($Query);
    }
    public function getBalanceFund($tableName, ?string  $staticWhere = null, ?Date $date = null)
    {
        $option = Options::getInstance()
            ->withStaticWhereQuery($staticWhere)
            ->addStaticQuery("(isDirect is NULL OR isDirect=0)")
            ->addStaticQuery("(FromBox is NULL OR FromBox=0)")
            ->withDate($date)->withGroupByArray(
                [
                    "currency.name"
                ]
            );

        $query = $option->getQuery($tableName);
        $query =  "SELECT 
                        currency.name AS currency,
                        Sum($tableName.value) AS sum
                    FROM 
                        currency
                    JOIN 
                        equalities ON currency.iD = equalities.CurrencyID
                    LEFT JOIN 
                    $tableName ON equalities.iD = $tableName.EqualitiesID
                    $query";


        return $this->getFetshALLTableWithQuery($query);
    }
    public function getTermsQuery(?int $iD = null, ?Date $date = null, $requiresEqualsSign = false, $isOverDue = false)
    {
        $sign = $isOverDue ? "<=" : ">=";
        $dateQuery = $date ? $date->getQuery("extended_order_refund", "termsDate", $requiresEqualsSign)
            : "Date(extended_order_refund.termsDate) $sign Date(NOW())";

        $Query = "
    SELECT 
        extended_order_refund.iD AS OrderID,
        customers.iD,
        customers.name,
        extended_order_refund.termsDate AS termsDate
    FROM
        customers
    INNER JOIN
        extended_order_refund ON customers.iD = extended_order_refund.CustomerID  
    WHERE
        $dateQuery";
        $Query = !$iD ?  $Query : $Query . " AND customers.iD='$iD'";
        return $Query;
    }
    public function getCachedCustomerBalance(int $iD, ?Date $date = null)
    {
        $key = $iD . ($date?->getQuery() ?? "");
        if (key_exists($key, $this->cachedCustomerBalances)) {
            return $this->cachedCustomerBalances[$key];
        } else {
            $this->cachedCustomerBalances[$key] = $this->getBalance($iD, $date);
            return $this->cachedCustomerBalances[$key];
        }
    }
    public function setDue(
        &$object,
        $tableName,
        ?string $staticWhere = null,
        ?Date $date = null,
        ?string $preString = null,
        string $postString = "Due",
    ) {
        $key = $preString ? $preString . $tableName . $postString : $tableName . $postString;
        Helpers::setKeyValueFromObj(
            $object,
            $key,
            $this->getBalanceFund($tableName, $staticWhere, $date)
        );
    }
    public function setLists(
        &$object,
        $tableName,
        $forginID,
        $detailTableName,
        $detailTableNameDetail,
        Options $option,
        ?string  $parentTableName,
        bool $withAnalysis = false,
    ) {

        Helpers::setKeyValueFromObj(
            $object,
            $tableName,
            $this->list($tableName, $parentTableName, $option)
        );

        $results = array_map(function ($tmp) {
            return $tmp['iD'];
        }, Helpers::getKeyValueFromObj($object, $tableName) ?? []);

        if (!empty($results)) {
            if ($withAnalysis) {
                Helpers::setKeyValueFromObj(
                    $object,
                    $tableName . "Analysis",
                    $this->getGrowthRate(($tableName), null, $option)
                );
            }
            $ids = implode("','", $results);
            $detailOption = Options::withStaticWhereQuery("$forginID IN ('$ids')")
                ->requireObjects()
                ->requireDetails([$detailTableNameDetail]);
            Helpers::setKeyValueFromObj(
                $object,
                $detailTableName,
                $this->list($detailTableName, $parentTableName, $detailOption)
            );
        } else {
            Helpers::setKeyValueFromObj(
                $object,
                $detailTableName,
                array()
            );
        }
    }
    public function setListsWithAnalysis(
        &$object,
        $tableName,
        Options $option,
        ?string  $parentTableName = null,
        bool $withAnalysis = false,

    ) {

        Helpers::setKeyValueFromObj(
            $object,
            $tableName,
            $this->list($tableName, $parentTableName, $option)
        );
        if ($withAnalysis) {
            Helpers::setKeyValueFromObj(
                $object,
                $tableName . "Analysis",
                $this->getGrowthRate(($tableName), null, $option)
            );
        }
    }
    public function setListsWithAnalysisByListByDetail(
        &$object,
        $tableName,
        $detailTableName,
        Options $option,
        ?string  $parentTableName = null,
        bool $withAnalysis = false,

    ) {

        Helpers::setKeyValueFromObj(
            $object,
            $tableName,
            $this->listByDetailListColumn($tableName, $detailTableName, $option)
        );
        if ($withAnalysis) {
        }
    }
    public function getProfits(Options $option)
    {

        return $this->getGrowthRate("profits_orders", "total", $option);
    }
}
