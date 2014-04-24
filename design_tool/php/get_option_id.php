<?php

header('Content-type: text/javascript');

require_once "database.php";

$q = "SELECT id FROM `option` WHERE name = '$_GET[o]'";

$ret = $db->q($q);
if(!$ret)
{
	echo "Fail: $q";
	return;
}


$r = mysqli_fetch_array($ret);
	
echo $r[id];

?>