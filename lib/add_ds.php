<?php 

require_once "database.php";

if(!isset($_POST["name"]))
{
	echo "Fail: No DS Name";
	return;
}

$name = addslashes($_POST["name"]);

$q = "SELECT * FROM `ds_list` WHERE name = \"$name\"";
$ret = $db->q($q);
if(!$ret)
{
	echo "Fail: $q";
	return;
}

if(mysqli_num_rows($ret) > 0)
{
	echo "Fail: A DS already exists with that name: $name";
	return;
}

$q = "INSERT INTO `ds_list` VALUES('', \"$name\")";
$ret = $db->q($q);
if(!$ret)
{
	echo "Fail: DB INSERT $q";
	return;
}

$q = "SELECT * FROM `ds_list` WHERE name = \"$name\"";
$ret = $db->q($q);
if(!$ret)
{
	echo "Fail: $q";
	return;
}

$r = mysqli_fetch_array($ret);
$id = $r["id"];

$qs = array(
	"INSERT INTO `thing` VALUES('', 'Functional', 'category', 'Functional', 0, -1, 'The root functional element for $name', $id)",
	"INSERT INTO `thing` VALUES('', 'Non-Functional', 'category', 'Non-Functional', 0, -1, 'The root non-functional element for $name', $id)",
	"INSERT INTO `thing` VALUES('', 'Structural', 'category', 'Structural', 0, -1, 'The root structural element for $name', $id)",
	"INSERT INTO `thing` VALUES('', 'Entity', 'category', 'Entity', 0, -1, 'The root entity element for $name', $id)",
);

foreach($qs AS $q)
{
	$ret = $db->q($q);
	if(!$ret)
	{
		echo "Fail: $q";
		return;
	}
}

echo "win";

?>