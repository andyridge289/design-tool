<?php

header('Content-type: text/javascript');

require_once "../../lib/database.php";

$cat = isset($_GET["c"]) ? $_GET["c"] : 1;

$q = "SELECT c.id AS catId, c.name AS catName, c.description AS catDesc, c.step AS catStep,
		d.id AS decId, d.name AS decName, d.description AS decDesc, d.step AS decStep
		FROM category AS c
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
		$root = new Thing($r["catId"], $r["catName"], "category", $r["catDesc"], $r["catStep"]);
		
	$decision = new Thing($r["decId"], $r["decName"], "decision", $r["decDesc"], $r["decStep"]);
	$decision->parent = "category" . $r["catId"];
	$decision->parentName = $r["decName"];

	$root->addChild($decision);
}

$optionList = array();

getChildDecisions($root);
lookForOptions($root);

echo "var ds$cat = " . $root->toString();
echo ";\nvar opt$cat = " . getOptionList();

function getOptionList()
{
	global $optionList;		
	
	$output = "[";
	for($i = 0; $i < count($optionList); $i++)
	{
		if($i > 0) 
			$output .= ",";
		
		$output.= $optionList[$i]->toString();
	}
	
	$output .= "];";
	
	return $output;
}

function getChildDecisions($root)
{
	global $db;

	for($i = 0; $i < count($root->kids); $i++)
	{
		$child = &$root->kids[$i];
	
		$q = "SELECT DISTINCT d.id, d.name, d.description, d.step, dd.parent_id AS parent_id
				FROM decision_has_decision AS dd
				INNER JOIN decision AS d ON dd.child_id = d.id
				WHERE dd.parent_id = " . $child->id;
				
		$ret = $db->q($q);
		if(!$ret)
			continue;
		
		while($r = mysqli_fetch_array($ret))
		{
			$description = addslashes($r["description"]);
			$d = new Thing($r["id"], $r["name"], "decision", $description, $r["step"]);
			$d->parent = "decision" . $r["parent_id"];
			$child->addChild($d);
		}
		
		// Once we've added the children to that decision, get the children of that decision
		getChildDecisions($child);
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
	global $optionList;
	global $db;
	$q = "SELECT o.id, o.name, o.description, o.step, do.decision_id AS decision_id
			FROM decision_has_option AS do 
			INNER JOIN `option` AS o ON do.option_id = o.id
			WHERE do.decision_id = $node->id";
	
	$ret = $db->q($q);
	if(!$ret)
		return;
		
	while($r = mysqli_fetch_array($ret))
	{
		$o = new Thing($r["id"], $r["name"], "option", $r["description"], $r["step"]);
		$o->parent = "decision" . $r["decision_id"];
		array_push($optionList, $o);
		$node->addChild($o);
	}
}

class Thing
{
	public $id;
	public $name;
	public $description;
	public $type;
	public $kids;
	public $ds;
	public $dsCode;
	public $step;
	public $parent;
	public $parentName;

	function Thing($id, $name, $type, $description, $step)
	{
		global $cat;
		
		$this->id = $id;
		$this->name = $name;
		$this->description = $description;
		$this->type = $type;
		$this->kids = array();
		$this->step = $step;

		if($cat == 1)
		{
			$this->ds = "Functional";
			$this->dsCode = "fxn";
		}
		else if($cat == 2)
		{
			$this->ds = "Non-functional";
			$this->dsCode = "nfxn";
		}
		else if($cat == 3)
		{
			$this->ds = "Structural";
			$this->dsCode = "struct";
		}
		else
		{
			$this->ds = "Service";
			$this->dsCode = "serv";
		}
	}
	
	function addChild($child)
	{
		array_push($this->kids, $child);
	}
	
	function toString()
	{	
		$ret = "{
				id: \"$this->type" . "$this->id\",
				" .
				// "name: \"$this->step: $this->name\"	.			"
				"name: \"$this->name\"" .
				",data: {
					type: \"$this->type\",
					description: \"$this->description\",
					ds: \"$this->ds\",
					dsCode: \"$this->dsCode\",
					step: \"$this->step\",
					parent: \"$this->parent\"
				},\n\tchildren: [";
		
		for($i = 0; $i < count($this->kids); $i++)
		{		
			if($i > 0)
				$ret .= ",";
	
			$children = $this->kids;
			$ret .= $children[$i]->toString();
		}
		
		$ret .= "
		]}";
		
		return $ret;
	}
}
?>