function TextCombo(idNameArray)
{
	this.sources = idNameArray;

	this.selected = null;

	this.container = $("<div></div>");
	this.container.css({

	});

	this.input = $("<input type='text'>");
	this.input.css({

	});

	this.results = $("<ul></ul>");
	this.results.css({
		"height": "100px",
		"overflow": "scroll"
	});

	this.getSelected = function()
	{
		if(this.selected == null) 
		{
			if(this.results.children().length > 0)
			{
				// Pick the first one
				this.selected = this.getThing($(this.results.children()[0]).attr("thing_id"));
			}
			else
			{
				return null;
			}
		}

		return this.selected;
	}

	this.getThing = function(id)
	{
		for(var i = 0; i < this.sources.length; i++)
		{
			if(this.sources[i][0] == id)
			{
				return this.sources[i];
			}
		}
	}

	this.filter = function(event)
	{
		var input = $(event.target).val();
		var parent = event.data.parent;
		var things = parent.sources;

		var results = [];

		for(var i = 0; i < things.length; i++)
		{
			if(things[i][1].toLowerCase().indexOf(input.toLowerCase()) != -1)
			{
				results.push(things[i]);
			}
		}

		parent.results.empty();
		for(var i = 0; i < results.length; i++)
		{
			var result = $("<li class='result' thing_id='" + results[i][0] + "'>" + results[i][1] + "</li>");
			
			liclick = function(event)
			{
				var parent = event.data.parent;

				var li = $(this);
				if(li.attr("class") == "result")
				{
					parent.selected = parent.getThing(li.attr("thing_id"));

					for(var j = 0; j < parent.results.children().length; j++)
					{
						parent.results.children()[j].className = "result";
					}

					li.attr({ "class": "chosen"});

					parent.input.val(li.html());

					// It's not selected, choose it and clear the classes of all of the others
				}
				else
				{
					// Dno, maybe don't do anything?
				}
			};

			result.bind("click", {	parent: parent }, liclick);

			result.css({
				"cursor": "pointer"
			});
			
			parent.results.append(result);
		}

		
	}

	this.input.bind("keyup", { parent: this }, this.filter);

	this.container.append(this.input);
	this.container.append(this.results);

	return this;
}


// 	ul
// 	{
// 		list-style: none;
// 		margin: 0;
// 		padding:0
// 	}

// 	li
// 	{
// 		cursor: pointer;
// 	}

// 	li:hover
// 	{
// 		background-color: #eee;
// 	}

// 	li:active
// 	{
// 		background-color: #aaa;
// 	}
// </style>
// <script type='text/javascript'>
// 	function filter(event)
// 	{
// 		
// 	}

// 	function choose(thing)
// 	{
// 		thing = $(thing);

// 		// Set the text box to have the right text and give it the thing_id of this one
// 		$("#entry").val(thing.html());
// 		$("#entry").attr({ "thing_id": thing.attr("thing_id") });
// 		$("#results").empty();
// 	}

// 	</script>
// <?php

// require_once "database.php";

// function tableCombo($tableName, $customId, $type)
// {
// 	global $db;
// 	$q = "SELECT * FROM `$tableName`";
// 	$ret = $db->q($q);
// 	if(!$ret)
// 	{
// 		echo "Fail: $q<br />";
// 	}
// 	// $options = "<script type='text/javascript'>var options = [";
// 	while($r = mysqli_fetch_array($ret))
// 	{
// 		$options .= "{id:'$r[id]',name:\"$r[name]\"},";
// 	}
// 	// $options = substr($options, 0, strlen($options) - 1) . "];</script>";
// 	// return $options;

// 	$str = "<div id='fred'>
// 		<input id='entry' type='text' style='width:50%;' onkeyup='filter(event)'></input>
// 		<button onclick='set(this, $tableName, $customId, \"$type\")'>Set</button>
// 		<div id='results' style='width:50%;height:200px;'>
			
// 		</div>
// 	</div>";

// 	return "$options $str";
// }

// class TC
// {
// 	public $id;
// 	public $name;

// 	function TC($id, $name)
// 	{
// 		$this->id = $id;
// 		$this->name = $name;
// 	}
// }

// ?>
// <script>
// </script>