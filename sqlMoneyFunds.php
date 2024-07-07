<?php
function balanceDuePrevious($tableName,$date){
    // echo $date;
    // die;
	switch($tableName){
		default:
		if(!is_numeric($date)){
			return getFetshALLTableWithQuery("SELECT 
				currency.name AS currency,$tableName.CashBoxID ,
				Sum($tableName.value) AS sum
				FROM currency
				JOIN equalities ON currency.iD = equalities.CurrencyID
				LEFT JOIN $tableName ON equalities.iD = $tableName.EqualitiesID
				WHERE (Date($tableName.date) < '$date') GROUP BY currency.name,$tableName.CashBoxID
			");
		}else{
			return getFetshALLTableWithQuery("SELECT currency.name AS currency,$tableName.CashBoxID ,
				Sum($tableName.value) AS sum
				FROM currency
				JOIN equalities ON currency.iD = equalities.CurrencyID
				LEFT JOIN $tableName ON equalities.iD = $tableName.EqualitiesID
				
				
				WHERE (month($tableName.date) < '$date') GROUP BY currency.name,$tableName.CashBoxID 
			");
		}
		case DEBT:case CRED:case SP:case INC:
				if(!is_numeric($date)){
			return getFetshALLTableWithQuery("SELECT currency.name AS currency,$tableName.CashBoxID ,
				Sum($tableName.value) AS sum
				FROM currency
				JOIN equalities ON currency.iD = equalities.CurrencyID
				LEFT JOIN $tableName ON equalities.iD = $tableName.EqualitiesID
				WHERE (Date($tableName.date) < '$date') AND  (FromBox is NULL OR FromBox=0) GROUP BY currency.name,$tableName.CashBoxID ");
			}
			return getFetshALLTableWithQuery("SELECT currency.name AS currency,$tableName.CashBoxID ,
			Sum($tableName.value) AS sum
			FROM currency
			JOIN equalities ON currency.iD = equalities.CurrencyID
			LEFT JOIN $tableName ON equalities.iD = $tableName.EqualitiesID
			WHERE (month($tableName.date) < '$date') AND  (FromBox is NULL OR FromBox=0) GROUP BY currency.name,$tableName.CashBoxID ");
			
		}
	}
function balanceDueFromTo($tableName,$date,$to){
	    switch($tableName)
		{
			default:
			if(!is_numeric($date)){
			return getFetshALLTableWithQuery("SELECT currency.name AS currency,$tableName.CashBoxID ,
			Sum($tableName.value) AS sum
			FROM currency
			JOIN equalities ON currency.iD = equalities.CurrencyID
			LEFT JOIN $tableName ON equalities.iD = $tableName.EqualitiesID
			WHERE (Date($tableName.date) >= '$date') AND (Date($tableName.date) <= '$to') GROUP BY currency.name,$tableName.CashBoxID ");}
			return getFetshALLTableWithQuery("SELECT currency.name AS currency,$tableName.CashBoxID ,
			Sum($tableName.value) AS sum
			FROM currency
			JOIN equalities ON currency.iD = equalities.CurrencyID
			LEFT JOIN $tableName ON equalities.iD = $tableName.EqualitiesID
			WHERE (month($tableName.date) >= '$date') AND (month($tableName.date) <= '$to') GROUP BY currency.name,$tableName.CashBoxID ");
			case DEBT:case CRED:case SP:case INC:
				if(!is_numeric($date)){
			return getFetshALLTableWithQuery("SELECT currency.name AS currency,$tableName.CashBoxID ,
			Sum($tableName.value) AS sum
			FROM currency
			JOIN equalities ON currency.iD = equalities.CurrencyID
			LEFT JOIN $tableName ON equalities.iD = $tableName.EqualitiesID
			WHERE (Date($tableName.date) >= '$date') AND (Date($tableName.date) <= '$to') AND  (FromBox is NULL OR FromBox=0) GROUP BY currency.name,$tableName.CashBoxID ");
				}
			return getFetshALLTableWithQuery("SELECT currency.name AS currency,
			Sum($tableName.value) AS sum
			FROM currency
			JOIN equalities ON currency.iD = equalities.CurrencyID
			LEFT JOIN $tableName ON equalities.iD = $tableName.EqualitiesID
			WHERE (month($tableName.date) >= '$date') AND (month($tableName.date) <= '$to')  AND (FromBox is NULL OR FromBox=0) GROUP BY currency.name,$tableName.CashBoxID ");
			
		}
	}
	function balanceFromToAccountName($tableName,$FROM,$TO){
	 
			return getFetshALLTableWithQuery("SELECT account_names.name AS name,
			Sum($tableName.value) AS sum
			FROM account_names
			JOIN $tableName ON $tableName.NameID = account_names.iD
			WHERE (Date($tableName.date) >= '$FROM') AND (Date($tableName.date) <= '$TO')  GROUP BY $tableName.NameID "
			//adding grouped by NameID
			
			);
				
// 			return getFetshALLTableWithQuery("SELECT account_names.name AS name,
// 			Sum($tableName.value) AS sum
// 			FROM account_names
// 			JOIN $tableName ON $tableName.NameID = account_names.iD
// 			WHERE (month($tableName.date) >= '$FROM') AND (month($tableName.date) <= '$TO')  GROUP BY $tableName.NameID "
// 			//adding grouped by NameID
			
// 			);   
	}
function balanceDue($tableName,$date){
		switch($tableName)
		{   
			default:
			if(!is_numeric($date)){
			return getFetshALLTableWithQuery("SELECT currency.name AS currency,$tableName.CashBoxID ,
			Sum($tableName.value) AS sum
			FROM currency
			JOIN equalities ON currency.iD = equalities.CurrencyID
			LEFT JOIN $tableName ON equalities.iD = $tableName.EqualitiesID
			WHERE (Date($tableName.date) <= '$date') GROUP BY currency.name ,$tableName.CashBoxID ");}
			return getFetshALLTableWithQuery("SELECT currency.name AS currency,$tableName.CashBoxID ,
			Sum($tableName.value) AS sum
			FROM currency
			JOIN equalities ON currency.iD = equalities.CurrencyID
			LEFT JOIN $tableName ON equalities.iD = $tableName.EqualitiesID
			WHERE (month($tableName.date) <= '$date') GROUP BY currency.name,$tableName.CashBoxID ");
			case DEBT:case CRED:case SP:case INC:
				if(!is_numeric($date)){
			return getFetshALLTableWithQuery("SELECT currency.name AS currency,$tableName.CashBoxID ,
			Sum($tableName.value) AS sum
			FROM currency
			JOIN equalities ON currency.iD = equalities.CurrencyID
			LEFT JOIN $tableName ON equalities.iD = $tableName.EqualitiesID
			WHERE (Date($tableName.date) <= '$date') AND  (FromBox is NULL OR FromBox=0) GROUP BY currency.name,$tableName.CashBoxID ");
				}
			return getFetshALLTableWithQuery("SELECT currency.name AS currency,$tableName.CashBoxID ,
			Sum($tableName.value) AS sum
			FROM currency
			JOIN equalities ON currency.iD = equalities.CurrencyID
			LEFT JOIN $tableName ON equalities.iD = $tableName.EqualitiesID
			WHERE (month($tableName.date) <= '$date') AND  (FromBox is NULL OR FromBox=0) GROUP BY currency.name,$tableName.CashBoxID ");
			
		}
	
	}
?>