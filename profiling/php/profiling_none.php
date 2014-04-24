<?php

// header("Content-type:application/gv");

require_once "../../lib/build_new_tree.php";
require_once "../../lib/library.php";

class IN
{
	public $id;
	public $name;
	public $count;

	function IN($id, $name)
	{
		$this->id = $id;
		$this->name = $name;
		$this->count = 0;
	}
}

$ds = isset($_GET["ds"]) ? $_GET["ds"] : 1;
// $tools = isset($_GET["t"]) ? $_GET["t"] : 18;
// $dec = isset($_GET["r"]) ? $_GET["r"] : -1;
$cat = isset($_GET["c"]) ? $_GET["c"] : 1;
// $heat = isset($_GET["h"]) ? $_GET["h"] == 1 : false;

// $CATEGORY = "node [color=\"047b35\",fillcolor=\"#8df2b6\",style=filled, shape=house];";

// $DECISION = "node[shape=box,color=\"#004a63\",fillcolor=lightblue2,style=filled,fontcolor=\"#444444\"];";
// $MADE_DECISION = "node[shape=box,color=\"#7F00FF\",fillcolor=\"#e0b2ff\",fontcolor=\"#000000\",style=filled,border=2];";

// $OPTION = "node [color=\"#aaaaaa\", style=\"rounded,filled\", shape=rect, fontcolor=\"#444444\", fillcolor=\"#ffffff\"];";
// $CHOSEN_OPTION = "node [color=\"#7f00ff\", style=\"rounded,filled\", shape=rect, fontcolor=\"black\", fillcolor=\"#ffffff\"];";

// $categories = array();

// $options = array();
// $chosenOptions = array();

// $decisions = array();
// $madeDecisions = array();

// $relations = array();

$root = buildTree($ds);

// for($j = 0; $j < 4 $)
// $root = $root->children[$cat - 1];
// print_r($root);

$unfound = array();
isThere($root);
usort($unfound, "cmp");
// print_r($unfound);

$q = "SELECT * FROM `ds_has_stage`";
$ret = $db->q($q);
if(!$ret)
{
	echo "fail $q<br />";
	return;
}
$stages = array();
while($r = mysqli_fetch_array($ret))
{
	$stages[$r["stage_num"]] = $r["stage_name"];
}
// print_r($stages);

$current = 0;
for($i = 0; $i < count($unfound); $i++)
{
	if($current != $unfound[$i][0])
	{
		echo "<h4>" . $stages[$unfound[$i][0]] . "</h4>";
		$current = $unfound[$i][0];
	}

	echo "<p>" . $unfound[$i][1] . " ( " . $unfound[$i][2] . " )</p>";
}


function isThere($node)
{
	global $db, $unfound;
	$q = "SELECT * FROM `tool_has_thing`
		WHERE thing_id = $node->id";
	$ret = $db->q($q);
	if(!$ret)
	{
		echo "Fail $q<br />";
		return;
	}

	if(mysqli_num_rows($ret) == 0 && $node->removed == -1)
	{
		array_push($unfound, array($node->added,$node->name,$node->type));
	}

	foreach($node->children AS $kid)
	{
		isThere($kid);
	}
}

function cmp($a, $b)
{
	if($a[0] == $b[0])
	{
		if($a[2] == $b[2])
			return 0;

		return $a[2] > $b[2];
	}
		

	return $a[0] > $b[0];
}



// if($dec != -1)
// {
// 	// Then we need to make the root of the tree that decision
// 	$root = findInTreeId($root, $dec);
// }



// $toolList = str_replace(",", " OR tt.tool_id = ", $tools);
// $q = "SELECT * FROM `tool_has_thing` AS tt
// 		WHERE tt.tool_id = $toolList";
// $ret = $db->q($q);
// if(!$ret)
// {
// 	echo "Fail $q<br />";
// }

// $toolThings = array();
// while($r = mysqli_fetch_array($ret))
// {
// 	array_push($toolThings, $r["thing_id"]);
// }

// addToArrays($root);
// getCounts();

// function getCounts()
// {
// 	global $madeDecisions, $chosenOptions, $toolList, $db;

// 	for($i = 0; $i < count($madeDecisions); $i++)
// 	{
// 		$q = "SELECT * FROM `tool_has_thing` AS tt WHERE tt.thing_id = " . 
// 		$madeDecisions[$i]->id . " AND (tt.tool_id = $toolList)";
// 		$ret = $db->q($q);
// 		if(!$ret)
// 		{
// 			echo "Fail $q";
// 			break;
// 		}

// 		$madeDecisions[$i]->count = mysqli_num_rows($ret);
// 	}

// 	for($i = 0; $i < count($chosenOptions); $i++)
// 	{
// 		$q = "SELECT * FROM `tool_has_thing` AS tt WHERE tt.thing_id = " . 
// 		$chosenOptions[$i]->id . " AND (tt.tool_id = $toolList)";
// 		$ret = $db->q($q);
// 		if(!$ret)
// 		{
// 			echo "Fail $q";
// 			break;
// 		}

