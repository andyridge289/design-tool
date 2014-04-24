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
$STAGE = 16;
$lines = File("data/" . $files[$STAGE - 1]);
$lastDecisions = array();

$category = "";
for($i = 0; $i < count($lines); $i++)
{
	$line = $lines[$i];

	// 1 Ignore empty lines
	if(strcmp(trim($line), "") == 0)
	{
		// array_splice($lines, $i, 1);
		echo "Empty line, continuing<br />";
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
		// echo "Decision $tabDepth = $name<br />";
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
	$q2 = "SELECT * FROM `thing` WHERE name = '$name'";
	$ret2 = $db->q($q2);
	if(!$ret2)
	{
		echo "Fail $q<br />";
		continue;
	}

	// If we need to add it, then we definitely need to add the links
	if(mysqli_num_rows($ret2) == 0)
	{
		$q = "INSERT INTO `thing` VALUES('', '$name', '$type', '$category', $STAGE, -1)";
		// echo "$q<br />";
		$ret = $db->q($q);
		if(!$ret)
		{
			echo "Fail: $q<br />";
			continue;
		}

		if($tabDepth > 0)
		{
			// Get the ID of the thing
			$q = "SELECT id FROM `thing` WHERE name = '$name'";
			// echo "$q<br />";
			$ret = $db->q($q);
			if(!$ret)
			{
				echo "Fail $q<br />";
				continue;
			}
			$r = mysqli_fetch_array($ret);
			$child_id = $r["id"];

			// Get the ID of the parent decision
			$q = "SELECT id FROM `thing` WHERE name = '" . $lastDecisions[$tabDepth - 1] . "'";
			// echo "$q<br />";
			$ret = $db->q($q);
			if(!$ret)
			{
				echo "Fail: $q<br />";
				continue;
			}
			$r = mysqli_fetch_array($ret);
			$parent_id = $r["id"];

			$q = "INSERT INTO `thing_has_thing` VALUES('', $parent_id, $child_id, 0)";
			echo "$q<br />";
			$ret = $db->q($q);
			if(!$ret)
			{
				echo "Fail: $q<br />";
				continue;
			}
			// echo "&nbsp;&nbsp;&nbsp;&nbsp;Add " . $lastDecision[$tabDepth - 1] . " -> " . $name . "<br />";
		}
		else if ($tabDepth == 0 && $type == "decision")
		{
		 	// echo "&nbsp;&nbsp;&nbsp;&nbsp;Add $category -> $name<br />";

		 	$q = "SELECT id FROM `thing` WHERE name = '$category'";
		 	// echo "$q<br />";
		 	$ret = $db->q($q);
		 	if(!$ret)
		 	{
		 		echo "Fail $q<br />";
		 		continue;
		 	}

		 	$r = mysqli_fetch_array($ret);
		 	$category_id = $r["id"];

		 	$q = "SELECT id FROM `thing` WHERE name = '$name'";
			$ret = $db->q($q);
			if(!$ret)
			{
				echo "Fail $q<br />";
				continue;
			}
			$r = mysqli_fetch_array($ret);
			$child_id = $r["id"];

			$q = "INSERT INTO `thing_has_thing` VALUES('', $category_id, $child_id, 0)";
			$ret = $db->q($q);
			if(!$ret)
			{
				echo "Fail: $q<br />";
				continue;
			}
		}

		if($hasSources)
		{
			$sources = explode(",", $lineArr[count($lineArr) - 1]);

		 	$q = "SELECT id FROM `thing` WHERE name = '$name'";
			$ret = $db->q($q);
			if(!$ret)
			{
				echo "Fail $q<br />";
				continue;
			}
			$r = mysqli_fetch_array($ret);
			$child_id = $r["id"];
			
			for($j = 0; $j < count($sources); $j++)
			{
				// echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Add source $name -> " . $sources[$j] . "<br />";
				$q = "INSERT INTO `thing_has_source` VALUES('', $child_id, $sources[$j])";
				$ret = $db->q($q);
				if(!$ret)
				{
					echo "Fail $q<br />";
					continue;
				}
			}
		}
	}
	else
	{
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
			// Then we need to add it
			if($tabDepth > 0)
			{
				// Get the ID of the thing
				$q = "SELECT id FROM `thing` WHERE name = '$name'";
				$ret = $db->q($q);
				if(!$ret)
				{
					echo "Fail $q<br />";
					continue;
				}
				$r = mysqli_fetch_array($ret);
				$child_id = $r["id"];

				// Get the ID of the parent decision
				$q = "SELECT id FROM `thing` WHERE name = '" . $lastDecisions[$tabDepth - 1] . "'";
				$ret = $db->q($q);
				if(!$ret)
				{
					echo "Fail: $q<br />";
					continue;
				}
				$r = mysqli_fetch_array($ret);
				$parent_id = $r["id"];

				$q = "INSERT INTO `thing_has_thing` VALUES('', $parent_id, $child_id, 0)";
				$ret = $db->q($q);
				if(!$ret)
				{
					echo "Fail: $q<br />";
					continue;
				}
				// echo "&nbsp;&nbsp;&nbsp;&nbsp;Add " . $lastDecision[$tabDepth - 1] . " -> " . $name . "<br />";
			}
			else if ($tabDepth == 0 && $type == "decision")
			{
			 	// echo "&nbsp;&nbsp;&nbsp;&nbsp;Add $category -> $name<br />";

			 	$q = "SELECT * FROM `thing` WHERE name = '$category'";
			 	echo "$q<br />";
			 	$ret = $db->q($q);
			 	if(!$ret)
			 	{
			 		echo "Fail $q<br />";
			 		continue;
			 	}

			 	$r = mysqli_fetch_array($ret);
			 	$category_id = $r["id"];
			 	echo "$r[id]<br />";

			 	$q = "SELECT id FROM `thing` WHERE name = '$name'";
				$ret = $db->q($q);
				if(!$ret)
				{
					echo "Fail $q<br />";
					continue;
				}
				$r = mysqli_fetch_array($ret);
				$child_id = $r["id"];

				$q = "INSERT INTO `thing_has_thing` VALUES('', $category_id, $child_id, 0)";
				echo $q;
				$ret = $db->q($q);
				if(!$ret)
				{
					echo "Fail: $q<br />";
					continue;
				}
			}
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

while($r = mysqli_fetch_array($ret))
{
	if(in_array($r["name"], $names))
	{
		// Then it doesn't need removing
	}
	else
	{
		// echo "Remove $r[name]<br />";
		$q2 = "UPDATE `thing` SET stage_removed = $STAGE WHERE id = $r[id]";
		$ret2 = $db->q($q2);
		if(!$ret2)
		{
			echo "Fail: $q<br />";
			continue;
		}
	}
}

?>