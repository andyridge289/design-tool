<?php

require_once "database.php";

$q = "SELECT * FROM code";

$ret = $db->q($q);
if(!$ret)
	return;
	
$codes = array();
while($r = mysqli_fetch_array($ret))
{
	array_push($codes, $r["id"]);
}

shrinkCodes("decision_has_code");
shrinkCodes("option_has_code");
shrinkCodes("not_applicable_codes");

function shrinkCodes($table)
{
	global $codes, $db;

	foreach($codes AS $code)
	{
		$q = "SELECT * FROM $table WHERE code_id = $code";
		$ret = $db->q($q);
		if(!$ret)
			continue;
			
		if(mysqli_num_rows($ret) > 0)
		{
			//echo "Removing $code<br />";
			$codes = array_diff($codes, array($code));
		}
	}
}

echo "<html><body><table>";
foreach($codes as $code)
{
	$q = "SELECT * FROM code WHERE id = $code";
	$ret = $db->q($q);
	if(!$ret)
		continue;
		
	$r = mysqli_fetch_array($ret);
	echo "<tr><td>$r[id]</td><td>$r[code]</td></tr>";
}

echo "</table></body></html>";

?>