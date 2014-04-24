<?php 

require_once "../../lib/database.php";

$q = "SELECT * FROM `thing_has_thing` WHERE parent_id = $_POST[parent] AND child_id = $_POST[child]";
$ret = $db->q($q);
if(!$ret)
{
	echo "Fail: $q<br />";
	return;
}
if(mysqli_num_rows($ret) == 0)
{
	$q = "INSERT INTO `thing_has_thing` VALUES('', $_POST[parent], $_POST[child], 0)";
	$ret = $db->q($q);
	if(!$ret)
	{
		echo "Fail: $q<br />";
		return;
	}
}

$q = "UPDATE `thing` SET stage_removed = -1 WHERE id = $_POST[parent]";
$ret = $db->q($q);
if(!$ret)
{
	echo "Fail: $q<br />";
	return;
}

$q= "UPDATE `thing` SET stage_removed = -1 WHERE id = $_POST[child]";
$ret = $db->q($q);
if(!$ret)
{
	echo "Fail: $q<br />";
	return;
}

echo "win";

// Look at the things and update the removed stage if it's not -1

?>