<?php 

require_once "database.php";

$q = "INSERT INTO `source` VALUES('', '" . addslashes($_POST["name"]) . "', '" . addslashes($_POST["author"]) . "', '$_POST[type]', $_POST[ds])";

// echo $q;

$ret = $db->q($q);
if(!$ret)
{
	echo "Fail: $q<br />";
	return;
}

echo "win";

?>