<?php 

require_once "../../lib/database.php";

$q = "INSERT INTO `thing` VALUES('', '" . addslashes($_POST["name"]) . 
	 "', '$_POST[type]', '$_POST[category]', $_POST[stage], -1, '" . addslashes($_POST["description"]) . "', $_POST[ds_id])";

// echo $q;

$ret = $db->q($q);
if(!$ret)
{
	echo "Fail: $q<br />";
	return;
}

if($_POST["source"] != -1)
{
	$q = "SELECT * FROM `thing` WHERE name = '" . addslashes($_POST["name"]) . "' AND type = '$_POST[type]' AND "
		. "category = '$_POST[category]' AND description = '" . addslashes($_POST["description"]) . "'";
	$ret = $db->q($q);
	if(!$ret)
	{
		echo "Fail $q";
		return;
	}

	$r = mysqli_fetch_array($ret);

	$q = "INSERT INTO `thing_has_source` VALUES('', $r[id], $_POST[source])";
	$ret = $db->q($q);
	if(!$ret)
	{
		echo "Fail $q";
		return;
	}
}

echo "win";

?>