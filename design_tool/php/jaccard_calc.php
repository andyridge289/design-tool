<?php

header('Content-type: text/javascript');

require_once "database.php";

$optionString = isset($_GET["id"]) ? $_GET["id"] : "";
$optionIds = explode(",", $optionString);

$option = new Opt($optionString, "");

$q = "SELECT * FROM `option`";
$ret = $db->q($q);
if(!$ret)
	return;

$options = array();	
while($r = mysqli_fetch_array($ret))
{
	array_push($options, new Opt($r["id"], $r["name"]));
}

foreach($options as $otherOption)
{
	$q = "SELECT DISTINCT tool_id FROM tool_has_option WHERE option_id = $otherOption->id";
	$ret = $db->q($q);
	if(!$ret)
	{
		break;
	}
		
	while($r = mysqli_fetch_array($ret))
	{
		array_push($otherOption->tools, $r["tool_id"]);
	}
}

$option->tools = getIntersection($optionIds);

//echo "var ret = [";
for($i = 0; $i < count($options); $i++)
{
	$ith = $options[$i];
	if($ith == null)
		continue;	

	if($i > 0) echo ",";

	$jaccard = polyJaccard(array($option->tools, $ith->tools)) * 100;
	
	//$rgb = fGetRGB(240, $jaccard, 100);	
	
	echo $jaccard;
	
	//echo "[ $ith->id, " .  json_encode("#$rgb") .  "]";
}
//echo "]";


class Opt
{
	public $id;
	public $name;
	public $tools;
	
	function Opt($id, $name)
	{
		$this->id = $id;
		$this->name = $name;
		$this->tools = array();
	}
}

function getIntersection( $ids )
{
	global $db;
	
	$allTools = array();
	
	for($i = 0; $i < count($ids); $i++)
	{
		$q = "SELECT DISTINCT tool_id FROM tool_has_option WHERE option_id = $ids[$i]";
		$ret = $db->q($q);
		if(!$ret)
			continue;
		
		$tools = array();
		while($r = mysqli_fetch_array($ret))
		{
			array_push($tools, $r["tool_id"]);	
		}
		
		if($i == 0)
		{
			$allTools = $tools;
		}
		else
		{
			$allTools = array_intersect($allTools, $tools);
		}
	}
	
	return $allTools;
}

function polyJaccard( $sets )
{
	$isect = $sets[0];
	$union = $sets[0];
	
	for($i = 1; $i < count($sets); $i++)
	{
		$isect = array_intersect($isect, $sets[$i]);
		$union = array_merge(array_intersect($union, $sets[$i]), 
							 array_diff($union, $sets[$i]), 
							 array_diff($sets[$i], $union));
	}

	if(count($union) == 0)
		return 0;
	else
		return count($isect) / count($union);
}

 function fGetRGB($iH, $iS, $iV) {
 
        if($iH < 0)   $iH = 0;   // Hue:
        if($iH > 360) $iH = 360; //   0-360
        if($iS < 0)   $iS = 0;   // Saturation:
        if($iS > 100) $iS = 100; //   0-100
        if($iV < 0)   $iV = 0;   // Lightness:
        if($iV > 100) $iV = 100; //   0-100
 
        $dS = $iS/100.0; // Saturation: 0.0-1.0
        $dV = $iV/100.0; // Lightness:  0.0-1.0
        $dC = $dV*$dS;   // Chroma:     0.0-1.0
        $dH = $iH/60.0;  // H-Prime:    0.0-6.0
        $dT = $dH;       // Temp variable
 
        while($dT >= 2.0) $dT -= 2.0; // php modulus does not work with float
        $dX = $dC*(1-abs($dT-1));     // as used in the Wikipedia link
 
        switch($dH) {
            case($dH >= 0.0 && $dH < 1.0):
                $dR = $dC; $dG = $dX; $dB = 0.0; break;
            case($dH >= 1.0 && $dH < 2.0):
                $dR = $dX; $dG = $dC; $dB = 0.0; break;
            case($dH >= 2.0 && $dH < 3.0):
                $dR = 0.0; $dG = $dC; $dB = $dX; break;
            case($dH >= 3.0 && $dH < 4.0):
                $dR = 0.0; $dG = $dX; $dB = $dC; break;
            case($dH >= 4.0 && $dH < 5.0):
                $dR = $dX; $dG = 0.0; $dB = $dC; break;
            case($dH >= 5.0 && $dH < 6.0):
                $dR = $dC; $dG = 0.0; $dB = $dX; break;
            default:
                $dR = 0.0; $dG = 0.0; $dB = 0.0; break;
        }
 
        $dM  = $dV - $dC;
        $dR += $dM; $dG += $dM; $dB += $dM;
        $dR *= 255; $dG *= 255; $dB *= 255;
 
       // return round($dR).",".round($dG).",".round($dB);
		
		$dR = str_pad(dechex(round($dR)), 2, "0", STR_PAD_LEFT);
		$dG = str_pad(dechex(round($dG)), 2, "0", STR_PAD_LEFT);
		$dB = str_pad(dechex(round($dB)), 2, "0", STR_PAD_LEFT);
		return $dR.$dG.$dB;
    }

?>