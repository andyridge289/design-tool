<?php

require_once "tree_thing.php";
require_once "../../php/database.php";

$oldRoot = new TreeThing(-1, "root", "");

$q0 = "SELECT * FROM `category`";
$ret0 = $db->q($q0);
if(!$ret0)
{
	echo "Fail: $q<br />";
	return;
}

while($r0 = mysqli_fetch_array($ret0))
{

	$q = "SELECT c.id AS catId, c.name AS catName, d.id AS decId, d.name AS decName FROM `category` AS c
			INNER JOIN `decision_has_category` AS dc ON c.id = dc.category_id
			INNER JOIN `decision` AS d ON dc.decision_id = d.id
			WHERE c.id = $r0[id]";

	$ret = $db->q($q);
	if(!$ret)
	{
		echo "Fail: $q <br />";
		return;
	}
		
	$aRoot = null;

	while($r = mysqli_fetch_array($ret))
	{
		if($aRoot === null)
			$aRoot = new TreeThing($r["catId"], $r["catName"], "category");
			
		$decision = new TreeThing($r["decId"], $r["decName"], "decision");
		$aRoot->addChild($decision);
		$decision->parent = $aRoot;
	}

	old_getChildDecisions($aRoot);
	old_lookForOptions($aRoot);

	array_push($oldRoot->children, $aRoot);
}

function old_getChildDecisions($node)
{
	global $db;

	for($i = 0; $i < count($node->children); $i++)
	{
		$child = &$node->children[$i];
	
		$q = "SELECT DISTINCT d.id, d.name FROM decision_has_decision AS dd
				INNER JOIN decision AS d ON dd.child_id = d.id
				WHERE dd.parent_id = " . $child->id;
		$ret = $db->q($q);
		if(!$ret)
			continue;
		
		while($r = mysqli_fetch_array($ret))
		{
			$d = new TreeThing($r["id"], $r["name"], "decision");
			$child->addChild($d);
			$d->parent = $child;
			// echo "setting decision parent $child->name -> $d->name<br />";
		}
		
		// Once we've added the children to that decision, get the children of that decision
		old_getChildDecisions($child);
	}
}

function old_lookForOptions($node)
{
	// echo "Look for options: " .  $node->type . "<br />";

	if(strcmp($node->type, "category") === 0)
	{
		for($i = 0; $i < count($node->children); $i++)
		{
			old_lookForOptions($node->children[$i]);
		}
	} 
	else if(strcmp($node->type, "decision") === 0)
	{
		old_getOptions($node);
		
		for($i = 0; $i < count($node->children); $i++)
		{
			old_lookForOptions($node->children[$i]);
		}
	}
}

function old_getOptions($node)
{
	// echo "get Options ";
	// print_r($node);
	// echo "<br />";

	global $db;
	$q = "SELECT o.id, o.name FROM decision_has_option AS do 
			INNER JOIN `option` AS o ON do.option_id = o.id
			WHERE do.decision_id = $node->id";
	
	$ret = $db->q($q);
	if(!$ret){ echo "Fail $q<br />"; return; }
		
	while($r = mysqli_fetch_array($ret))
	{
		// print_r($r); echo "<br />";
		$o = &new TreeThing($r["id"], $r["name"], "option");
		$node->addChild($o);
		$o->parent = $node;
		// echo "setting Option parent $node->name -> $o->name<br />";
	}
}

function findInTree($node, $name)
{	
	for($i = 0; $i < count($node->children); $i++)
	{
		$kid = $node->children[$i];
		if($kid->parent == null)
			$kid->parent = $node;

		if(strcmp($kid->name, $name) == 0)
		{
			return $kid;
		}

		$thing = findInTree($kid, $name);
		if($thing != null)
		{
			return $thing;
		}
	}	
}

?>