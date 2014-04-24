<?php

header('Content-type: text/javascript');

require_once "../../lib/database.php";

$ds = isset($_POST["ds"]) ? $_POST["ds"] : 1;

$q = "SELECT * FROM `thing` WHERE ds_id = $ds";
$ret = $db->q($q);
if(!$ret)
{
	echo "fail: $q<br />";
	return;
}

$names = array();
while($r = mysqli_fetch_array($ret))
{
	array_push($names, new Thing($r["id"], $r["name"]));
}

echo "var things = " . json_encode($names);

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

?>