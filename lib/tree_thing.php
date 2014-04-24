<?php

class TreeThing
{
	public $id;
	public $name;
	public $childen;
	public $type;
	public $description;
	public $ds;
	public $dsCode;
	public $added;
	public $removed;
	public $parent;
	public $pCount;

	function TreeThing($id, $name, $type)
	{
		$this->id = $id;
		$this->name = $name;
		$this->type = $type;
		$this->children = array();
		$this->description = "";
		$this->ds = "";
		$this->dsCode = "";
		$this->step = "";
		$this->pCount = 0;
	}
	
	function addChild($child)
	{
		array_push($this->children, $child);
	}

	function makeString()
	{
		$ret = "{
				id: $this->id,
				" .
				// "name: \"$this->step: $this->name\"	.			"
				"name: \"$this->name\"" .
				",data: {
					type: \"$this->type\",
					description: \"" . addslashes($this->description) . "\",
					ds: \"$this->ds\",
					dsCode: \"$this->dsCode\",
					step: \"$this->step\",
					pCount: $this->pCount,
					parent: \"";


		if($this->parent != null)
			$ret .= $this->parent->name;

		$ret .= "\"
				},\n\tchildren: [";
		
		for($i = 0; $i < count($this->children); $i++)
		{		
			if($i > 0)
				$ret .= ",";
	
			$kids = $this->children;
			$ret .= $kids[$i]->makeString();
		}
		
		$ret .= "
		]}";
		
		return $ret;
	}
}
?>