<?php

require_once "../database.php";

ini_set("auto_detect_line_endings", true);

$stuff = array();

for($index = 1; $index < 5; $index++)
{
	$filename = "out_json" . $index . ".json";
	$lines = file($filename);
	
	$stuff[$index] = array();

	for($i = 0; $i < count($lines); $i++)
	{
		$line = $lines[$i];	
		
		if(strpos($line, "name") === false)
		{
			continue;
		}
		
		$line = substr($line, strpos($line, "\"") + 1);
		$colIndex = strpos($line, ":");
		
		$step = trim(substr($line, 0, $colIndex));
		$name = str_replace("\"", "", trim(substr($line, $colIndex + 1)));
		
		if(array_key_exists($step, $stuff[$index]))
		{
			$stuff[$index][$step]++;
		}
		else 
		{
			$stuff[$index][$step] = 0;
		}
	}
}

print_r($stuff);
?>