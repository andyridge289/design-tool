<?php 

require_once "../../lib/database.php";

$out = "var info = {";

$q = "SELECT * FROM `thing` WHERE id = $_POST[id]";
$ret = $db->q($q);
if(!$ret)
{
	echo "Fail: $q<br />";
	return;
}

$r = mysqli_fetch_array($ret);

$out .= "\"node_info\": {
	\"id\": \"$r[id]\",
	\"name\": \"$r[name]\",
	\"type\": \"$r[type]\",
	\"stage_added\": $r[stage_added],
	\"stage_removed\": $r[stage_removed]
}, \"incoming_connections\": [";

// Find anything that links to this one

$q = "SELECT * FROM `thing_has_thing` AS tt 
	LEFT JOIN `thing` AS t ON t.id = tt.parent_id
	WHERE child_id = $_POST[id] AND tt.dead = 0";
$ret = $db->q($q);
if(!$ret)
{
	echo "Fail: $q<br />";
	return;
}

$i = 0;
while($r = mysqli_fetch_array($ret))
{
	if($i > 0) $out .= ",";
	
	$out .= "{
		\"id\": $r[id],
		\"name\":\"$r[name]\",
		\"type\":\"$r[type]\"
	}";

	$i++;
}

$out .= "], \"outgoing_connections\":[";

$q = "SELECT * FROM `thing_has_thing` AS tt
	LEFT JOIN `thing` AS t ON t.id = tt.child_id
	WHERE parent_id = $_POST[id] AND tt.dead = 0";
$ret = $db->q($q);
if(!$ret)
{
	echo "Fail: $q<br />";
	return;
}

$i = 0;
while($r = mysqli_fetch_array($ret))
{
	if($i > 0) $out .= ",";
	
	$out .= "{
		\"id\": $r[id],
		\"name\":\"$r[name]\",
		\"type\":\"$r[type]\"
	}";

	$i++;
}

$out .= "], \"dead_incoming\":["; 

$q = "SELECT * FROM `thing_has_thing` AS tt 
	LEFT JOIN `thing` AS t ON t.id = tt.parent_id
	WHERE child_id = $_POST[id] AND tt.dead = 1";
$ret = $db->q($q);
if(!$ret)
{
	echo "Fail: $q<br />";
	return;
}

$i = 0;
while($r = mysqli_fetch_array($ret))
{
	if($i > 0) $out .= ",";
	
	$out .= "{
		\"id\": $r[id],
		\"name\":\"$r[name]\",
		\"type\":\"$r[type]\"
	}";

	$i++;
}

$out .= "], \"dead_outgoing\": [";

$q = "SELECT * FROM `thing_has_thing` AS tt
	LEFT JOIN `thing` AS t ON t.id = tt.child_id
	WHERE parent_id = $_POST[id] AND tt.dead = 1";
$ret = $db->q($q);
if(!$ret)
{
	echo "Fail: $q<br />";
	return;
}

$i = 0;
while($r = mysqli_fetch_array($ret))
{
	if($i > 0) $out .= ",";
	
	$out .= "{
		\"id\": $r[id],
		\"name\":\"$r[name]\",
		\"type\":\"$r[type]\"
	}";

	$i++;
}

$out .="]}";

echo $out;

?>