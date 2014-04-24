<?php

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

	function Thing($id, $name, $type, $ds)
	{
		$this->id = $id;
		$this->name = trim(trim($name));
		$this->type = $type;
		$this->kids = array();

		$this->description = "description";
		$this->ds = "";
		$this->dsCode = "";
		$this->step = 1;
		$this->parentName = " A name";
	}
	
	function addChild($child)
	{
		array_push($this->kids, $child);
	}

	function toString()
	{	
		$ret = "{
				id: $this->id,
				" .
				// "name: \"$this->step: $this->name\"	.			"
				"name: \"$this->name\"" .
				",data: {
					type: \"$this->type\",
					description: \"$this->description\",
					ds: \"$this->ds\",
					dsCode: \"$this->dsCode\",
					step: \"$this->step\",
					parent: \"$this->parentName\"
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