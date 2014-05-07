<?php 

require_once "database.php";

$ds = isset($_GET["ds"]) ? $_GET["ds"] : 1;

$q = "SELECT * FROM `thing` WHERE ds_id = $ds AND type = 'decision'";

// echo $q;

$ret = $db->q($q);
if(!$ret)
{
	echo "Fail: $q<br />";
	return;
}

$sources = array();
while($r = mysqli_fetch_array($ret))
{
	array_push($sources, new Source($r["id"], $r["name"], $r["type"]));
}
echo json_encode($sources);

class Source
{
	public $id;
	public $name;
	public $type;

	function Source($id, $name, $type)
	{
		$this->id = $id;
		$this->name = $name;
		$this->type = $type;
	}
}

?>