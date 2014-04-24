<?php

header('Content-type: text/javascript');

require_once "database.php";

$id = isset($_GET["id"]) ? $_GET["id"] : -1;

if($id == -1)
{
	$q = "SELECT * FROM `option` WHERE name = '$_GET[name]'";
	$ret = $db->q($q);
	if(!$ret)
	{
		echo "Lookup fail";
		return;
	}
	
	$r = mysqli_fetch_array($ret);
	$id = $r["id"];
}

$q = "SELECT t.name, t.id FROM tool_has_option AS tho
		LEFT JOIN tool AS t ON tho.tool_id = t.id
		WHERE tho.option_id = " . $id;

$ret = $db->q($q);
if(!$ret)
{
	echo "Fail: $q";
	return;
}

echo "var t = [";
$first = true;

$tools = array();
while($r = mysqli_fetch_array($ret))
{
	if($first)
		$first = false;
	else 
		echo ",";
	
	array_push($tools, $r["id"]);
	echo "[ " . $r["id"] . "," . json_encode($r["name"]) . "]";
}
echo "];";
echo "var tLen = " . count($tools) . ";";

$q = "SELECT * FROM tool";
$ret = $db->q($q);
if(!$ret)
	return;

echo "var tNot = [";
$first = true;

$notTools = array();

while($r = mysqli_fetch_array($ret))
{	
	if(!in_array($r["id"], $tools))
	{
		if($first)
			$first = false;
		else
			echo ",";
		
		array_push($notTools, $r["name"]);
		echo "[ " . $r["id"] . "," . json_encode($r["name"]) . "]";
	}
}
echo "];";
echo "var tNotLen = " . count($notTools) . ";";

?>