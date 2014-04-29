<?php
	require_once "lib/database.php";
?>
<html lang="en">
	<head>
		
		<meta charset="utf-8">
		
		<title>Design Space Tool!</title>
		
		<meta name="description" content="">
		<meta name="author" content="">
		
		<link rel="stylesheet" href="profiling/css/bootstrap.css" />
		<link rel="stylesheet" href="profiling/css/bootstrap-responsive.css" />
		<link rel="stylesheet" href="profiling/css/style.css" />

		<script type="text/javascript">

			function addNew()
			{
				$("#create_div").css({ "display": "block"});
			}

			function add(event)
			{
				var dsName = $("#new_text").val();

				$.ajax({
					url: "lib/add_ds.php",
					type: "post",
				 	data: { 
				 		name: dsName
				 	}
				}).done(function( msg )
				{
					if(msg == "win")
					{
						location.reload();
					}
					else
					{
						alert("Failed to create a DS called " + dsName + ": " + msg);
					}
				});
			}

		</script>

		<style type="text/css">
		</style>

	</head>
	<body>
	<body>

		<div class="navbar navbar-inverse navbar-fixed-top">
			<div class="navbar-inner">
				<div class="container-fluid">
					<a class="brand" href="#" style="font-weight:bold;colour;white;">Design Space Tool</a>
					<a class="brand" href="#" onclick="addNew()">Create New</a>
					<span id="create_div" style="display:none;">
						Name:&nbsp;<input id="new_text" type="text"></input>
						<button onclick="add(event)">Add</button>
					</span>
				</div>
			</div>
		</div>
			<table style='margin-top:50px;margin-left:20px;width:80%;table-layout:fixed;'>
				<tr><td>Name</td></tr>
		<?php

		$q = "SELECT * FROM `ds_list`";
		$ret = $db->q($q);
		if(!$ret)
		{
			echo "Fail $q<br />";
			return;
		}

		while($r = mysqli_fetch_array($ret))
		{
			echo "<tr>
					<td><b>$r[name]</b></td>
					<td><a href='tidy/index.php?ds=$r[id]'>Build/Modify</a></td>
					<td><a href='profiling/index.php?ds=$r[id]'>Profiling</a></td>
					<td><a href='design_tool/index.php?ds=$r[id]'>Create design</a></td>
				</tr>";
		}

		
		
		?>
			</table>
		<script src="lib/jquery-2.0.0.js"></script>
		<script src="profiling/js/bootstrap.js"></script>
	</body>
</html>