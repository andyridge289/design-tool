<?php

class TreeThing
{
	public $id;
	public $name;
	// public $data;
	public $childen;
	public $type;
	public $description;
	public $ds;
	public $dsCode;
	public $step;
	public $parent;

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
	}
	
	function addChild($child)
	{
		array_push($this->children, $child);
	}

	function makeString()
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