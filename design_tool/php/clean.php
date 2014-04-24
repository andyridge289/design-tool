<?php

require_once "database.php";

$q = "SELECT * FROM `option`";
	
/*echo "<h3>Dead</h3><h4>Dead options</h4>";
$retval = $database->query($q);
if(!$retval)
{
	return;
}

while($row = mysqli_fetch_array($retval))
{
	$q2 = "SELECT * FROM `decision_has_option` WHERE option_id = $row[id]";
	$retval2 = $database->query($q2);
	if(!$retval2)
	{
		continue;
	}
	
	if(mysqli_num_rows($retval2) == 0)
	{
		echo "$row[name]<br />";
	}
}

echo "<h4>Dead decisions</h4>";
$q = "SELECT * FROM decision";
$retval = $database->query($q);
if(!$retval)
{
	return;
}
while($r = mysqli_fetch_array($retval))
{
	$queries = array("SELECT * FROM decision_has_category WHERE decision_id = $r[id]",
	"SELECT * FROM decision_has_decision WHERE parent_id = $r[id]",
	"SELECT * FROM decision_has_decision WHERE child_id = $r[id]",
	"SELECT * FROM decision_has_option WHERE decision_id = $r[id]");
	
	$resuts = array(0, 0, 0, 0);
	
	for($i = 0; $i < count($queries); $i++)
	{
		$q2 = $queries[$i];
		$retval2 = $database->query($q2);
		if(!$retval)
		{
			continue;
		}
		
		$results[$i] = mysqli_num_rows($retval2);
	}
	
	$sum = $results[0] + $results[1] + $results[2] + $results[3];
	
	if($sum == 0)
	{
		echo "$r[name]<br />";
	}
}*/

echo "<h3>Doubly Used</h3><h4>Doubly-used options</h4>";
$q = "SELECT * FROM `option`";
$retval = $database->query($q);
if(!$retval)
{
	return;
}

while($row = mysqli_fetch_array($retval))
{
	$q2 = "SELECT 
		DISTINCT decision_id, option_id, decision.name AS decision_name, `option`.name AS option_name
		FROM decision_has_option 
		LEFT JOIN decision ON decision_has_option.decision_id = decision.id
		LEFT JOIN `option` ON decision_has_option.option_id = `option`.id
		WHERE option_id = $row[id]";

	$retval2 = $database->query($q2);
	if(!$retval2)
	{
		continue;
	}

	
	
	if(mysqli_num_rows($retval2) > 1)
	{
		echo $q2;
		echo "More than one use for Option $row[name]<br />";
	}
}

echo "<h4>Doubly-used decisions</h4>";
$q = "SELECT * FROM `decision`";
$retval = $database->query($q);
if(!$retval)
{
	return;
}

while($row = mysqli_fetch_array($retval))
{
	$q2 = "SELECT DISTINCT parent_id FROM decision_has_decision WHERE child_id = $row[id]";
	$retval2 = $database->query($q2);
	if(!$retval2)
	{
		continue;
	}
	
	$numDecisionUsed = mysqli_num_rows($retval2);
	
	$q2 = "SELECT DISTINCT category_id FROM decision_has_category WHERE decision_id = $row[id]";
	$retval2 = $database->query($q2);
	if(!$retval2)
	{
		continue;
	}
	
	$numCategoryUsed = mysqli_num_rows($retval2);
	
	if($numDecisionUsed + $numCategoryUsed > 1)
	{
		echo "$row[name]<br />";
	}
}

/*echo "<h3>No such thing</h3>";
$noDecisions = array();
$noCategories = array();
$noOptions = array();


// decision_has_option
$q = "SELECT * FROM decision_has_category";
$ret = $db->q($q);
if(!$ret)
{
	return;
}

while($r = mysqli_fetch_array($ret))
{
	$q2 = "SELECT * FROM decision WHERE id = $r[decision_id]";
	$ret2 = $database->query($q2);
	if(!$retval2)
	{
		continue;
	}
	
	if(mysqli_num_rows($ret2) == 0)
	{
		// If it's not used then add it to an array of unused decisions
		$row = mysqli_fetch_array($retval2);
		array_push($noDecisions, $row["name"]);
	}
	
	
	$q2 = "SELECT * FROM category WHERE id = $r[category_id]";
	$ret2 = $database->query($q2);
	if(!$ret2)
	{
		continue;
	}
	
	if(mysqli_num_rows($ret2) == 0)
	{
		$row = mysqli_fetch_array($retval2);
		array_push($noCategories, $row["name"]);
	}
}

$q = "SELECT * FROM decision_has_decision";
$ret = $database->query($q);
if(!$ret)
{
	return;
}

while($r = mysqli_fetch_array($retval))
{
	$q2 = "SELECT * FROM decision WHERE id = $r[parent_id]";

	$retval2 = $database->query($q);
	if(!$ret2)
	{
		continue;
	}
	
	if(mysqli_num_rows($ret2) > 0)
	{
		$row = mysqli_fetch_array($retval2);
		print_r($row);

		if(in_array($row["name"], $noDecisions))
		{
			$noDecisions = array_diff($noDecisions, array($row["name"]));
		}
	}
	
	$q2 = "SELECT * FROM decision WHERE id = $r[child_id]";
	$ret2 = $database->query($q);
	if(!$ret2)
	{
		continue;
	}
	
	if(mysqli_num_rows($ret2) > 0)
	{
		$row = mysqli_fetch_array($retval2);
		if(in_array($row["name"], $noDecisions))
		{
			$noDecisions = array_diff($noDecisions, array($row["name"]));
		}
	}
}

$q = "SELECT * FROM decision_has_option";
$ret = $database->query($q);
if(!$ret)
{
	return;
}

while($r = mysqli_fetch_array($retval))
{
	$q2 = "SELECT * FROM decision WHERE id = $r[decision_id]";
	$ret2 = $database->query($q2);
	if(!$ret2)
	{
		continue;
	}
	
	if(mysqli_num_rows($ret2) > 0)
	{
		$row = mysqli_fetch_array($retval2);
		if(in_array($row["name"], $noDecisions))
		{
			$noDecisions = array_diff($noDecisions, array($row["name"]));
		}
	}
	
	$q2 = "SELECT * FROM `option` WHERE id = $r[option_id]";
	$ret2 = $database->query($q2);
	if(!$ret2)
	{
		continue;
	}
	
	if(mysqli_num_rows($ret2) == 0)
	{
		$row = mysqli_fetch_array($retval2);
		array_push($noOptions, $r["option_id"]);
	}
	
}*/


echo "<h4>Categories</h4>";
print_r($noCategories);
echo "<h4>Decisions</h4>";
print_r($noDecisions);
echo "<h4>Options</h4>";
print_r($noOptions);

?>