<?php

require_once "../../../lib/database.php";
//require_once "thing.php";
require_once "tree_thing.php";

$newRoot = new TreeThing(-1, "root", "");
$nodes = array();

$q = "SELECT * FROM `thing` WHERE type = 'category'";
$ret = $db->q($q);
if(!$ret)
{
	echo "Fail: $q<br />";
	return;
}

while($r = mysqli_fetch_array($ret))
{
	$node = &new TreeThing($r["id"], "", "ds");
	array_push($newRoot->children, $node);
	$node->parent = $newRoot;
	array_push($nodes, $node);
}

$q = "SELECT * FROM `thing_has_thing` WHERE dead = 0";
$ret = $db->q($q);
if(!$ret)
{
	echo "Fail: $q<br />";
	return;
}

$relations = array();
while($r = mysqli_fetch_array($ret))
{
	$parent = $r["parent_id"];
	$child = $r["child_id"];

	$q2 = "SELECT stage_removed FROM thing WHERE id = $parent";
	$ret2 = $db->q($q2);
	if(!$ret2){ echo "Fail: $q2<br />"; continue; }
	$r2 = mysqli_fetch_array($ret2);
	if($r2["stage_removed"] != -1)
		continue;

	$q2 = "SELECT stage_removed FROM thing WHERE id = $child";
	$ret2 = $db->q($q2);
	if(!$ret2){ echo "Fail: $q2<br />"; continue; }
	$r2 = mysqli_fetch_array($ret2);
	if($r2["stage_removed"] != -1)
		continue;

	array_push($relations, $r);
}

for($i = 0; $i < count($nodes); $i++)
{
	// Look through the relations for anything where the parent_id is the current id
	for($j = 0; $j < count($relations);)
	{
		if($nodes[$i]->id == $relations[$j]["parent_id"])
		{
			$newNode = &new TreeThing($relations[$j]["child_id"], "", "");
			array_push($nodes[$i]->children, $newNode);
			$newNode->parent = $nodes[$i];
			array_push($nodes, $newNode);
			array_splice($relations, $j, 1);
		}
		else
		{
			$j++;
		}
	}
}

$q = "SELECT * FROM `thing`";
$ret = $db->q($q);
if(!$ret)
{
	echo "Fail: $q<br />";
	return;
}

$info = array();
while($r = mysqli_fetch_array($ret))
{
	$info[$r["id"]] = $r;
}

setInfo($newRoot);

function setInfo(&$node)
{
	global $info;

	if(strcmp($node->name, "") == 0)
	{
		// Then we need to look up the info
		$row = $info[$node->id];
		$node->name = $row["name"];
		$node->type = $row["type"];
	}

	for($i = 0; $i < count($node->children); $i++)
	{
		setInfo($node->children[$i]);
	}
}

?>