<?php

ini_set("auto_detect_line_endings", true);

require_once "../database.php";

$lines = File("req_tag.txt");

for($i = 0; $i < count($lines); $i++)
{
	$line = explode(",", $lines[$i]);	
	$q = "INSERT INTO req_has_code VALUES('', $line[0], $line[1])";
	
	$ret = $db->q($q);
	if(!$ret)
	{
		echo "Fail $q<br />";
	}
}

?>