<!DOCTYPE html>
<html lang="en">
	<head>
		
		<meta charset="utf-8">
		
		<title>Design Tool!</title>
		
		<meta name="description" content="">
		<meta name="author" content="">
		
		<link rel="stylesheet" href="css/bootstrap.css" />
		<link rel="stylesheet" href="css/bootstrap-responsive.css" />
		<link rel="stylesheet" href="css/style.css" />
		<link rel="stylesheet" href="../lib/jquery-ui.css" />

		<?php
			$dsId = isset($_GET["ds"]) ? $_GET["ds"] : 1;
			echo "<script type='text/javascript'>var DS_ID = $dsId</script>";
		?>

	</head>
	<body onload="init()">
	<body>

			<div class="navbar navbar-inverse navbar-fixed-top">
			<div class="navbar-inner">
				<div class="container-fluid">
					<a class="navbar-btn brand" href="..">&larr;</a>
					<a class="brand" href="#" style="font-weight:bold;colour;white;">Design Space Profiling</a>
					
					<div class="btn-group btn-grou-lg">
					  <button id="btn_add" type="button" class="btn btn-default" onclick='setMode(MODE_ADD)'>Profile</button>
					  <button id="btn_out" type="button" class="btn btn-default" onclick='setMode(MODE_OUT)'>Export</button>
					</div>

					<a class="brand" href="#" style="float:right;" onclick="$('#help_modal').modal()">Help</a>


				</div>
			</div>
		</div>
		
		<div id="content" class="container-fluid" style="margin-top:50px;">

			<div class="row-fluid">
				<div id="status" class="alert alert-warning alert-dismissable" style="display:none;">
				 	<button ype="button" class="close" onclick="$('#status').css({'display':'none'});">&times;</button>
				  	<strong id="status_title"></strong>:&nbsp;&nbsp;<span id="status_text"></span>
				</div>
			</div>

			<div  class="row-fluid">
		
				<div id="nav_span" class="span3">

					<h4>Applications</h4>
					<div id="radio_container">
						Adding profile information for application:
						<div id="radiogroup"></div>
						<button id="show_source_button" type="button" style="margin-top:5px;" onclick="$('#source_modal').modal();">Add Application</button>
					</div>

					<div id="check_container">
						Choose applications to show profile output:
						<div id="checkgroup"></div>
						<div><br /><b>Root:</b> <span id="root_name">none</span></div>

						<div class="radio">
						  <label><input type="radio" name="typeRadio" id="graphRadio" value="graph" checked> Graph (.gv)</label>
						</div>
						<div class="radio">
						  <label><input type="radio" name="typeRadio" id="heatRadio" value="heat"> Graph-based heat map (.gv)</label>
						</div>
						<div class="radio">
						  <label><input type="radio" name="typeRadio" id="tableRadio" value="table"> Table (.csv)</label>
						</div>
		
						<button class="btn" type="button" onclick="makeOutput()">Export</button>
					</div>
					
				</div>
				
				<div id="canvas_container" class="span9">
					
					<div id="status_bar" class="alert" style="display:none;">
						<span id="status_text"></span>
						<button type="button" class="close" onclick="clearStatus()">&times;</button>
					</div>

					<span id="sub_nav" class="btn-group" style="margin-bottom:5px;">
			  			<button id="subnav_fxn" class="btn" onClick="setDS(1, this)">Functional</button>
			  			<button id="subnav_nfxn" class="btn" onClick="setDS(2, this)">Non-Functional</button>
			  			<button id="subnav_struct" class="btn" onClick="setDS(3, this)">Structural</button>
			  			<button id="subnav_serv" class="btn" onClick="setDS(4, this)">Entity</button>
					</span>

					<div><b>Status:&nbsp;</b><span id="doing_span"></span><span id="application_span"></span></div>

					<div id="canvas" class="well" style="padding:0px;overflow:hidden;">
						<div id="functional_canvas" class="ds_canvas"></div>
						<div id="nonfunctional_canvas" class="ds_canvas"></div>
						<div id="structural_canvas" class="ds_canvas"></div>
						<div id="service_canvas" class="ds_canvas"></div>	
					</div>

					<div id="source_modal" class="modal hide fade" tabindex="-1" role="dialog"
						aria_labelledby="source_modal_title" aria-hidden="true">

						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
							<h4 id="add_modal_title">Add Application Source</h4>
						</div>

						<div class="modal-body">

							<table>
							<tr><td>Name:</td><td><input id="source_name" type="text"></input></td></tr>
						</table>
						</div>

						<div class="modal-footer">
							<div id="btn_close" class="btn btn-inverse" data-dismiss="modal" aria-hidden="true">Close</div>
							<div id="btn_update" class="btn btn-primary" onclick="addSource()">Add</div>
						</div>
					</div>

					<div id="help_modal" class="modal hide fade" tabindex="-1" role="dialog"
						aria_labelledby="help_modal_title" aria-hidden="true">

						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
							<h4 id="help_modal_title">Help</h4>
						</div>

						<div class="modal-body">
							<p>In profiling mode, click elements to add them to the profile.</p>
							<p>In export mode, click elements to choose them as the root of your exported output.
							<p>To export .gv files, you need to download <a href="http://www.graphviz.org/">GraphViz</a></p>
						</div>

						<div class="modal-footer">
							<div id="btn_close" class="btn btn-inverse" data-dismiss="modal" aria-hidden="true">Close</div>
						</div>
					</div>
					
					
			</div> <!-- end row fluid -->
			
		</div> <!-- end content -->
		
		<script src="jquery-2.0.0.js"></script>
		<script src="js/bootstrap.js"></script>
		<script src="../lib/jquery-ui.js"></script>
		
		<script src="jit.js"></script>
		<script src="main.js"></script>
		
		
	</body>
</html>