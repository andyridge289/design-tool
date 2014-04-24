<html>
	<head>
		<style type="text/css">
			tr
			{
				cursor: pointer;
			}
		</style>
		<script type="text/javascript" src="../lib/jquery-2.0.0.js"></script>
		<script type="text/javascript">

			var first;
			var second;

			function go(firstStage, secondStage)
			{
				$.ajax({
					url: "set_manipulated.php",
					type: "post",
				 	data: { 
				 		firstThing: first, 
				 		secondThing: second,
				 		stageA: firstStage,
				 		stageB: secondStage
				 	}
				}).done(function( msg ){
					if(msg == "win")
					{
						location.reload();
					}
					else
					{
						alert(msg);
					}
				});
			}

			function setFirst(firstId, firstName)
			{
				first = firstId;
				$("#first_thing").html(firstName);
			}	

			function setSecond(secondId, secondName)
			{
				second = secondId;
				$("#second_thing").html(secondName);
			}

		</script>
	</head>
	<body>
		<div style="width:100%;">
			<div style="width:40%;display:inline;"><b>First:</b>&nbsp;<span id="first_thing"></span></div>
			<div style="width:40%;display:inline;"><b>Second:</b>&nbsp;<span id="second_thing"></span></div>
<?php

require_once "../lib/database.php";
require_once "../lib/build_new_tree.php";

$output = "";

// for($i = 1; $i < 20; $i++)
// {
	// $output .= doStuff($i,4) . "\n\n";
	// break;
// }

$firstStage = (isset($_GET["s"])) ? $_GET["s"] : 1;
$secondStage = $firstStage + 1;

echo "<button onclick='go($firstStage, $secondStage)'>Go</button>
		</div>";


$output = $firstStage . " -> " . $secondStage . "\n,Functional,Non-Functional,Structural,Entity\n";
global $db;

// First get the state of the world at FIRST
$q = "SELECT * FROM `thing` WHERE stage_added <= $firstStage AND (stage_removed > $firstStage OR stage_removed = -1)";
$ret = $db->q($q);
if(!$ret)
{
	echo "Fail $q<br />";
	return;
}
$firstThings = array();

while($r = mysqli_fetch_array($ret))
{
	array_push($firstThings, new A($r["id"], $r["name"], $r["ds"], $r["stage_added"], $r["stage_removed"]));
}


// Now get the state of the world at second
$q = "SELECT * FROM `thing` WHERE stage_added <= $secondStage AND (stage_removed > $secondStage OR stage_removed = -1)";
$ret = $db->q($q);
if(!$ret)
{
	echo "Fail $q<br />";
	return;
}
$secondThings = array();

while($r = mysqli_fetch_array($ret))
{
	array_push($secondThings, new A($r["id"], $r["name"], $r["ds"], $r["stage_added"], $r["stage_removed"]));
}

$firstNames = array();
foreach($firstThings AS $f)
	array_push($firstNames, $f->name);
$secondNames = array();
foreach($secondThings AS $f)
	array_push($secondNames, $f->name);

$removedNames = array_diff($firstNames, $secondNames);
$addedNames = array_diff($secondNames, $firstNames);

$removed = array();
foreach($removedNames AS $r)
{
	foreach($firstThings AS $f)
	{
		if($r == $f->name)
		{
			array_push($removed, $f);
			break;
		}
	}
}

foreach($removed AS $r)
{
	$q = "SELECT * FROM `ds_modified` WHERE thing_removed = " . $r->id . " AND stage = $secondStage"; 
	$ret = $db->q($q);
	if(!$ret)
	{
		echo "$q<br />";
		continue;
	}

	if(mysqli_num_rows($ret) > 0)
		$r->edit = true;
}

$added = array();
foreach($addedNames AS $r)
{
	foreach($secondThings AS $f)
	{
		if($r == $f->name)
		{
			array_push($added, $f);
			break;
		}
	}
}

