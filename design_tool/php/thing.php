<?php

class Thing
{
	public $id;
	public $name;
	public $type;
	public $children;
	public $chosen;
	public $size;
	public $placeholder;

	function Thing($id, $name, $type)
	{
		$this->id = $id;
		$this->name = $name;
		$this->type = $type;
		$this->children = array();
		$this->chosen = 0;
		$this->placeholder = false;
	}

	function addChild($child)
	{
		array_push($this->children, $child);
	}

	function toString()
	{
		return "$this->name [$this->type] = $this->chosen<br />";
	}

	function addToTree($id, $type, $thing)
	{
		if($this->id === $id && $this->type === $type)
		{
			array_push($this->children, $thing);
			return true;
		}
		else
		{
			for($i = 0; $i < count($this->children); $i++)
			{
				$next = $this->children[$i]->addToTree($id, $type, $thing);

				if($next)
					return true;
			}

			// If we get to this point then it's not added to any of its chilren so give up
			return false;
		}
	}

	function toTreeString($level)
	{
		$s = $this->toString();

		for($i = 0; $i < count($this->children); $i++)
		{
			for($j = 0; $j < $level; $j++)
				$s .= "&nbsp;&nbsp;&nbsp;&nbsp;";

			$s .= $this->children[$i]->toTreeString($level+1);
		}

		return $s;	
	}

	function lookup($everything)
	{
		global $toolId;

		if($this->type === "category" || $this->type === "decision")
		{
			for($i = 0; $i < count($this->children); $i++)
			{
				$this->children[$i]->lookup($everything);
				if($this->children[$i]->chosen === 1)
					$this->chosen = 1;
			}
		}
		else if($this->type === "option" && !$everything)
		{
			global $db;
			$q = "SELECT * FROM tool_has_option WHERE tool_id = $toolId AND option_id = $this->id";
			$retval = $db->q($q);
			if(!$retval) return;
			$count = mysqli_num_rows($retval);
			if($count != 0)
				$this->chosen = 1;
		}
		else if($this->type === "option" && $everything)
		{
			global $db;
			$q = "SELECT * FROM tool_has_option WHERE option_id = $this->id";
			$retval = $db->q($q);
			if(!$retval) return;
			$usedCount = mysqli_num_rows($retval);

			$q = "SELECT * FROM tool";
			$retval = $db->q($q);
			if(!$retval) return;
			$toolCount = mysqli_num_rows($retval);

			$this->chosen = $usedCount / $toolCount;
		}
	}

	function arrayicise($all)
	{
		global $madeDecisions, $notmadeDecisions, $chosenOptions, $otherOptions;

		if($this->type === "decision")
			if($this->chosen || $all)
				array_push($madeDecisions, $this);
			else
				array_push($notmadeDecisions, $this);
		else if($this->type == "option")
			if($this->chosen || $all)
				array_push($chosenOptions, $this);
			else
				array_push($otherOptions, $this);

		for($i = 0; $i < count($this->children); $i++)
		{
			$this->children[$i]->arrayicise($all);
		}
	}

	function makeLinks(&$links)
	{

		if(count($this->children) == 0)
			return;

		$dKids = array();
		$oKids = array();
		$pKids = array();

		foreach($this->children AS $child)
		{
			if($child->type === "decision")
			{
				if($child->placeholder === true)
				{
					echo "Adding placeholder $child->name<br />";
					array_push($pKids, $child);
				}
				else
					array_push($dKids, $child);
			}
			else if($child->type === "option")
			{
				array_push($oKids, $child);
			}
		}

		if(count($pKids) > 0)
		{
			$link = "\"$this->name\"->";

			for($i = 0; $i < count($pKids); $i++)
			{
				if($i > 0) $link .= ",";

				$name = $pKids[$i]->name;
				$link .= "\"$name\"";
			}

			$link .= " [style=dashed,arrowhead=none]";

			echo count($pKids) .  " $link<br />";
			array_push($links, $link);
		}

		if(count($dKids) > 0)
		{
			$link = "\"$this->name\"->";

			for($i = 0; $i < count($dKids); $i++)
			{
				if($i > 0) $link .= ",";

				$name = $dKids[$i]->name;	
				$link .= "\"$name\"";
			}

			$link .= " [arrowhead=none]";

			array_push($links, $link);
		}

		if(count($oKids) > 0)
		{
			$link = "\"$this->name\"->";

			for($i = 0; $i < count($oKids); $i++)
			{
				if($i > 0) $link .= "->";

				$name = $oKids[$i]->name;	
				$link .= "\"$name\"";
			}

			// We shouldn't have to worry about placeholders for this one
			$link .= " [arrowhead=none]";

			array_push($links, $link);
		}

		foreach($this->children AS $child)
			$child->makeLinks($links);
	}

