<?php

require_once "../database.php";

$q = "SELECT * FROM reqs";
$ret = $db->q($q);
if(!$ret) return;
while($r = mysqli_fetch_array($ret))
{

	$q2 = "SELECT * FROM req_has_tag WHERE req_id = $r[id]";
	$ret2 = $db->q($q2);
	if(!$ret2) continue;
	
	$c = mysqli_num_rows($ret2);
	
	if($c == 0)
	{
		echo "Req no code $r[requirement]<br />";
	}
	
}

?>