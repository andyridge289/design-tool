<?php

header('Content-type: text/javascript');

require_once "database.php";

$ds = isset($_GET["ds"]) ? $_GET["ds"] : 1;

$q = "SELECT * FROM `source` WHERE ds_id = $ds AND type = 'tool'";
$ret = $db->q($q);
if(!$ret)
{
	echo "Fail $q";
	return;
}

echo "var TOOL_COUNT = " . mysqli_num_rows($ret);
 
?>