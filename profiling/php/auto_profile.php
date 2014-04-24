<?php

require_once "../../lib/build_new_tree.php";

$ds = isset($_GET["ds"]) ? $_GET["ds"] : 1;

$newRoot = buildTree($ds);
autoProfile($newRoot);

function autoProfile($node)
{
	global $db;

	if($node->id != -1)
	{
		$q = "SELECT * FROM `thing_has_source` as ts
				LEFT JOIN `source` AS s ON ts.source_id = s.id
				WHERE ts.thing_id = $node->id
				AND s.type = 'tool'";
		$ret = $db->q($q);
		if(!$ret)
		{
			echo "Fail: $q<br />";
			return;
		}

		while($r = mysqli_fetch_array($ret))
		{
			$q2 = "SELECT * FROM `tool_has_thing` WHERE tool_id = $r[source_id] AND thing_id = $r[thing_id]";
			$ret2 = $db->q($q2);
			if(!$ret2)
			{
				echo "Fail: $q2<br />";
				continue;
			}

			if(mysqli_num_rows($ret2) == 0)
			{
				$q3 = "INSERT INTO `tool_has_thing` VALUES('', $r[source_id], $r[thing_id], NOW())";
				$ret3 = $db->q($q3);
				if(!$ret3)
				{
					echo "Fail: $q3<br />";
				}
			}

		}
	}


	foreach($node->children AS $kid)
	{
		autoProfile($kid);
	}
}

?>