<?php 

require_once "../../lib/database.php";
require_once "../../lib/tree_thing.php";

// This will be the list of things that aren't parents
$q = "SELECT `thing`.* FROM `thing` WHERE id NOT IN(SELECT parent_id FROM `thing_has_thing`) AND `thing`.ds_id = $_GET[ds_id] AND `thing`.stage_removed = -1 ORDER BY `thing`.id";
$ret = $db->q($q);
if(!$ret)
{
	echo "Fail: q<br />";
	return;
}

$notParents = array();
while($r = mysqli_fetch_array($ret))
{
	array_push($notParents, $r);
}

// This will be the list of things that aren't parents
$q = "SELECT `thing`.* FROM `thing` WHERE id NOT IN(SELECT child_id FROM `thing_has_thing`) AND `thing`.ds_id = $_GET[ds_id] AND `thing`.stage_removed = -1 ORDER BY `thing`.id";
$ret = $db->q($q);
if(!$ret)
{
	echo "Fail: q<br />";
	return;
}

$notKids = array();
while($r = mysqli_fetch_array($ret))
{
	array_push($notKids, $r);
}

$neither = array();
$j = 0;

for($i = 0; $i < count($notParents); $i++)
{
	$pid = $notParents[$i]["id"];

	while($notKids[$j]["id"] < $pid && $j < count($notKids) - 1)
	{
		$j++;
	}

	if($notKids[$j]["id"] == $pid)
	{
		$t = new TreeThing($notParents[$i]["id"], $notParents[$i]["name"], $notParents[$i]["type"]);
		array_push($neither, $t);
	}
}

echo "var unThings = " . json_encode($neither) . ";";

?>