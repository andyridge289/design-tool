<?php 

require_once "database.php";

$ds = isset($_GET["ds"]) ? $_GET["ds"] : 1;

$q = "SELECT * FROM `source` WHERE ds_id = $ds AND type = 'tool'";

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
	array_push($sources, new Source($r["id"], $r["name"], $r["creator"], $r["type"]));
}
echo json_encode($sources);

class Source
{
	public $id;
	public $name;
	public $creator;
	public $type;

	function Source($id, $name, $creator, $type)
	{
		$this->id = $id;
		$this->name = $name;
		$this->creator = $creator;
		$this->type = $type;
	}
}

?>