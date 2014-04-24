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
}

function setDS(ds)
{
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


	});
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



function getJSON(number)
{
	var lookupUrl = "php/out_gv.php?c=" + number;

	$.ajax({
		url: lookupUrl,
		type: "get"
	}).done(function( msg )
	{

		eval(msg);

		// alert("After eval");

		currentST = funST;

		if(MODE == 1)
			currentST.loadJSON(ds1);
		else if(MODE == 2)
			currentST.loadJSON(ds2);
		else if(MODE == 3)
		{
			// alert("getting 3");
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

	$("#incoming_container").css({ "display": "block" });

	// We need a combo box of stuff

}

function addOutgoing()
{
	if(currentNode == null)
		return;

	$("#outgoing_container").css({ "display": "block" });
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

function choose(li, name)
{
	li = $(li);
	var newLinkId = li.attr("thing_id");
	var entry = $("#" + name + "_filter");
	entry.val(li.html());
	entry.attr({ "thing_id": newLinkId });
	$("#" + name + "results").empty();
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






















