<?php

require_once "database.php";
require_once "things.php";

getThingsForParticipant(2);

function getThingsForParticipant($participantId)
{
	global $db;
	$oldThings = array();

	// Get the ones that are definitely options
	// $q = "SELECT * FROM `participant_has_option` AS po
	// 	LEFT JOIN `option` AS o ON po.option_id = o.id
	// 	WHERE po.participant_id = $participantId";
	// $ret = $db->q($q);
	// if(!$ret){ echo "Fail"; return; }
	// while($r = mysqli_fetch_array($ret))
	// {
	// 	array_push($oldThings, new OldThing($r["option_id"], $r["name"], "option"));
	// }

	// Now get the ones that are custom and options
	$q = "SELECT o.id, o.name
			FROM `custom_is_option` AS co
			LEFT JOIN `participant_has_custom` AS pc ON co.custom_id = pc.id
			LEFT JOIN `option` AS o ON co.option_id = o.id
			WHERE co.option_id != -1 
			AND pc.participant_id = $participantId";

	$ret = $db->q($q);
	if(!$ret){ echo "Fail"; return; }
	while($r = mysqli_fetch_array($ret))
	{
		array_push($oldThings, new OldThing($r["id"], $r["name"], "option"));
		// print_r($r);
		// break;
	}

	print_r($oldThings);

	// And then the custom decisions

	// And then the custom DSs

}

/**
 *  Maps the list of options, decisions or DSs to be a list of things
 **/
function map($oldThings)
{
	global $db;
	$newThings = array();

	if($options != null)
	{
		foreach($options AS $option)
		{
			$q = "SELECT * 
					FROM `ds_map` AS dm
					LEFT JOIN `thing` AS t ON dm.thing_id = t.id
					WHERE old_id = $option->id AND old_table = '$option->type'";
			
			$ret = $db->q($q);
			if(!$ret){ echo "Fail"; continue; }
			while($r = mysqli_fetch_array($ret))
			{
				array_push($newThings, new NewThing($r["id"], $r["name"], $r["type"]));
			}
		}
	}

	return $newThings;
}

?>