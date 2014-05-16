
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
		<link rel="stylesheet" href="../lib/TextCombo.css" />

		<?php
			$dsId = isset($_GET["ds"]) ? $_GET["ds"] : 1;
			echo "<script type='text/javascript'>var DS_ID = $dsId</script>";
		?>

		<style type="text/css">

			li.unselected
			{
				background-color: #fff;
				color: #000;
			}

			li.unselected:hover
			{
				background-color: #eee;
			}

			li.selected
			{
				background-color: #444;
				color: #fff;
			}

		</style>

	</head>
	<body onload="init()">
	<body>

		<div class="navbar navbar-inverse navbar-fixed-top">
			<div class="navbar-inner">
				<div class="container-fluid">
					<a class="navbar-btn brand" href="..">&larr;</a>
					<a class="brand" href="#" style="font-weight:bold;colour;white;">Design Space Creation and Manipulation</a>
				</div>
			</div>
		</div>

		<div id="content" class="container-fluid">

			<div class="row-fluid">

				<div>
					<div id="added_container" style="display:none;">
						<div id="num_added" onclick="showAdded();" style="cursor:pointer;"><span id="num_added_num"></span> added to DS but not linked</div>
						<div id="num_added_things" style='display:none'></div>
					</div>

					<div id="status" class="alert alert-warning alert-dismissable" style="display:none;">
					 	<button type="button" class="close" onclick="$('status').css({'display':'none'});">&times;</button>
					  	<strong id="status_title"></strong>:&nbsp;&nbsp;<span id="status_text"></span>
					</div>

					<div>
						<strong>Stage:</strong>&nbsp;[<span id="stage_num"></span>]:<span id="stage_name"></span>
						<button type="button" onclick="showStageModal()" style="float:right;">Change Stage</button>
					</div>
				</div>

			</div>

			<div  class="row-fluid">

				<div id="nav_span" class="span3">

					<div id="info_container" style="display:none;">
						<h4>Info: <span id="node_name"></span><span></span></h4>
						<button type="button" class="close" onclick="clearInfo()">&times;</button>

						<div id="node_type" style="cursor:pointer" onclick="$('#choose_type').css({ 'display': 'block'});"></div>
						<div id="choose_type" style="display:none;">
							<div class="label label-success" onclick="setType('category')" style="cursor:pointer;">Category</div>
							<div class="label label-info" onclick="setType('decision')" style="cursor:pointer;">Decision</div>
							<div class="label" onclick="setType('option')" style="cursor:pointer;">Option</div>
						</div>
						<ol id="info2" class="breadcrumb" style="display:none;">
						  <li id="added"></li>
						  <li>&raquo;</li>
						  <li id="removed"></li>
						</ol>
						<!--<button id="undead_node" onclick="undeadThing(this)">Undead</button><br /><br />-->

						<p>Sources</p>
						<ul id="source_list"></ul>

						<p><b>Incoming connections</b></p>
						<button onclick="addIncoming()">Add incoming link</button>
						<div id="incoming_container" style="border:1px solid black;"></div>
						<ul id="incoming"></ul>

						<p><b>Outgoing connections</b></p>
						<button onclick="addOutgoing()">Add outgoing link</button>
						<div id="outgoing_container"  style="border:1px solid black;"></div>
						<ul id="outgoing"></ul>

						<p>Dead Incoming</p>
						<ul id="dead_incoming"></ul>

						<p>Dead Outgoing</p>
						<ul id="dead_outgoing"></ul>
					</div>

					
					<button id="show_add_button" type="button" onclick="showAddModal();">Add Design Element</button><br />
					<button id="show_source_button" type="button" style="margin-top:5px;" onclick="$('#source_modal').modal();">Add Source</button>
					
				</div>
				
				<div id="canvas_container" class="span9">

					<span id="sub_nav" class="btn-group" style="margin-bottom:5px;">
			  			<button id="subnav_fxn" class="btn" onClick="setDS(1, this)">Functional</button>
			  			<button id="subnav_nfxn" class="btn" onClick="setDS(2, this)">Non-Functional</button>
			  			<button id="subnav_struct" class="btn" onClick="setDS(3, this)">Structural</button>
			  			<button id="subnav_serv" class="btn" onClick="setDS(4, this)">Entity</button>
					</span>

					<div id="canvas" class="well" style="padding:0px;overflow:hidden;">
						<div id="functional_canvas" class="ds_canvas"></div>
						<div id="nonfunctional_canvas" class="ds_canvas"></div>
						<div id="structural_canvas" class="ds_canvas"></div>
						<div id="service_canvas" class="ds_canvas"></div>	
					</div>
					
					
			</div> <!-- end row fluid -->

			<div id="add_modal" class="modal hide fade" tabindex="-1"
				role="dialog" aria_labelledby="add_modal_title" aria-hidden="true">
				
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
					<h4 id="add_modal_title">Add Design Element</h4>
				</div>
					
				<div class="modal-body">
					<table>
						<tr><td>Name:</td><td><input id="add_name" type="text"></input></td></tr>
						<tr><td>Type:</td><td><div id="add_type" class="label" onclick="changeAddType()" style="cursor:pointer;">Option</div></td></tr>
						<tr><td>Description:</td><td><input id="add_description" type="text"></input></td></tr>
						<tr><td>Category:</td><td><div class="btn-group">
						<button id="add_f" class="btn active" onclick="changeAddCategory(this)">F</button>
			  			<button id="add_nf" class="btn" onclick="changeAddCategory(this)">NF</button>
			  			<button id="add_s" class="btn" onclick="changeAddCategory(this)">S</button>
			  			<button id="add_e" class="btn" onclick="changeAddCategory(this)">E</button>
			  			</div></td></tr>

			  			<tr><td>Source:</td><td id="source_placeholder"></td></tr>
			  			<!-- This needs to be a funny lookup thing -->
			  			<!-- MAke a class for this as I'll probably need a few of them? -->

					 
					</table>
				</div>
					
				<div class="modal-footer">
					<div id="btn_close" class="btn btn-inverse" data-dismiss="modal" aria-hidden="true">Close</div>
					<div id="btn_update" class="btn btn-primary" onclick="addNew()">Add</div>
				</div>
					
			</div>

			<div id="source_modal" class="modal hide fade" tabindex="-1" role="dialog"
					aria_labelledby="source_modal_title" aria-hidden="true">

					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
						<h4 id="add_modal_title">Add Source</h4>
					</div>

					<div class="modal-body">

						<table>
						<tr><td>Name:</td><td><input id="source_name" type="text"></input></td></tr>
						<tr><td>Author:</td><td><input id="source_author" type="text"></input></td></tr>
						<tr><td>Source type:</td><td><div class="btn-group">
							<button id="source_tool" class="btn active" onclick="changeSourceType(this)">Application</button>
			  				<button id="source_lit" class="btn" onclick="changeSourceType(this)">Literature</button>
			  			</div></td></tr>
					</table>
					</div>

					<div class="modal-footer">
						<div id="btn_close" class="btn btn-inverse" data-dismiss="modal" aria-hidden="true">Close</div>
						<div id="btn_update" class="btn btn-primary" onclick="addSource()">Add</div>
					</div>
			</div>

			<div id="stage_modal" class="modal hide fade" tabindex="-1" role="dialog"
					aria_labelledby="stage_modal_title" aria-hidden="true">

					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
						<h4 id="stage_modal_title">Add Stage</h4>
					</div>

					<div class="modal-body" style="display:block;">
						<div style="width:500px;position:relative;">
							<div id="old_stage_container" style="display:inline;width:50%;float:left;">
								<p>Old</p>
								<ul id="stage_list" style="border:1px solid #aaa;max-height:200px;overflow:scroll;">
								</ul><br />
								<button onclick="stageChooseOld()">Choose Old Stage</button>
							</div>
							<div style="display:inline;width:50%;left:50%;float:right;">
								<p>New</p>
								Name:&nbsp;<input type="text" id="new_stage_name"></input>
								<button onclick="stageAddNew()">Add New Stage</button>
							</div>
						</div>
					</div>

					<div class="modal-footer">
						<div id="btn_close" class="btn btn-inverse" data-dismiss="modal" aria-hidden="true">Cancel</div>
					</div>

			</div>
			
		</div> <!-- end content -->
		
		<script type="text/javascript" src="php/get_things.php"></script>
		<script type="text/javascript" src="../lib/TextCombo.js"></script>
		
		<script src="jquery-2.0.0.js"></script>
		<script src="js/bootstrap.js"></script>
		<script src="../lib/jquery-ui.js"></script>
		
		<script src="jit.js"></script>
		<script src="main.js"></script>
		
		
	</body>
</html>	