<?php

require_once "database.php";

$cat = isset($_GET["c"]) ? $_GET["c"] : 1;

$q = "SELECT c.id AS catId, c.name AS catName, d.id AS decId, d.name AS decName FROM category AS c
		INNER JOIN decision_has_category AS dc ON c.id = dc.category_id
		INNER JOIN decision AS d ON dc.decision_id = d.id
		WHERE c.id = $cat";
$ret = $db->q($q);
if(!$ret)
	return;
	
$root = null;
while($r = mysqli_fetch_array($ret))
{
	if($root === null)
		$root = new Thing($r["catId"], $r["catName"], "category");
		
	$decision = new Thing($r["decId"], $r["decName"], "decision");
	$root->addChild($decision);
}

getChildDecisions($root);
lookForOptions($root);
lookForCodes($root);
lookForRequirements($root);

// So here we have a tree of stuff, now add in the requirements too







$filename = "req$cat.json";
$handle = fopen($filename, "w");
fwrite($handle, "var dsreq$cat = " . $root->toString());
fclose($handle);

function getChildDecisions($root)
{
	global $db;

	for($i = 0; $i < count($root->kids); $i++)
	{
		$child = &$root->kids[$i];
	
		$q = "SELECT DISTINCT d.id, d.name FROM decision_has_decision AS dd
				INNER JOIN decision AS d ON dd.child_id = d.id
				WHERE dd.parent_id = " . $child->id;
		$ret = $db->q($q);
		if(!$ret)
			continue;
		
		while($r = mysqli_fetch_array($ret))
		{
			$d = new Thing($r["id"], $r["name"], "decision");
			$child->addChild($d);
		}
		
		// Once we've added the children to that decision, get the children of that decision
		getChildDecisions($child);
	}
}

function lookForRequirements($node)
{
	if(strcmp($node->type, "code") === 0)
	{
		getRequirements($node);
	}
	else
	{
		for($i = 0; $i < count($node->codes); $i++)
		{
			lookForRequirements($node->codes[$i]);
		}
	
		for($i = 0; $i < count($node->kids); $i++)
		{
			lookForRequirements($node->kids[$i]);
		}
	}
}

function getRequirements($node)
{
	global $db;
	$q = "SELECT * FROM req_has_tag AS rt
			INNER JOIN reqs AS r ON rt.req_id = r.id
			WHERE rt.tag_id = $node->id";
	$ret = $db->q($q);
	if(!$ret)
		return;
		
	while($r = mysqli_fetch_array($ret))
	{
		$req = new Thing($r["req_id"], $r["requirement"], "requirement");
		$node->addChild($req);
	}
}

function lookForCodes($node)
{
	if(strcmp($node->type, "option") === 0)
	{
		// Just lookup, don't recurse
		getCodes($node, "option_has_code", "option_id");
	}
	else if(strcmp($node->type, "requirement") == 0)
	{
		// Don't do anything
	}
	else if(strcmp($node->type, "decision") == 0)
	{
		getCodes($node, "decision_has_code", "decision_id");
		
		for($i = 0; $i < count($node->kids); $i++)
		{
			lookForCodes($node->kids[$i]);
		}
	}
	else
	{
		for($i = 0; $i < count($node->kids); $i++)
		{
			lookForCodes($node->kids[$i]);
		}
	}
}

function getCodes($node, $table, $id)
{
	global $db;
	
	$q = "SELECT r.id, r.code FROM $table AS t
			INNER JOIN req_has_tag AS rt ON t.code_id = rt.tag_id
			INNER JOIN code AS r ON rt.tag_id = r.id
			WHERE t.$id = $node->id";
			
	$ret = $db->q($q);
	if(!$ret)
	{
		echo "Fail $q<br >";
		return;
	}
	
	while($r = mysqli_fetch_array($ret))
	{
		$req = new Thing($r["id"], $r["code"], "code");
		$node->addChild($req);
	}
}

function lookForOptions($node)
{
	if(strcmp($node->type, "category") === 0)
	{
		for($i = 0; $i < count($node->kids); $i++)
		{
			lookForOptions($node->kids[$i]);
		}
	} 
	else if(strcmp($node->type, "decision") === 0)
	{
		getOptions($node);
		
		for($i = 0; $i < count($node->kids); $i++)
		{
			lookForOptions($node->kids[$i]);
		}
	}
}

function getOptions($node)
{
	global $db;
	$q = "SELECT o.id, o.name FROM decision_has_option AS do 
			INNER JOIN `option` AS o ON do.option_id = o.id
			WHERE do.decision_id = $node->id";
	
	$ret = $db->q($q);
	if(!$ret)
		return;
		
	while($r = mysqli_fetch_array($ret))
	{
		$o = new Thing($r["id"], $r["name"], "option");
		$node->addChild($o);
	}
}

class Thing
{
	public $id;
	public $name;
	public $type;
	public $kids;
	public $codes;

	function Thing($id, $name, $type)
	{
		$this->id = $id;
		$this->name = $name;
		$this->type = $type;
		$this->kids = array();
		$this->codes = array();
	}
	
	function addChild($child)
	{
		array_push($this->kids, $child);
	}
	
	function addCode($req)
	{
		array_push($this->codes, $req);
	}
	
	function toString()
	{
		$ret = "{\n\tid: $this->id,\n\tname: \"$this->name\",\n\tdata: {\n\t\ttype: \"$this->type\"\n\t}";

		$ret .= "\n,codes:[";
		
		$reqs = $this->reqs;
		for($i = 0; $i < count($this->reqs); $i++)
		{
			if($i > 0) $ret .= ",";
			
			$ret .= $reqs[$i]->toString();
		}
		
		$ret .= "],children:[";

		$children = $this->kids;		
		for($i = 0; $i < count($this->kids); $i++)
		{		
			if($i > 0)
				$ret .= ",";
	

			$ret .= $children[$i]->toString();
		}
		
		$ret .= "]}";
		
		return $ret;
	}
}



?>