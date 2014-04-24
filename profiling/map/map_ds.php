<html>
	<head>
		<link rel="stylesheet" type="text/css" href="../css/bootstrap.css" />
		<style type="text/css">
		.header
		{
			position: absolute;
			left: 0px;
			top: 0px;
			width: 100px;
			height: 10%;
		}

		.containercontainer
		{
			position: absolute;
			height: 90%;
			width: 100%;
			left: 0px;
			top: 50px;
		}

		.container
		{
			position: absolute;
			top: 0;
			width: 50%;
			display:inline;
			height: 100%;
		}

		ul
		{
			margin: 0;
			padding: 0;
			overflow: scroll;
		}

		li
		{
			list-style: none;
			cursor: pointer;
			margin: 0;
			padding: 10px;
			width: 90%;
		}

		li.category
		{
			background-color: #8df2b6;
			border: 1px solid #458b00;
		}

		li.decision
		{
			background-color: #b2dfee;
			border: 1px solid #004a63;
		} 

		li.option
		{
			background-color: #DDDDDD;
			border: 1px solid #444444;
		}

		</style>
		<script type="text/javascript" src="../jquery-2.0.0.js" ></script>
		<script type="text/javascript">

		var oldSelected;
		var oldClass;
		var newSelected;

		function submit()
		{
			if(newSelected == -1 || oldSelected == -1)
				return;

			$.ajax({
				url: "connect.php",
				type: "post",
			 	data: { 
			 		oldId: oldSelected,
			 		newId: newSelected,
			 		tableName: oldClass
			 	}
			}).done(function( msg ){
				if(msg == "win")
				{
					$("#old_" + oldSelected).remove();
					$("#new_" + newSelected).remove();
					$("#num").html(($("#num").html() * 1) - 1);
					$("#old_name").html("");
					$("#new_name").html("");
					oldSelected = -1;
					newSelected = -1;
					oldClass = "";
				}
				else
				{
					// Don't do anything
					alert("Fail " + msg);
				}
			});

			// alert(oldClass + ": " + oldSelected + " -> " + newSelected);
		}

		function submitMatch(oldId, newId, tableName, button)
		{
			// alert(oldId + " , " + newId + " , " + tableName);

			var li = $(button.parentNode.parentNode.parentNode.parentNode.parentNode);

			$.ajax({
				url: "connect.php",
				type: "post",
			 	data: { 
			 		oldId: oldId,
			 		newId: newId,
			 		tableName: tableName
			 	}
			}).done(function( msg ){
				if(msg == "win")
				{
					// $("#old_" + oldSelected).remove();
					// $("#new_" + newSelected).remove();
					// $("#num").html(($("#num").html() * 1) - 1);
					// $("#old_name").html("");
					// $("#new_name").html("");
					li.remove();
				}
				else
				{
					// Don't do anything
					alert("Fail " + msg);
				}
			});
		}

		function oldclick(li)
		{
			li = $(li);

			oldSelected = li.attr("id").substring(4);
			oldClass = li.attr("class");
			if(oldClass == "category")
				oldClass = "ds";

			$("#old_name").html(oldSelected + ": " + li.children(0).html());
		}

		function newclick(li)
		{
			li = $(li);

			newSelected = li.attr("id").substring(4);;
			$("#new_name").html(newSelected + ": " + li.children(0).html());

		}

		function over(li)
		{
			li = $(li);

			if(li.attr("class") == "option")
			{
				li.css({ "background-color": "#aaaaaa" });
			}
			else if(li.attr("class") == "decision")
			{
				li.css({ "background-color": "#30BEEE" });
			}
			else 
			{
				li.css({ "background-color": "#30F27F" });
			}
		}

		function out(li)
		{
			li = $(li);

			if(li.attr("class") == "option")
			{
				li.css({ "background-color": "#dddddd" });
			}
			else if(li.attr("class") == "decision")
			{
				li.css({ "background-color": "#b2dfee" });
			}
			else 
			{
				li.css({ "background-color": "#8df2b6" });
			}
		}

		function hide(li)
		{
			li = $(li);
			var oldId = li.attr("id").substring(4);
			var oldClass = li.attr("class");
			$.ajax({
				url: "later.php",
				type: "post",
			 	data: { 
			 		oldId: oldId,
			 		tableName: oldClass
			 	}
			}).done(function( msg ){
				if(msg == "win")
				{
					li.remove();
				}
				else
				{
					// Don't do anything
					alert("Fail " + msg);
				}
			});

			return false;
		}

		</script>
	</head>
	<body>
