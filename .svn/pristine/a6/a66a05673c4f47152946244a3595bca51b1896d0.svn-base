<?php
ini_set('display_errors', 0);
$user = "u_avtotot";
$pass = "zTZ9QVGd";
$db   = "avtotot";

$conn = mysql_pconnect("127.0.0.1", $user, $pass)
  or die("Could not connect: ".mysql_error());


mysql_select_db($db)
  or die("Could not select database: ".mysql_error());
mysql_query("SET NAMES utf8;");

$categoryId = isset($_POST['category_id']) ? (int) $_POST['category_id'] : null;
//$result = mysql_query("SELECT name FROM SS_categories WHERE categoryID = {$categoryId};");
$name = isset($_POST['value']) ? mysql_real_escape_string( urldecode($_POST['value']) ) : null;
$whereClause = '';
if ($name){
    $whereClause .= " AND c1.name LIKE '{$name}%'";
}
$query = "SELECT c1.categoryID, c1.name, c1.level
FROM SS_categories as c1
WHERE c1.categoryID != 1
 AND c1.SUP_ID IS NULL
 AND NOT path LIKE '1/2208%'
 {$whereClause}
ORDER BY c1.path asc;";

$result = mysql_query($query);
$data = array();
$i = 0;
while ($row = mysql_fetch_assoc($result)){
    $data[$row['categoryID']] = array(
        'label' => str_repeat('-', $row['level']-1).$row['name'],
        'sort_order' => $i++
        );
}
die(json_encode($data));