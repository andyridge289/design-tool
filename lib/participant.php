<?php

class Participant
{
	public $id;
	public $condition;

	public $customs;
	public $customsAreThings;
	public $split;

	public $things;
	public $new;

	function Participant($id, $condition)
	{
		$this->id = $id;
		$this->condition = $condition;
		
		$this->customs = array();
		$this->customsAreThings = array();
		$this->split = array();
		
		$this->things = array();
		$this->new = array();
	}

	function pString()
	{
		
	}
}

class Thing
{
	public $id;
	public $name;
	public $type;
	public $description;
	public $other;

	function Thing($id, $name, $type)
	{
		$this->id = $id;
		$this->name = $name;
		$this->type= $type;
		$this->description = "";
	}
}

function printAll($participants)
{
	foreach($participants AS $p)
		echo "<br />[$p->id]: $p->condition<br />Customs: " . count($p->customs) . 
				"<br />Split: " . count($p->split) . "<br />Things: " . count($p->things) . 
				"<br />Custom things " . count($p->customsAreThings) .
				"<br />New: " . count($p->new) . "<br />";
}

function write($participants)
{
	$handle = fopen("participants.txt", "w");
 	fwrite($handle, json_encode($participants));
	fclose($handle);
}

function read()
{
	$contents = file_get_contents("participants.txt");
	$participants = json_decode($contents);

	return $participants;
}

?>