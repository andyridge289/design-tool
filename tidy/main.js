var labelType = "HTML";
var useGradients = false;
var naviteTextSupport = false;
var animate = false;

var ds1;
var ds2;
var ds3;
var ds4;

var currentST;
var currentNode = null;

MODE = 1;

var stageList = null;
var currentStage = null;

var addCombo = null;
var addIncomingCombo = null;
var addOutgoingCombo = null;

function init()
{
  	$jit.ST.Plot.NodeTypes.implement(
	{
		"stroke-rect": 
		{
			"render": function(node, canvas)
			{
				var width = node.getData("width"),
					height = node.getData("height"),
					pos = this.getAlignedPos(node.pos.getc(true), width, height),
					posX = pos.x + width/2,
					posY = pos.y + height/2;
				this.nodeHelper.rectangle.render("fill", { x: posX, y: posY }, width, height, canvas);
				this.nodeHelper.rectangle.render("stroke", { x: posX, y: posY}, width, height, canvas);
			}
		}
	});

	funST = getST("functional_canvas");
	
	getJSON(MODE);

	$.ajax({
		url: "php/get_stages.php",
		type: "post",
		data: 
		{
			ds: DS_ID
		}
	}).done(function(msg){

		console.log(msg);
		eval(msg);

		if(stages.length > 0)
		{
			// Then we just pick the first one because this will be the latest stage
			stageList = stages;

			$("#stage_num").html(stages[0].id);
			currentStage = stages[0].id * 1;
			$("#stage_name").html(stages[0].name);
		}
		else
		{
			// Prompt them that they will need to create one
			$("#stage_name").html("None")
		}

	}).fail(function ( jqXHR, textStatus, errorThrown ){ alert("Fail " + textStatus + ", " + errorThrown)});


}

function setDS(ds, thing)
{
	if(thing !== -1)
	{
		$("#sub_nav").children().attr("class", "btn");
		thing.className = "btn active";
	}

	MODE = ds;
	getJSON(MODE);
	$("#ds_num").html(ds);
}

function getST(container)
{
	var t = new $jit.ST(
	{
		injectInto: container,
		levelDistance: 30,
		offsetX: 130,
		constrained: false,
		levelsToShow: 10,
		duration: 40,
		orientation: 'top',
		width: $("#canvas").width(),
		height: $(window).height() - 20,
        
		Navigation: {
			enable: true,
			panning: true
		},
		
		Node:
		{
			overridable: true,
			type: "stroke-rect",
			height: 40,
			width: 100,
			
			CanvasStyles:
			{
				fillStyle: "#daa",
				strokeStyle: "#ffc",
				lineWidth: 1
			}
		},
		
		Edge:
		{
			overridable: true,
			type: "bezier",
			color: "#888",
			lineWidth: 1
		},
		
		Label:
		{
			overridable: true,
			type: labelType,
			size: 10,
			color: "#333",
			margin: 0,
			padding: 0,
		},
		
		Events:
		{
			enable: true,
		},
		
		onCreateLabel: function(label, node)
		{
			label.id = node.id;
			label.innerHTML = node.name;			

			label.oncontextmenu = function(e)
			{
				// nodeRightClick(node);
				return false;
			}
			
			label.onclick = function(e)
			{
				lookup(node);
				return false;
			}
			
			var style = label.style;
			style.width = 90 + "px";
			style.height = 50 + "px";
			style.marginLeft = "5px";
			style.marginTop = "5px";
			label.className = "unselectable";
			style.color = "#333";
			style.fontSize = "10px";
			style.textAlign = "center";
			style.lineHeight = "11px";
			style.maxWidth = "90px";
			style.paddingTop = "3px";
			style.cursor = "pointer";
		},
		
		onPlaceLabel: function(label, node)
		{
			var style = label.style;
			style.width = node.getData("width") + "px";
			style.height = node.getData("height")  + "px";
			style.color = node.getLabelData("color");
			style.fontSize = node.getLabelData("size")  + "px";
			style.textAlign = "center";
			style.paddingTop = "3px";
		}
	});
	
	return t;
}

