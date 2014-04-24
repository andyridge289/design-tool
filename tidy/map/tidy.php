<html>
	<head>
		<script type="text/javascript" src="../jquery-2.0.0.js"></script>
		<script type="text/javascript">
			function removeRelation(relationId)
			{
				$.ajax({
					url: "killLink.php",
					type: "post",
					data:
					{
						id: relationId
					}
				}).done(function( msg )
				{
					if(msg == "win")
					{
						alert("OK");
					}
					else
					{
						alert(msg);
					}
				});
			}
		</script>
	</head>
	<body>
<?php

require_once "../../php/database.php";
require_once "build_new_tree.php";

$q = "SELECT * FROM `thing`";
$ret = $db->q($q);
if(!$ret){ echo "Fail: $q<br />"; return; }
$things = array();
while($r = mysqli_fetch_array($ret))
{
	$id = $r["id"];
	$things[$id] = $r;
}

$q = "SELECT * FROM `thing_has_thing`";
$ret = $db->q($q);
if(!$ret){ echo "Fail: $q<br />"; return; }
$relations = array();
while($r = mysqli_fetch_array($ret))
{
	array_push($relations, $r);
}

// countRelations();
// findRecursion();
thingsWithDeadRelations();

function thingsWithDeadRelations()
{
	global $db, $things, $relations;

	$list = array();
	foreach($things AS $t)
	{
		if($t["stage_removed"] != -1)
			continue;

		$allDead = true;
		$q = "SELECT dead FROM `thing_has_thing` WHERE parent_id = $t[id] OR child_id = $t[id]";
		$ret = $db->q($q);
		if(!$ret){ echo "Fail: $q<br />"; continue; }
		
		if(mysqli_num_rows($ret) == 0)
		{
			continue;
		}

		while($row = mysqli_fetch_array($ret))
		{
			if($row["dead"] == 0)
			{
				$allDead = false;
				break;
			}
		}

		if($allDead)
		{
			echo "All dead: $t[name]<br />";
			$q = "UPDATE `thing` SET stage_removed = 0 WHERE id = $t[id]";
			$ret = $db->q($q);
			if(!$ret)
			{
				echo "Fail $q<br />";
			}
		}
	}
}

function findRecursion()
{
	global $newRoot;

	$alreadyParents = array();
	recurse($newRoot, $alreadyParents);
}

function recurse($node, $alreadyParents)
{
	if(in_array($node->name, $alreadyParents))
	{
		echo "Fail $node->name<br />";
	}

	// Put this one in the list of things that are already parents
	array_push($alreadyParents, $node->name);

	// Then look at the kids
	foreach($node->children AS $kid)
	{
		recurse($kid, $alreadyParents);
	}
}

function findDuplicates()
{
	global $relations, $things;

	foreach($things AS &$t)
	{
		$t["is_parent"] = 0;
		$t["is_child"] = 0;
	}

	foreach($relations AS $r)
	{
		if($r["dead"] == 1)
			continue;

		if($things[$r["parent_id"]]["stage_removed"] == -1)
			$things[$r["parent_id"]]["is_parent"]++;

		if($things[$r["child_id"]]["stage_removed"] == -1)
			$things[$r["child_id"]]["is_child"]++;
	}

	$derp = array();
	foreach($things AS $t)
	{
		if($t["is_child"] > 1)
			array_push($derp, $t);
	}

	foreach($derp AS $d)
	{
		echo "<h4>$d[name]</h4>";
		foreach($relations AS $r)
		{
			if($r["dead"] == 1)
				continue;

			if($r["child_id"] == $d["id"])
			{
				echo $r["id"] . "(" . $r["dead"] . ")" . ": " . $things[$r["parent_id"]]["name"] . 
					"   ->   " . $d["name"] . 
					"<button onclick='removeRelation(" . $r["id"] . ")'>Remove</button><br />";
			}
		}
	}
}

function countRelations()
{
	global $relations, $things;

	foreach($things AS $t)
	{
		if($t["stage_removed"] != -1)
			continue;

		$relationCount = 0;

		foreach($relations AS $r)
		{
			if($r["dead"] == 1)
				continue;

			if($r["parent_id"] == $t["id"])
				$relationCount++;

			if($r["child_id"] == $t["id"])
				$relationCount++;
		}

		echo "$t[id] $t[name]: $relationCount<br />";
	}
}





function setDeadRelation()
{
	global $relations, $things, $db;

	foreach($relations AS $r)
	{
		$parent = $things[$r["parent_id"]];
		$child = $things[$r["child_id"]];

		if($parent["stage_removed"] != -1 || $child["stage_removed"] != -1)
		{
			$q = "UPDATE `thing_has_thing` SET dead = 1 WHERE id = $r[id]";
			$ret = $db->q($q);
			if(!$ret)
			{
				echo "Query fail: $q<br />";
			}
		}
	}
}

?>
</body>
</html>