<?php

require_once "../../php/database.php";
require_once "build_old_tree.php";
require_once "build_new_tree.php";

$tables = array("category", "decision", "option");

$old = array();
for($i = 0; $i < count($tables); $i++)
{
	$q = "SELECT * FROM `$tables[$i]`";
	$ret = $db->q($q);
	if(!$ret)
	{
		echo "Fail: $q<br />";
		return;
	}

	$table = $i == 0 ? "ds" : $tables[$i];

	while($r = mysqli_fetch_array($ret))
	{

		$q2 = "SELECT * FROM `ds_map` WHERE old_id = $r[id] AND old_table = '$table'";
		$ret2 = $db->q($q2);

		if(!$ret2){ echo "Fail: $q2<br />"; continue; }
		$c = mysqli_num_rows($ret2);

		if($c > 0)
			continue;

		$q2 = "SELECT * FROM `to_map` WHERE old_id = $r[id] AND old_table = '$table'";
		$ret2 = $db->q($q2);
		if(!$ret2){ echo "Fail: $q2<br />"; continue; }
		$c = mysqli_num_rows($ret2);

		if($c > 0)
			continue;

		array_push($old, new TreeThing($r["id"], $r["name"], $tables[$i]));
		// Need to check if the old one has already been linked, if it has then remove it
	}
}
usort($old, "cmp");
$num = count($old);

$q = "SELECT * FROM `thing` WHERE stage_removed = -1";//" WHERE stage_added = 16";
$ret = $db->q($q);
if(!$ret)
{
	echo "Fail: $q<br />";
	return;
}

$new = array();
while($r = mysqli_fetch_array($ret))
{
	array_push($new, new TreeThing($r["id"], $r["name"], $r["type"]));
}
usort($new, "cmp");

// $pairs = array();
// for($i = 0; $i < count($old); $i++)
// {
// 	$stop = false;

// 	for($j = 0; $j < count($new); $j++)
// 	{
// 		if(strcasecmp($old[$i]->name, $new[$j]->name) == 0)
// 		{
// 			$arr = array($old[$i], $new[$j]);
// 			array_push($pairs, $arr);
// 			//array_splice($new, $j, 1);
// 			$stop = true;
// 			break;
// 		}
// 	}

// 	if($stop)
// 	{
// 		$i--;
// 		continue;
// 	}
// }

// echo "<h4>Matching names</h4>
// <ul>";

// for($i = 0; $i < count($pairs); $i++)
// {
// 	$row = $pairs[$i];
	
// 	$q = "SELECT * FROM `ds_map` WHERE old_id = " . $row[0]->id . " AND thing_id = " . $row[1]->id;
// 	$ret = $db->q($q);
// 	if(!$ret){ echo "Fail: $q<br />"; continue; }
// 	if(mysqli_num_rows($ret) > 0)
// 		continue;

// 	// Check if the pair is already in the table, if it is then return!!!

// 	$oldNode = findInTree($oldRoot, $row[0]->name);
// 	$temp = $oldNode;
// 	$oldTreeName = "";

// 	while($temp->parent != null)
// 	{
// 		$oldTreeName = $temp->parent->name . " -> " . $oldTreeName;
// 		$temp = $temp->parent;
// 	}

	

