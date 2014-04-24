<?php

require_once "study_database.php";

// echo $_POST["stuff"];
$postData = $_POST["stuff"];


// "customOptions":[{"id":-1,"name":"Test Decision","rationale":"Why not indeed?","description":"Test Decision thingy","ds":"Custom Option","dsCode":"custom","addedFrom":null,"toolView":null,"optionView":null,"optionViewProportion":0,"decision":null},{"id":-1,"name":"Trigger Type Menu","rationale":"Users need a high level view to be able to choose triggers or actions. Needs to be suitably general and unambiguous. E.g. \"SMS\" or \"Webpage\". ","description":"Top level menu to select a trigger/action type","ds":"Custom Option","dsCode":"custom","addedFrom":null,"toolView":null,"optionView":null,"optionViewProportion":0,"decision":null},{"id":-1,"name":"Trigger Type Submenu","rationale":"As the user has already selected a general area, they need to be able to pick their exact trigger or action.","description":"Select the exact trigger/action from an area selected in the main menu. For example, \"receive a message\" (under an \"SMS\" menu) or \"at 5pm\" (under a \"Time\" menu).","ds":"Custom Option","dsCode":"custom","addedFrom":null,"toolView":null,"optionView":null,"optionViewProportion":0,"decision":null},{"id":-1,"name":"Trigger/Action Parameters Menu","rationale":"Users should be able to personalise a trigger/action with respect to that trigger/action. For example being able to construct a text message to send, or restrict what is being gathered in a trigger.","description":"For a given trigger/action, a choice of par...ing most frequently and which most recently. This also allows them to help diagnose any problems if the composition tool claims a composition has run, whereas the user sees no \'real life\' evidence for this.","description":"The list of compositions (currently running, and all compositions) should be sortable by a range of metrics. In particular, when a composition was last run, and how many times a composition has run.","ds":"Custom Option","dsCode":"custom","addedFrom":null,"toolView":null,"optionView":null,"optionViewProportion":0,"decision":null},{"id":-1,"name":"Large \"Make Composition\" button on opening","rationale":"Users should be able to quickly get into the area for making a composition to allow for maximum productivity.","description":"The first page/screen/window of the tool should have a large button to make a new composition.","ds":"Custom Option","dsCode":"custom","addedFrom":null,"toolView":null,"optionView":null,"optionViewProportion":0,"decision":null},{"id":-1,"name":"Colour coding/Logos for each Trigger/Action type","rationale":"Users should be able to easy distinguish between compositions or service choices at a glance.","description":"Each high-level service type should have either a colour coding or large easy to see logo. This should be prominent both in menus and in displaying compositions.","ds":"Custom Option","dsCode":"custom","addedFrom":null,"toolView":null,"optionView":null,"optionViewProportion":0,"decision":null}],"unChosenCustomOptions":[]}';

$stuff = json_decode($postData);

$participant = $stuff->p;
$clicks = $stuff->c;
$time = floor($stuff->time);

// The normal option arrays
$options = $stuff->options;
$unchosen = $stuff->unChosenOptions;

$q = "INSERT INTO participant_metrics VALUES('',$participant,$clicks,$time)";
$ret = $db->q($q);
if(!$ret) { echo "Fail $q<br />"; return; }

$queries = makeSQL($options, false);
$unChosenQueries = makeSQL($unchosen, true);

$failures = array();

foreach($queries AS $q)
{
	$ret = $db->q($q);

	if(!$ret)
		array_push($failures, "fail: $q");
}

// The custom option arrays
$custom = $stuff->customOptions;
$unchosenCustom = $stuff->unChosenCustomOptions;

$customQueries = makeCustomSQL($custom, false);
$unchosenCustomQueries = makeCustomSQL($unchosenCustom, true);

foreach($customQueries AS $q)
{
	$ret = $db->q($q);

	if(!$ret)
		array_push($failures, "fail: $q");
}

if(count($failures) === 0)
{
	echo "win";
}
else
{
	echo "lose: ";

	foreach($failures AS $fail)
		echo $fail . "\n";
}


function makeSQL($array, $unchosen)
{
	global $participant;
	$sqls = array();

	for($i = 0; $i < count($array); $i++)
	{
		$item = $array[$i];

		$sql = "INSERT INTO `participant_has_option` VALUES(\"\"," . $participant . "," .
			 substr($item->id, 6) . ", \"" . mysql_real_escape_string($item->rationale) . "\", ";

			 // print_r($item);

		// It should always be -1
		$sql .= $item->addedFromId . ",";

		if($item->toolView == null)
			$sql .= "\"\",";
		else
			$sql .= "\"" . $item->toolView . "\",";

		if($item->optionView == null)
			$sql .= "\"\",0,";
		else
			$sql .= "\"" . $item->optionView . "\"," . $item->optionViewProportion . ",";

		if($unchosen)
			$sql .= "1,NOW())";
		else
			$sql .= "0,NOW())";

		array_push($sqls, $sql);
	}

	return $sqls;
}

function makeCustomSQL($array, $unchosen)
{
	global $participant;
	$sqls = array();

	for($i = 0; $i < count($array); $i++)
	{
		$item = $array[$i];
		$sql = "INSERT INTO `participant_has_custom` VALUES(\"\", " . $participant . ",\"" . mysql_real_escape_string($item->name) .
				"\", \"" . mysql_real_escape_string($item->description) . "\", \"" . mysql_real_escape_string($item->rationale) . "\",";

		if($unchosen)
			$sql .= "1,NOW(),0,0)";
		else
			$sql .= "0,NOW(),0,0)";


		array_push($sqls, $sql);
	}

	return $sqls;
}


?>