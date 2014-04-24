<?php

header('Content-type: text/javascript');

require_once "../../lib/build_new_tree.php";
require_once "../../lib/build_participants.php";

$CATEGORY = "node [color=\"047b35\",fillcolor=\"#8df2b6\",style=filled, shape=house];";
$DECISION = "node[shape=box,color=\"#004a63\",fillcolor=lightblue2,style=filled];";
$OPTION = "node [color=\"#444444\", style=\"rounded,filled\", shape=rect, fontcolor=\"black\", fillcolor=\"#DDDDDD\"];";

$categories = array();
$options = array();
$decisions = array();
$relations = array();

// $categoryId = $_GET["c"];

// $things = array();
// $option_things = array();

$cat = isset($_GET["c"]) ? $_GET["c"] : 1;
$pId = isset($_GET["p"]) ? $_GET["p"] : 1;
$p = null;
for($i = 0; $i < count($participants); $i++)
{
	if($participants[$i]->id == $pId)
	{
		$p = $participants[$i];
		break;
	}
}

$first = $newRoot->children[$cat - 1];

// Now we re-jig the tree to have only the things for that participant


// hasChildParticipant($ first, $p);


addToArrays($first);

echo "ds$cat = ";

function hasChildParticipant(&$node, $participant)
{
	if(strcmp($node->type, "option") == 0)
	{
		// We need to check if this one is one
		if(thingInArray($node, $participant->things))
			return true;
		else
			return false;
	}
	else
	{
		$any = false;
		for($i = 0; $i < count($node->children); $i++)
		{
			$children = &$node->children;
			$child = &$children[$i];

			// Check if one of the children is one, if it is we need to render this one anyway
			if(hasChildParticipant($child, $participant) === false)
			{
				// Don't do anything?
				array_splice($children, $i, 1);
				$node->children = &$children;
			}
			else
			{
				echo "Adding " + $child->name . "<br />";
				$any = true;
			}
		}

		if(strcmp($node->type, "decision") == 0)
		{
			// If it ain't, remove it from the children array
			if($any)
				return true;
			else
				return false;
		}
		else
		{
			// It's a category
			// print_r($children);
			echo count($node->children);
		}

		
	}
}

function addToArrays($node)
{
	global $categories, $options, $decisions, $relations;

	if(strcmp($node->type, "category") == 0)
	{
		array_push($categories, $node->name);
	}
	else if(strcmp($node->type, "decision") == 0)
	{
		array_push($decisions, $node->name);
	}
	else
	{
		array_push($options, $node->name);
	}

	for($i = 0; $i < count($node->children); $i++)
	{
		$kid = $node->children[$i];
		array_push($relations, "\"" . $node->name . "\"->\"" . $kid->name . "\"");
		addToArrays($kid);
	}
}

echo $first->makeString();

?>