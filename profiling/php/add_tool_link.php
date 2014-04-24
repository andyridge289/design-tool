<?php 

require_once "../../lib/build_new_tree.php";

if(!isset($_POST["thing_id"]) || !isset($_POST["tool_id"]))
{
	echo "Fail, POSTS not set";
	return;
}

$ds = isset($_POST["ds"]) ? $_POST["ds"] : 1;

$q = "SELECT * FROM `tool_has_thing` WHERE tool_id = $_POST[tool_id] AND thing_id = $_POST[thing_id]";
$ret = $db->q($q);
if(!$ret)
{
	echo "Fail: $q";
	return;
}

if(mysqli_num_rows($ret) > 0)
{
	echo "Fail: it's already there! $_POST[thing_id] $_POST[tool_id]";
	return;
}

$thingsToInsert = array($_POST["thing_id"]);

$newRoot = buildTree($ds);
$node = findInTreeId($newRoot, $_POST["thing_id"]);

if($node->parent != null)
{
	while(($node = $node->parent) != null)
	{
		if($node->type != "category")
			array_push($thingsToInsert, $node->id);
		else
			break;
	}
}

$added = array();
foreach($thingsToInsert AS $t)
{
	$q = "SELECT * FROM `tool_has_thing` WHERE tool_id = $_POST[tool_id] AND thing_id = $t";
	$ret = $db->q($q);
	if(!$ret)
	{
		echo "Fail: $q";
		return;
	}

	if(mysqli_num_rows($ret) > 0)
	{
		continue;
	}

	$q = "INSERT INTO `tool_has_thing` VALUES ('', $_POST[tool_id], $t, NOW())";
	$ret = $db->q($q);
	if(!$ret)
	{
		echo "Fail: $q";
		return;
	}
	else
	{
		array_push($added, $t * 1);
	}
}

// Now find all the parents and add them to the DB if they aren't already there
echo "var info = " . json_encode($added);

?>