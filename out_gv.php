<?php

require_once "../lib/build_new_tree.php";

$CATEGORY = "node [color=\"047b35\",fillcolor=\"#8df2b6\",style=filled, shape=house];";
$DECISION = "node[shape=box,color=\"#004a63\",fillcolor=lightblue2,style=filled];";
$OPTION = "node [color=\"#444444\", style=\"rounded,filled\", shape=rect, fontcolor=\"black\", fillcolor=\"#DDDDDD\"];";

$categories = array();
$options = array();
$decisions = array();
$relations = array();

$output = "digraph output {";

$first = $newRoot->children[0];
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

	for($i = 0; $i < count($node->children); $i++)
	{
		$kid = $node->children[$i];
		
		if(!array_key_exists($node->name, $relations))
		{
			$relations[$node->name] = new DecisionOptions($node->name, $kid);
		}
		else
		{
			// echo "$kid->name: $kid->type<br />";
			// if($kid->name == "Feasibility")
				// print_r($kid);

			if($kid->type == "option")
				array_push($relations[$node->name]->options, $kid->name);
			else if($kid->type == "decision")
				array_push($relations[$node->name]->decisions, $kid->name);
			// else
				// echo "What the holy hell $kid->name<br />";
		}

		addToArrays($kid);
	}
}

$output .= "$CATEGORY\n";
$output .= printArray($categories);

$output .= "\n\n$DECISION\n";
$output .= printArray($decisions);

$output .= "\n\n$OPTION\n";
$output .= printArray($options);

$output .= "\n\n\n";
$toPrint = array();

$keys = array_keys($relations);
foreach($keys AS $k)
{
	$do = $relations[$k];

	// echo "$do->decision: " . count($do->decisions) . ", " . count($do->options) . "<br />";

	foreach($do->decisions AS $dod)
	{
		$decisionString = "\"" . $do->decision . "\"->\"" . $dod . "\"";
		array_push($toPrint, $decisionString);
		// echo "decision $decisionString<br />";
	}

	if(count($do->options) > 0)
	{
		$optionString = "\"$do->decision\" -> ";
	
		for($i = 0; $i < count($do->options); $i++)
		{
			if($i > 0)
				$optionString .= "->";

			$optionString .= "\"" . $do->options[$i] . "\"";
		}

		// echo "option $optionString<br />"; 
		array_push($toPrint, $optionString);
	}
	
}

foreach($toPrint as $thing)
{
	$output .= "\n$thing [arrowhead=none]";
}

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

class DecisionOptions
{
	public $decision;
	public $decisions;
	public $options;

	function DecisionOptions($decision, $thing)
	{
		global $options;

		$this->decision = $decision;
		
		$this->options = array();
		$this->decisions = array();

		if($thing->type == "option")
		{
			array_push($this->options, $thing->name);
		}
		else
		{
			array_push($this->decisions, $thing->name);
		}

		// array_push($this->options, $option);
	}
}

$output .= "}"; 
echo nl2br($output);
$handle = fopen("0.gv", "w");
fwrite($handle, $output);
fclose($handle);

?>