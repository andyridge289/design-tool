<?php

require_once "../lib/database.php";

if(!isset($_POST["name"]))
{
	echo "Fail: No name set";
	return;
}

$q = "INSERT INTO `ds_list` VALUES('', '" . addslashes($_POST["name"]) . "')";
$ret = $db->q($q);
if(!$ret)
{
	echo "Fail: $q";
}
else
{
	echo "win";
}

?>