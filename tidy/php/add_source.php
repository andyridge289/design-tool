<?php 

require_once "../../lib/database.php";

$q = "INSERT INTO `source` VALUES('', '" . addslashes($_POST["name"]) . "', '" . addslashes($_POST["author"]) . "', '$_POST[type]')";

// echo $q;

$ret = $db->q($q);
if(!$ret)
{
	echo "Fail: $q<br />";
	return;
}

echo "win";

?>