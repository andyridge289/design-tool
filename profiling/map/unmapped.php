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

		function actuallyMap(id, button)
		{
			$.ajax({
				url: "later.php",
				type: "post",
			 	data: { 
			 		remove: id
			 	}
			}).done(function( msg ){
				if(msg == "win")
				{
					var li = $(button);
					li = li.parent().parent();
					li.remove();
				}
				else
				{
					// Don't do anything
					alert("Fail " + msg);
				}
			});
		}

		</script>
</head>
<body>
	<table>
<?php

require_once "../../php/database.php";
require_once "build_old_tree.php";

$q = "SELECT * FROM `to_map`";
$ret = $db->q($q);
if(!$ret)
{
	echo "Fail: $q<br />"; return;
}

while($r = mysqli_fetch_array($ret))
{
	$q2 = "SELECT * FROM `$r[old_table]` WHERE id = $r[old_id]";
	$ret2 = $db->q($q2);
	if(!$ret2){ echo "Fail: $q2<br />"; continue; }
	$r2 = mysqli_fetch_array($ret2);

	echo "<tr><td><b>$r2[name]</b><br />$r2[description]</td><td><button onclick='actuallyMap($r[id], this)'>Nope, this can be mapped.</button></td></tr>";
}

?>
	</table>
</body>
</html>