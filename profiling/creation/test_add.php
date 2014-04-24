<?php

require_once "../../php/database.php";

$files = scandir("data");

usort($files, "cmp");

function cmp($a, $b)
{
	return intval(substr($a, 0, 2)) > intval(substr($b, 0, 2));
}


for($i = 0; $i < count($files); )
{
	if(intval(substr($files[$i], 0, 2)) === 0)
	{
		array_splice($files, $i, 1);
	}
	else
		$i++;
}

$names = array();
$lines = File("data/" . $files[15]);
$lastDecisions = array();

$category = "";
for($i = 0; $i < count($lines); $i++)
{
	$line = $lines[$i];

	// 1 Ignore empty lines
	if(strcmp(trim($line), "") == 0)
	{
		// array_splice($lines, $i, 1);
		continue;
	}

	// Break it up into an array
	$lineArr = explode(" ", $line);

	// // Ignore the first one
	$tabDepth = strlen($lineArr[0]) - 1;

	$lineArr[0] = trim($lineArr[0]);

	$name = "";
	$hasSources = false;

	// And now split up the name, depending on if there's numbers or not
	if(is_numeric(substr($lineArr[count($lineArr) - 1], 0, 1)))
	{
		for($j = 1; $j < count($lineArr) - 1; $j++)
		{
			$name .= $lineArr[$j] . " ";
		}

		$hasSources = true;
	}
	else // If it isn't a number then add them all
	{
		for($j = 1; $j < count($lineArr); $j++)
		{
			$name .= $lineArr[$j] . " ";
		}
	}

	$name = trim($name);
	array_push($names, $name);

	// Work out what type of thing we're dealing with
	$type = "";
	// Sort out the category stuff
	if(strcmp($lineArr[0], "C") == 0)
	{
		$category = $name;
		$type = "category";
		echo "<h4>Category: $category</h4>";
	}
	else if(strcmp($lineArr[0], "D") == 0)
	{
		$type = "decision";
		$lastDecisions[$tabDepth] = $name;
	}
	else if(strcmp($lineArr[0], "O") == 0)
	{
		$type = "option";
	}
	else
	{
		continue;
	}

	// Work out if we need to add it
	$q = "SELECT * FROM `thing` WHERE name = '$name'";
	$ret = $db->q($q);
	if(!$ret)
	{
		echo "Fail $q<br />";
		continue;
	}

	// If we need to add it, then we definitely need to add the links
	if(mysqli_num_rows($ret) == 0)
	{
		echo "Add $type $name<br />";

		if($tabDepth > 0)
			echo "&nbsp;&nbsp;&nbsp;&nbsp;Add " . $lastDecisions[$tabDepth - 1] . " -> " . $name . "<br />";
		else if ($tabDepth == 0 && $type == "decision")
		 	echo "&nbsp;&nbsp;&nbsp;&nbsp;Add $category -> $name<br />";

		if($hasSources)
		{
			$sources = explode(",", $lineArr[count($lineArr) - 1]);
			
			for($j = 0; $j < count($sources); $j++)
			{
				echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Add source $name -> " . $sources[$j] . "<br />";
			}
		}
	}
	else
	{
		// echo "$q " . mysqli_num_rows($ret) . "<br />";

		// If we don't need to add it, we *might* need to add the links
		$q = "";

		if($tabDepth > 0)
			$q = "SELECT * FROM `thing_has_thing` WHERE parent_id IN (SELECT id FROM `thing` WHERE name = '" . 
					$lastDecisions[$tabDepth - 1] . "') AND child_id IN (SELECT id FROM `thing` WHERE name = '$name')";
		else if($type == "decision")
			$q = "SELECT * FROM `thing_has_thing` WHERE parent_id IN (SELECT id FROM `thing` WHERE name = '$category') " .
			 " AND child_id IN (SELECT id FROM `thing` WHERE name = '$name')"; 
		else
			continue;
		// Select * from user_table where id in(SELECT UserID FROM relations WHERE GroupID = '1')

		$ret = $db->q($q);
		if(!$ret)
		{
			echo "Fail $q<br />";
			continue;
		}

		if(mysqli_num_rows($ret) == 0)
		{
			if($tabDepth > 0)
				echo "Add link " . $lastDecisions[$tabDepth - 1] . " -> $name <br />";
			else
				echo "Add link $category -> $name <br />";
		}
		else
		{
			// if($tabDepth > 0)
			// 	echo "Don't link " . $lastDecisions[$tabDepth - 1] . " -> $name <br />";
			// else
			// 	echo "Add link $category -> $name <br />";
		}

	}

	
}

$q = "SELECT * FROM `thing` WHERE stage_removed = -1";
$ret = $db->q($q);
if(!$ret)
{
	echo "Fail $q<br />";
	return;
}

$binList = array();
while($r = mysqli_fetch_array($ret))
{
	/*if(in_array($r["name"], $names))
	{
		// Then it doesn't need removing
	}
	else
	{
		
	}*/

	$bin = true;
	foreach($names AS $name)
	{
		$name = trim($name);
		$arrName = trim($r["name"]);

		if(strcmp($name, $arrName) == 0 || strcasecmp($name, $arrName) == 0)
		{
			$bin = false;
			break;
		}
	}

	if($bin)
			array_push($binList, $arrName);
}

foreach($binList AS $bin)
	echo "Remove $bin<br />";


?>