function redrawGraph()
{	
	currentST.graph.eachNode(function(n)
	{	
		// Set the colour of the thing here and then get it back if it's an option. Otherwise default to the thing
		if(n.data.type == "category")
		{
			n.setCanvasStyle("fillStyle", "#8df2b6"); 
			n.setCanvasStyle("strokeStyle", "#047b35");
			n.setCanvasStyle("lineWidth", "2");
		}
		else if(n.data.type == "decision")
		{
			n.setCanvasStyle("fillStyle", "#b2dfee"); 
			n.setCanvasStyle("strokeStyle", "#004a63");
			n.setCanvasStyle("lineWidth", "2");
		}
		else
		{	
			n.setCanvasStyle("fillStyle", "#DDD");
			n.setCanvasStyle("lineWidth", "1");
			n.setCanvasStyle("strokeStyle", "#444");
		}
	});
	
	currentST.compute();
	currentST.refresh();
}

function lookup(node)
{
	currentNode = node;

	$.ajax({
		url: "php/lookup.php",
		type: "post",
	 	data: { 
	 		id: node.id
	 	}
	}).done(function( msg )
	{
		console.log(msg);
		eval(msg);
		var node = info.node_info;
		$("#node_name").html(node.name);

		var className = "label-default";
		if(node.type == "category")
			className = "label-success";
		else if(node.type == "decision")
			className = "label-info";

		var typeTag = "<span class='label " + className + "'>" + node.type + "</span>&nbsp;&nbsp;" + node.id;
		$("#node_type").html(typeTag);
		$("#info_container").css({ "display": "block" });

		$("#info2").css({ "display": "block" });
		$("#added").html(node.stage_added);
		if(node.stage_removed == -1)
			$("#removed").html("Not removed");
		else
			$("#removed").html(node.stage_removed);

		var incoming = $("#incoming");
		incoming.empty();
		for(var i = 0; i < info.incoming_connections.length; i++)
		{
			var cnxn = info.incoming_connections[i];
			var li = $(getConnection(cnxn, node, true, false));
			incoming.append(li);
		}
		
		var outgoing = $("#outgoing");
		outgoing.empty();
		for(var i = 0; i < info.outgoing_connections.length; i++)
		{
			var cnxn = info.outgoing_connections[i];
			var li = $(getConnection(cnxn, node, false, false));
			outgoing.append(li);
		}

		var deadIncoming = $("#dead_incoming");
		deadIncoming.empty();
		for(var i = 0; i < info.dead_incoming.length; i++)
		{
			var cnxn = info.dead_incoming[i];
			var li = $(getConnection(cnxn, node, true, true));
			deadIncoming.append(li);
		}

		var deadOutgoing = $("#dead_outgoing");
		deadOutgoing.empty();
		for(var i = 0; i < info.dead_outgoing.length; i++)
		{
			var cnxn = info.dead_outgoing[i];
			var li = $(getConnection(cnxn, node, false, true));
			deadOutgoing.append(li);
		}

		var sourcesList = $("#source_list");
		sourcesList.empty();
		for(var i = 0; i < info.sources.length; i++)
		{
			var src = info.sources[i];
			var li = $("<li>" + src.name + "</li>");
			sourcesList.append(li);
		}

	});
}

function clearInfo()
{
	// TODO Hide the info panel
	$("#info_container").css({ "display": "none" });
}

function showAddModal()
{
	$.ajax({
		url: "../lib/get_sources.php",
		type: "get"
	}).done(function(msg){

		eval("var sources = " + msg);

		var data = [];
		for(var i = 0; i < sources.length; i++)
		{
			if(sources[i].type == "literature")
				data.push([sources[i].id, sources[i].creator + ": " + sources[i].name]);
			else
				data.push([sources[i].id, sources[i].name]);
		}

		addCombo = new TextCombo(data);

		$("#source_placeholder").empty();
		$("#source_placeholder").append(addCombo.container);

		$("#add_modal").modal();

		}).fail(function ( jqXHR, textStatus, errorThrown ){ alert("Fail " + textStatus + ", " + errorThrown)});;
}

function statusText(title, text, type)
{
	var container = $("#status");
	container.css({ "display": "block" });

	var titleDOM = $("#status_title");
	titleDOM.html(title);

	var textDOM = $("#status_text");
	textDOM.html(text);

	container.attr({ "class": "alert alert-dismissable alert-" + type });
}

function changeAddCategory(thing)
{
	var buttons = [];
	buttons.push($("#add_f"));
	buttons.push($("#add_nf"));
	buttons.push($("#add_s"));
	buttons.push($("#add_e"));

	for(var i = 0; i < buttons.length; i++)
	{
		buttons[i].attr({ "class": "btn" });
	}

	thing.className = "btn active";
}

function changeSourceType(thing)
{
	$("#source_tool").attr({ "class": "btn" });
	$("#source_lit").attr({ "class": "btn" });
	thing.className = "btn active";
}

