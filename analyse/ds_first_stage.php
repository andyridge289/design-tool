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

$q = "SELECT * FROM `thing` WHERE stage_added <= 1 AND (stage_removed > 1 OR stage_removed = -1)";
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


$sources = array();
foreach($firstThings AS $f)
{
	$q2 = "SELECT * FROM `thing_has_source` WHERE thing_id = $f->id";
	$ret2 = $db->q($q2);
	if(!$ret2)
	{
		echo "Fail $q2<br />";
		return;
	}

	while($r2 = mysqli_fetch_array($ret2))
	{
		if($r2["source_id"] > 10)
			continue;

		if(!array_key_exists($r2["source_id"], $sources))
			$sources[$r2["source_id"]] = array();

		array_push($sources[$r2["source_id"]], $f);
	}
}


$keys = array_keys($sources);
$total = 0;
echo "Source,FD,FO,NFD,NFO,SD,SO<br />";
foreach($keys AS $k)
{
	$counts = getSplitThings($sources[$k]);

	echo "$k";
	for($i = 0; $i < 3; $i++)
	{
		$counts2 = getOtherCounts($counts[$i]);
		echo "," . $counts2[0] . "," . $counts2[1];
	}

	echo "<br />";

	$total += count($sources[$k]);
}

echo $total;

function getOtherCounts($arr)
{
	$counts = array(0, 0);
	for($i = 0; $i < count($arr); $i++)
	{
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