// 	$newNode = findInTree($newRoot, $row[1]->name);
// 	$temp = $newNode;
// 	$newTreeName = "";

// 	while($temp->parent != null)
// 	{
// 		$newTreeName = $temp->parent->name . " -> " . $newTreeName;
// 		$temp = $temp->parent;
// 	}

// 	echo "<li><table><tr>
// 		<td><b>" . $row[0]->name . "</b>$oldTreeName<br/><b>" . $row[1]->name . "</b>$newTreeName</td>
// 		<td><button style=\"float:left;right:0px;\" onclick=\"submitMatch(" . $row[0]->id . "," . $row[1]->id . ",'" . $row[0]->type . "', this)\">Yup</button></td>
// 	</tr></table></li>";
// }

// echo "</ul>";

// return;

	// $q2 = "SELECT * FROM `ds_map` WHERE thing_id = $r[id]";
	// $ret2 = $db->q($q2);
	// if(!$ret2)
	// {
	// 	echo "Fail: $q2<br />";
	// 	continue;
	// }

	// if(mysqli_num_rows($ret2) > 0)
	// 	continue;

// $table = $tables[$i];
		// $q2 = "SELECT * FROM ds_map WHERE old_id = $r[id] AND old_table = '$table'";
		// $ret2 = $db->q($q2);
		// if(!$ret2)
		// {
		// 	echo "Fail: $q2<br />";
		// 	continue;
		// }

		// if(mysqli_num_rows($ret2) > 0)
		// 	continue;


echo "<div class='header' style='width:100%;padding:10px;height:100px;'>
			<table style='width:100%;'><tr>
			<td style='width:30px;font-size:10px'><span id='num'>$num</span>&nbsp;Old:</td> <td id='old_name' style='width:120px;font-size:10px'></td>
			<td style='width:30px;font-size:10px'>New:</td> <td id='new_name' style='width:120px;font-size:10px'></td>
			<td style='width:15%;'><button style='float:left;right:0px;' onclick='submit()'>Select</button></td>
			</tr></table>
		</div>
		<div class='containercontainer'>";

echo "<div class='container' style='left:0;overflow:scroll;'>
			<h4>Old</h4>
			<ul>";

for($i = 0; $i < count($old); $i++)
{
	$name = $old[$i]->name;
	$class = $old[$i]->type;
	$id = $old[$i]->id;
	if(strcmp($class, "ds") === 0)
		$class = "category";

	$oldNode = findInTree($oldRoot, $old[$i]->name);
	$temp = $oldNode;
	$tree = "";

	while($temp != null && $temp->parent != null)
	{
		$tree = $temp->parent->name . " &gt; " . $tree;
		$temp = $temp->parent;
	}	

	echo "<li onmouseover='over(this)' onmouseout='out(this)' oncontextmenu='return hide(this)' onclick='oldclick(this)' id='old_$id' class='$class'><p><b>$name</b></p>$tree</li>";
}

echo "</ul></div>";



echo "<div class='container' style='left:50%;overflow:scroll;'>
			<h4>New</h4>
			<ul>";

for($i = 0; $i < count($new); $i++)
{
	$name = $new[$i]->name;
	$class= $new[$i]->type;
	$id = $new[$i]->id;

	$newNode = findInTree($newRoot, $new[$i]->name);
	$temp = $newNode;
	$tree = "";

	if($temp == null)
	{
		echo "Null for $name<br />";
		continue;
	}

	while($temp->parent != null)
	{
		$tree = $temp->parent->name . " &gt; " . $tree;
		$temp = $temp->parent;
	}	

	echo "<li onmouseover='over(this)' onmouseout='out(this)' onclick='newclick(this)' id='new_$id' class='$class'><p><b>$name</b></p>$tree</li>";
}

echo "</ul></div>";

function cmp($a, $b)
{
	return strcmp($a->name, $b->name);
}

?>
	</div>
	</body>
</html>