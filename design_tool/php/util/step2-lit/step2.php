<?php

require_once "../../database.php";

$tables = array("decision", "option");

for($i = 0; $i < count($tables); $i++)
{
	echo "<h4>$tables[$i]</h4>";
	
	$q = "SELECT * 
			FROM `$tables[$i]` AS t
			WHERE step = 2";
			
	$ret = $db->q($q);	
	if(!$ret) return;
	
	while($r = mysqli_fetch_array($ret))
	{
		echo "<p>$r[name]</p>";
	}
	
}

?>