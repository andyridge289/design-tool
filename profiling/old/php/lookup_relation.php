<?php

header('Content-type: text/javascript');

require_once "../../../lib/database.php";

$name = isset($_GET["name"]) ? $_GET["name"] : "";

$q = "SELECT * FROM `thing` AS t
		LEFT JOIN `thing_has_thing` AS tt ON t.id = tt.child_id
		WHERE t.name = '$name'";
$ret = $db->q($q);
if(!$ret){ echo "Fail: $q<br />"; return; }

while($r = mysqli_fetch_array($ret))
{
	print_r($r);

	if(!isset($r["parent_id"]))
	{
		continue;
	}

	$q2 = "SELECT * FROM `thing` AS t
		LEFT JOIN `thing_has_thing` AS tt ON t.id = tt.child_id
		WHERE t.id = $r[parent_id]";
	$ret2 = $db->q($q2);
	if(!$ret2){ echo "Fail: $q2<br />"; continue; }
	$r2 = mysqli_fetch_array($ret2);

	print_r($r2);
}

?>