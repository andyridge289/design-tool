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

			function add(event)
			{
				var dsName = $("#new_text").val();

				if(dsNames.indexOf(dsName) != -1)
				{
					alert("The DS name you've entered already exists. Pick a new one.");
					return;
				}

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
				</div>
			</div>
		</div>
			<table style='margin-top:50px;margin-left:20px;width:80%;table-layout:fixed;'>
				<tr><td>DS Name</td></tr>
		<?php

		$q = "SELECT * FROM `ds_list`";
		$ret = $db->q($q);
		if(!$ret)
		{
			echo "Fail $q<br />";
			return;
		}

		$dsNames = array();

		while($r = mysqli_fetch_array($ret))
		{
			array_push($dsNames, $r["name"]);

			echo "<tr>
					<td><b>$r[name]</b></td>
					<td><a href='tidy/index.php?ds=$r[id]'>Build/Modify DS</a></td>
					<td><a href='profiling/index.php?ds=$r[id]'>Profiling Applications</a></td>
					<td><a href='design_tool/index.php?ds=$r[id]'>Generate design</a></td>
				</tr>";
		}
		
		
		?>
			<tr>
				<td colspan="2"><label style="margin-top:20px;">Name:&nbsp;<input id="new_text" type="text" style="margin-left:10px;margin-top:5px;"></input></label></td>
				<td><button onclick="add(event)" style="margin-top:5px;">Create New DS</button></td>

			</tr>
			</table>
		<script src="lib/jquery-2.0.0.js"></script>
		<?php
			echo "<script type='text/javascript'>
				var dsNames = " . json_encode($dsNames) . ";
			</script>";
		?>
		<script src="profiling/js/bootstrap.js"></script>
	</body>
</html>