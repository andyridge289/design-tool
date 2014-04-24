<?php 

require_once "../../lib/database.php";

if(!isset($_POST["type"]))
	return;

if(strcmp($_POST["type"], "relation") === 0)
{
	$q = "UPDATE `thing_has_thing` SET dead = $_POST[new_dead] WHERE parent_id = $_POST[parent_id] AND child_id = $_POST[child_id]";
	$ret = $db->q($q);
	if(!$ret)
	{
		echo "Fail: $q<br />";
		return;
	}

	echo "win";
}
else if(strcmp($_POST["type"], "thing") === 0)
{
	$q = "UPDATE `thing` SET stage_removed = -1 WHERE id = $_POST[id]";
	$ret = $db->q($q);
	if(!$ret)
	{
		echo "Fail: $q<br />";
		return;
	}

	echo "win";
}

?>