foreach($added AS $r)
{
	$q = "SELECT * FROM `ds_modified` WHERE thing_added = " . $r->id . " AND stage = $secondStage"; 
	$ret = $db->q($q);
	if(!$ret)
	{
		echo "$q<br />";
		continue;
	}

	if(mysqli_num_rows($ret) > 0)
		$r->edit = true;
}

echo "<table><tr><th>Removed</th><th>Added</th></tr>";

for($i = 0; $i < max(array(count($removed), count($added))); $i++)
{
	echo "<tr>";

	if($i < count($removed))
	{
		if($removed[$i]->edit)
			echo "<td style='background:#ffd2e8;' onclick='setFirst(" . $removed[$i]->id . ", \"" . $removed[$i]->name . "\")'>" . $removed[$i]->name . "</td>";
		else
			echo "<td onclick='setFirst(" . $removed[$i]->id . ", \"" . $removed[$i]->name . "\")'>" . $removed[$i]->name . "</td>";
	}
	else
		echo "<td></td>";

	if($i < count($added))
	{
		if($added[$i]->edit)
			echo "<td style='background:#ffd2e8;' onclick='setSecond(" . $added[$i]->id . ", \"" . $added[$i]->name . "\")'>" . $added[$i]->name . "</td>";
		else
			echo "<td onclick='setSecond(" . $added[$i]->id . ", \"" . $added[$i]->name . "\")'>" . $added[$i]->name . "</td>";
	}
	else
	{
		echo "<td></td>";
	}

	echo "</tr>";
}

echo "</table>";



$modr = array();
for($i = 0; $i < count($removed); $i++)
{
	if($removed[$i]->edit)
	{
		array_push($modr, $removed[$i]);
		array_splice($removed, $i, 1);
		$i--;
	}
}

$moda = array();
for($i = 0; $i < count($added); $i++)
{
	if($added[$i]->edit)
	{
		array_push($moda, $added[$i]);
		array_splice($added, $i, 1);
		$i--;
	}
}

$start = getCounts($firstThings);
$add = getCounts($added);
$rem = getCounts($removed);
$modAdd = getCounts($moda);
$modRem = getCounts($modr);

// Then do what it should be

$end = getCounts($secondThings);

$output .= "Start,$start[0],$start[1],$start[2],$start[3]\n";

$output .= "Removed,$rem[0],$rem[1],$rem[2],$rem[3]\n";
$output .= "Added,$add[0],$add[1],$add[2],$add[3]\n";

$output .= "Mod -,$modRem[0],$modRem[1],$modRem[2],$modRem[3]\n";
$output .= "Mod +,$modAdd[0],$modAdd[1],$modAdd[2],$modAdd[3]\n";

$arr = array();
for($i = 0; $i < 4; $i++)
	$arr[$i] = $start[$i] - $rem[$i] + $add[$i] - $modRem[$i] + $modAdd[$i];

$output .= "End,$end[0],$end[1],$end[2],$end[3]\n";
$output .= "arr,$arr[0],$arr[1],$arr[2],$arr[3]\n";

$handle = fopen("stuff_$secondStage.csv", "w");
fwrite($handle, $output);
fclose($handle);


// 	$idOnes = array();
// 	$oneThings = array();
// 	while($r = mysqli_fetch_array($ret))
// 	{
// 		array_push($oneThings, $r);
// 		array_push($idOnes, $r["name"]);
// 	}

// 	$counts = getCounts($oneThings);
// 	$output .= "Initial,$counts[0],$counts[1],$counts[2],$counts[3]\n";

// 	$q = "SELECT * FROM `thing` WHERE stage_added <= " . ($STAGE + 1) . " AND (stage_removed > " . ($STAGE + 1) . " OR stage_removed = -1)";
// 	$ret = $db->q($q);
// 	if(!$ret)
// 	{
// 		echo "Fail $q<br />";
// 		return;
// 	}