	function subtreeSize()
	{
		if(count($this->children) == 0)
		{
			return 1;
		}
		else
		{
			$runningTotal = 1; // 1 for me
			foreach($this->children AS $child)
			{
				$runningTotal += $child->subtreeSize();
			}

			return $runningTotal;
		}
	}

	function containsThing($thing)
	{
		if($this->name === $thing->name)
		{
			return true;
		}
		else if(count($this->children) === 0)
		{
			return false;
		}
		else
		{
			foreach($this->children AS $child)
			{
				if($child->containsThing($thing))
				{
					return true;
				}
			}

			return false;
		}
	}

	function contains($name)
	{
		if($this->name === $name)
		{
			return true;
		}
		else if(count($this->children) === 0)
		{
			return false;
		}
		else 
		{
			foreach($this->children AS $child)
			{
				if($child->contains($name))
				{
					return true;
				}
			}

			return false;
		}
	}
}


function inThingArray($thing, $thingArray)
{
	foreach($thingArray AS $aThing)
	{
		if($thing->name === $aThing->name)
		{
			return true;
		}
	}

	return false;
}

function cmp($a, $b)
{
	$aSize = $a->subtreeSize();
	$bSize = $b->subtreeSize();

	if($aSize == $bSize)
		return 0;
	else if($aSize > $bSize)
		return -1;
	else if($aSize < $bSize)
		return 1;
}

function getV($top, $bottom, $percentage)
{
	$distance = $top - $bottom;
	$distance *= $percentage;
	return $distance + $bottom;
}

function hslToRgb($h, $s, $l)
{
   	$r = 0;
   	$g = 0;
   	$b = 0;

    if($s == 0)
    {
        $r = $g = $b = $l; // achromatic
    }
    else
    {
        $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
        $p = 2 * $l - $q;
        $r = hue2rgb($p, $q, $h + 1/3);
        $g = hue2rgb($p, $q, $h);
        $b = hue2rgb($p, $q, $h - 1/3);
    }

    return dechex($r * 255) .  dechex($g * 255) . dechex($b * 255);
}

function hue2rgb($p, $q, $t)
{
    if($t < 0) $t += 1;
    if($t > 1) $t -= 1;
    if($t < 1/6) return $p + ($q - $p) * 6 * $t;
    if($t < 1/2) return $q;
    if($t < 2/3) return $p + ($q - $p) * (2/3 - $t) * 6;
    return $p;
}

function HSV_TO_RGB ($H, $S, $V) // HSV Values:Number 0-1
{ // RGB Results:Number 0-255
	$RGB = array();

	if($S == 0)
	{
		$R = $G = $B = $V * 255;
	}
	else
	{
		$var_H = $H * 6;
		$var_i = floor( $var_H );
		$var_1 = $V * ( 1 - $S );
		$var_2 = $V * ( 1 - $S * ( $var_H - $var_i ) );
		$var_3 = $V * ( 1 - $S * (1 - ( $var_H - $var_i ) ) );

		if ($var_i == 0) { $var_R = $V ; $var_G = $var_3 ; $var_B = $var_1 ; }
		else if ($var_i == 1) { $var_R = $var_2 ; $var_G = $V ; $var_B = $var_1 ; }
		else if ($var_i == 2) { $var_R = $var_1 ; $var_G = $V ; $var_B = $var_3 ; }
		else if ($var_i == 3) { $var_R = $var_1 ; $var_G = $var_2 ; $var_B = $V ; }
		else if ($var_i == 4) { $var_R = $var_3 ; $var_G = $var_1 ; $var_B = $V ; }
		else { $var_R = $V ; $var_G = $var_1 ; $var_B = $var_2 ; }

		$R = $var_R * 255;
		$G = $var_G * 255;
		$B = $var_B * 255;
	}

	$RGB['R'] = dechex($R);
	$RGB['G'] = dechex($G);
	$RGB['B'] = dechex($B);

	return $RGB['R'] . $RGB['G'] . $RGB['B'];
}

?>