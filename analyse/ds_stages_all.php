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
		<!-- <div style="width:100%;">
			<div style="width:40%;display:inline;"><b>First:</b>&nbsp;<span id="first_thing"></span></div>
			<div style="width:40%;display:inline;"><b>Second:</b>&nbsp;<span id="second_thing"></span></div> -->
<?php

require_once "../lib/database.php";
require_once "../lib/build_new_tree.php";


$output = "";
for($i = 1; $i < 21; $i++)
{
	$output .= gogogo($i,$i+1) . "\n\n";
}

echo nl2br($output);

$handle = fopen("stuff_ALL.csv", "w");
fwrite($handle, $output);
fclose($handle);

function gogogo($firstStage, $secondStage)
{

	$output = $firstStage . " -> " . $secondStage . "\n";//,Functional,Non-Functional,Structural,Entity\n";
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
		array_push($firstThings, new A($r["id"], $r["name"], $r["type"], $r["ds"], $r["stage_added"], $r["stage_removed"]));
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
		array_push($secondThings, new A($r["id"], $r["name"], $r["type"],  $r["ds"], $r["stage_added"], $r["stage_removed"]));
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

	$start = getSplitThings($firstThings);
	$add = getSplitThings($added);
	$rem = getSplitThings($removed);
	$modAdd = getSplitThings($moda);
	$modRem = getSplitThings($modr);

	// Then do what it should be
	$end = getSplitThings($secondThings);

	$output .= "Source,FD,FO,NFD,NFO,SD,SO,ED,EO\n";

	$output = printShit("Initial", $start, $output);
	$output = printShit("Removed", $rem, $output);
	$output = printShit("Added", $add, $output);
	$output = printShit("Modified out", $modRem, $output);
	$output = printShit("Modified in", $modAdd, $output);
	$output = printShit("Final", $end, $output);

	return $output;
}

function printShit($text, $arr, $output)
{
	$output .= $text;
	for($i = 0; $i < 4; $i++)
	{
		$counts = getOtherCounts($arr[$i]);
		$output .= "," . $counts[0] . "," . $counts[1];
	}
	$output .= "\n";
	return $output;
}

function getOtherCounts($arr)
{
	

	$counts = array(0, 0);
	for($i = 0; $i < count($arr); $i++)
	{
		// print_r($arr[$i]);	
		// echo "<br /><br />";

		if($arr[$i]->type == "decision")
			$counts[0]++;
		else if($arr[$i]->type == "option")
			$counts[1]++;
	}

	return $counts;
}

function getSplitThings($arr)
{
	$counts = array(array(), array(), array(), array());
	for($i = 0; $i < count($arr); $i++)
	{
		if($arr[$i]->ds == "Functional")
			array_push($counts[0], $arr[$i]);
		else if($arr[$i]->ds == "Non-Functional")
			array_push($counts[1], $arr[$i]);
		else if($arr[$i]->ds == "Structural")
			array_push($counts[2], $arr[$i]);
		else if($arr[$i]->ds == "Entity")
			array_push($counts[3], $arr[$i]);
	}
	return $counts;
}

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
	public $type;
	public $ds;
	public $added;
	public $removed;
	public $edit;

	function A($id, $name, $type, $ds, $added, $removed)
	{
		$this->id = $id;
		$this->name = $name;
		$this->type = $type;
		$this->ds = $ds;
		$this->added = $added;
		$this->removed = $removed;
		$this->edit = false;
	}
}

?>
	</body>
</html>