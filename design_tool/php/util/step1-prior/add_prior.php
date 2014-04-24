<?php

require_once "../database.php";

ini_set("auto_detect_line_endings", true);

$lines = file("new_step.csv");

for($i = 0; $i < count($lines); $i++)
{
	$line = explode(",", $lines[$i]);
	
	
	$q = "INSERT INTO option_has_prior VALUES('', $line[0], $line[1])";
	
	//$ret = $db->q($q);
	if(!$ret) continue;
}

?>