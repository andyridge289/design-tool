var labelType = "HTML";
var useGradients = false;
var naviteTextSupport = false;
var animate = false;

var ds1 = null;
var ds2 = null;
var ds3 = null;
var ds4 = null;

var currentST;
var currentNode = null;

var currentTool = null;
var toolThings = null;

var DS_MODE = 1;

var MODE_ADD = 10;
var MODE_OUT = 11;
var MODE = MODE_ADD;

var outputTop = -1;

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

	$("#radio_container").css({ "display": "block" });
	$("#check_container").css({ "display": "none" });

	$("#btn_add").css({ 
		"font-weight": "bold",
		"background": "#aaa" 
	});
	$("#btn_out").css({ 
		"font-weight": "normal",
		"background": "#eee"
	 });

	getSources();
}

function setMode(mode)
{
	MODE = mode;

	var addButton = $("#btn_add");
	var outButton = $("#btn_out");

	if(MODE == MODE_ADD)
	{
		// We need to make it change the name of the header
		// Clicking on tools needs to change the tool

		$("#radio_container").css({ "display": "block" });
		$("#check_container").css({ "display": "none" });

		addButton.css({ 
			"font-weight": "bold",
			"background": "#aaa" 
		});
		outButton.css({ 
			"font-weight": "normal",
			"background": "#eee"
		 });
	}
	else if(MODE == MODE_OUT)
	{
		// We need to make it change the name of the header
		// The tools need to be a checklist instead, so clikcing on them can add and remove things from the output
		
		$("#radio_container").css({ "display": "none" });
		$("#check_container").css({ "display": "block" });

		outButton.css({ 
			"font-weight": "bold",
			"background": "#aaa" 
		});
		addButton.css({ 
			"font-weight": "normal",
			"background": "#eee"
		 });

		// toolThings needs to be null at this point to clear the purple
		toolThings = null;

	}

	set();
}

function getSources()
{
	$.ajax({
		url: "../lib/get_sources.php",
		type: "get",
	 	data: { 
	 		ds: DS_ID,
	 		to: "1"
	 	}
	}).done(function( msg )
	{
		console.log("Sources = " + msg);

		if(msg.substring(0, 4) == "Fail")
		{
			alert(msg);
		}
		else
		{
			eval("var sources = " + msg);

			var radioContainer = $("#radiogroup");
			var checkContainer = $("#checkgroup");

			for(var i = 0; i < sources.length; i++)
			{
				var rc = $("<div class='radio'></div>");
				var label = $("<label>" + sources[i].name + "</label>");
				rc.append(label);

				var input = $("<input onclick='toolRadio()'>");
				input.attr({
					 "type": "radio",
					 "name": "toolRadio",
					 "value": sources[i].id
				})
				label.append(input);
				radioContainer.append(rc);

				var cc = $("<div class='check'></div>");
				
				var cLabel = $("<label><input type='checkbox' onclick='toolCheck()' name='toolCheck' value=" + sources[i].id + ">&nbsp;" + sources[i].name + "</label>");
				cc.append(cLabel);
				checkContainer.append(cc);

				if(i == 10)
				{
					input.prop('checked', true);
				}
			}

			if(MODE == MODE_ADD)
			{
				$("radio_cotnainer").css({ "display": "block" });
				$("check_cotnainer").css({ "display": "none" });
			}
			else
			{
				$("radio_cotnainer").css({ "display": "none" });
				$("check_cotnainer").css({ "display": "block" });
			}
		

			funST = getST("functional_canvas");
			getJSON(DS_MODE);
		}
	}).fail(function ( jqXHR, textStatus, errorThrown ){ alert("Fail " + textStatus + ", " + errorThrown)});
}

function makeOutput()
{
	// What are the tool IDs
	var ids = $('input[name=toolCheck]:checked', '#checkgroup');

	if(ids.length == 0)
	{
		alert("You didn't choose any tools to export the profile for!");
		return;
	}

	var list = [];
	for(var i = 0; i < ids.length; i++)
	{
		list.push($(ids[i]).val() * 1);
	}

	var strList = JSON.stringify(list);
	strList = strList.substring(1, strList.length - 1);

	// What radio is set - this tells us the URL
	var outputType = $('input[name=typeRadio]:checked').val();

	var params = "?ds=" + DS_ID + "&r=" + outputTop + "&t=" + strList;
	var outputUrl = outputType == "table" ? "php/profiling_results_csv.php" : "php/profiling_results_gv.php";
		
	params += (outputType == "heat") ? "&h=1" : "&h=0"
	outputUrl += params;

	console.log(outputUrl);

	window.open(outputUrl, '_blank');
}

