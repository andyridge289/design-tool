<?php

require_once "../../lib/database.php";


$q = "SELECT * FROM `thing` WHERE stage_removed = -1";
$ret = $db->q($q);
if(!$ret)
{
	echo "Fail: $q<br />";
	return;
}

$things = array();
while($r = mysqli_fetch_array($ret))
{
	array_push($things, $r);
}


$q = "SELECT * FROM `thing_has_thing`";
$ret = $db->q($q);
if(!$ret)
{
	echo "Fail: $q<br />";
	return;
}

$relations = array();
while($r = mysqli_fetch_array($ret))
{
	array_push($relations, $r);
}


foreach($things AS $thing)
{
	if(strcmp($thing["type"], "option") == 0)
	{
		// Then we need to see if it's a child
		$childCount = 0;
		foreach($relations as $rel)
		{
			if($rel["child_id"] == $thing["id"])
			{
				$childCount++;
			}
		}

		if($childCount == 0)
		{
			echo "$thing[name] is never used in a relation<br />";
		}
	}
	else if(strcmp($thing["type"], "decision") == 0)
	{
		$childCount = 0;
		foreach($relations as $rel)
		{
			if($rel["child_id"] == $thing["id"])
			{
				$childCount++;
			}
		}

		if($childCount == 0)
		{
			echo "$thing[name] is never used in a relation<br />";
		}

		$parentCount = 0;
		foreach($relations AS $rel)
		{
			if($rel["parent_id"] == $thing["id"])
			{
				$parentCount++;
			}
		}

		if($parentCount == 0)
		{
			echo "$thing[name] is never used in a relation<br />";
		}
		// Then we need to see if it's a child
		// Or a parent
	}

	
}

?>