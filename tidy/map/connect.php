<?php

require_once "../../php/database.php";

$q = "INSERT INTO `ds_map` VALUES('', $_POST[newId], '$_POST[tableName]', $_POST[oldId])";
$ret = $db->q($q);
if(!$ret)
{
	echo "Fail: $q<br />";
}
else
{
	echo "win";
}

?>