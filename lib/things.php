<?php

class OldThing
{
	public $id;
	public $name;
	public $type;

	function OldThing($id, $name, $type)
	{
		$this->id = $id;
		$this->name = $name;
		$this->type = $type;
	}
}

class NewThing
{
	public $id;
	public $name;
	public $type;

	function NewThing($id, $name, $type)
	{
		$this->id = $id;
		$this->name = $name;
		$this->type = $type;
	}
}

?>