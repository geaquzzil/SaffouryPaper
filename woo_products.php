<?php
    require_once("db_config.php");
	require_once ("extension_api.php");
    header('Content-Type: application/xls');
    header('Content-Disposition: attachment; filename='.$fileName);
    
    $connect = mysqli_connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);
    $query = "SELECT * FROM `".PR_SEARCH."` LIMIT 4";
    $result = mysqli_query($connect, $query);
    print_r($result);
    $rows='';
    $contents='';
    while($row=mysqli_fetch_array($result)) {
    foreach($row as $column => $value) {
         echo "$column $value\n";
        }
    }
//     $res=getFetshALLTableWithQuery("SELECT * FROM `".PR_SEARCH."`");
// 	if(empty($res) || is_null($res)){	
// 	    returnResponse(array());
// 	}
// 	foreach($res as $key=>$value){
	    
// 	}
if(isset($_POST["submit"]))
{
    
 if(mysqli_num_rows($res) > 0)
 {
 $export .= '
 <table> 
 <tr> 
 <th> id </th>
 <th>firstname</th> 
 <th>lastname</th> 
 <th>dob</th> 
 
 </tr>
 ';
 while($row = mysqli_fetch_array($res))
 {
 $export .= '
 <tr>
 <td>'.$row["id"].'</td> 
 <td>'.$row["firstname"].'</td> 
 <td>'.$row["lastname"].'</td> 
 <td>'.$row["dob"].'</td> 
 
 
 </tr>
 ';
 }
 $export .= '</table>';
//  header('Content-Type: application/xls');
//  header('Content-Disposition: attachment; filename=info.xls');
 echo $export;
 }
}






// //Store table records into an array
// foreach($results as $key=>$value){
//      $items[] = $key;
// }
//     $heading = false;

//     //Add the MySQL table data to excel file
//     if(!empty($results)) {
//         foreach($results as $item) {
//             if(!$heading) {
//                 echo implode("\t", array_keys($item)) . "\n";
//                 $heading = true;
//             }
//     echo implode("\t", array_values($item)) . "\n";
//         }
//     }
// exit();

?>