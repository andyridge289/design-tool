<?php

// First get the IDs
require_once "participant.php";
require_once "database.php";

///// CONDITIONS

$q = "SELECT * FROM `participant_has_condition`";
$ret = $db->q($q);
if(!$ret){
	echo "Fail $q<br/>";
	return;
}

$participants = array();

while($r = mysqli_fetch_array($ret))
{
	array_push($participants, new Participant($r["participant_id"], $r["condition_id"]));
}

/////////////// CUSTOMS

$q = "SELECT * FROM `participant_has_custom` WHERE split = 1";
$ret = $db->q($q);
if(!$ret)
{
	echo "Fail $q";
	return;
}

while($r = mysqli_fetch_array($ret))
{
	// print_r($r);
	$thing = new Thing($r["id"], $r["name"], "custom"); 
	$thing->description = $r["description"];

	for($i = 0; $i < count($participants); $i++)
	{
		if($r["participant_id"] == $participants[$i]->id)
		{
			// if($participants[$i]->custom == null)
			// 	$participants[$i]->custom = array();

			array_push($participants[$i]->customs, $thing);
		}
	}
}

/////////////// OPTIONS

$q = "SELECT * 
		FROM `participant_has_option`";
$ret = $db->q($q);
if(!$ret)
{
	echo "Fail $q";
	return;
}

while($r = mysqli_fetch_array($ret))
{
	$q2 = "SELECT * 
			FROM `ds_map` AS dm
			LEFT JOIN `thing` AS t ON dm.thing_id = t.id
			WHERE old_id = $r[option_id] AND old_table = 'option'";
	$ret2 = $db->q($q2);
	if(!$ret2)
	{
		echo "Fail $q2<br />";
		continue;
	}

	$r2 = mysqli_fetch_array($ret2);
	$thing = new Thing($r2["id"], $r2["name"], "thing");

	for($i = 0; $i < count($participants); $i++)
	{
		if($r["participant_id"] == $participants[$i]->id)
		{
			if(!thingInArray($thing, $participants[$i]->things))
				array_push($participants[$i]->things, $thing);
		}
	}
}

/////////////  CUSTOM_IS_THINGS

$q = "SELECT ct.thing_id, t.name, pc.participant_id
		FROM `custom_is_thing` AS ct
		LEFT JOIN `thing` AS t ON ct.thing_id = t.id
		LEFT JOIN `participant_has_custom` AS pc ON ct.custom_id = pc.id
		WHERE pc.ignore_custom = 0";
$ret = $db->q($q);
if(!$ret)
{
	echo "Fail $q";
	return;
}

while($r = mysqli_fetch_array($ret))
{
	$thing = new Thing($r["thing_id"], $r["name"], "thing");
	for($i = 0; $i < count($participants); $i++)
	{
		if($r["participant_id"] == $participants[$i]->id)
		{
			// if($r["participant_id"] == 1)
			// {
			// 	echo "ONE ";
			// }

			if(!thingInArray($thing, $participants[$i]->customsAreThings))
				array_push($participants[$i]->customsAreThings, $thing);
		}
	}
}

////////// NEW things

$q = "SELECT tn.id, tn.name, pc.participant_id
		FROM `custom_has_new` AS cn
		LEFT JOIN `thing_new` AS tn on cn.new_id = tn.id
		LEFT JOIN `participant_has_custom` AS pc on cn.custom_id = pc.id";
$ret = $db->q($q);
if(!$ret)
{
	echo "Fail $q";
	return;
}

while($r = mysqli_fetch_array($ret))
{
	$thing = new Thing($r["id"], $r["name"], "new");
	for($i = 0; $i < count($participants); $i++)
	{
		if($r["participant_id"] == $participants[$i]->id)
		{
			if(!thingInArray($thing, $participants[$i]->new))
				array_push($participants[$i]->new, $thing);
		}
	}
}

//////////// SPLIT SPLIT SPLIT

$q = "SELECT sc.id, sc.name, pc.participant_id, pc.id AS old_id
		FROM `participant_has_custom` AS pc
		LEFT JOIN `split_custom` AS sc on pc.id = sc.old_id
		WHERE pc.split = 3";
$ret = $db->q($q);
if(!$ret)
{
	echo "Fail $q";
	return;
}

while($r = mysqli_fetch_array($ret))
{
	// Add the split thing
	$thing = new Thing($r["id"], $r["name"], "split");
	$thing->other = $r["old_id"];
	for($i = 0; $i < count($participants); $i++)
	{
		if($r["participant_id"] == $participants[$i]->id)
		{
			array_push($participants[$i]->split, $thing);
		}
	}
}

/////////// THINGS from SPLIT

// $q = "SELECT t.id, t.name, pc.participant_id
// 		FROM `split_is_thing` AS st
// 		LEFT JOIN `thing` AS t ON st.thing_id = t.id
// 		LEFT JOIN `split_custom` AS sc ON st.split_id = sc.id
// 		LEFT JOIN `participant_has_custom` AS pc ON sc.old_id = pc.id";
// $ret = $db->q($q);
// if(!$ret)
// {
// 	echo "Fail $q";
// 	return;
// }

// while($r = mysqli_fetch_array($ret))
// {
// 	$thing = new Thing($r["id"], $r["name"], "thing");
// 	for($i = 0; $i < count($participants); $i++)
// 	{
// 		if($r["participant_id"] == $participants[$i]->id)
// 		{
// 			if(!thingInArray($thing, $participants[$i]->splitAreThings))
// 				array_push($participants[$i]->splitAreThings, $thing);
// 		}
// 	}
// }

// $participants = read();
// printAll($participants);
write($participants);


//////////////////////////// Support functions

function thingInArray($thing, $array)
{
	for($i = 0; $i < count($array); $i++)
	{
		if($array[$i]->id == $thing->id)
		{
			return true;
		}
	}

	return false;
}

?>