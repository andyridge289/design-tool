<?php

require_once "../database.php";

$handle = fopen("options_and_decisions.csv", "w");

$q = "SELECT * FROM decision WHERE step = 1";
$ret = $db->q($q);
if(!$ret) return;

while($r = mysqli_fetch_array($ret))
{
	$line = "$r[id],$r[name],\n";
	fwrite($handle, $line);
}

$q = "SELECT * FROM `option` WHERE step = 1";
$ret = $db->q($q);
if(!$ret) return;

while($r = mysqli_fetch_array($ret))
{
	$line = "$r[id],$r[name],\n";
	fwrite($handle, $line);
}

fclose($handle);

?>