// 		$chosenOptions[$i]->count = mysqli_num_rows($ret);
// 	}
// }

// function addToArrays($node)
// {
// 	global $categories, $options, $decisions, $madeDecisions, $chosenOptions, $relations, $toolThings;

// 	if($node->type == "category")
// 	{
// 		array_push($categories, new IN($node->id, $node->name));
// 	}
// 	else if($node->type == "decision")
// 	{
// 		if(in_array($node->id, $toolThings))
// 			array_push($madeDecisions,new IN($node->id, $node->name));
// 		else
// 			array_push($decisions, new IN($node->id, $node->name));

// 	}
// 	else
// 	{
// 		if(in_array($node->id, $toolThings))
// 			array_push($chosenOptions, new IN($node->id, $node->name));
// 		else
// 			array_push($options, new IN($node->id, $node->name));
// 	}

// 	for($i = 0; $i < count($node->children); $i++)
// 	{
// 		$kid = $node->children[$i];

// 		if(!array_key_exists($node->name, $relations))
// 		{
// 			$relations[$node->name] = array();
// 			array_push($relations[$node->name], $kid);
// 		}
// 		else
// 		{
// 			array_push($relations[$node->name], $kid);
// 		}

// 		addToArrays($kid);
// 	}
// }

// $output = "digraph bob {";

// $output .= "$CATEGORY\n";
// $output .= printArray($categories);

// $output .= "\n\n$DECISION\n";
// $output .= printArray($decisions);

// $output .= "\n\n$OPTION\n";
// $output .= printArray($options);

// if($heat)
// {
// 	$output .= printHeatedArray($madeDecisions, "decision");
// 	$output .= printHeatedArray($chosenOptions, "option");
// }
// else
// {
// 	$output .= "\n\n$MADE_DECISION\n";
// 	$output .= printArray($madeDecisions);

// 	$output .= "\n\n$CHOSEN_OPTION\n";
// 	$output .= printArray($chosenOptions);
// }

// $output .= "\n\n\n";

// $keys = array_keys($relations);

/*foreach($keys as $k)
{
	$kids = $relations[$k];
	$kd = array();
	$kco = array();
	$ko = array();
	 
	// The ones that are decisions and chosen options need to be out on their own
	foreach($kids AS $kid)
	{
		if($kid->type == "decision")
		{
			array_push($kd, $kid->name);
		}
		else
		{
			if(in_array($kid->name, $chosenOptions))
				array_push($kco, $kid->name);
			else
				array_push($ko, $kid->name);
		}
	}

	foreach($kd AS $d)
	{
		$output .= "\n\"$k\"->\"$d\" [arrowhead=none]";
	}

	if(count($kco > 0))
	{
		$output .= "\n\"$k\"";

		foreach($kco AS $o)
		{
			$output .= "->\"$o\"";
		}

		$output .= " [arrowhead=none]";
	}

	if(count($ko) > 0)
	{
		$output .= "\n\"$k\"";

		foreach($ko AS $o)
		{
			$output .= "->\"$o\"";
		}

		$output .= " [arrowhead=none,color=\"#888888\",style=\"dashed\"]";
	}
}*/

// function printArray($array)
// {
// 	$arr = "";
// 	for($i = 0; $i < count($array); $i++)
// 	{
// 		$arr .= "\"" . $array[$i]->name . "\"";
// 		if($i < count($array) - 1) $arr .= ",";
// 	}
	
// 	return $arr;
// }



// function printHeatedArray($array, $type)
// {
// 	global $tools, $output;
// 	$toolCount = count(json_decode("[" . $tools . "]"));

// 	// 7F00FF: 269, 100, 100
// 	// e0b2ff: 275, 30, 100

// 	// $optionColour = "

// 	// Need the total number of tools
	

// 	foreach($array AS $a)
// 	{
// 		$sat = ($a->count / $toolCount);

// 		$h1 = 269/360;
// 		$h2 = 275/360;

// 		$rgb1 = HSV_TO_RGB($h1, $sat, 1);
// 		$rgb2 = HSV_TO_RGB($h2, $sat, 1);

// 		if($type == "decision")
// 		{
// 			$output .= "\nnode[shape=box,color=\"#000000\",fillcolor=\"#$rgb1\",fontcolor=\"#000000\",style=filled];\n";
// 		}
// 		else
// 		{
// 			$output .= "\nnode [color=\"#$rgb2\", style=\"rounded,filled\", shape=rect, fontcolor=\"black\", fillcolor=\"#$rgb1\"];\n";
// 		}

// 		$output .= "\"" . $a->name . "\"\n";
// 	}
// }

// $output .= "}";
// echo nl2br($output); 

// echo $output;

// $handle = fopen("output.gv", "w");
// fwrite($handle, $output);
// fclose($handle);

// header('Content-Type: application/octet-stream');
// header('Content-Disposition: attachment; filename='.basename('output.gv'));
// header('Expires: 0');
// header('Cache-Control: must-revalidate');
// header('Pragma: public');
// header('Content-Length: ' . filesize('output.gv'));
// readfile('output.gv');

?>