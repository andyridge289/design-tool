<?php

require_once "../lib/database.php";
require_once "../lib/build_new_tree.php";

$index = (isset($_GET["c"])) ? $_GET["c"] : 0;

printNode($newRoot->children[$index]);

function printNode($node)
{
	global $db;
	echo "\\textbf{" . fix($node->name) . "} \\hfill " . $node->type . ": " . getStageName($node->step);

	if($node->parent->name != "root")
		echo "\\hfill solves " . $node->parent->name; 
	echo " \\\\";

	// If the description isn't blank, then echo it and go away.
	if($node->description != "")
	{
		echo fix($node->description);
	}
	else
	{
		// If the description is blank, look for the corresponding decision/option/ds, and find its description
		$q = "SELECT * FROM `ds_map` WHERE thing_id = $node->id";
		$ret = $db->q($q);
		if(!$ret)
		{
			echo "Fail $q<br />";
			return;
		}

		$r = mysqli_fetch_array($ret);
		if(mysqli_num_rows($ret) > 0)
		{
			$q = "SELECT * FROM `$r[old_table]` WHERE id= $r[old_id]";
			$ret = $db->q($q);
			if(!$ret)
			{
				echo "Fail $q<br />";
				return;
			}

			// If the description for that isn't blank, then add it to the thing, and echo it.

			$r = mysqli_fetch_array($ret);
			
			// Add it to the thing!!!
			if($r["description"] == "")
			{
				// If it is, then output at todo
				echo "\\todo{Write a description}";
			}
			else
			{
				$q = "UPDATE `thing` SET description = \"" . addslashes($r["description"]) . "\" WHERE id = $node->id";
				$ret = $db->q($q);
				if(!$ret)
				{
					echo "Fail: $q<br />";
				}

				echo fix($r["description"]);
			}


		}
		else
		{
			// If it is, then output at todo
			echo "\\todo{Write a description}";
		}
	}

	echo "\\\\<br /><br />";
	
	// % Other component attributes are attributes presented by components that relate to neither the functionality of the component nor its popularity.

	for($i = 0; $i < count($node->children); $i++)
	{
		printNode($node->children[$i]);
	}	
}

function getStageName($s)
{
	switch($s)
	{
		case 1:
		case 2:
			return "DS collation";

		case 3:
		case 4:
			return "Literature review";

		case 5:
		case 6:
		case 7:
		case 8:
		case 9:
		case 10:
		case 11:
		case 12:
		case 13:
			return "Tool review 1";

		case 14:
		case 15:
		case 16:
		case 17:
			return "Requirements Gathering";
		
		case 18:
			return "Design study";

		default:
			return "Tool review 2";
	}
}


function fix($text){
    $find = array('&', ' "', '" ', '_');
    $replace = array('\\&', ' ``', '\'\' ', '\_');
    $newtext = str_replace($find, $replace, $text);
    return $newtext;
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