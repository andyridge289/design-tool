<?php

require_once "../../lib/build_new_tree.php";

$ds = isset($_GET["ds"]) ? $_GET["ds"] : 1;
$toolIds = isset($_GET["t"]) ? $_GET["t"] : 18;
$dec = isset($_GET["r"]) ? $_GET["r"] : -1;
$cat = isset($_GET["c"]) ? $_GET["c"] : 1;

class IN
{
	public $id;
	public $name;
	public $data;

	function IN($id, $name)
	{
		$this->id = $id;
		$this->name = $name;
		$this->data = array();
	}
}

$options = array();
$tools = array();

// 1 - Work out what options we need to show

$root = buildTree($ds);
$root = $root->children[$cat - 1];

if($dec != -1)
{
	// Then we need to make the root of the tree that decision
	$root = findInTreeId($root, $dec);
}

// Find all the options under this that we need to look up
$optionList = array();
addToArrays($root);
// print_r($optionList);

// 2 - Work out what tools we need to show
$toolIds = "[" . $toolIds . "]";
$toolIds = json_decode($toolIds);
$tools = array();

foreach($toolIds AS $id)
{
	$q = "SELECT * FROM `source` WHERE id = $id";
	$ret = $db->q($q);
	if(!$ret)
	{
		echo "Fail $q";
		continue;
	}

	$r = mysqli_fetch_array($ret);
	$tool = new IN($r["id"], $r["name"]);

	$q2 = "SELECT * FROM `tool_has_thing` WHERE tool_id = $id";
	$ret2 = $db->q($q2);
	if(!$ret2)
	{
		echo "Fail $q2";
		continue;
	}

	while($r2 = mysqli_fetch_array($ret2))
	{
		array_push($tool->data, new IN($r2["id"], $r2["thing_id"]));
	}

	array_push($tools, $tool);
}

$output = "";

// Get all the data for the tools that we want to show, and 
$output .= "Tool,";
for($i = 0; $i < count($optionList); $i++)
{	
	$output .= $optionList[$i]->name;
}

$output .= "\n";

for($i = 0; $i < count($tools); $i++)
{
	$output .= $tools[$i]->name;
	$data = $tools[$i]->data;

	// print_r($tools[$i]->data);

	for($j = 0; $j < count($optionList); $j++)
	{
		$optionId = $optionList[$j]->id;
		$found = false;

		for($k = 0; $k < count($data); $k++)
		{
			if($optionId == $data[$k]->name)
			{
				$found = true;
				break;
			}
		}

		if($found) $output .= "1";
		$output .= ",";
	}

	$output .= "\n";
}

$handle = fopen("output.csv", "w");
fwrite($handle, $output);
fclose($handle);

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename='.basename('output.csv'));
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize('output.csv'));
readfile('output.csv');

function addToArrays($node)
{
	global $optionList;

	if($node->type == "option")
	{
		array_push($optionList, new IN($node->id, $node->name));
	}

	for($i = 0; $i < count($node->children); $i++)
	{
		addToArrays($node->children[$i]);
	}
}





?>