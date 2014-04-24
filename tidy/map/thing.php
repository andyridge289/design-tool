<?php

class Thing
{
	public $id;
	public $name;
	public $type;
	public $kids;
	public $parent;

	function Thing($id, $name, $type)
	{
		$this->id = $id;
		$this->name = $name;
		$this->type = $type;
		$this->kids = array();
	}
	
	function addChild($child)
	{
		array_push($this->kids, $child);
	}
}

?>