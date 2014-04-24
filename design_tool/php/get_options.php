<?php

header('Content-type: text/javascript');

require_once "database.php";

$q = "SELECT * FROM `option`";

$ret = $db->q($q);
if(!$ret)
{
	echo "Fail: $q";
	return;
}

echo "var o = [";
$first = true;

while($r = mysqli_fetch_array($ret))
{
	if($first)
		$first = false;
	else 
		echo ",";
	
	$thing = array("{", "}");
	
	echo json_encode($r["name"]);
}
echo "];";
?>