function changeAddType()
{
	var typeDiv = $("#add_type");
	var className = typeDiv.attr("class");

	if(className == "label")
	{
		typeDiv.attr({ "class": "label label-info" });
		typeDiv.html("Decision");
	}
	else if(className == "label label-info")
	{
		typeDiv.attr({ "class": "label label-success"});
		typeDiv.html("Category");
	}
	else // it's label label-success
	{
		typeDiv.attr({ "class": "label"});
		typeDiv.html("Option");
	}
}

function addSource()
{
	var sourceName = $("#source_name").val();
	var sourceAuthor = $("#source_author").val();
	var sourceType = "tool";
	if($("#source_lit").attr("class") == "btn active")
		sourceType = "literature";

	$.ajax({
		url: "../lib/add_source.php",
		type: "post",
		data:
		{
			name: sourceName,
			author: sourceAuthor,
			type: sourceType,
			ds: DS_ID
		}
	}).done(function(msg){

		if(msg == "win")
		{
			statusText("Success", "New source \"" + sourceName + "\" added successfully", "success");

			$("#source_name").val("");
			$("#source_author").val("");
			$("#source_lit").attr({ "class": "btn" });
			$("#source_lit").attr({ "class": "btn active" });

			$("#add_modal").modal("hide");
		}
		else
		{
			alert(msg);
		}

	}).fail(function ( jqXHR, textStatus, errorThrown ){ alert("Fail " + textStatus + ", " + errorThrown)});;
}

function addNew()
{
	var thingName = $("#add_name").val();
	var thingDescription = $("#add_description").val();
	var className = $("#add_type").attr("class");
	var thingType = "option";
	if(className == "label label-info")
		thingType = "decision";
	else if(className == "label label-success")
		thingType = "category";

	var thingCategory = ""
	if($("#add_f").attr("class") == "btn active")
		thingCategory = "Functional";
	else if($("#add_nf").attr("class") == "btn active")
		thingCategory = "Non-Functional";
	else if($("#add_s").attr("class") == "btn active")
		thingCategory = "Structural";
	else if($("#add_e").attr("class") == "btn active")
		thingCategory = "Entity";

	var thingSource = addCombo.getSelected() == null ? -1 : addCombo.getSelected()[0];

	$.ajax({
		url: "php/add_thing.php",
		type: "post",
		data:
		{
			name: thingName,
			type: thingType,
			category: thingCategory,
			description: thingDescription,
			source: thingSource,
			stage: currentStage,
			ds_id: DS_ID
		}
	}).done(function( msg )
	{
		if(msg == "win")
		{
			// That's fine, show a hover thing that says it was added successfully
			// Clear all the fields and hide the box
			statusText("Success", "new " + thingType + " \"" + thingName + "\" added successfully", "success");
		
			$("#add_name").val("");
			$("#add_description").val("");
			
			$("#add_type").attr({ "class": "label"});
			$("#add_type").html("Option");

			$("#add_f").attr({ "class": "btn active" });
			$("#add_nf").attr({ "class": "btn" });
			$("#add_s").attr({ "class": "btn" });
			$("#add_e").attr({ "class": "btn" });

			$("#add_modal").modal("hide");
		}
		else
		{
			alert(msg);
		}

	}).fail(function ( jqXHR, textStatus, errorThrown ){ alert("Fail " + textStatus + ", " + errorThrown)});
}

function stageChooseOld()
{
	// Find the one in the list that has the class selected and then set the current stage to that one
	var ul = $("#stage_list");
	var selectedLi = null;

	for(var i = 0; i < ul.children().length; i++)
	{
		if($(ul.children()[i]).attr("class") == "selected")
		{
			// Then it's this one
			selectedLi = $(ul.children()[i]);
			break;
		}
	}

	if(selectedLi == null)
	{
		alert("Nothing selected")
	}
	else
	{
		currentStage = selectedLi.attr("stage_num");
		$("#stage_num").html(currentStage);
		$("#stage_name").html(selectedLi.html());
		$("#stage_modal").modal("hide");
	}
}

function stageAddNew()
{
	// Get the name of the current stage and then send an AJAX
	var stageName = $("#new_stage_name").val();

	$.ajax({
		url: "php/add_stage.php",
		type: "get",
		data:
		{
			name: stageName,
			ds: DS_ID
		}
	}).done(function( msg )
	{
		currentStage = newStage.id;
		$("#stage_num").html(currentStage);
		$("#stage_name").html(newStage.name);
		$("#stage_modal").modal("hide");

		if(stages.length > 0)
		{
			// Then we just pick the first one because this will be the latest stage
			stageList = stages;
		}

	}).fail(function ( jqXHR, textStatus, errorThrown ){ alert("Fail " + textStatus + ", " + errorThrown)});
}

