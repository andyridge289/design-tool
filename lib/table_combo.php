<style type='text/css'>
	ul
	{
		list-style: none;
		margin: 0;
		padding:0
	}

	li
	{
		cursor: pointer;
	}

	li:hover
	{
		background-color: #eee;
	}

	li:active
	{
		background-color: #aaa;
	}
</style>
<script type='text/javascript'>
	function filter(event)
	{
		var input = $(event.target).val();
		var results = [];

		for(var i = 0; i < options.length; i++)
		{
			if(options[i].name.indexOf(input) != -1)
			{
				results.push(options[i]);
			}
		}

		var resultsContainer = $('#results');
		resultsContainer.empty();
		var resultsList = $("<ul id='ul_results'></ul>");
		for(var i = 0; i < results.length; i++)
		{
			var result = $("<li id='li" + results[i].id + "' onclick='choose(this)'>" + results[i].name + "</li>");
			result.attr({ "thing_id": results[i].id });
			resultsList.append(result);
		}

		resultsContainer.append(resultsList);
	}

	function choose(thing)
	{
		thing = $(thing);

		// Set the text box to have the right text and give it the thing_id of this one
		$("#entry").val(thing.html());
		$("#entry").attr({ "thing_id": thing.attr("thing_id") });
		$("#results").empty();
	}

	</script>
<?php

require_once "database.php";

function tableCombo($tableName, $customId, $type)
{
	global $db;
	$q = "SELECT * FROM `$tableName`";
	$ret = $db->q($q);
	if(!$ret)
	{
		echo "Fail: $q<br />";
	}
	// $options = "<script type='text/javascript'>var options = [";
	while($r = mysqli_fetch_array($ret))
	{
		$options .= "{id:'$r[id]',name:\"$r[name]\"},";
	}
	// $options = substr($options, 0, strlen($options) - 1) . "];</script>";
	// return $options;

	$str = "<div id='fred'>
		<input id='entry' type='text' style='width:50%;' onkeyup='filter(event)'></input>
		<button onclick='set(this, $tableName, $customId, \"$type\")'>Set</button>
		<div id='results' style='width:50%;height:200px;'>
			
		</div>
	</div>";

	return "$options $str";
}

class TC
{
	public $id;
	public $name;

	function TC($id, $name)
	{
		$this->id = $id;
		$this->name = $name;
	}
}

?>
<script>
</script>