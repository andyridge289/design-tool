<?php

header('Content-type: text/javascript');

require_once "../../../lib/database.php";

$q = "SELECT * FROM `thing`";
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