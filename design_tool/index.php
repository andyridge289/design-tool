<!DOCTYPE html>
<html lang="en">
	<head>
		
		<meta charset="utf-8">
		
		<title>Design Tool!</title>
		
		<meta name="description" content="">
		<meta name="author" content="">
		
		<link rel="stylesheet" href="css/bootstrap.css" />
		<link rel="stylesheet" href="css/bootstrap-responsive.css" />
		<link rel="stylesheet" href="../lib/slider.css" />
		<link rel="stylesheet" href="style.css" />

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
					
					<a class="brand" href="#">Design Tool</a>

					<span class="brand" style="padding-bottom:0px;padding-top:4px;">
						<a id="participant_num" class="brand" href="#" style="padding-top:6px;padding-bottom:0px;" onclick="participant()">Design 1</a>
						<input id="participant_text" type="number" style="margin-bottom:2px;display:none;" onkeydown="setParticipant(event)"/>
					</span>
					
					<ul id="nav" class="nav">
						<li><a id="nav_export" href="#export_modal" onclick="exportDesign()">Export Design</a></li>
						<li><a id="nav_clear" href="#clear_save" onclick="clearData();">Clear Data</a></li>
					</ul>
				
				</div>
			</div>
		</div>
		
		<div id="content" class="container-fluid">
		
			<div  class="row-fluid">
		
				<div id="nav_span" class="span3">
						
					<h4>DS Information</h4>
					<p>Decisions showing:&nbsp;<b id="decision_value">100%</b></p>
					<div id="decision_slider"></div>
					<p>Options showing:&nbsp;<b id="option_value">100%</b></p>
					<div id="option_slider"></div>
					<div id="slider_value"></div>

					<h4>Options</h4>
					<h5>Other Decision</h5>
					
					<fieldset>
						<input id="custom_option" type="text" placeholder="Add custom solution:" onkeypress="customKeyDown(event)"><br />
						<button onclick="addCustom()" class="btn">Add</button>
					</fieldset>
					
					<h5>Other options</h5>
					<div id="options" class="well sidebar-nav">
						<p>Functional</p>
						<div id="fxn_options" ></div>
						<p>Non-Functional</p>
						<div id="nfxn_options"></div>
						<p>Structural</p>
						<div id="struct_options"></div>
						<p>Entity</p>
						<div id="serv_options"></div>
						<p>Custom</p>
						<div id="custom_options"></div>
					</div>

					<div>
						<p style="font-style:italic;color:#AAA;cursor:pointer" class="unselectable" onclick="unmade()">Un-Chosen Options</p>
						<div id="unChosen" class="well sidebar-nav" style="display:none;"></div>
					</div>
				</div>
				
				<div id="canvas_container" class="span9">
					
					<div style="position:relative;">
						
						<span id="sub_nav" class="btn-group">
				  			<button id="subnav_fxn" class="btn" onClick="setDS(FUNCTIONAL, this)">Functional</button>
				  			<button id="subnav_nfxn" class="btn" onClick="setDS(NONFUNCTIONAL, this)">Non-Functional</button>
				  			<button id="subnav_struct" class="btn" onClick="setDS(STRUCTURAL, this)">Structural</button>
				  			<button id="subnav_serv" class="btn" onClick="setDS(SERVICE, this)">Entity</button>
						</span>

						<!-- <button id="heatmap" class="btn" onClick="heatmap(this)" style="margin-left:20px;margin-bottom:10px;">Show heat map</button> -->
						
					</div>
					
					<div id="status_bar" class="alert" style="display:none;">
						<span id="status_text"></span>
						<button type="button" class="close" onclick="clearStatus()">&times;</button>
					</div>

					<div id="canvas" class="well" style="padding:0px;overflow:hidden;">
						<div id="functional_canvas" class="ds_canvas"></div>
						<div id="nonfunctional_canvas" class="ds_canvas"></div>
						<div id="structural_canvas" class="ds_canvas"></div>
						<div id="service_canvas" class="ds_canvas"></div>	
					</div>
					
					<div id="overlay" class="modal hide fade" tabindex="-1" 
						 role="dialog" aria-labelledby="overlay_title" aria-hidden="true">
						 
					 	<div class="modal-header" style="position:relative;">
					 		<button type="button" class="close" data-dismiss="modal"  aria-hidden="true">x</button>
					 		
							<h4 id="overlay_title">Title</h4>
							<div id="overlay_type" class="label label-info overlay_label">Default</div>
							<div id="overlay_ds" class="label overlay_label">Default</div>
						</div>
						
						<div class="modal-body">
							<h5 class="overlay_sub">Description:</h5>
							<div id="overlay_description">Description</div>
							<h5 id="overlay_related_header" class="overlay_sub">Related Options</h5>
							<div id="overlay_related"></div>
							<h5 id="overlay_tools_header" class="overlay_sub">Tools:</h5>
							<div id="overlay_tools"></div>
							
						</div>
						
						<div id="button_container" class="modal-footer">
							<div class="btn btn-inverse" data-dismiss="modal" aria-hidden="true">Close</div>
							<div id="btn_preview" class="btn">Preview</div>
							<div id="btn_choose" class="btn btn-primary">Choose</div>
						</div>
					</div>
				</div>
				
				<div id="rationale_modal" class="modal hide fade" tabindex="-1"
					 role="dialog" aria-labelledby="rationale_title" aria-hidden="true">
				
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
						<div id="rationale_type">Default</div>
						<h4 id="rationale_title">Choose</h4>
					</div>
					
					<div class="modal-body">
						<div id="rationale_status_bar" class="alert alert-error" style="display:none;"></div>

						<div id="rationale_description_container">
							<b>Description</b><br />
							<span id="rationale_description"></span>
						</div>

						<div id="custom_description_container" style="display:none;">

							<p>Enter a description for <i id="rationale_description_name"></i></p>
							<textarea id="custom_description_text" class="rationale_text" rows="5" columns="160"></textarea>
						</div>

						<br /><br /><b>Rationale</b><br />
						<p>Enter the rationale for choosing <i id="rationale_name"></i></p>
						<textarea id="rationale_text" class="rationale_text" rows="5" columns="160"></textarea>
											
						<p id="link_text">Link to decision</p>
						<div id="link_placeholder"></div>

					</div>
					
					<div class="modal-footer">
						<div class="btn btn-inverse" data-dismiss="modal" aria-hidden="true">Close</div>
						<div id="btn_ok" class="btn btn-primary" onclick="setRationale()">Okay</div>
					</div>
					
				</div>
				
				<div id="export_modal" class="modal hide fade" tabindex="-1"
					role = "dialog" aria-labelledby="export_title" aria-hidden="true">
					
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
						
						<h4 id="export_title">Title</h4>
						
						<div class="modal-body">
							<label class="checkbox">Export text descriptions<input type="checkbox" /></label>
							<label class="checkbox">Export graphviz file<input type="checkbox" /></label>
						</div>
						
						<div class="modal-footer">
							<div id="btn_close" class="btn btn-inverse"
								data-dismiss="modal" aria-hidden="true">Close</div>
							<div id="btn_export" class="btn btn-primary" onclick="exportDS()">Go</div>
						</div>
					</div>
					
				</div>
				
				<div id="tag_modal" class="modal hide fade" tabindex="-1"
					role="dialog" aria_labelledby="tag_modal_title" aria-hidden="true">
					
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
						<h4 id="tag_modal_title">Title</h4>

						<div id="tag_modal_type" class="label label-info overlay_label">Default</div>
						<div id="tag_modal_ds" class="label overlay_label">Default</div>
					</div>
						
					<div class="modal-body">
						<h4 id="tag_designspace"></h4>
						
						<h5>Description</h5>
						<p id="tag_description_text"></p>

						<h5 id="tag_rationale_heading">Rationale</h5>
						<textarea id="tag_rationale_text"></textarea>

						<p id="tag_link">Linked to decision:<span id="link_label" class="label"></span></p>
						
						<p id="tag_prior_heading">Added automatically by <b id="tag_prior"></b>
						</p>

						<h5 id="tag_view_heading">Previewing</h5>
						<p id="tag_view"></p>
					</div>
						
					<div class="modal-footer">
						<div id="btn_close" class="btn btn-inverse" data-dismiss="modal" aria-hidden="true">Close</div>
						<div id="btn_unmake" class="btn btn-danger" onclick="unmake()">Unmake</div>
						<div id="btn_remake" class="btn btn-danger" onclick="remake()">Remake</div>
						<div id="btn_update" class="btn btn-primary" onclick="updateRationale()">Update</div>
					</div>
						
				</div>
					
			</div> <!-- end row fluid -->
			
		</div> <!-- end content -->
		
		<?php

		echo "<script type='text/javascript' src='../lib/out_json.php?ds=$dsId&c=1'></script>
			<script type='text/javascript' src='../lib/out_json.php?c=2&ds=$dsId'></script>
			<script type='text/javascript' src='../lib/out_json.php?c=3&ds=$dsId'></script>
			<script type='text/javascript' src='../lib/out_json.php?c=4&ds=$dsId'></script>
			<script type='text/javascript' src='../lib/get_tools.php?ds=$dsId'></script>
			";
		?>

		<script src="js/jquery-2.0.0.js"></script>
		<script src="js/bootstrap.js"></script>
		
		<script src="js/jit.js"></script>
		<script src="js/lib.js"></script>		
		<script src="js/main.js"></script>
		<script src="js/designoption.js"></script>
		<script type='text/javascript' src='../lib/bootstrap-slider.js'></script>
		<script type='text/javascript' src='../lib/TextCombo.js'></script>
		
		
	</body>
</html>