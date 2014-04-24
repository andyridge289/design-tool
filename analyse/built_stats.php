<?php

require_once "../lib/database.php";
require_once "../lib/build_new_tree.php";

// $q = "SELECT * FROM `thing` ORDER BY stage_added";
// $ret = $db->q($q);
// if(!$ret)
// {
// 	echo "Fail $q<br />";
// 	return;
// }

// $add = array();
// while($r = mysqli_fetch_array($ret))
// {
// 	if(!array_key_exists($r["stage_added"], $add))
// 	{
// 		$add[$r["stage_added"]] = array();
// 	}

// 	array_push($add[$r["stage_added"]], $r);
// }

// $q = "SELECT * FROM `thing` ORDER BY stage_removed";
// $ret = $db->q($q);
// if(!$ret)
// {
// 	echo "Fail $q<br />";
// 	return;
// }

// $remove = array();
// while($r = mysqli_fetch_array($ret))
// {
// 	if(!array_key_exists($r["stage_removed"], $remove))
// 	{
// 		$remove[$r["stage_removed"]] = array();
// 	}

// 	array_push($remove[$r["stage_removed"]], $r);
// }

// for($i = 1; $i < 17; $i++)
// {
// 	$added = array_key_exists($i, $add) ? count($add[$i]) : 0;
// 	$removed = array_key_exists($i, $remove) ? count($remove[$i]) : 0;
// 	// echo "$i: $added $removed<br />";
// }

// $handle = fopen("added_stage.csv", "w");

// for($i = 1; $i < 17; $i++)
// {
// 	$addedF = 0;
// 	$addedNF = 0;
// 	$addedS = 0;
// 	$addedE = 0;

// 	if(array_key_exists($i, $add))
// 	{
// 		$row = $add[$i];
// 		for($j = 0; $j < count($row); $j++)
// 		{
// 			$node = $row[$j];

// 			if($node["ds"] == "Structural")
// 				$addedS++;
// 			else if($node["ds"] == "Functional")
// 				$addedF++;
// 			else if($node["ds"] == "Non-Functional")
// 				$addedNF++;
// 			else if($node["ds"] == "Entity")
// 				$addedE++;
// 			else
// 			{
// 				echo $node["name"];
// 				break;
// 			}	
// 		}
// 	}

// 	fwrite($handle, "$addedF,$addedNF,$addedS,$addedE\n");
// }

// fclose($handle);
// $handle = fopen("removed_stage.csv", "w");

// for($i = 1; $i < 17; $i++)
// {
// 	$removedF = 0;
// 	$removedNF = 0;
// 	$removedS = 0;
// 	$removedE = 0;

// 	if(array_key_exists($i, $remove))
// 	{
// 		$row = $remove[$i];
// 		for($j = 0; $j < count($row); $j++)
// 		{
// 			$node = $row[$j];

// 			if($node["ds"] == "Structural")
// 				$removedS++;
// 			else if($node["ds"] == "Functional")
// 				$removedF++;
// 			else if($node["ds"] == "Non-Functional")
// 				$removedNF++;
// 			else if($node["ds"] == "Entity")
// 				$removedE++;
// 			else
// 			{
// 				echo $node["name"];
// 				break;
// 			}	
// 		}
// 	}

// 	fwrite($handle, "$removedF,$removedNF,$removedS,$removedE\n");
// }

// fclose($handle); 

// Added sources

// 1 is the initial grab, 2 is also part of this
// 3 is the lit review
	// 4 is Reorganisation
// 5, 6 are Tool review iteration 1
	// 7, 8 are rationalisation
// 9, 10 are Tool review iteration 2
	// 11 is reorganisation
// 12, 13 are tool review
// 14 is SCRAM
	// 15, 16 is reorganisation


$stages = array(array(1, 2), array(3, 3), array(4, 4));

for($j = 0; $j < count($stages); $j++)
{


	$first = $stages[$j][1];
	$second = $stages[$j][0];

	echo "<br /><br /><b>$second - $first</b><br />";

	$q = "SELECT * FROM thing_has_source AS ts
			LEFT JOIN thing AS t ON ts.thing_id = t.id
			WHERE t.stage_added <= $first
			AND t.stage_added >= $second";

	$ret = $db->q($q);
	if(!$ret)
	{
		echo "Fail $q<br />";
	}

	$sources = array();
	for($i = 0; $i < 20; $i++)
	{
		$sources[$i] = array();
	}

	while($r = mysqli_fetch_array($ret))
	{
		array_push($sources[$r["source_id"]], $r);
	}




	for($i = 1; $i < count($sources); $i++)
	{
		$s = $sources[$i];
		
		$counts = getCounts($s);
		echo "$i & $counts[0] & $counts[1] & $counts[2] & $counts[3] & " . count($s) . "<br />";
	}


}

function getCounts($arr)
{
	$counts = array(0, 0, 0, 0);
	for($i = 0; $i < count($arr); $i++)
	{
		if($arr[$i]["ds"] == "Functional")
			$counts[0]++;
		else if($arr[$i]["ds"] == "Non-Functional")
			$counts[1]++;
		else if($arr[$i]["ds"] == "Structural")
			$counts[2]++;
		else if($arr[$i]["ds"] == "Entity")
			$counts[3]++;
	}
	return $counts;
}

?>