<?php

require_once "../lib/database.php";

$q = "INSERT INTO `ds_modified` VALUES('', $_POST[firstThing], $_POST[secondThing], $_POST[stageB])";
$ret = $db->q($q);
if(!$ret)
{
	echo "Fail $q";
	return;
}

// $id = -1;
// if(mysqli_num_rows($ret) == 0)
// {
// 	$q = "INSERT INTO `ds_modified` VALUES('', $_POST[firstThing], $_POST[stageA], -1)";
// 	$ret = $db->q($q);
// 	if(!$ret)
// 	{
// 		echo "Fail $q";
// 		return;
// 	}

// 	$q = "SELECT * FROM `ds_modified` WHERE thing_id = $_POST[firstThing] AND added_at = -1";
// 	$ret = $db->q($q);
// 	if(!$ret)
// 	{
// 		echo "Fail $q";
// 		return;
// 	}

// 	$r = mysqli_fetch_array($ret);
// 	$id = $r["id"];
// }
// else
// {
// 	$r = mysqli_fetch_array($ret);
// 	$id = $r["id"];
// }

// $q = "SELECT * FROM `ds_modified` WHERE thing_id = $_POST[secondThing] AND removed_at = -1";
// $ret = $db->q($q);
// if(!$ret)
// {
// 	echo "Fail $q";
// 	return;
// }

// if(mysqli_num_rows($ret) == 0)
// {
// 	$q = "INSERT INTO `ds_modified` VALUES('', $_POST[secondThing], -1, $_POST[stageB])";
// 	$ret = $db->q($q);
// 	if(!$ret)
// 	{
// 		echo "Fail $q";
// 	}
// }

// Make sure you add the link for the second one


echo "win";

?>