function setDS(ds, thing)
{
	if(thing !== -1)
	{
		$("#sub_nav").children().attr("class", "btn");
		thing.className = "btn active";
	}

	DS_MODE = ds;
	getJSON(DS_MODE);
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
				nodeRightClick(node);
				return false;
			}
			
			label.onclick = function(e)
			{
				if(MODE == MODE_ADD)
					nodeLeftClick(node);
				else
					nodeLeftClickOut(node);

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

function nodeRightClick(node)
{
	console.log(node);
	statusText(node.name, node.data.description, "info");
}

function statusText(title, text, type)
{
	var container = $("#status");
	container.css({ "display": "block" });

	var titleDOM = $("#status_title");
	titleDOM.html(title);

	var textDOM = $("#status_text");
	textDOM.html(text);

	container.css({ "display": "block" });
	container.attr({ "class": "alert alert-" + type });
}

function nodeLeftClickOut(node)
{
	outputTop = node.id;
	$("#root_name").html(node.name);
	statusText("New output root", "Set output root to " + node.name, "info")
}

function nodeLeftClick(node)
{
	// Get the ID
	var id = node.id;

	// See if it's in the array
	if(toolThings.indexOf(id * 1) != -1)
	{
		// If it is then we need to remove it from the DB
		// And reload
		$.ajax({
			url: "php/kill_tool_link.php",
			type: "post",
		 	data: { 
		 		thing_id: id,
		 		tool_id: currentTool
		 	}
		}).done(function( msg )
		{
			if(msg.substring(0, 4) == "Fail")
			{
				alert(msg);
			}
			else
			{
				toolRadio();
			}

		}).fail(function ( jqXHR, textStatus, errorThrown ){ alert("Fail " + textStatus + ", " + errorThrown)});
	}
	else
	{
		// If if isn't then we need to set it in the DB
		$.ajax({
			url: "php/add_tool_link.php",
			type: "post",
		 	data: { 
		 		thing_id: id,
		 		tool_id: currentTool
		 	}
		}).done(function( msg )
		{
			console.log(msg);

			if(msg.substring(0, 4) == "Fail")
			{
				alert(msg);
			}
			else
			{
				eval(msg);
				for(var i = 0; i < info.length; i++)
				{
					// toolThings.push(info[i]);
					toolRadio();
				}
			}

		}).fail(function ( jqXHR, textStatus, errorThrown ){ alert("Fail " + textStatus + ", " + errorThrown)});
	}
	
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
		else if(toolThings != null)
		{
			if(toolThings.indexOf(n.id * 1) != -1)
			{
				if(n.data.type == "decision")
				{
					n.setCanvasStyle("fillStyle", "#b2dfee"); 
					n.setCanvasStyle("strokeStyle", "#7F00FF");
					n.setCanvasStyle("lineWidth", "2");
				}
				else
				{
					// Then it's an option
					n.setCanvasStyle("fillStyle", "#BF5FFF");
					n.setCanvasStyle("lineWidth", "1");
					n.setCanvasStyle("strokeStyle", "#444");
				}
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

function toolCheck()
{
	var ids = $('input[name=toolCheck]:checked', '#checkgroup');

	if(ids.length == 0)
		return;

	var list = [];
	for(var i = 0; i < ids.length; i++)
	{
		list.push($(ids[i]).val() * 1);
	}

	var strList = JSON.stringify(list);
	strList = strList.substring(1, strList.length - 1);

	$.ajax({
		url: "php/lookup_tools.php",
		type: "get",
		data:
		{
			ds: DS_ID,
			t: strList
		}
	}).done(function( msg )
	{
		eval(msg);
		toolThings = info;
		set();
		
	}).fail(function ( jqXHR, textStatus, errorThrown ){ alert("Fail " + textStatus + ", " + errorThrown)});
}

function toolRadio()
{
	// Get the checked radio button
	var id = $('input[name=toolRadio]:checked', '#radiogroup').val();
	currentTool = id;

	$.ajax({
		url: "php/lookup_tool.php",
		type: "post",
		data:
		{
			tool_id: id
		}
	}).done(function( msg )
	{
		eval(msg);
		toolThings = info;
		set();
		
	}).fail(function ( jqXHR, textStatus, errorThrown ){ alert("Fail " + textStatus + ", " + errorThrown)});
}

function thingInArray(thing, array)
{
	for(var i = 0; i < array.length; i++)
	{
		if(thing.id == array[i])
			return true;
	}

	return false;
}

function set()
{
	currentST = funST;

	if(DS_MODE == 1 && ds1 != null)
		currentST.loadJSON(ds1);
	else if(DS_MODE == 2)
		currentST.loadJSON(ds2);
	else if(DS_MODE == 3)
		currentST.loadJSON(ds3);
	else
		currentST.loadJSON(ds4);

	currentST.compute();
	currentST.onClick(currentST.root);

	redrawGraph(); 
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

		if(currentTool == null)
			toolRadio();
		else
			set();

		
	}).fail(function ( jqXHR, textStatus, errorThrown ){ alert("Fail " + textStatus + ", " + errorThrown)});
}

function addSource()
{
	var sourceName = $("#source_name").val();
	var sourceType = "tool";
	if($("#source_lit").attr("class") == "btn active")
		sourceType = "literature";

	$.ajax({
		url: "../lib/add_source.php",
		type: "post",
		data:
		{
			name: sourceName,
			author: "",
			type: "tool",
			ds: DS_ID
		}
	}).done(function(msg){

		if(msg == "win")
		{
			statusText("Success", "New source \"" + sourceName + "\" added successfully", "success");

			$("#source_name").val("");
			$("#add_modal").modal("hide");
			location.reload();
		}
		else
		{
			alert(msg);
		}

	}).fail(function ( jqXHR, textStatus, errorThrown ){ alert("Fail " + textStatus + ", " + errorThrown)});;
}