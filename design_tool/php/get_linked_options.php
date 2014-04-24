<?php

header('Content-type: text/javascript');

require_once "database.php";

if(!isset($_GET["o"]))
{
	echo "var links = [];";
	return;
}

$q = "SELECT DISTINCT ol.sink, o.name FROM `option_link` AS ol 
		LEFT JOIN `option` AS o ON ol.sink = o.id 
		WHERE ol.source = $_GET[o]";
$ret = $db->q($q);
if(!$ret)
{
	echo "Fail: $q";
	return;
}

echo "var links = [";
$first = true;

while($r = mysqli_fetch_array($ret))
{
	if($first)
		$first = false;
	else 
		echo ",";

	echo "[$r[sink], \"$r[name]\" ]";
}
echo "];";
?>