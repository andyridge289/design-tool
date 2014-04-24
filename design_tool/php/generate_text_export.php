<?php

//header('Content-type: text/javascript');

require_once "database.php";

//$constraints = isset($_GET["c"]) ? explode(",", $_GET["c"]) : array();
$options = isset($_GET["o"]) ? explode(",", $_GET["o"]) : array();

echo "<h1>Design Tool Export</h1>";

echo "<h2>Constraints</h2>";

echo getStuff($constraints);

echo "<h2>Options</h2>";
echo getStuff($options);

function getStuff($arr)
{
	global $db;
	$out = "";
	
	for($i = 0; $i < count($arr); $i++)
	{
		$q = "SELECT
			o.name AS o_name,
			o.description AS o_description,
			d.name AS d_name,
			c.name AS c_name
			 FROM `option` AS o 
			LEFT JOIN `decision_has_option` AS do
				ON o.id = do.id
			LEFT JOIN `decision` AS d
				ON do.decision_id = d.id
			LEFT JOIN `category` AS c
				ON do.category_id = c.id
			WHERE o.id = $arr[$i]";
			
		$ret = $db->q($q);
		if(!$ret)
			continue;
			
		$r = mysqli_fetch_array($ret);
		
		$out .= "<h4>$r[o_name]</h4><p>$r[o_description]</p><p>Solves: <i>$r[d_name]</i> in <i>$r[c_name]</i></p>";
	}
	
	return $out;
}

?>