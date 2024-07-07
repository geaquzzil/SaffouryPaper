<?php
function inverntory($CCID){
	return getFetshALLTableWithQuery("SELECT 
		products.iD AS ProductID,
		warehouse.iD AS WarehouseID,

		(
		 (
			(COALESCE(inventory_purchases.sumq, 0) + COALESCE(inventory_orders_refund.sumq, 0))
			+
			(COALESCE(inventory_input.sumq, 0) + COALESCE(inventory_transfer_to.sumq, 0))
		 )
			-
		 (
			(COALESCE(inventory_orders.sumq, 0) + COALESCE(inventory_purchases_refund.sumq, 0))
			+
			(COALESCE(inventory_output.sumq, 0) + COALESCE(inventory_transfer_from.sumq, 0))
		 )
			
		) AS quantity
		FROM products  CROSS JOIN warehouse
		
		LEFT JOIN 
			(
				SELECT inventory_orders.ProductID,
				inventory_orders.WarehouseID,
				Sum(inventory_orders.quantity) AS sumq
				FROM inventory_orders
				GROUP BY inventory_orders.ProductID ,inventory_orders.WarehouseID
			) inventory_orders ON inventory_orders.ProductID = products.iD 
			AND  inventory_orders.WarehouseID= warehouse.iD
			
		LEFT JOIN 
			(
				SELECT inventory_purchases_refund.ProductID,
				inventory_purchases_refund.WarehouseID,
				Sum(inventory_purchases_refund.quantity) AS sumq
				FROM inventory_purchases_refund
				GROUP BY inventory_purchases_refund.ProductID ,inventory_purchases_refund.WarehouseID
			) inventory_purchases_refund ON inventory_purchases_refund.ProductID = products.iD 
			AND  inventory_purchases_refund.WarehouseID= warehouse.iD
			
		LEFT JOIN 
			(
				SELECT inventory_output.ProductID,
				inventory_output.WarehouseID,
				Sum(inventory_output.quantity) AS sumq
				FROM inventory_output
				GROUP BY inventory_output.ProductID ,inventory_output.WarehouseID
			) inventory_output ON inventory_output.ProductID = products.iD 
			AND  inventory_output.WarehouseID= warehouse.iD
			
		LEFT JOIN 
			(
				SELECT inventory_transfer_from.ProductID,
				inventory_transfer_from.WarehouseID,
				Sum(inventory_transfer_from.quantity) AS sumq
				FROM inventory_transfer_from
				GROUP BY inventory_transfer_from.ProductID ,inventory_transfer_from.WarehouseID
			) inventory_transfer_from ON inventory_transfer_from.ProductID = products.iD 
			AND  inventory_transfer_from.WarehouseID= warehouse.iD
		
		LEFT JOIN 
			(
				SELECT inventory_purchases.ProductID,
				inventory_purchases.WarehouseID,
				Sum(inventory_purchases.quantity) AS sumq
				FROM inventory_purchases
				GROUP BY inventory_purchases.ProductID ,inventory_purchases.WarehouseID
			) inventory_purchases ON inventory_purchases.ProductID = products.iD 
			AND  inventory_purchases.WarehouseID= warehouse.iD
			
		LEFT JOIN 
			(
				SELECT inventory_orders_refund.ProductID,
				inventory_orders_refund.WarehouseID,
				Sum(inventory_orders_refund.quantity) AS sumq
				FROM inventory_orders_refund
				GROUP BY inventory_orders_refund.ProductID ,inventory_orders_refund.WarehouseID
			) inventory_orders_refund ON inventory_orders_refund.ProductID = products.iD 
			AND  inventory_orders_refund.WarehouseID= warehouse.iD
			
		LEFT JOIN 
			(
				SELECT inventory_transfer_to.ProductID,
				inventory_transfer_to.WarehouseID,
				Sum(inventory_transfer_to.quantity) AS sumq
				FROM inventory_transfer_to
				GROUP BY inventory_transfer_to.ProductID ,inventory_transfer_to.WarehouseID
			) inventory_transfer_to ON inventory_transfer_to.ProductID = products.iD 
			AND  inventory_transfer_to.WarehouseID= warehouse.iD
			
		LEFT JOIN 
			(
				SELECT inventory_input.ProductID,
				inventory_input.WarehouseID,
				Sum(inventory_input.quantity) AS sumq
				FROM inventory_input
				GROUP BY inventory_input.ProductID ,inventory_input.WarehouseID
			) inventory_input ON inventory_input.ProductID = products.iD 
			AND  inventory_input.WarehouseID= warehouse.iD
			
		 WHERE products.iD='$CCID'
		 
		 ");
}
function getMinMaxMonth(){
	return getFetshTableWithQuery("SELECT month(( Max(products.date))) AS maxMonth,
		Max(products.date) as maxDate,Min(products.date) as minDate,
		month(Min(products.date)) AS minMonth
		FROM products");
}
function getWhereDateQueryFromData($tableName,$FROM,$TO){
  //  echo "\n WHERE (Date($tableName.date) >= '$FROM') AND (Date($tableName.date) <= '$TO')   \n";
    return "WHERE (Date($tableName.date) >= '$FROM') AND (Date($tableName.date) <= '$TO')";
}
//life time functions
function getBestSellingSize($Limit){
	return getFetshAllTableWithQuery("SELECT 
	   Sum(extended_order_refund.quantity -  COALESCE(extended_order_refund.refundQuantity,0)) AS total,
			products.iD AS iD
			FROM extended_order_refund
			INNER JOIN orders_details ON orders_details.OrderID = extended_order_refund.iD
			INNER JOIN products ON products.iD = orders_details.ProductID
			GROUP BY products.iD
			ORDER  BY total DESC LIMIT $Limit"); 
}
function getBestSellingGSM($Limit){
	 return getFetshAllTableWithQuery("SELECT 
	   Sum(extended_order_refund.quantity -  COALESCE(extended_order_refund.refundQuantity,0)) AS total,
			products.iD AS iD
			FROM extended_order_refund
			INNER JOIN orders_details ON orders_details.OrderID = extended_order_refund.iD
			INNER JOIN products ON products.iD = orders_details.ProductID
			GROUP BY products.iD
			ORDER  BY total DESC LIMIT $Limit");
}
function getBestSellingType($Limit){
	return getFetshAllTableWithQuery("SELECT 
	   Sum(extended_order_refund.quantity -  COALESCE(extended_order_refund.refundQuantity,0)) AS total,
			products.iD AS iD
			FROM extended_order_refund
			INNER JOIN orders_details ON orders_details.OrderID = extended_order_refund.iD
			INNER JOIN products ON products.iD = orders_details.ProductID
			GROUP BY products.iD
			ORDER  BY total DESC LIMIT $Limit");
}
function getBestProfitableType(){
	return getFetshAllTableWithQuery("SELECT products.iD AS iD,
		Sum(round(orders_details.quantity * orders_details.unitPrice ,3)) AS sellPrice,
		Sum(round(orders_details.quantity * products_types.purchasePrice,3)) AS purchasePrice,
		Count(orders_details.ProductID) AS Count_ProductID
		FROM ((orders_details
		JOIN products ON products.iD = orders_details.ProductID)
		JOIN products_types ON products_types.iD = products.ProductTypeID)
		GROUP BY products_types.name");
}


//Net Sales with refund
function getTotalNetSalesQuantity($FROM,$TO){
    $whereQuery=getWhereDateQueryFromData("extended_order_refund",$FROM,$TO);
	return getFetshAllTableWithQuery("SELECT Year(extended_order_refund.date) AS year,
		Month(extended_order_refund.date) AS month,
		COALESCE(Sum(extended_order_refund.extendedNetQuantity),0) AS total
		FROM extended_order_refund
		 $whereQuery 
		GROUP BY Year(extended_order_refund.date),
		Month(extended_order_refund.date)
		ORDER BY year,month");
}

// All Sales without refund
function getTotalSalesQuantity($FROM,$TO){
     $whereQuery=getWhereDateQueryFromData("extended_order_refund",$FROM,$TO);
	return getFetshAllTableWithQuery("SELECT Year(extended_order_refund.date) AS year,
		Month(extended_order_refund.date) AS month,
		COALESCE( round(Sum(extended_order_refund.quantity),3) ,0) AS total
		FROM extended_order_refund
		 $whereQuery  
		GROUP BY Year(extended_order_refund.date),Month(extended_order_refund.date)
		ORDER BY year,
		month");
}
function getTotalSalesQuantityWithWhere($FROM,$TO,$query){
     $whereQuery=getWhereDateQueryFromData("extended_order_refund",$FROM,$TO);
	return getFetshAllTableWithQuery("SELECT Year(extended_order_refund.date) AS year,
		Month(extended_order_refund.date) AS month,
		COALESCE( round(Sum(extended_order_refund.quantity),3) ,0) AS total
		FROM extended_order_refund
		 $whereQuery  
		GROUP BY Year(extended_order_refund.date),Month(extended_order_refund.date)
		ORDER BY year,
		month");
}


function getTotalRefundsQuantity($FROM,$TO){
        $whereQuery=getWhereDateQueryFromData("extended_order_refund",$FROM,$TO);
	return getFetshAllTableWithQuery("SELECT Year(extended_order_refund.date) AS year,
		Month(extended_order_refund.date) AS month,
		COALESCE( round( Sum(extended_order_refund.refundQuantity),3) ,0) AS total
		FROM extended_order_refund
		 $whereQuery  
		GROUP BY Year(extended_order_refund.date),
		Month(extended_order_refund.date)
		ORDER BY year,month");
}
function getTotalRefundsQuantityWithWhere($FROM,$TO,$query){
        $whereQuery=getWhereDateQueryFromData("extended_order_refund",$FROM,$TO);
	return getFetshAllTableWithQuery("SELECT Year(extended_order_refund.date) AS year,
		Month(extended_order_refund.date) AS month,
		COALESCE( round( Sum(extended_order_refund.refundQuantity),3) ,0) AS total
		FROM extended_order_refund
		 $whereQuery  
		GROUP BY Year(extended_order_refund.date),
		Month(extended_order_refund.date)
		ORDER BY year,month");
}
//if order of 200
// it cant return 300 kg  can not be negative


///SELECT sum(orders_details.price) as revenue, sum(product.price) as cost, sum(orders_details.price)-sum(product.price) as profit
//FROM orders_details 
//    INNER JOIN (SELECT distinct purchases_details.price, purchases_details.ProductID FROM purchases_details) as product 
//        ON orders_details.ProductID = product.ProductID



//SELECT SalesTotal, PurchasesTotal, (SalesTotal-PurchasesTotal) as Profit
//FROM (SELECT (SELECT SUM(price) FROM orders_details ) as SalesTotal,
//             (SELECT SUM(price) FROM purchases_details ) as PurchasesTotal
//     ) t
function getProfitsByMonths($FROM,$TO){
     $whereQuery=getWhereDateQueryFromData("extended_order_refund",$FROM,$TO);
	return getFetshAllTableWithQuery("
		SELECT Year(extended_order_refund.date) AS year,
		Month(extended_order_refund.date) AS month,
		Count(orders_details.ProductID) AS count,
          CASE WHEN 
			purchases.CustomerID IS NULL 
		THEN 
			Sum(round(purchases_d.price,3))
		ELSE
        	(
			Sum(orders_details.price- orders_details.discount)  -
			Sum( (  purchases_d.unitPrice * orders_details.quantity)-purchases_d.discount  )
            )  END AS total
FROM orders_details
  INNER JOIN (SELECT purchases_details.ProductID,purchases_details.price,
    purchases_details.unitPrice,purchases_details.discount,
    purchases.iD,
    purchases_details.PurchaseID
  FROM purchases_details
    INNER JOIN purchases ON purchases.iD = purchases_details.PurchaseID
  GROUP BY 
    purchases_details.PurchaseID) purchases_d ON orders_details.ProductID =
    purchases_d.ProductID
  INNER JOIN extended_order_refund ON extended_order_refund.iD = orders_details.OrderID
  INNER JOIN purchases ON purchases.iD = purchases_d.PurchaseID
  $whereQuery
  
  GROUP BY month,year
  ");
}
function getProfitsByCutRequests($FROM ,$TO){
         $whereQuery=getWhereDateQueryFromData("profits_cut_requests_products",$FROM,$TO);
    	return getFetshAllTableWithQuery("SELECT * FROM profits_cut_requests_products  $whereQuery");
}

function getWastsByMonths(){
	return getFetshAllTableWithQuery("SELECT * FROM wasted_cut_requests_products");
}










function getProfitsByMonthsAndCustomers($iD){
	return getFetshAllTableWithQuery("
		SELECT Year(extended_order_refund.date) AS year,
		Month(extended_order_refund.date) AS month,
		Count(orders_details.ProductID) AS count,
		(CASE WHEN 
			purchases.CustomerID IS NULL 
		THEN 
			Sum(round(extended_order_refund.extendedNetPrice,3))   
		ELSE
			(
				Sum(round( (extended_order_refund.quantity - COALESCE(extended_order_refund.refundQuantity,0)) * orders_details.unitPrice ,3)) 
				-
				Sum(round((extended_order_refund.quantity - COALESCE(extended_order_refund.refundQuantity,0)) * products_types.purchasePrice,3))
			)
		END) AS total
		
		FROM ((orders_details
			JOIN purchases_details ON purchases_details.ProductID = orders_details.ProductID
			JOIN products ON products.iD = orders_details.ProductID)
			JOIN products_types ON products_types.iD = products.ProductTypeID)
			
			JOIN purchases ON purchases.iD = purchases_details.PurchaseID
			
		INNER JOIN extended_order_refund ON extended_order_refund.iD = orders_details.OrderID
		WHERE  extended_order_refund.CustomerID='$iD'
			GROUP BY extended_order_refund.CustomerID,
			year,month ");
}

?>