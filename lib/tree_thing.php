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

	function makeString($dsId)
	{
		$dsCode = "serv";
		$ds = "Entity";
		
		switch ($dsId) 
		{
			case 1:
				$dsCode = "fxn";
				$ds = "Functional";
				break;
			
			case 2:
				$dsCode = "nfxn";
				$ds = "Non-Functional";
				break;

			case 3:
				$dsCode = "struct";
				$ds = "Structural";
				break;
		}

		$ret = "{
				id: $this->id,
				" .
				"name: \"$this->name\"" .
				",data: {
					type: \"$this->type\",
					description: \"" . addslashes($this->description) . "\",
					ds: \"$ds\",
					dsCode: \"$dsCode\",
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
			$ret .= $kids[$i]->makeString($dsId);
		}
		
		$ret .= "
		]}";
		
		return $ret;
	}
}
?>