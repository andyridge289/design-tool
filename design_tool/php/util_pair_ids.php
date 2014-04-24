<?php

require_once "database.php";

ini_set("auto_detect_line_endings", true);

$index = 4;
$lines = file($index . "x" . $index . ".csv");

for($i = 0; $i < count($lines); $i++)
{
	$line = explode(",", $lines[$i]);
	
	$source = getID(trim($line[0]));
	$sinks = array();
	
	for($j = 0; $j < $index - 1; $j++)
	{
		$sink = trim($line[$j + 1]);
		array_push($sinks, getID($sink));
	}
	
	for($k = 0; $k < count($sinks); $k++)
	{
		addLink($source, $sinks[$k]);
	}
}

function addLink($source, $sink)
{
	
	global $db;
	$q = "INSERT INTO option_link VALUES('', $source, $sink)";
	$ret = $db->q($q);
	if(!$ret)
	{
		echo "FAIL FAIL $q<br />";
		return -1;
	}
	
	return 1;
}

function getID($name)
{
	global $db;
	$q = "SELECT id FROM `option` WHERE name = '$name'";
	$ret = $db->q($q);
	if(!$ret)
	{
		echo "err $q";	
		return -1;
	}
	
	$r = mysqli_fetch_array($ret);
	return $r["id"];
}
?>