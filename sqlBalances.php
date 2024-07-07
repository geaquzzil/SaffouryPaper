<?php
function getBalanceDue($CCID){
	return getFetshTableWithQuery("SELECT customers.iD,
		customers.name,
		COALESCE(credits.sumPay,0) AS sumPay,
		COALESCE(debits.Sum_eq,0) AS Sum_eq,
		COALESCE(orders.Sum_ExtendedPrice,0) AS Sum_ExtendedPrice,
		COALESCE(purchases.Sum_sumPurchuses,0) AS Sum_sumPurchuses,
		(
			(COALESCE(orders.Sum_ExtendedPrice, 0) + COALESCE(debits.Sum_eq, 0))
			-
			(COALESCE(credits.sumPay, 0) + COALESCE(purchases.Sum_sumPurchuses, 0))
			
		) AS balance FROM customers
		
		LEFT JOIN 
			(
				SELECT equality_credits.CustomerID,
				Sum(equality_credits.value) AS sumPay
				FROM equality_credits
				GROUP BY equality_credits.CustomerID
			) credits ON customers.iD = credits.CustomerID
			
		LEFT JOIN 
			(
				SELECT equality_debits.CustomerID,
				Sum(equality_debits.value) AS Sum_eq
				FROM equality_debits
				GROUP BY equality_debits.CustomerID
			) debits ON customers.iD = debits.CustomerID
			
		LEFT JOIN 
			(
				SELECT extended_order_refund.CustomerID,
				Sum(extended_order_refund.extendedNetPrice) AS Sum_ExtendedPrice
				FROM extended_order_refund
				GROUP BY extended_order_refund.CustomerID
			) orders ON customers.iD = orders.CustomerID
			
		LEFT JOIN 
			(
				SELECT extended_purchases_refund.CustomerID,
				Sum(extended_purchases_refund.extendedNetPrice) AS Sum_sumPurchuses
				FROM extended_purchases_refund
				GROUP BY extended_purchases_refund.CustomerID
			) purchases ON customers.iD = purchases.CustomerID
			
		 WHERE customers.iD='$CCID'
		 
		 ");
}
function getBalanceDueTo($CCID,$to){
	return getFetshTableWithQuery("SELECT customers.iD,
		customers.name,
		COALESCE(credits.sumPay,0) AS sumPay,
		COALESCE(debits.Sum_eq,0) AS Sum_eq,
		COALESCE(orders.Sum_ExtendedPrice,0) AS Sum_ExtendedPrice,
		COALESCE(purchases.Sum_sumPurchuses,0) AS Sum_sumPurchuses,
		(
			(COALESCE(orders.Sum_ExtendedPrice, 0) + COALESCE(debits.Sum_eq, 0))
			-
			(COALESCE(credits.sumPay, 0) + COALESCE(purchases.Sum_sumPurchuses, 0))
			
		) AS balance FROM customers
		
		LEFT JOIN 
			(
				SELECT equality_credits.CustomerID,
				Sum(equality_credits.value) AS sumPay
				FROM equality_credits WHERE Date(equality_credits.date) <= '$to'
				GROUP BY equality_credits.CustomerID
			) credits ON customers.iD = credits.CustomerID
			
		LEFT JOIN 
			(
				SELECT equality_debits.CustomerID,
				Sum(equality_debits.value) AS Sum_eq
				FROM equality_debits WHERE Date(equality_debits.date) <= '$to'
				GROUP BY equality_debits.CustomerID
			) debits ON customers.iD = debits.CustomerID
			
		LEFT JOIN 
			(
				SELECT extended_order_refund.CustomerID,
				Sum(extended_order_refund.extendedNetPrice) AS Sum_ExtendedPrice
				FROM extended_order_refund  WHERE Date(extended_order_refund.date) <= '$to'
				GROUP BY extended_order_refund.CustomerID
			) orders ON customers.iD = orders.CustomerID
			
		LEFT JOIN 
			(
				SELECT extended_purchases_refund.CustomerID,
				Sum(extended_purchases_refund.extendedNetPrice) AS Sum_sumPurchuses
				FROM extended_purchases_refund WHERE Date(extended_purchases_refund.date) <= '$to'
				GROUP BY extended_purchases_refund.CustomerID
			) purchases ON customers.iD = purchases.CustomerID
			
		WHERE customers.iD='$CCID'
		 ");
}
function getBalanceDueFromTo($CCID,$from,$to){
	return getFetshTableWithQuery("SELECT customers.iD,
		customers.name,
		COALESCE(credits.sumPay,0) AS sumPay,
		COALESCE(debits.Sum_eq,0) AS Sum_eq,
		COALESCE(orders.Sum_ExtendedPrice,0) AS Sum_ExtendedPrice,
		COALESCE(purchases.Sum_sumPurchuses,0) AS Sum_sumPurchuses,
		(
			(COALESCE(orders.Sum_ExtendedPrice, 0) + COALESCE(debits.Sum_eq, 0))
			-
			(COALESCE(credits.sumPay, 0) + COALESCE(purchases.Sum_sumPurchuses, 0))
			
		) AS balance FROM customers
		
		LEFT JOIN 
			(
				SELECT equality_credits.CustomerID,
				Sum(equality_credits.value) AS sumPay
				FROM equality_credits WHERE Date(equality_credits.date)  >= '$from' AND Date(equality_credits.date)<= '$to'
				GROUP BY equality_credits.CustomerID
			) credits ON customers.iD = credits.CustomerID
			
		LEFT JOIN 
			(
				SELECT equality_debits.CustomerID,
				Sum(equality_debits.value) AS Sum_eq
				FROM equality_debits WHERE Date(equality_debits.date)  >= '$from' AND Date(equality_debits.date)<= '$to'
				GROUP BY equality_debits.CustomerID
			) debits ON customers.iD = debits.CustomerID
			
		LEFT JOIN 
			(
				SELECT extended_order_refund.CustomerID,
				Sum(extended_order_refund.extendedNetPrice) AS Sum_ExtendedPrice
				FROM extended_order_refund  WHERE Date(extended_order_refund.date) >= '$from' AND Date(extended_order_refund.date) <= '$to'
				GROUP BY extended_order_refund.CustomerID
			) orders ON customers.iD = orders.CustomerID
			
		LEFT JOIN 
			(
				SELECT extended_purchases_refund.CustomerID,
				Sum(extended_purchases_refund.extendedNetPrice) AS Sum_sumPurchuses
				FROM extended_purchases_refund WHERE Date(extended_purchases_refund.date)  >= '$from' AND Date(extended_purchases_refund.date)<= '$to'
				GROUP BY extended_purchases_refund.CustomerID
			) purchases ON customers.iD = purchases.CustomerID
			
		WHERE customers.iD='$CCID'
	");
}
function getBalanceAll(){
    $Query="SELECT ((SELECT SUM(extendedNetPrice) FROM extended_order_refund)  + (SELECT SUM(value) FROM equality_debits)) -  ( (SELECT SUM(value) FROM equality_credits) -(SELECT SUM(extendedNetPrice) FROM extended_purchases_refund) ) as balance";
	return getFetshTableWithQuery($Query);
}
function getBalanceAllByTable($tableName){
    $Query="";
    switch($tableName){
        case ORDR:
            $Query="(SELECT SUM(extendedNetPrice) as balance FROM extended_order_refund)";
            break;
        case CRED:
            $Query="(SELECT SUM(value) as balance FROM equality_credits)";
            break;
        case DEBT:
            $Query="(SELECT SUM(value) as balance FROM equality_debits)";
            break;
        case PURCH:
            $Query="(SELECT SUM(extendedNetPrice)as balance FROM extended_purchases_refund) ";
            break;
            
    }
    	return getFetshTableWithQuery($Query);
}


?>