function showStageModal()
{
	var ul = $("#stage_list");
	ul.empty();

	if(stageList == null)
	{
		$("#old_stage_container").css({ "display": "none" });
	}
	else
	{
		$("#old_stage_container").css({ "display": "block" });

		for(var i = 0; i < stageList.length; i++)
		{
			var li = $("<li>" + stageList[i].name + "</li>");
			li.css({ 
				"border-bottom": "1px solid #eee", 
				"cursor": "pointer"
			});

			li.attr({
				"class": "unselected",
				"stage_num": stageList[i].id
			})

			li.click(function()
			{
				for(var j = 0; j < ul.children().length; j++)
				{
					$(ul.children()[j]).attr({ "class": "unselected" });
				}

				$(this).attr({ "class": "selected" });
			});

			ul.append(li);
		}
	}

	$('#stage_modal').modal();
}

function getConnection(cnxn, origin, isIncoming, isDead)
{
	var id = cnxn.id;
	var name = cnxn.name;
	var type = cnxn.type;

	var className = "label-default";
		if(type == "category")
			className = "label-success";
		else if(type == "decision")
			className = "label-info";

	var incoming = isIncoming ? "&raquo;<span class='label label-warning' style='margin-left:10px;'>" + origin.name + "</span>" : "";
	var outgoing = isIncoming ? "" : "<span class='label label-warning' style='margin-right:10px;'>" + origin.name + "</span>&raquo;";
	var style = isIncoming ? " style = 'margin-right:10px;'" : " style = 'margin-left:10px;'";
	var params = isIncoming ? id + "," + origin.id : origin.id + "," + id; 
	var deadStatus = isDead ? "<span class='label label-inverse' style='margin-left:20px;'>Dead</span>" : "";

	// var parentId = isIncoming ?  : id;
	// var childId = isIncoming ? id : origin.id;

	var deadButton = isDead ? "<button onclick=\"undead(" + origin.id + "," + id + ")\">Undead</button>" : "" ;

	return "<li class='connection'>" + 
		"<div>" + outgoing + 
			"<span class='label " + className + "' " + style + ">" + name + "</span>" + 
		incoming + deadStatus + "</div>" + 
		"<button onclick='removeLink(" + params + ", this)'>Remove</button>" +
		"<button onclick=\"followLink(" + id + ",'" + name + "','" + type + "')\">Follow link</button>" +
		deadButton
	"</li>";
}

function undeadThing()
{
	if(currentNode == null)
		return;

	$.ajax({
		url: "php/set_dead.php",
		type: "post",
		data:
		{
			type: "thing",
			id: currentNode.id
		}
	}).done(function( msg )
	{
		// if(msg == "win")
		// {
		// 	getJSON(MODE);
		// 	$("#removed").html("Not removed");
		// }
		// else
		// {
			alert(msg);
		// }
	});
}

function undead(parentId, childId)
{
	$.ajax({
		url: "php/set_dead.php",
		type: "post",
	 	data: { 
	 		type: "relation",
	 		parent_id: parentId,
	 		child_id: childId,
	 		new_dead: 0
	 	}
	}).done(function( msg )
	{
		if(msg == "win")
		{
			// $(button.parentNode).remove();

			redrawGraph();
			
			getJSON(MODE);
		}
		else
		{
			alert(msg);
		}
	});
}

function removeLink(parentId, childId, button)
{
	// alert("Remove " + parentId + ", " + childId);
	$.ajax({
		url: "php/remove.php",
		type: "post",
	 	data: { 
	 		parent_id: parentId,
	 		child_id: childId
	 	}
	}).done(function( msg )
	{
		if(msg == "win")
		{
			$(button.parentNode).remove();

			redrawGraph();
			
			getJSON(MODE);
		}
		else
		{
			alert(msg);
		}
	});
}

function getJSON(number)
{
	var lookupUrl = "../lib/out_json.php?c=" + number + "&ds=" + DS_ID;

	$.ajax({
		url: lookupUrl,
		type: "get"
	}).done(function( msg )
	{
		eval(msg);

		currentST = funST;

		if(MODE == 1)
			currentST.loadJSON(ds1);
		else if(MODE == 2)
			currentST.loadJSON(ds2);
		else if(MODE == 3)
		{
			currentST.loadJSON(ds3);
		}
		else
			currentST.loadJSON(ds4);

		currentST.compute();
		currentST.onClick(currentST.root);

		redrawGraph(); 
	}).fail(function ( jqXHR, textStatus, errorThrown ){ alert("Fail " + textStatus + ", " + errorThrown)});
}

