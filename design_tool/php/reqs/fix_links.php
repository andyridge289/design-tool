<?php

require_once "../database.php";

echo "<table><tr><td>";
$q = "SELECT * FROM reqs";
$ret = $db->q($q);
if(!$ret) return;
while($r = mysqli_fetch_array($ret))
{
	$q2 = "SELECT * FROM req_has_code WHERE req_id = $r[id]";
	$ret2 = $db->q($q2);
	if(!$ret2) continue;
	$c = mysqli_num_rows($ret2);
	
	if($c == 0)
		echo "<p>$r[id]: $r[requirement]</p>";
}
echo"</td><td>";

$q = "SELECT * FROM code";
$ret = $db->q($q);
if(!$ret) return;
while($r = mysqli_fetch_array($ret))
{
	echo "<p>$r[id]: $r[code]</p>";
}

echo "</td></tr></table>";

?>