<?php

require_once "database.php";

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

$duplicates = array();
$lines = File("data/" . $files[0]);

for($i = 0; $i < count($lines); )
{
	$line = trim($lines[$i]);

	// 1 Ignore empty lines
	if(strcmp($line, "") == 0)
	{
		array_splice($lines, $i, 1);
		continue;
	}

	// Break it up into an array
	$lineArr = explode(" ", $line);
	// print_r($lineArr);

	// Ignore the first one


	// $line = substr($line, 2);
	// $line = substr($line, 0, strrpos($line, " "));
	$line = "";

	// If the last one is a number then ignore that too
	if(is_numeric(substr($lineArr[count($lineArr) - 1], 0, 1)))
	{
		for($j = 1; $j < count($lineArr) - 1; $j++)
		{
			$line .= $lineArr[$j] . " ";
		}
		// echo "numeric new line $line <br />";
	}
	else // If it isn't a number then add them all
	{
		for($j = 1; $j < count($lineArr); $j++)
		{
			$line .= $lineArr[$j] . " ";
		}
		// echo "new line $line <br />";
	}

	$lines[$i] = trim($line);
	$i++;
}

$last = "";
sort($lines);

for($i = 0; $i < count($lines); $i++)
{
	if(strcmp($lines[$i], "") == 0)
		continue;

	if(strcmp($lines[$i], $last) == 0)
	{
		// if(!in_array($lines[$i], $duplicates))
		// {
			array_push($duplicates, $lines[$i]);
		// }
	}

	$last = $lines[$i];
}

print_r($duplicates)

?>