// 	$idTwos = array();
// 	$twoThings = array();
// 	while($r = mysqli_fetch_array($ret))
// 	{
// 		array_push($twoThings, $r);
// 		array_push($idTwos, $r["name"]);
// 	}

// 	$diff = array_diff($idOnes, $idTwos);

// 	// So these are the ones that've been removed
// 	echo "<h4>Removed " . count($diff)  . "</h4>";

// 	$keys = array_keys($diff);
// 	for($i = 0; $i < count($keys); $i++)
// 	{
// 		$k = $keys[$i];
// 		$d = $diff[$k];

// 		if(in_array($d, $idOnes))
// 			echo "" . $d ." (Remove)<br />";
// 		else
// 			echo "" . $d ." (Add)<br />";

// 	}

// 	$same = array();
// 	$unFound = array();
// 	for($i = 0; $i < count($oneThings); $i++)
// 	{
// 		$found = false;
// 		for($j = 0; $j < count($twoThings); $j++)
// 		{
// 			if($oneThings[$i]["id"] == $twoThings[$j]["id"] && $twoThings[$j]["stage_removed"] != $STAGE + 1)
// 			{
// 				array_push($same, $oneThings[$i]);
// 				$found = true;
// 				break;
// 			}
// 		}

// 		if(!$found)
// 		{
// 			// If we get to here then we've not found anything, so look for similar things
// 			array_push($unFound, $oneThings[$i]);
// 		}
// 	}

// 	$counts = getCounts($same);
// 	$output .= "Same,$counts[0],$counts[1],$counts[2],$counts[3]\n";

// 	$added = array();
// 	for($i = 0; $i < count($twoThings); $i++)
// 	{
// 		$found = false;

// 		// If it's in same then we aren't interested.
// 		for($j = 0; $j < count($same); $j++)
// 		{
// 			// HERE HERE HERE HERE HERE HERE HEREREERERERERERERERERERERRE
// 			if($twoThings[$i]["id"] == $same[$j]["id"])
// 			{
// 				$found = true;
// 				break;
// 			}
// 		}

// 		if($found)
// 			continue;

// 		// If it's in unFound then we also aren't interested?
// 		for($j = 0; $j < count($unFound); $j++)
// 		{
// 			if($twoThings[$i]["id"] == $unFound[$j]["id"])
// 			{
// 				$found = true;
// 				break;
// 			}
// 		}

// 		if(!$found)
// 		{
// 			array_push($added, $twoThings[$i]);
// 		}
// 	}

// 	$addedNames = array();

// 	// Duplicate added here
// 	$added2 = array();
// 	foreach($added AS $a)
// 	{
// 		array_push($added2, $a);
// 		array_push($addedNames, $a["name"]);
// 	}

// 	$q = "SELECT * FROM `ds_modified` AS dm 
// 			LEFT JOIN `thing` AS t ON dm.thing_id = t.id
// 			WHERE dm.removed_at = $STAGE";
// 	$ret = $db->q($q);
// 	if(!$ret)
// 	{
// 		echo "Fail $q<br />";
// 		return;
// 	}

// 	$modRemovedNames = array();
// 	$modRemoved = array();
// 	while($r = mysqli_fetch_array($ret))
// 	{
// 		array_push($modRemoved, $r);
// 		array_push($modRemovedNames, $r["name"]);
// 	}

// 	echo "<h4>Modified removed " . count($modRemovedNames) . "</h4>";
// 	foreach($modRemovedNames AS $m)
// 	{
// 		echo "$m<br />";
// 	}


// 	$q = "SELECT * FROM `ds_modified` AS dm 
// 			LEFT JOIN `thing` AS t ON dm.thing_id = t.id
// 			WHERE dm.added_at = " . ($STAGE + 1);
// 	$ret = $db->q($q);
// 	if(!$ret)
// 	{
// 		echo "Fail $q<br />";
// 		return;
// 	}