function followLink(id, name, type)
{
	var node = new Object();
	node.id = id;
	node.name = name;
	node.type = type;

	lookup(node);
}

function addIncoming()
{
	if(currentNode == null)
		return;

	$.ajax({
		url: "php/get_things.php",
		type: "post",
		data:
		{
			ds: DS_ID 
		}
	}).done(function( msg )
	{
		eval(msg);
		$("#incoming_container").empty();

		var data = [];
		for(var i = 0; i < things.length; i++)
		{
			data.push([things[i].id, things[i].name]);
		}

		addIncomingCombo = new TextCombo(data);
		$("#incoming_container").append(addIncomingCombo.container);

		var button = $("<button type='button' onclick='actuallyAddIncoming()'>Add</button>");
		$("#incoming_container").append(button)

	}).fail(function ( jqXHR, textStatus, errorThrown ){ alert("Fail " + textStatus + ", " + errorThrown)});
}

function addOutgoing()
{
	if(currentNode == null)
		return;

	$.ajax({
		url: "php/get_things.php",
		type: "post",
		data:
		{
			ds: DS_ID 
		}
	}).done(function( msg )
	{
		eval(msg);
		$("#outgoing_container").empty();

		var data = [];
		for(var i = 0; i < things.length; i++)
		{
			data.push([things[i].id, things[i].name]);
		}

		addOutgoingCombo = new TextCombo(data);
		$("#outgoing_container").append(addOutgoingCombo.container);

		var button = $("<button type='button' onclick='actuallyAddOutgoing()'>Add</button>");
		$("#outgoing_container").append(button)
	}).fail(function ( jqXHR, textStatus, errorThrown ){ alert("Fail " + textStatus + ", " + errorThrown)});
}

function actuallyAddOutgoing()
{
	var parentId = currentNode.id;
	var childId = addOutgoingCombo.getSelected()[0];
	actuallyAdd(parentId, childId);
}

function actuallyAddIncoming()
{
	var parentId = addIncomingCombo.getSelected()[0];
	var childId = currentNode.id;

	actuallyAdd(parentId, childId);
}

function actuallyAdd(parentId, childId)
{
	$.ajax({
		url: "php/link.php",
		type: "post",
		data:
		{
			parent: parentId,
			child: childId
		}
	}).done(function( msg )
	{
		if(msg == "win")
		{
			$("#incoming_container").empty();
			$("#outgoing_container").empty();

			getJSON(MODE);
		}
		else
		{
			alert(msg);
		}
	});
}

function filter(name, event)
{
	var input = $(event.target).val();
	var results = [];

	for(var i = 0; i < things.length; i++)
	{
		if(things[i].name.toLowerCase().indexOf(input.toLowerCase()) != -1)
		{
			results.push(things[i]);
		}
	}

	var resultsContainer = $("#" + name + "_results");
	resultsContainer.empty();
	var resultsList = $("<ul id='" + name + "_results'></ul>");

	for(var i = 0; i < results.length; i++)
	{
		var result = $("<li class='bob' thing_id='" + results[i].id + "' onclick=\"choose(this, '" + name + "')\">" + results[i].name + "</li>");
		resultsList.append(result);
	}

	resultsContainer.append(resultsList);
}



function done(name)
{
	var newLinkId = $("#" + name + "_filter").attr("thing_id");
	var id = currentNode.id;

	var parentId = -1;
	var childId = -1;

	if(name == "incoming")
	{
		parentId = newLinkId;
		childId = id;
	}
	else if(name == "outgoing")
	{
		parentId = id;
		childId = newLinkId;
	}

	$.ajax({
		url: "php/link.php",
		type: "post",
		data:
		{
			parent: parentId,
			child: childId
		}
	}).done(function( msg )
	{
		if(msg == "win")
		{
			getJSON(MODE);
		}
		else
		{
			alert(msg);
		}
	});

}

function setType(type)
{
	if(currentNode == null)
		return;

	$.ajax({
		url: "php/set_type.php",
		type: "post",
		data:
		{
			id: currentNode.id,
			type: type
		}
	}).done(function( msg )
	{
		if(msg == "win")
		{
			getJSON(MODE);
		}
		else
		{
			alert(msg);
		}
	});
}






















