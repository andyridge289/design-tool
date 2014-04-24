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

		// var oldSelected;
		// var oldClass;
		// var newSelected;

		// function submit()
		// {
		// 	if(newSelected == -1 || oldSelected == -1)
		// 		return;

		// 	$.ajax({
		// 		url: "connect.php",
		// 		type: "post",
		// 	 	data: { 
		// 	 		oldId: oldSelected,
		// 	 		newId: newSelected,
		// 	 		tableName: oldClass
		// 	 	}
		// 	}).done(function( msg ){
		// 		if(msg == "win")
		// 		{
		// 			$("#old_" + oldSelected).remove();
		// 			$("#new_" + newSelected).remove();
		// 			$("#num").html(($("#num").html() * 1) - 1);
		// 			$("#old_name").html("");
		// 			$("#new_name").html("");
		// 			oldSelected = -1;
		// 			newSelected = -1;
		// 			oldClass = "";
		// 		}
		// 		else
		// 		{
		// 			// Don't do anything
		// 			alert("Fail " + msg);
		// 		}
		// 	});

		// 	// alert(oldClass + ": " + oldSelected + " -> " + newSelected);
		// }

		// function submitMatch(oldId, newId, tableName, button)
		// {
		// 	// alert(oldId + " , " + newId + " , " + tableName);

		// 	var li = $(button.parentNode.parentNode.parentNode.parentNode.parentNode);

		// 	$.ajax({
		// 		url: "connect.php",
		// 		type: "post",
		// 	 	data: { 
		// 	 		oldId: oldId,
		// 	 		newId: newId,
		// 	 		tableName: tableName
		// 	 	}
		// 	}).done(function( msg ){
		// 		if(msg == "win")
		// 		{
		// 			// $("#old_" + oldSelected).remove();
		// 			// $("#new_" + newSelected).remove();
		// 			// $("#num").html(($("#num").html() * 1) - 1);
		// 			// $("#old_name").html("");
		// 			// $("#new_name").html("");
		// 			li.remove();
		// 		}
		// 		else
		// 		{
		// 			// Don't do anything
		// 			alert("Fail " + msg);
		// 		}
		// 	});
		// }

		// function oldclick(li)
		// {
		// 	li = $(li);

		// 	oldSelected = li.attr("id").substring(4);
		// 	oldClass = li.attr("class");
		// 	if(oldClass == "category")
		// 		oldClass = "ds";

		// 	$("#old_name").html(oldSelected + ": " + li.html());
		// }

		// function newclick(li)
		// {
		// 	li = $(li);

		// 	newSelected = li.attr("id").substring(4);;
		// 	$("#new_name").html(newSelected + ": " + li.html());

		// }

		// function over(li)
		// {
		// 	li = $(li);

		// 	if(li.attr("class") == "option")
		// 	{
		// 		li.css({ "background-color": "#aaaaaa" });
		// 	}
		// 	else if(li.attr("class") == "decision")
		// 	{
		// 		li.css({ "background-color": "#30BEEE" });
		// 	}
		// 	else 
		// 	{
		// 		li.css({ "background-color": "#30F27F" });
		// 	}
		// }

		// function out(li)
		// {
		// 	li = $(li);

		// 	if(li.attr("class") == "option")
		// 	{
		// 		li.css({ "background-color": "#dddddd" });
		// 	}
		// 	else if(li.attr("class") == "decision")
		// 	{
		// 		li.css({ "background-color": "#b2dfee" });
		// 	}
		// 	else 
		// 	{
		// 		li.css({ "background-color": "#8df2b6" });
		// 	}
		// }

		</script>
	</head>
	<body>
		<ul>
<?php

require_once "../../php/database.php";
// require_once "build_old_tree.php";
// require_once "build_new_tree.php";

// TODO
// Find the ones whose names match
// Show the rest

// return;

$q = "SELECT * FROM `ds_map` AS `m`
		LEFT JOIN `thing` AS `t` ON m.thing_id = t.id";
$ret = $db->q($q);
if(!$ret){ echo "Fail: $q<br />"; return; }

while($r = mysqli_fetch_array($ret))
{
	// print_r($r);

	$q2 = "SELECT * FROM `$r[old_table]` WHERE id = $r[old_id]";
	$ret2 = $db->q($q2);
	if(!$ret2){ echo "Fail: $q2<br />"; continue; }

	$r2 = mysqli_fetch_array($ret2);
	// echo "<br />";
	// print_r($r2);

	echo "<li>$r[name] -> $r2[name]</li>";
}

?>
	</ul>
	</body>
</html>