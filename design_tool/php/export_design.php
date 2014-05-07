<?php

require_once "../../lib/database.php";

header('Content-Type: text/html');

$output = "<html><head></head><body>";

$options = json_decode($_POST["o"]);
$customs = json_decode($_POST["c"]);

$fxn = array(); $nfn = array(); $str = array(); $ent = array();

foreach($options AS $option)
{
	if($option->ds == "Functional")
			array_push($fxn, $option);
		else if($option->ds == "Non-Functional")
			array_push($nfn, $option);
		else if($option->ds == "Structural")
			array_push($str, $option);
		else
			array_push($ent, $option);
}

$dss = array($fxn, $nfn, $str, $ent);
$names = array("Functional", "Non-Functional", "Structural", "Entity");

for($j = 0; $j < count($dss); $j++)
{
	$output .= "<h4>$names[$j] Design choices</h4>";
	$ds = $dss[$j];

	for($i = 0; $i < count($ds); $i++)
	{
		$d = $ds[$i];

		$q = "SELECT * FROM `tool_has_thing` AS tt 
				LEFT JOIN `source` AS s ON tt.tool_id = s.id
				WHERE tt.thing_id = $d->id";
		$ret = $db->q($q);
		if(!$ret)
		{
			echo "Fail $q<br />";
			continue;
		}

		$tools = array();
		while($r = mysqli_fetch_array($ret))
		{
			array_push($tools, $r["name"]);
		}

		$output .= "<p><b>" . $d->name . "</b></p>";
		$output .=  "<p>" . $d->description . "</p>";
		$output .= "<p>Rationale: " . $d->rationale . "</p>";

		$output .= "<p>Tools selected:";
		foreach ($tools as $tool){
			$output .= "<span>" . $tool . "</span>";
		}
		$output .= "</p>";
	}
}

// And now do customs
$output .= "<h4>Custom choices</h4>";

for($i = 0; $i < count($ds); $i++)
{
	$d = $ds[$i];

	$output .= "<p><b>" . $d->name . "</b></p>";
	$output .=  "<p>" . $d->description . "</p>";
	$output .= "<p>Rationale: " . $d->rationale . "</p>";

	if($d->decisionLink)
	{
		$output .= "<p>Solves: " . $d->decisionLink . "</p>";
	}
}


$output .=  "</body></html>";

echo $output;

?>