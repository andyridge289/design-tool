<?php

require_once "../../php/database.php";

if(isset($_POST["remove"]))
{
	$q = "DELETE FROM `to_map` WHERE id = $_POST[remove]";
	$ret = $db->q($q);
	if($ret)
	{
		echo "win";
	}
	else
	{
		echo "Fail: $q<br />";
	}
}
else
{
	$q = "INSERT INTO `to_map` VALUES('', $_POST[oldId], '$_POST[tableName]')";
	$ret = $db->q($q);
	if(!$ret)
	{
		echo "Fail: $q<br />";
	}
	else
	{
		echo "win";
	}
}
?>