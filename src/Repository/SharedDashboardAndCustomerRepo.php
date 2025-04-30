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
    protected function getNextAndOverDuePayment(?int $iD = null, ?Date $date = null, $requiresEqualsSign = false, $isOverDue = false)
    {
        $result = array();
        $response = array();
        $Query = $this->getTermsQuery($iD, $date, $requiresEqualsSign, $isOverDue);
        echo $Query;
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
    protected function getBalance(?int $iD = null, ?Date $date = null)
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
    protected function getBalanceAll()
    {
        $Query = "SELECT ((SELECT SUM(extendedNetPrice) FROM extended_order_refund)  + (SELECT SUM(value) FROM equality_debits)) -  ( (SELECT SUM(value) FROM equality_credits) -(SELECT SUM(extendedNetPrice) FROM extended_purchases_refund) ) as balance";
        return $this->getFetshTableWithQuery($Query);
    }

    protected function getBalanceAllByTable($tableName)
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
    protected function getBalanceFund($tableName, $date)
    {

        switch ($tableName) {

            default:
                if (!is_numeric($date)) {
                    return getFetshALLTableWithQuery("SELECT currency.name AS currency,
			Sum($tableName.value) AS sum
			FROM currency
			JOIN equalities ON currency.iD = equalities.CurrencyID
			LEFT JOIN $tableName ON equalities.iD = $tableName.EqualitiesID
			WHERE (Date($tableName.date) <= '$date') GROUP BY currency.name  ");
                }
                return getFetshALLTableWithQuery("SELECT currency.name AS currency,
			Sum($tableName.value) AS sum
			FROM currency
			JOIN equalities ON currency.iD = equalities.CurrencyID
			LEFT JOIN $tableName ON equalities.iD = $tableName.EqualitiesID
			WHERE (month($tableName.date) <= '$date') GROUP BY currency.name  ");
            case DB_TABLE_DEBTS:
            case DB_TABLE_PAYMENTS:
            case DB_TABLE_SPENDING:
            case DB_TABLE_INCOMES:
                if (!is_numeric($date)) {
                    return getFetshALLTableWithQuery("SELECT currency.name AS currency,
			Sum($tableName.value) AS sum
			FROM currency
			JOIN equalities ON currency.iD = equalities.CurrencyID
			LEFT JOIN $tableName ON equalities.iD = $tableName.EqualitiesID
			WHERE (Date($tableName.date) <= '$date') AND (isDirect is NULL OR isDirect=0) AND (FromBox is NULL OR FromBox=0) GROUP BY currency.name  ");
                }
                return getFetshALLTableWithQuery("SELECT currency.name AS currency,
			Sum($tableName.value) AS sum
			FROM currency
			JOIN equalities ON currency.iD = equalities.CurrencyID
			LEFT JOIN $tableName ON equalities.iD = $tableName.EqualitiesID
			WHERE (month($tableName.date) <= '$date') AND (isDirect is NULL OR isDirect=0) AND (FromBox is NULL OR FromBox=0) GROUP BY currency.name  ");
        }
    }
    private function getTermsQuery(?int $iD = null, ?Date $date = null, $requiresEqualsSign = false, $isOverDue = false)
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
    protected function getCachedCustomerBalance(int $iD, ?Date $date = null)
    {
        $key = $iD . ($date?->getQuery() ?? "");
        if (key_exists($key, $this->cachedCustomerBalances)) {
            return $this->cachedCustomerBalances[$key];
        } else {
            $this->cachedCustomerBalances[$key] = $this->getBalance($iD, $date);
            return $this->cachedCustomerBalances[$key];
        }
    }
    protected function setLists(
        &$object,
        $tableName,
        $forginID,
        $detailTableName,
        $detailTableNameDetail,
        bool $withAnalysis = false,
        ?Options $option = null
    ) {

        Helpers::setKeyValueFromObj(
            $object,
            $tableName,
            $this->list($tableName, CUST, $option)
        );

        $results = array_map(function ($tmp) {
            return $tmp['iD'];
        }, Helpers::getKeyValueFromObj($object, $tableName) ?? []);

        if (!empty($results)) {
            if ($withAnalysis) {
                $iD = Helpers::getKeyValueFromObj($object, "iD");
                Helpers::setKeyValueFromObj(
                    $object,
                    $tableName . "Analysis",
                    $this->getGrowthRate(($tableName), null, "CustomerID ='$iD' ", $option?->date)
                );
            }
            $ids = implode("','", $results);
            $detailOption = Options::withStaticWhereQuery("$forginID IN ('$ids')")
                ->requireObjects()
                ->requireDetails([$detailTableNameDetail]);
            Helpers::setKeyValueFromObj(
                $object,
                $detailTableName,
                $this->list($detailTableName, CUST, $detailOption)
            );
        } else {
            Helpers::setKeyValueFromObj(
                $object,
                $detailTableName,
                array()
            );
        }
    }
    protected function setListsWithAnalysis(
        &$object,
        $tableName,
        ?string  $parentTableName = null,
        bool $withAnalysis = false,
        ?Options $option = null
    ) {
        Helpers::setKeyValueFromObj(
            $object,
            $tableName,
            $this->list($tableName, $parentTableName, $option)
        );
        if ($withAnalysis) {
            $iD = Helpers::getKeyValueFromObj($object, "iD");
            Helpers::setKeyValueFromObj(
                $object,
                $tableName . "Analysis",
                $this->getGrowthRate(($tableName), null, "CustomerID ='$iD' ", $option?->date)
            );
        }
    }
}
