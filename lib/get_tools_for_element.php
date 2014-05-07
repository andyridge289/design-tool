<?php

require_once "database.php";

$ds = isset($_GET["ds"]) ? $_GET["ds"] : 1;
$id = isset($_GET["id"]) ? $_GET["id"] : 3;

$q = "SELECT * FROM `tool_has_thing` AS tt
		LEFT JOIN `source` AS s ON s.id = tt.tool_id
		WHERE tt.thing_id = $id";

$ret = $db->q($q);
if(!$ret)
{
	echo "Fail $q";
	return;
}

$tools = array();
while($r = mysqli_fetch_array($ret))
{
	array_push($tools, $r["name"]);
}

if(count($tools) == 0)
{
	echo "-1";
}

echo "var t = ";
echo json_encode($tools);

 
?>