<?php

header('Content-type: text/javascript');

require_once "../../lib/database.php";

$ds = isset($_POST["ds"]) ? $_POST["ds"] : 1;

$q = "SELECT * FROM `ds_has_stage` WHERE ds_id = $ds";
$ret = $db->q($q);
if(!$ret)
{
	echo "fail: $q<br />";
	return;
}

$stages = array();
while($r = mysqli_fetch_array($ret))
{
	array_push($stages, new Thing($r["stage_num"], $r["stage_name"]));
}
usort($stages, "cmp");

echo "var stages = " . json_encode($stages);

class Thing
{
	public $id;
	public $name;

	function Thing($id, $name)
	{
		$this->id = $id;
		$this->name = $name;
	}
}

function cmp($a, $b)
{
	if($a->id == $b->id) return 0;
	return $a->id < $b->id;
}

?>