<?php
 
require_once "database.php";

set_time_limit(0);
 
 // $optionId = isset($_GET["id"]) ? $_GET["id"] : "";
// $optionName = isset($_GET["name"]) ? $_GET["name"] : "";
// $option = new Opt($optionId, $optionName);
// 
$q = "SELECT * FROM `option`";
$ret = $db->q($q);
if(!$ret)
	return;

$options = array();	
while($r = mysqli_fetch_array($ret))
{
	array_push($options, new Option($r["id"], $r["name"]));
}

foreach($options as $option)
{
	$q = "SELECT DISTINCT tool_id FROM tool_has_option WHERE option_id = $option->id";
	$ret = $db->q($q);
	if(!$ret)
		return;
		
	while($r = mysqli_fetch_array($ret))
	{
		array_push($option->tools, $r["tool_id"]);
	}
}

$q = "SELECT * FROM tool";
$ret = $db->q($q);
if(!$ret)
	return;

$c = mysqli_num_rows($ret);
$t = $c / 3;

for($i = 0; $i < count($options); $i++)
{
	$option = $options[$i];
	
	if(count($options[$i]->tools) < $t)
	{
		unset($options[$i]);
	}
}

$handle = fopen("jaccard4.csv", "w");

for($i = 0; $i < count($options); $i++)
{
	$ith = $options[$i];
	
	$ic = count($ith->tools);
	if($ic < $t)
		continue;
	
	for($j = $i + 1; $j < count($options); $j++)
	{
		$jth = $options[$j];
		$jc = count($jth->tools);
		if($jc < $t)
			continue;

		
		for($k = $j + 1; $k < count($options); $k++)
		{
			$kth = $options[$k];
			$kc = count($kth->tools);
			if($kc < $t)
				continue;
			
			for($m = $k + 1; $m < count($options); $m++)
			{
				$mth = $options[$m];
				$mc = count($mth->tools);
				if($mc < $t)
					continue;
				
				$ret = polyJaccard(array($ith->tools, $jth->tools, $kth->tools, $mth->tools));
				$jaccard = $ret[1];
				$isect = $ret[0];
				
				if($jaccard < 0.5) // We really don't care if it's lower than this
					continue;
							
				$line = "$jaccard,$isect,$ith->name,$ic,$jth->name,$jc,$kth->name,$kc,$mth->name,$mc\n";
				fwrite($handle, $line);
			}
		}
	}
	
	echo (count($options) - $i) . " to go<br />";
	flush();
}

echo "Done";

fclose($handle);

// if(count($option->tools) < 6)
// {
	// // The count is too low, it's not worth doing
	// echo "Not enough data";
	// return;
// }
// 
// for($i = 0; $i < count($options); $i++)
// {
	// $ith = $options[$i];
	// if($ith == null)
		// continue;
// 	
	// for($j = $i; $j < count($options); $j++)
	// {
		// $jth = $options[$j];
		// if($jth == null)
			// continue;
// 		
		// if($ith->id === $jth->id)
			// continue;
// 			
		// //$jaccard = polyJaccard(array($ith->tools, $jth->tools));
		// //echo "<tr><td>$ith->name</td><td>$jth->name</td><td>$jaccard</td></tr>";
// 		
		// for($k = $j; $k < count($options); $k++)
		// {
			// $kth = $options[$k];
			// if($kth == null)
				// continue;
// 			
			// if($ith->id === $kth->id || $jth->id === $kth->id)
				// continue;
// 				
			// $jaccard = polyJaccard(array($ith->tools, $jth->tools, $kth->tools));
			// echo "<tr><td>$ith->name</td><td>$jth->name</td><td>$kth->name</td><td>$jaccard</td></tr>";
// 			
			// /*for($m = 0; $m < count($options); $m++)
			// {
				// $mth = $options[$m];
// 			
				// if($ith->id === $mth->id || $jth->id === $mth->id || $kth === $mth->id)
					// continue;
// 					
				// array_push($combos, array($ith, $jth, $kth, $mth));
			// }*/
		// }
	// }
// 	
// }


class Option
{
	public $id;
	public $name;
	public $tools;
	
	function Option($id, $name)
	{
		$this->id = $id;
		$this->name = $name;
		$this->tools = array();
	}
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
	
	return array(count($isect), count($isect) / count($union));
}

?>