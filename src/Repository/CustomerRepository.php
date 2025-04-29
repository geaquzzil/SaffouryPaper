<?php



namespace Etq\Restful\Repository;

use Etq\Restful\Helpers;
use Etq\Restful\Repository\BaseRepository;



class CustomerRepository extends BaseRepository
{
    private $cachedCustomerBalances = [];

    private function getCachedCustomerBalance(int $iD, ?Date $date = null)
    {
        $key = $iD . ($date?->getQuery() ?? "");
        if (key_exists($key, $this->cachedCustomerBalances)) {
            return $this->cachedCustomerBalances[$key];
        } else {
            $this->cachedCustomerBalances[$key] = $this->getBalance($iD, $date);
            return $this->cachedCustomerBalances[$key];
        }
    }
    public function invoicesOverduesAndDesposited($From, $To)
    {
        $result = array();
        $response = array();
        $response["notDueYet"] = 0;
        $response["overDue"] = 0;
        $response["notDisposited"] = 0;
        $response["desposited"] = 0;
        $response["paid"] = 0;
        $response["unpaid"] = 0;
        //not due yet
        $Query = "SELECT 
	    extended_order_refund.iD AS OrderID,
	    customers.iD,
	    customers.name,
		extended_order_refund.termsDate AS termsDate,
		extended_order_refund.extendedNetPrice
		
		FROM customers
		INNER JOIN extended_order_refund ON customers.iD = extended_order_refund.CustomerID  
	    WHERE (Date(extended_order_refund.termsDate)) >= (CurDate())";
        $result = $this->getFetshALLTableWithQuery($Query);
        $resultID = array_map(function ($tmp) {
            return $tmp['iD'];
        }, $result);
        if (is_array($result)) {
            for ($x = 0; $x < count($result); $x++) {
                $balance = getBalanceDueTo($result[$x]['iD'], curdate())['balance'];
                if ($balance <= 0) {
                    unset($result[$x]);
                } else {

                    $response["notDueYet"] += $result[$x]["extendedNetPrice"];
                }
            }
        }


        //over due
        $Query = "SELECT 
	    customers.iD,
	    customers.name,
		extended_order_refund.termsDate AS termsDate	,
		extended_order_refund.extendedNetPrice
		
		FROM customers
		INNER JOIN extended_order_refund ON customers.iD = extended_order_refund.CustomerID
		 WHERE (Date(extended_order_refund.termsDate)) <= (CurDate())";
        $result = getFetshALLTableWithQuery($Query);
        $resultID = array_map(function ($tmp) {
            return $tmp['iD'];
        }, $result);
        if (is_array($result)) {
            for ($x = 0; $x < count($result); $x++) {
                $toDate = date("Y-m-d", strtotime($result[$x]['termsDate']));
                $balance = getBalanceDueTo($result[$x]['iD'], $toDate)["balance"];
                if ($balance <= 0) {
                    unset($result[$x]);
                } else {
                    $response["overDue"] += $result[$x]["extendedNetPrice"];
                }
            }
        }
        $response["unpaid"] = $response["overDue"] + $response["notDueYet"];
        $orders = getBalanceAllByTable(ORDR)['balance'];
        $purchases = getBalanceAllByTable(PURCH)['balance'];
        $debits = getBalanceAllByTable(DEBT)['balance'];
        $credits = getBalanceAllByTable(CRED)['balance'];
        // print_r($allBalances);
        $response["paid"] = $purchases + $credits;
        $response["desposited"] = $credits;
        $response["notDisposited"] = ($credits + $purchases) - ($debits + $orders);
        return $response;
    }
    public  function customersTerms($iD)
    {
        $result = array();
        $response = array();
        $Query = "SELECT 
	    customers.iD,
	    extended_order_refund.iD AS OrderID,
		extended_order_refund.termsDate AS termsDate
		FROM customers
		INNER JOIN extended_order_refund ON customers.iD = extended_order_refund.CustomerID
		";

        $Query = is_null($iD) ?  $Query : $Query . " WHERE customers.iD='$iD'";
        //echo $Query;

        $result = getFetshALLTableWithQuery($Query);
        if (is_array($result)) {
            for ($x = 0; $x < count($result); $x++) {
                $balance = getBalanceDueTo($result[$x]['iD'], $result[$x]['termsDate'])['balance'];
                if ($balance <= 0) {
                    unset($result[$x]);
                } else {
                    array_push($response, $result[$x]);
                }
            }
        }
        return $response;
    }
    public function notPayedCustomers()
    {
        $result = array();
        $response = array();
        $Query = "SELECT 
	    customers.iD,
	    customers.name,
		extended_order_refund.termsDate AS termsDate
		FROM customers
		INNER JOIN extended_order_refund ON customers.iD = extended_order_refund.CustomerID
		 WHERE (Date(extended_order_refund.termsDate)) <= (CurDate())";
        $result = getFetshALLTableWithQuery($Query);
        $resultID = array_map(function ($tmp) {
            return $tmp['iD'];
        }, $result);
        if (is_array($result)) {
            for ($x = 0; $x < count($result); $x++) {
                $toDate = date("Y-m-d", strtotime($result[$x]['termsDate']));
                $balance = getBalanceDueTo($result[$x]['iD'], $toDate)["balance"];
                if ($balance <= 0) {
                    unset($result[$x]);
                } else {
                    $customer = depthSearch($result[$x]['iD'], CUST, 1, null, true, null);
                    $customer['termsDate'] = $result[$x]['termsDate'];
                    array_push($response, $customer);
                }
            }
        }
        return $response;
    }
    ///if date is null then current date is execution
    public function getNextPayment(?int $iD = null, ?Date $date = null, $requiresEqualsSign = false)
    {
        $dateQuery = $date ? $date->getQuery("extended_order_refund", "termsDate", $requiresEqualsSign)
            : "Date(extended_order_refund.termsDate) >= Date(NOW())";
        $result = array();
        $response = array();
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
        $result = $this->getFetshALLTableWithQuery($Query);
        if (is_array($result)) {
            for ($x = 0; $x < count($result); $x++) {
                $balance = $this->getCachedCustomerBalance($result[$x]['iD'], $date ? $date : Date::currentDate())[0]['balance'];
                if ($balance <= 0) {
                    unset($result[$x]);
                } else {
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

        // echo "$c $d $o $p";
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
        return getFetshTableWithQuery($Query);
    }
}
