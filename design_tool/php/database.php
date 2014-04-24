<?php

class Database
{
	private $username = "root";
	private $password = "password";
	private $database = "design_tool";
	private $host = "localhost";

	private $connection = null;

	function Database()
	{
		$this->connect();
	}
	
	private function connect()
	{
		$this->connection = mysqli_connect($this->host, $this->username, $this->password, $this->database);
	}
	
	function query($sql)
	{
		if(!$this->connection)
			$this->connect();
			
		$result = mysqli_query($this->connection, $sql);
		
		if(!$result)
		{
			//echo "Query fail: $sql<br />";
			return false;
		}
		else
		{
			return $result;
		}
	}

	function q($sql)
	{
		if(!$this->connection)
			$this->connect();
			
		$result = mysqli_query($this->connection, $sql);
		
		if(!$result)
		{
			//echo "Query fail: $sql<br />";
			return false;
		}
		else
		{
			return $result;
		}
	}	
	
}

$database = new Database();
$db = new Database();

?>