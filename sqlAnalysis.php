<?php
function getGrowthRateBeforeDate($tableName,$toFind,$before){
    $iD=getUserID();
    return getFetshAllTableWithQuery("SELECT Year($tableName.`date`) AS `year`,
		Month(`$tableName`.date) AS month,
		COALESCE( round(Sum($tableName.`$toFind`),3) ,0) AS `total`
		FROM $tableName   ".(isCustomer()? " WHERE CustomerID= '$iD' && Date(`date`) <= '$before' ":" Date(`date`) <= '$before'  " )."
		GROUP BY Year($tableName.`date`),Month($tableName.`date`)
		ORDER BY `year`,
		month"
		);
}
function getGrowthRateByQuery($tableName,$toFind,$whereQuery){
    $iD=getUserID();
    return getFetshAllTableWithQuery("SELECT Year($tableName.`date`) AS `year`,
		Month(`$tableName`.date) AS month,
		COALESCE( round(Sum($tableName.`$toFind`),3) ,0) AS `total`
		FROM $tableName   ".(isCustomer()? " WHERE CustomerID= '$iD' AND $whereQuery":"WHERE  $whereQuery" )."
		GROUP BY Year($tableName.`date`),Month($tableName.`date`)
		ORDER BY `year`,
		month "
		);
}
function getGrowthRateByInvoiceDetailsQuery($tableName,$detailsTable,$joinID,$toFind,$whereQuery){
    $iD=getUserID();
    $qurey="SELECT Year($tableName.`date`) AS `year`,
		Month(`$tableName`.date) AS month,
		COALESCE( round(Sum(`$detailsTable`.`$toFind`),3) ,0) AS `total`
		FROM `$detailsTable`   
		INNER JOIN `$tableName` ON `$tableName`.`iD` = `$detailsTable`.`$joinID`
		".(isCustomer()? " WHERE CustomerID= '$iD' AND $whereQuery":"WHERE  $whereQuery" )."
		GROUP BY Year(`$tableName`.`date`),Month(`$tableName`.`date`)
		ORDER BY `year`,
		month ";
	//	echo $qurey;
    return getFetshAllTableWithQuery($qurey	);
}
function getGrowthRateAfterAndBeforeCount($tableName,$after,$before){
    $iD=getUserID();
    return getFetshAllTableWithQuery("SELECT Year($tableName.`date`) AS `year`,
		Month(`$tableName`.date) AS month,
		Count(*) AS `total`
		FROM $tableName   ".(isCustomer()? " WHERE CustomerID= '$iD' &&  ( (Date(`date`) >= '$after') AND (Date(`date`) <= '$before') )":"WHERE   (  (Date(`date`) >= '$after') AND (Date(`date`) <= '$before') ) " )."
		GROUP BY Year(`$tableName`.`date`),Month(`$tableName`.`date`)
		ORDER BY `year`,
		month
		"
		);
}
function getGrowthRateAfterAndBefore($tableName,$toFind,$after,$before){
    $iD=getUserID();
    return getFetshAllTableWithQuery("SELECT Year($tableName.`date`) AS `year`,
		Month(`$tableName`.date) AS month,
		COALESCE( round(Sum($tableName.`$toFind`),3) ,0) AS `total`
		FROM $tableName   ".(isCustomer()? " WHERE CustomerID= '$iD' &&  ( (Date(`date`) >= '$after') AND (Date(`date`) <= '$before') )":"WHERE   (  (Date(`date`) >= '$after') AND (Date(`date`) <= '$before') ) " )."
		GROUP BY Year(`$tableName`.`date`),Month(`$tableName`.`date`)
		ORDER BY `year`,
		month"
		);
}
function getGrowthRateAfterAndBeforeWithWhereQuery($tableName,$toFind,$after,$before,$query){
    $iD=getUserID();
    return getFetshAllTableWithQuery("SELECT Year($tableName.`date`) AS `year`,
		Month(`$tableName`.date) AS month,
		COALESCE( round(Sum($tableName.`$toFind`),3) ,0) AS `total`
		FROM $tableName   ".(isCustomer()? " WHERE CustomerID= '$iD' &&  ( (Date(`date`) >= '$after') AND (Date(`date`) <= '$before') )":"WHERE   (  (Date(`date`) >= '$after') AND (Date(`date`) <= '$before') ) " )."
		AND $query GROUP BY Year(`$tableName`.`date`),Month(`$tableName`.`date`)
		ORDER BY `year`,
		month"
		);
}
function getGrowthRateAfterAndBeforeWithWhereQueryCount($tableName,$after,$before,$query){
    $iD=getUserID();
    return getFetshAllTableWithQuery("SELECT Year($tableName.`date`) AS `year`,
		Month(`$tableName`.date) AS month,
		Count(*) AS `total`
		FROM $tableName   ".(isCustomer()? " WHERE CustomerID= '$iD' &&  ( (Date(`date`) >= '$after') AND (Date(`date`) <= '$before') )":"WHERE   (  (Date(`date`) >= '$after') AND (Date(`date`) <= '$before') ) " )."
		AND $query GROUP BY Year(`$tableName`.`date`),Month(`$tableName`.`date`)
		ORDER BY `year`,
		month"
		);
}
function getGrowthRateAfterAndBeforeDaysInterval($tableName,$toFind,$after,$before){
    $iD=getUserID();
    return getFetshAllTableWithQuery("SELECT Year($tableName.`date`) AS `year`,
		Month(`$tableName`.date) AS month,
		Day(`$tableName`.date) As day,
		COALESCE( round(Sum($tableName.`$toFind`),3) ,0) AS `total`
		FROM $tableName   ".(isCustomer()? " WHERE CustomerID= '$iD' &&  ( (Date(`date`) >= '$after') AND (Date(`date`) <= '$before') )":"WHERE   (  (Date(`date`) >= '$after') AND (Date(`date`) <= '$before') ) " )."
		GROUP BY Year(`$tableName`.`date`),Month(`$tableName`.`date`),Day(`$tableName`.date)
		ORDER BY `year`,month,day"
		);
}
function getGrowthRateAfterAndBeforeDaysIntervalWithWhereQuery($tableName,$toFind,$after,$before,$whereQuery){
    $iD=getUserID();
    return getFetshAllTableWithQuery("SELECT Year($tableName.`date`) AS `year`,
		Month(`$tableName`.date) AS month,
		Day(`$tableName`.date) As day,
		COALESCE( round(Sum($tableName.`$toFind`),3) ,0) AS `total`
		FROM $tableName   ".(isCustomer()? " WHERE CustomerID= '$iD' &&  ( (Date(`date`) >= '$after') AND (Date(`date`) <= '$before') )":"WHERE   (  (Date(`date`) >= '$after') AND (Date(`date`) <= '$before') ) " )."
		AND $whereQuery GROUP BY Year(`$tableName`.`date`),Month(`$tableName`.`date`),Day(`$tableName`.date)
		ORDER BY `year`,month,day"
		);
}
function getGrowthRateAfterAndBeforeWithGroup($tableName,$toFind,$groupText,$after,$before){
 $iD=getUserID();
    return getFetshAllTableWithQuery("SELECT Year($tableName.`date`) AS `year`,
		Month(`$tableName`.date) AS month,
		COALESCE( round(Sum($tableName.`$toFind`),3) ,0) AS `total`
		FROM $tableName   ".(isCustomer()? " WHERE CustomerID= '$iD' &&  ( (Date(`date`) >= '$after') AND (Date(`date`) <= '$before') )":"WHERE   (  (Date(`date`) >= '$after') AND (Date(`date`) <= '$before') ) " )."
		GROUP BY Year($tableName.`date`),Month($tableName.`date`),$groupText 
		ORDER BY `year`,
		month"
		);
    
    
}
function getGrowthRate($tableName,$toFind){
    $iD=getUserID();
    return getFetshAllTableWithQuery("SELECT Year($tableName.`date`) AS `year`,
		Month(`$tableName`.date) AS month,
		COALESCE( round(Sum($tableName.`$toFind`),3) ,0) AS `total`
		FROM $tableName   " .(isCustomer()? " WHERE CustomerID= '$iD'  ":" " ) .   "
		GROUP BY Year($tableName.`date`),Month($tableName.`date`)
		ORDER BY `year`,
		month"
		);
}
//view should be 
//SELECT Year(equality_credits.`date`) AS `year`,
//		Month(`equality_credits`.date) AS month,
//		COALESCE( round(Sum(equality_credits.`value`),3) ,0) AS `total`
//		FROM equality_credits WHERE CustomerID= '25' 
//		GROUP BY Year(equality_credits.`date`),Month(equality_credits.`date`)
//		ORDER BY `year`,
//		month 
function getGrowthRateWithCustomerID($tableName,$toFind,$iD){
    return getFetshAllTableWithQuery("SELECT Year($tableName.`date`) AS `year`,
		Month(`$tableName`.date) AS month,
		COALESCE( round(Sum($tableName.`$toFind`),3) ,0) AS `total`
		FROM $tableName  WHERE CustomerID= '$iD'  
		GROUP BY Year($tableName.`date`),Month($tableName.`date`)
		ORDER BY `year`,
		month"
		);
}
function getGrowthRateWithCustomerIDAfterAndBefore($tableName,$toFind,$iD,$after,$before){
    return getFetshAllTableWithQuery("SELECT Year($tableName.`date`) AS `year`,
		Month(`$tableName`.date) AS month,
		COALESCE( round(Sum($tableName.`$toFind`),3) ,0) AS `total`
		FROM $tableName  WHERE CustomerID= '$iD'  &&  ( (Date(`date`) >= '$after') AND (Date(`date`) <= '$before') )
		GROUP BY Year($tableName.`date`),Month($tableName.`date`)
		ORDER BY `year`,
		month"
		);
}

?>