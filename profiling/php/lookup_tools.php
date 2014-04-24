<?php 

require_once "../../lib/database.php";

$out = "var info = [";

$ds = isset($_GET["ds"]) ? $_GET["ds"] : 1;
$tools = isset($_GET["t"]) ? $_GET["t"] : 18;
$toolList = str_replace(",", " OR tool_id = ", $tools);

$q = "SELECT * FROM `tool_has_thing` WHERE tool_id = $toolList";
$ret = $db->q($q);
if(!$ret)
{
	echo "Fail: $q<br />";
	return;
}

$first = true;

while($r = mysqli_fetch_array($ret))
{
	if($first)
		$first = false;
	else
		$out .= ",";

	$out .= $r["thing_id"];
}

$out .= "]";

echo $out;

?>