// 	$modAddedNames = array();
// 	$modAdded = array();
// 	while($r = mysqli_fetch_array($ret))
// 	{
// 		array_push($modAdded, $r);
// 		array_push($modAddedNames, $r["name"]);
// 	}


// 	echo "<h4>Modified added " . count($modAddedNames) . "</h4>";
// 	foreach($modAddedNames AS $m)
// 	{
// 		echo "$m<br />";
// 	}

// 	$addedNames = array_diff($addedNames, $modAddedNames);
// 	echo "<h4>Added " . count($addedNames) . "</h4>";
// 	foreach($addedNames AS $m)
// 	{
// 		echo "$m<br />";
// 	}


// 	$unFound2 = array();
// 	foreach($unFound AS $u)
// 		array_push($unFound2, $u);

// 	for($i = 0; $i < count($unFound); $i++)
// 	{
// 		for($j = 0; $j < count($modRemoved); $j++)
// 		{
// 			if($unFound[$i]["id"] == $modRemoved[$j]["thing_id"])
// 			{
// 				// echo "Removing unfound" . $unFound[$i]["name"] . "<br />";
// 				array_splice($unFound, $i, 1);
// 				$i--;
// 				break;
// 			}
// 		}
// 	}



// 	for($i = 0; $i < count($added); $i++)
// 	{
// 		for($j = 0; $j < count($modAdded); $j++)
// 		{
// 			if($added[$i]["id"] == $modAdded[$j]["thing_id"])
// 			{
// 				// echo "Removing added " . $added[$i]["name"] . "<br />";
// 				array_splice($added, $i, 1);
// 				$i--;
// 				break;
// 			}
// 		}
// 	}

// 	echo "Start: " . count($oneThings) . "<br />";
// 	echo "MATHS: " . (count($oneThings) - count($modRemovedNames) + count($modAddedNames)) . "<br />";
// 	echo "End: " . count($twoThings) . "<br />";



// 	$counts = getCounts($unFound);
// 	$output .= "Removed,$counts[0],$counts[1],$counts[2],$counts[3]\n";

// 	$counts = getCounts($added);
// 	$output .= "Added,$counts[0],$counts[1],$counts[2],$counts[3]\n";

// 	$counts = getCounts($modAdded);
// 	$output .= "Mod removed,$counts[0],$counts[1],$counts[2],$counts[3]\n";

// 	$counts = getCounts($modRemoved);
// 	$output .= "Mod added,$counts[0],$counts[1],$counts[2],$counts[3]\n";

// 	// $q = "SELECT * FROM `thing` WHERE stage_added <= " . ($STAGE + 1) . " AND (stage_removed >= " . ($STAGE + 1) . " OR stage_removed = -1)";
// 	// $ret = $db->q($q);
// 	// if(!$ret)
// 	// {
// 	// 	echo "$q<br />";
// 	// 	return;
// 	// }

// 	$counts = getCounts($twoThings);
// 	$output .= "total,$counts[0],$counts[1],$counts[2],$counts[3]\n";

	// return $output;




function getCounts($arr)
{
	$counts = array(0, 0, 0, 0);
	for($i = 0; $i < count($arr); $i++)
	{
		if($arr[$i]->ds == "Functional")
			$counts[0]++;
		else if($arr[$i]->ds == "Non-Functional")
			$counts[1]++;
		else if($arr[$i]->ds == "Structural")
			$counts[2]++;
		else if($arr[$i]->ds == "Entity")
			$counts[3]++;
	}
	return $counts;
}

class A
{
	public $id;
	public $name;
	public $ds;
	public $added;
	public $removed;
	public $edit;

	function A($id, $name, $ds, $added, $removed)
	{
		$this->id = $id;
		$this->name = $name;
		$this->ds = $ds;
		$this->added = $added;
		$this->removed = $removed;
		$this->edit = false;
	}
}

?>
	</body>
</html>