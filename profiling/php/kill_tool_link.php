<?php 

require_once "../../lib/build_new_tree.php";

if(!isset($_POST["thing_id"]) || !isset($_POST["tool_id"]))
{
	echo "Fail, POSTS not set";
	return;
}

$deleteThings = array($_POST["thing_id"]);
// Kill the thing
$q = "DELETE FROM `tool_has_thing` WHERE tool_id = $_POST[tool_id] AND thing_id = $_POST[thing_id]";
$ret = $db->q($q);
if(!$ret)
{
	echo "Fail: $q";
	return;
}

// $node = findInTreeId($newRoot, $_POST["thing_id"]);

// if($node->parent != null)
// {
// 	while($node = $node->parent)
// 	{
// 		if(!hasLivingChildren($node))
// 		{
// 			array_push($deleteThings, $node->id);
// 		}
// 	}
// }

// print_r($deleteThings);

// function hasLivingChildren($node)
// {
// 	for($i = 0; $i < count($node->kids); $i++)
// 	{
// 		$q = "SELECT * FROM `tool_has_thing` WHERE tool_id = $_POST[tool_id] AND thing_id = $_POST[thing_id]";
// 		$ret = $db->q($q);
// 		if(!$ret)
// 		{
// 			echo "Fail: $q<br />";
// 			continue;
// 		}

// 		if(mysqli_num_rows($ret) > 0)
// 		{
// 			// It's alive
// 			return true;
// 		}
// 	}

// 	// If we get here, none of the immediate children are alive - may as well recurse.
// 	for($i = 0; $i < count($node->kids); $i++)
// 	{
// 		if($node->kids[$i]->type == "decision")
// 		{
// 			if(hasLivingChildren($node->kids[$i]))
// 				return true;
// 		}
// 	}

// 	return false;
// }


// Kill its children
// Kill its parent unless its parent has other children

// if(mysqli_num_rows($ret) > 0)
// {
// 	echo "Fail: it's already there! $_POST[thing_id] $_POST[tool_id]";
// 	return;
// }

// $thingsToInsert = array($_POST["thing_id"]);

// $node = findInTreeId($newRoot, $_POST["thing_id"]);

// if($node->parent != null)
// {
// 	while(($node = $node->parent) != null)
// 	{
// 		if($node->type != "category")
// 			array_push($thingsToInsert, $node->id);
// 		else
// 			break;
// 	}
// }

// $added = array();
// foreach($thingsToInsert AS $t)
// {
// 	$q = "SELECT * FROM `tool_has_thing` WHERE tool_id = $_POST[tool_id] AND thing_id = $t";
// 	$ret = $db->q($q);
// 	if(!$ret)
// 	{
// 		echo "Fail: $q";
// 		return;
// 	}

// 	if(mysqli_num_rows($ret) > 0)
// 	{
// 		continue;
// 	}

// 	$q = "INSERT INTO `tool_has_thing` VALUES ('', $_POST[tool_id], $t, NOW())";
// 	$ret = $db->q($q);
// 	if(!$ret)
// 	{
// 		echo "Fail: $q";
// 		return;
// 	}
// 	else
// 	{
// 		array_push($added, $t * 1);
// 	}
// }

// // Now find all the parents and add them to the DB if they aren't already there
// echo "var info = " . json_encode($added);

?>