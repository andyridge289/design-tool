<?php 

require_once "../../lib/database.php";

$out = "var info = [";

$toolId = isset($_POST["tool_id"]) ? $_POST["tool_id"] : 18;

$q = "SELECT * FROM `tool_has_thing` WHERE tool_id = $toolId";
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