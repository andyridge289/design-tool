<?php

require_once "build_new_tree.php";

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
$output = "digraph output {";

$first = $newRoot->kids[0];
addToArrays($first);

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

	for($i = 0; $i < count($node->kids); $i++)
	{
		$kid = $node->kids[$i];
		array_push($relations, "\"" . $node->name . "\"->\"" . $kid->name . "\"");
		addToArrays($kid);
	}
}

// $q = "SELECT category.name AS category_name, decision.name AS decision_name FROM `decision_has_category` INNER JOIN category ON category.id = decision_has_category.category_id INNER JOIN decision ON decision.id = decision_has_category.decision_id WHERE decision_has_category.category_id = $categoryId";
// $retval = $database->query($q);
// if(!$retval)
// {
// 	echo "Fail: $q<br />";
// 	return;
// }

// while($row = mysqli_fetch_array($retval))
// {
// 	$category = $row["category_name"];
// 	$decision = $row["decision_name"];

// 	if(!in_array($category, $categories))
// 		array_push($categories, $category);
		
// 	if(!in_array($decision, $decisions))
// 		array_push($decisions, $decision);

// 	array_push($things, "\"$category\"->\"$decision\"");
// }

// $q = "SELECT decision_has_decision.child_id, decision.name AS parent_name FROM decision_has_decision INNER JOIN decision ON decision_has_decision.parent_id = decision.id WHERE decision_has_decision.category_id = $categoryId";
// $retval = $database->query($q);
// if(!$retval)
// {
// 	echo "Fail: $q<br />";
// 	return;
// }

// while($row = mysqli_fetch_array($retval))
// {
// 	$parent = $row["parent_name"];
	
// 	$q2 = "SELECT name FROM decision WHERE id = $row[child_id]";
// 	$retval2 = $database->query($q2);
// 	if(!$retval) continue;
	
// 	$row2 = mysqli_fetch_array($retval2);
// 	$child = $row2["name"];
	
// 	if(!in_array($child, $decisions))
// 	{
// 		echo "Added decision $child<br />";
// 		array_push($decisions, $child);
// 	}

// 	if(!in_array($parent, $decisions))
// 		array_push($decisions, $parent);
		
// 	array_push($things, "\"$parent\"->\"$child\"");
// }

// $q = "SELECT decision.name AS decision_name, `option`.name AS option_name FROM decision_has_option INNER JOIN decision ON decision_has_option.decision_id = decision.id INNER JOIN `option` ON decision_has_option.option_id = `option`.id WHERE category_id = $categoryId";
// $retval = $database->query($q);
// if(!$retval)
// {
// 	echo "Fail: $q<br />";
// 	return;
// }

// while($row = mysqli_fetch_array($retval))
// {
// 	$decision = $row["decision_name"];
// 	$option = $row["option_name"];
// 	//echo "$decision -> $option<br />";
	
// 	if(!in_array($decision, $decisions))
// 		array_push($decisions, $decision);

// 	if(!in_array($option, $options))
// 		array_push($options, $option);
		
// 	if(!array_key_exists($decision, $option_things))
// 	{
// 		$option_things["$decision"] = array();
// 		array_push($option_things["$decision"], $option);
// 	}
// 	else
// 	{
// 		array_push($option_things["$decision"], $option);
// 	}
// }

$output .= "$CATEGORY\n";
$output .= printArray($categories);

$output .= "\n\n$DECISION\n";
$output .= printArray($decisions);

$output .= "\n\n$OPTION\n";
$output .= printArray($options);

$output .= "\n\n\n";
foreach($relations as $thing)
{
	$output .= "\n$thing [arrowhead=none]";
}

// $keys = array_keys($option_things);
// foreach($keys as $key)
// {
// 	$output .= "\n\"$key\"->";
// 	$opts = $option_things[$key];
// 	for($i = 0; $i < count($opts); $i++)
// 	{
// 		$output .= "\"" . $opts[$i] . "\"";
// 		if($i < count($opts) - 1) $output .= "->";
// 	}
	
// 	$output .= " [arrowhead=none]";
// }

function printArray($array)
{
	$arr = "";
	for($i = 0; $i < count($array); $i++)
	{
		$arr .= "\"" . $array[$i] . "\"";
		if($i < count($array) - 1) $arr .= ",";
	}
	
	return $arr;
}

$output .= "}"; 
echo $output;
$handle = fopen("0.gv", "w");
fwrite($handle, $output);
fclose($handle);

?>