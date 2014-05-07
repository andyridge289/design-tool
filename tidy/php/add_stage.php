<?php

header('Content-type: text/javascript');

require_once "../../lib/database.php";

$ds = isset($_GET["ds"]) ? $_GET["ds"] : 1;
$name = isset($_GET["name"]) ? addslashes($_GET["name"]) : "a new name";

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

$nextStage = count($stages) > 0 ? $stages[0]->id + 1 : 1;


// The first one is the largest
$q = "INSERT INTO `ds_has_stage` VALUES('', $ds, $nextStage, '$name')";
$ret = $db->q($q);
if(!$ret)
{
	echo "fail $q";
}

$q = "SELECT * FROM `ds_has_stage` WHERE stage_name = '$name'";
$ret = $db->q($q);
if(!$ret)
{
	echo "Fail $q";
}

$r = mysqli_fetch_array($ret);

echo "var newStage = " . json_encode(new Thing($r["stage_num"], $r["stage_name"])) . ";";

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

echo "var stages = " . json_encode($stages) . ";";




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