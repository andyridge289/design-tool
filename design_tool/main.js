

var labelType, useGradients, nativeTextSupport, animate;
var baseURL = "http://localhost/htdocs/thesis_stuff/design_tool/php/";
var jaccardURL = baseURL + "jaccard.php?id=";
var toolURL = baseURL + "get_tools.php?id=";
var optionURL = baseURL + "get_options.php";
var optionIdURL = baseURL + "get_option_id.php?o=";
var linkedOptionURL = baseURL + "get_linked_options.php?o=";
var getForToolURL = baseURL + "get_for_tool.php?id="

var importURL = "http://localhost/htdocs/thesis_stuff/study2/json_participant.php?p=";

var optionSpanText = "<span oncontextmenu='tagInfo(event, options); return false;' ondblclick='tagDoubleClick(event);'>";
var unchosenSpanText = "<span oncontextmenu='tagInfo(event, unChosenOptions); return false;' ondblclick='tagUnmake(event);'>";
var customSpanText = "<span oncontextmenu='tagInfo(event, customOptions); return false;' ondblclick='customUnmake(event);'>";
// onclick='tagClick(event)'

var funST, nonST, strST, serST;
var orig1, orig2, orig3, orig4;
var currentST;
var funUL, nonUL, strUL, serUL;
var currentUL;

var labelType = "HTML";
var useGradients = false;
var naviteTextSupport = false;
var animate = false;

var colours = new Array();
var percentages = new Array();

var options = new Array();
var unChosenOptions = new Array();

var customOptions = new Array();
var unChosenCustomOptions = new Array();

var allOptions = new Array();

var instructions = "";

var lookupOption = "";
var lookupOptionId = -1;
var lookupTool = "";

var mode;

var CONSTRAINTS = 0;
var OPTIONS = 1;
var TOOLS = 2;

var PREVIEW_TOOL = 3;
var PREVIEW_OPTION = 4;

var REP_TREE = 5;

var representationMode = REP_TREE;
var SHOW_OPTIONS = true;

var participantNum = 1; 

var currentDS;

var FUNCTIONAL = "fxn";
var NONFUNCTIONAL = "nfxn";
var STRUCTURAL = "struct";
var SERVICE = "serv";
var CUSTOM = "custom";

var HEURISTICS = false;
var SAVE = false;

var currentOption = null;

var unChosenView = false;

var startTime = -1;
var timeTaken = -1;

var sliderValues = ["Sod all", "Some decisions", "All decisions", "All decisions, some options", "All decisions & options"];

function init()
{	
	lookupOptions();

	SAVE = canStore();
	
	if(representationMode === REP_TREE)
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

		// if(!SHOW_OPTIONS)
		// {
		// 	trimDS(ds1);
		// 	trimDS(ds2);
		// 	trimDS(ds3);
		// 	trimDS(ds4);
		// }
		
		// Pre-load all of the DS trees
		funST = getST("functional_canvas");
		funST.loadJSON(ds1);
		orig1 = jQuery.extend(true, {}, ds1);
		
		nonST = getST("nonfunctional_canvas");
		nonST.loadJSON(ds2);
		orig2 = $.extend(true, {}, ds2);
		
		strST = getST("structural_canvas");
		strST.loadJSON(ds3);
		orig3 = $.extend(true, {}, ds3);
		
		serST = getST("service_canvas");
		serST.loadJSON(ds4);
		orig4 = $.extend(true, {}, ds4);

		setMode('options', false);
		$("#nav_constraints").click();
		$("#subnav_fxn").click();

		$('#slider').slider({
			min: 0,
			max: 4, 
			step: 1,
			orientation: "horizontal",
			value: 4,
			tooltip: "hide"
		});

		$("#slider").on('slide', function(slideEvt) {
			var sVal = slideEvt.value;
			$("#sliderValue").text(sliderValues[sVal]);
			trimDS(sVal);
		});
	}
	
	if(SAVE)
	{	
		if(localStorage.options)
		{
			options = JSON.parse(localStorage.options);

			// Put them in the options list on the right
			for(var i = 0; i < options.length; i++)
			{
				var option = options[i];
				
				var optionSpan = $(optionSpanText + option.name + "</span>");
		
				optionSpan.attr({
					"id": "tag_" + option.id,
					"class": "label label-important choice_label unselectable",
					"active": "true", 
					"tagId": option.id,
					"tagDs": option.ds,
					"tagRationale": option.rationale
				});
				
				$("#" + option.dsCode + "_options").append(optionSpan);
			}
		}

		if(localStorage.unChosenOptions)
		{
			unChosenOptions = JSON.parse(localStorage.unChosenOptions);

			for(var i = 0; i < unChosenOptions.length; i++)
			{
				var o = unChosenOptions[i];
				var optionSpan = $(unchosenSpanText + o.name + "</span>");
				optionSpan.attr({
					"id": "tag_" + o.id,
					"class": "label label-warning choice_label unselectable",
					"active": "true", 
					"tagId": o.id,
					"tagDs": o.ds,
					"tagRationale": o.rationale
				});

				$("#unChosen").append(optionSpan);
			}
		}

		if(localStorage.customOptions)
		{
			customOptions = JSON.parse(localStorage.customOptions);

			for(var i = 0; i < customOptions.length; i++)
			{
				var option = customOptions[i];
				var optionSpan = $(customSpanText + option.name + "</span>");
			
				optionSpan.attr({
					"id": "tag_" + option.id,
					"class": "label label-success choice_label unselectable",
					"active": "true", 
					"tagId": option.id,
					"tagDs": option.ds,
					"tagRationale": option.rationale
				});

				$("#custom_options").append(optionSpan);
			}
		}
	}

	$("#participant_num").html("Design " + participantNum);
	
	if(!HEURISTICS)
	{
		// Make all the tool things invisible
		$("overlay_related_header").css({ "display": "none" });
		$("overlay_related").css({ "display": "none" });
		$("overlay_tools_header").css({ "display": "none" });
		$("overlay_tools").css({ "display": "none" });
		
		// Make the button for viewing the heuristics invisible
		$("btn_preview").css({ "display": "none" }); 
	}

	redraw();
}

function trimDS(filterVal)
{
	console.log(filterVal);
	// For each of the DSs, look at the original, and apply the relevant filter

	// Put the DS back to its original state, and then recursively remove elements
	ds1 = jQuery.extend(true, {}, orig1);
	ds2 = jQuery.extend(true, {}, orig2);
	ds3 = jQuery.extend(true, {}, orig3);
	ds4 = jQuery.extend(true, {}, orig4);

	trimElement(ds1, filterVal);
	trimElement(ds2, filterVal);
	trimElement(ds3, filterVal);
	trimElement(ds4, filterVal);

	setDS(currentDS, -1);
}

function trimElement(elem, val)
{
	if(elem.data.type == "category")
	{
		for(var i = 0; i < elem.children.length; i++)
		{
			trimElement(elem.children[i], val);
		}
	}
	else if(elem.data.type == "decision")
	{
		for(var i = 0; i < elem.children.length; i++)
		{
			var child = elem.children[i];

			if(child.data.type == "decision")
			{
				if(val == 1)
				{
					var prop = child.data.pCount / TOOL_COUNT;

					if(prop < 0.5)
					{
						elem.children.splice(i, 1);
						i--;
					}
				}

				trimElement(child, val);
			}
			else if(child.data.type == "option")
			{
				if(val < 3)
				{	
					// If it's < 3 then we can remove all the options.

					console.log("Should be removing " + child.name);
					elem.children.splice(i, 1);
					i--;
				}	
				else if(val == 3)
				{
					var prop = child.data.pCount / TOOL_COUNT;

					if(prop < 0.5)
					{
						elem.children.splice(i, 1);
						i--;
					}
				}
				// If it's 4 then we're alright
			}
		}
	}
}

function importJSON()
{
	var pNum = $("#import_pid").val();

	$.ajax({ 
		url: importURL + pNum 
	}).done(function(data)
	{
		if(SAVE)
		{
			localStorage.customOptions = data;
			init();
		}
	});
}

function timer(e)
{
	var seconds = new Date().getTime() / 1000;
	
	if(startTime == -1)
	{
		startTime = seconds;
		$(e.target).html("Stop Timer")

		if(SAVE)
		{
			localStorage.startTime = startTime;
		}
	}
	else
	{
		if(timeTaken == -1)
			timeTaken = seconds - startTime;
		else
			timeTaken += (seconds - startTime);

		$(e.target).html("Start Timer");

		if(SAVE)
		{
			localStorage.timeTaken = timeTaken;
		}
	}
}

function makeList(JSON, id)
{
	var rootUL = $("<ul id='list_container" + id + "' class='ds_ul'></ul>");

	listAdd(rootUL, JSON, 0);

	return rootUL;
}

// There's never a decision at depth 0
var decisionColours = [ "", "#00a6dc", "#2cb1dc", "#58bbdc", "#84c6dc", "#b0d1dc", "#dcdcdc" ];

function listAdd(ul, node, depth)
{
	var contents = $("<div id='contents_" + node.id + "' class='li_contents'>" + node.name + "<span>&nbsp;&nbsp;&nbsp;&nbsp;[" + node.data.type + "]</span></div>");
	var li = $("<li id='" + node.id + "'></li>");

	li.append(contents);

	if(node.data.type == "option")
		li.attr({ "class": "unselectable li_ds li_option" });
	else if(node.data.type == "decision")
	{
		li.attr({ "class": "unselectable li_ds li_decision" });
		
	}
	else
		li.attr({ "class": "unselectable li_ds li_category" });
	

	ul.append(li);

	var desc = $("<div class='unselectable'></div>");
	desc.html(node.data.description);
	contents.append(desc);

	var parentName = $("<div class='parent_name'></div>");
	parentName.html("" + depth);
	contents.append(parentName);
	// parentName.html();

	li.mousedown(function(e)
		{
			if(e.button == 2)
			{
				// oncontextmenu
				nodeRightClick(node);
				return false;
			}

			return true;

		});

	if(node.data.type === "option")
	{
		li.click(function(e) 
		{
			nodeClick(node);
			return false;
		});
	}
	else
	{
		if(node.data.type == "decision")
		{
			contents.attr({ "class": "li_contents contents_decision" });
			contents.css({ "background-color": decisionColours[depth] });
		}
		else if(node.data.type == "category")
		{
			contents.attr({ "class": "li_contents contents_category" });
			
		}

		var numChildren = $("<div class='unselectable hidden_kids'></div>");

		if(node.children.length == 1)
			numChildren.html("(1 hidden child)");
		else
			numChildren.html("(" + node.children.length + " hidden children)");

		numChildren.css({ "display": "none" });

		contents.append(numChildren);

		li.click(function()
		{
			var subUL = $(this).children(".ds_ul");
			var subKids = $(this).children(".li_contents").children(".hidden_kids");

			// Append something to show that there are invisible children	

			if(subUL.attr("visible") == "true")
			{
				// Make it invisible
				subUL.attr({ "visible": "false" });
				subUL.css({ "display": "none" });

				subKids.css({ "display": "block" });
			}
			else
			{
				// Make it visible
				subUL.attr({ "visible": "true" });
				subUL.css({ "display": "block" });

				subKids.css({ "display": "none" });
			}

			return false;
		});
	}

	if(node.children.length == 0)
		return;

	var subUL = $("<ul class='ds_ul'></ul>");
	subUL.attr({ "visible": "true" });
	li.append(subUL);

	var decisions = [];
	var options = [];

	for(var i = 0; i < node.children.length; i++)
	{
		if(node.children[i].data.type == "decision")
			decisions.push(node.children[i]);
		else if(node.children[i].data.type == "option")
			options.push(node.children[i]);
	}

	for(var i = 0; i < options.length; i++)
	{
		listAdd(subUL, options[i], depth + 1);
	}

	for(var i = 0; i < decisions.length; i++)
	{
		listAdd(subUL, decisions[i], depth + 1);
	}
}

function participant()
{
	$("#participant_num").css({ "display": "none" });
	$("#participant_text").css({ "display": "block" });
}

function unmade()
{
	var unChosen = $("#unChosen");

	if(unChosenView)
	{
		unChosenView = false;
		unChosen.css({ "display": "none" });
	}
	else
	{
		unChosenView = true;
		unChosen.css({ "display": "block" });
	}
}

function unmake()
{
	if(currentOption.id == -1)
	{
		var tags = $("#custom_options").children();
		for(var i = 0; i < tags.length; i++)
		{
			var tag = $(tags[i]);

			if(tag.html() == currentOption.name)
			{
				tag.remove();
			}
		}
	}
	else
	{
		$("#tag_" + currentOption.id).remove();
	}

	unmakeDecision(currentOption);
	currentOption = null;

	// And close the modal
	$("#tag_modal").modal("hide");

	redraw();
}

function updateRationale()
{
	currentOption.rationale = $("#tag_rationale_text").val();

	if(SAVE)
	{
		localStorage.options = JSON.stringify(options);
	}

	// Close the modal
	$("#tag_modal").modal("hide");
}

function setParticipant(event)
{
	if(event.keyCode != 13)
		return;

	var textbox = $(event.target)
	var participantText = $("#participant_num");

	participantNum = textbox.val() * 1;

	participantText.html("Participant " + textbox.val());
	textbox.val("");

	participantText.css({ "display": "block" });
	textbox.css({ "display": "none" });

	if(SAVE)
	{
		localStorage.participantNum = participantNum;
		localStorage.clickCount = 0;
	}
}

function clearData()
{
	localStorage.options = "";
	localStorage.unChosenOptions = "";

	localStorage.customOptions = "";
	localStorage.unChosenCustomOptions = "";

	localStorage.toolName = "";
	localStorage.optionName = "";
	localStorage.optionId = -1;

	localStorage.clickCount = 0;

	location.reload();
}

function setMode(newMode, thing)
{
	if(thing !== false)
	{
		$("#nav").children().attr("class", "");
		thing.parentNode.className = "active";
	}
	
	if(newMode == "constraints")
	{
		mode = CONSTRAINTS;
	}
	else if(newMode == "options")
	{
		mode = OPTIONS;
	}
}

function setDS(ds, thing)
{
	if(thing !== -1)
	{
		$("#sub_nav").children().attr("class", "btn");
		thing.className = "btn active";
	}
	
	currentDS = ds;
	
	// Hide all of the canvases
	$(".ds_canvas").css({ "display": "none" });
	
	var json = ds1;
	if(ds == FUNCTIONAL)
	{
		$("#functional_canvas").css({ "display": "block" });
		json = ds1;
		currentST = funST;
		currentUL = funUL;
	}
	else if(ds == NONFUNCTIONAL)
	{
		$("#nonfunctional_canvas").css({ "display": "block" });
		json = ds2;
		currentST = nonST;
		currentUL = nonUL;
	}
	else if(ds == STRUCTURAL)
	{
		$("#structural_canvas").css({ "display": "block" });
		json = ds3;
		currentST = strST;
		currentUL = strUL;
	}
	else
	{
		$("#service_canvas").css({ "display": "block" });
		json = ds4;
		currentST = serST;
		currentUL = serUL;
	}
	
	if(representationMode === REP_TREE)	
	{
		currentST.loadJSON(json);
		currentST.compute();
		currentST.onClick(currentST.root);
	}
	else
	{
		// Don't think we really need to do anything other than in the redraw
		// TODO What is the list equivalent of reloading the JSON???
	}

	
	redraw();
}

function lookupOptions()
{
	$.ajax({ 
		url: optionURL 
	}).done(function(data)
	{
		if(data == "-1")
		{
			// Do nothing
		}	
		else
		{
			eval(data);
			allOptions = o;
			
			// $( "#search" ).autocomplete({
   //    			source: allOptions	
		 //    });
		    
		 //    $('#search').removeAttr('disabled');
		}
	});
}

function customKeyDown(event)
{
	if(event.keyCode == 13)
		addCustom();
}

function addCustom()
{
	var customText = $("#custom_option");
	var custom = customText.val();

	if(custom == "")
	{
		return;
	}

	showRationale(custom, true);
	customText.val("");
}

// function searchPress(event)
// {
// 	if(event.keyCode == 13)
// 		find();
// }

function redraw()
{
	if(representationMode === REP_TREE)
	{
		redrawGraph();
	}
	else
	{
		redrawList();
	}
}

function redrawElement(elem)
{	
	// Need to look in the LIs
	var lis = $(elem).children("li");

	for(var i = 0; i < lis.length; i++)
	{
		// Need to do something
		var li = $(lis[i]);
		var id = li.attr("id");
		var contents = li.children()[0];
		var type = findAcrossDS(id).data.type;

		// Find out what it's called, and then put it in the
		if(idInArray(id, options))
		{
			// Then it's a selected option, make it red!
			li.attr({ "class": "unselectable li_ds li_option label label-important" });
			li.css({ "background-color": "#b94a48" });
		}
		// else if(idInArray(id, search))
		// {
			// Not sure what to do here?
		// }
		else if(id in colours)
		{
			li.attr({ "class": "unselectable li_ds li_option" });

			if(colours[id] == "#7D0541")
				li.css({ "background-color": "#C38EC7" });
			else
				li.css({ "background-color": colours[id]});
		}
		else if (type == "option")
		{
			li.attr({ "class": "unselectable li_ds li_option" });
			li.css({ "background-color": "white" });
		}
		else
		{
			// // There should only be one of these
			li.css({ "background-color": "transparent" });
		}

		// And then look inside of the ULs
		redrawElement(li);
	}


	// Just for recursion....
	var uls = $(elem).children("ul");
	for(var i = 0; i < uls.length; i++)
	{
		// we just need to keep looking?
		redrawElement(uls[i]);
	}	
}

function redrawList()
{
	redrawElement(funUL);
	redrawElement(nonUL);
	redrawElement(strUL);
	redrawElement(serUL);
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
		else if(idInArray(n.id, options))
		{
			n.setCanvasStyle("fillStyle", "#FFF");
			n.setCanvasStyle("strokeStyle", "#F00");
			n.setCanvasStyle("lineWidth", "2");
			n.setLabelData("style", "bold");
		}
		else
		{	
			if(n.id in colours)
			{
				if(colours[n.id] == "#7D0541")
				{
					n.setCanvasStyle("strokeStyle", "#7D0541");
					n.setCanvasStyle("fillStyle", "#C38EC7");
					n.setCanvasStyle("lineWidth", "3");
				}
				else
				{
					n.setCanvasStyle("fillStyle", colours[n.id]);
					n.setCanvasStyle("lineWidth", 1);
				}
			}
			else
			{
				// Use a default colour?
				n.setCanvasStyle("fillStyle", "#DDD");
				n.setCanvasStyle("lineWidth", "1");
				n.setCanvasStyle("strokeStyle", "#444");
			}
		}
	});
	
	currentST.compute();
	currentST.refresh();
}

function remake()
{
	var option = getOptionFromArray(currentOption.id, unChosenOptions);

	if(option !== null)
	{
		options.push(option);
		var index = getOptionIndexFromArray(option.id, unChosenOptions);
		unChosenOptions.splice(index, 1);

		var optionSpan = $(optionSpanText + option.name + "</span>");
		optionSpan.attr({
			"id": "tag_" + option.id,
			"class": "label label-important choice_label unselectable",
			"active": "true", 
			"tagId": option.id,
			"tagDs": option.ds,
			"tagRationale": option.rationale
		});

		$("#" + option.dsCode + "_options").append(optionSpan);

		var unChosenTags = $("#unChosen").children();
		for(var i = 0; i < unChosenTags.length; i++)
		{
			var tag = $(unChosenTags[i]);
			if(tag.attr("tagId") == option.id)
			{
				tag.remove();
			}
		}
	}
	else
	{
		option = getOptionFromArray(currentOption.id, unChosenCustomOptions);

		if(option !== null)
		{
			tag.remove();
		}
	}

	$("#tag_modal").modal("hide");

	if(SAVE)
	{
		localStorage.options = JSON.stringify(options);
		localStorage.unChosenOptions = JSON.stringify(unChosenOptions);
		localStorage.customOptions = JSON.stringify(customOptions);
		localStorage.unChosenCustomOptions = JSON.stringify(unChosenCustomOptions);
	}

	redraw();
}

function tagUnmake(e)
{
	var tag = $(e.target);
	var tagId = tag.attr("tagId");


	var option = getOptionFromArray(tagId, unChosenOptions);

	if(option !== null)
	{
		currentOption = option;
		remake();
	}
	else
	{
		option = getOptionFromArray(tagId, unChosenCustomOptions);
		if(option !== null)
		{
			currentOption = option;
			remake();
		}
	}

	if(SAVE)
	{
		localStorage.options = JSON.stringify(options);
		localStorage.unChosenOptions = JSON.stringify(unChosenOptions);
		localStorage.customOptions = JSON.stringify(customOptions);
		localStorage.unChosenCustomOptions = JSON.stringify(unChosenCustomOptions);
	}
}

function customUnmake(e)
{
	var tag = $(e.target);

	// Remove it from the custom array
	var bob = getOptionFromCustom(tag.html())
	var index = getIndexFromCustom(tag.html());
	customOptions.splice(bob, 1);

	unChosenCustomOptions.push(bob);

	if(SAVE)
	{
		localStorage.unChosenCustomOptions = JSON.stringify(unChosenCustomOptions);
		localStorage.customOptions = JSON.stringify(customOptions);
	}

	tag.remove();
}

function tagDoubleClick(e)
{
	// var tag = $(e.target);
	// var tagId = tag.attr("tagId");
	// var option = getOptionFromArray(tagId, options);

	// // This shouldn't really be a problem.....
	// if(option !== null)
	// {
	// 	unmakeDecision(option);
	// 	tag.remove();
	// }

	// redraw();
}

// This one unmakes the decision under the hood, we then need to reflect this in the UI
function unmakeDecision(option)
{
	if(option.dsCode == "custom")
	{
		var index = getIndexFromCustom(option.name);
		options.splice(index, 1);

		if(SAVE)
		{
			localStorage.customOptions = customOptions;
		}
	}
	else
	{
		unChosenOptions.push(option);
		var index = getOptionIndexFromArray(option.id, options);
		options.splice(index, 1);

		var optionSpan = $(unchosenSpanText + option.name + "</span>");
		optionSpan.attr({
			"id": "tag_" + option.id,
			"class": "label label-warning choice_label unselectable",
			"active": "true", 
			"tagId": option.id,
			"tagDs": option.ds,
			"tagRationale": option.rationale
		});

		$("#unChosen").append(optionSpan);

		if(SAVE)
		{
			localStorage.options = JSON.stringify(options);
			localStorage.unChosenOptions = JSON.stringify(unChosenOptions);
		}
	}
}

function tagInfo(e, list)
{
	var tag = $(e.target);
	
	// var thing = findAcrossDS(tag.attr("tagId"));
	var option = getOptionFromArray(tag.attr("tagId"), list);

	if(tag.attr("tagId") == -1)
		option = getOptionFromCustom(tag.html());

	$("#tag_modal_title").html(option.name);

	if(option.id == -1)
		$("#tag_modal_type").html("Custom option");
	else
		$("#tag_modal_type").html("Option");

	$("#tag_modal_ds").html(option.ds);

	$("#tag_description_text").html(option.description);

	if(option.addedFromId == -1)
	{
		$("#tag_prior_heading").css({ "display": "none" });
		$("#tag_prior").css({ "display": "nonremae" });

		$("#tag_rationale_text").css({ "display": "block" });
		$("#tag_rationale_text").html(option.rationale);
		$("#tag_rationale_text").val(option.rationale);
		$("#tag_rationale_heading").css({ "display": "block" });
	}
	else
	{
		$("#tag_prior_heading").css({ "display": "block" });
		$("#tag_prior").html(option.addedFromName);

		// If it was added automatically, then we don't need it
		$("#tag_rationale_text").css({ "display": "none" });
		$("#tag_rationale_heading").css({ "display": "none" });
	}

	if(option.toolView != null)
	{
		$("#tag_view_heading").css({ "display": "block" });
		$("#tag_view").html(option.toolView);
	}
	else if(option.optionView != -1)
	{
		$("#tag_view_heading").css({ "display": "block" });

		var proportion = "" + option.optionViewProportion;
		proportion = proportion.substring(proportion.indexOf(".") + 2);

		$("#tag_view").html(option.optionView + " with " + ((proportion * 1) * 100) + "% similarity");
	}
	else
	{
		$("#tag_view_heading").css({ "display": "none" });
		$("#tag_view").css({ "display": "none" });
	}

	// Check if if's in either current options or not current options
	if(tag.parent().attr("id") == "unChosen")
	{
		$("#btn_unmake").css({ "display": "none" });
		$("#btn_remake").css({ "display": "inline" });
	}
	else
	{
		$("#btn_unmake").css({ "display": "inline" });
		$("#btn_remake").css({ "display": "none" });
	}

	currentOption = option;
	$("#tag_modal").modal();

	return false;
}

function nodeClick(node)
{
	if(node.data.type == "option")
	{
		if(mode == CONSTRAINTS)
		{
	 		addToConstraints(node);
	 	}
	 	else if(mode == OPTIONS)
	 	{
	 		var option = getOptionFromArray(node.id, options);

	 		if(option == null)
	 		{
	 			showRationale(node);
	 			setDS(currentDS, -1);	
	 		}
	 		else
	 		{
	 			// Do something else?
	 		}

	 		
	 	}
	 			
	 	
	}
	else
	{
		if(node.collapsed)
		{
			currentST.op.expand(node, {
				type: "animate",
				duration: 100,
				hideLabels: true,
				transition: $jit.Trans.Quart.easeOut
			});
		}
		else
		{
			currentST.op.contract(node, {
				type: "animate",
				duration: 100,
				hideLabels: true,
				transition: $jit.Trans.Quart.easeOut
			});
		}
	}
}

function nodeRightClick(node)
{
	nodeClickOptions(node);
}

function nodeClickOptions(node)
{
	$("#overlay").modal();	
	setOverlay(node);
}

function setOverlay(node)
{
	if(HEURISTICS)
	{
		lookupTools(node);
		getLinkedOptions(node);
	}
	else
	{
		$("#overlay_related_header").css({ "display": "none" });
		$("#overlay_tools_header").css({ "display": "none" });
	}
	
	$("#overlay_title").html(node.name);
	$("#overlay_ds").html(node.data.ds);

	if(node.data.description == "")
	{
		$("#overlay_description").html("<i style='color:#888;'>No description yet.</i>");	
	}
	else
	{	
		$("#overlay_description").html(node.data.description);
	}
		
	$("#overlay_type").html(node.data.type);
	
	var choose = $("#btn_choose");
	
	var preview = $("#btn_preview");
	
	if(node.data.type == "option")
	{
		choose.click(function()
		{
			$("#overlay").modal("hide");
			showRationale(node);
		});
		
		choose.css({ "display": "inline" });
		
		if(HEURISTICS)
		{
			preview.click(function(){
				lookup(node);
				$("#overlay").modal("hide");
			});
			
			preview.css({ "display": "inline" });
		}
		else
		{
			preview.css({ "display": "none" });
		}
	}
	else
	{
		choose.css({ "display": "none" });
		preview.css({ "display": "none" });
	}
}

function getLinkedOptions(option)
{
	var linkedURL = linkedOptionURL + option.id.substring(6);
	
	$.ajax({
		url: linkedURL
	}).done(function(data)
	{
		if(data == "-1")
		{
			// Do nothing
		}
		else
		{
			eval(data);
			
			var container = $("#overlay_related");
			container.children().remove();

			if(links.length == 0)
			{
				var optionSpan = $("<i>No linked options</i>");
				container.append(optionSpan);
				return;
			}
			
			for(var i = 0; i < links.length; i++)
			{
				var optionSpan = $("<span id='option" + links[i][0] + "' class='label choice_label label-important'>" + links[i][1] + "</span>");
				container.append(optionSpan);
			}
		}
	});
}

function addLinkedOptions(option)
{
	if(option.id == -1)
	{
		// It's custom, so just don't bother
		return;
	}

	var linkedURL = linkedOptionURL + option.id.substring(6);
	
	$.ajax({
		url: linkedURL
	}).done(function(data)
	{
		eval(data);
		
		for(var i = 0; i < links.length; i++)
		{
			// If it's in the list of constraints or options then don't add it
			var id = "option" + links[i][0];

			if(!idInArray(id, options))
			{
				var node = findAcrossDS(id);
				var newOption = new DesignOption(node.id, node.name, node.data.ds, node.data.dsCode);
				newOption.addedFromId = option.id.substring(6);
				newOption.addedFromName = option.name;
				newOption.decision = findAcrossDS(node.data.parent).name;
				newOption.description = node.data.description;

				addToOptions(newOption, false);
			}
			
			redraw();
		}
	});
}

function addToOptions(option, addLinked)
{
	if(option.id == -1)
	{
		// This is when it's a custom option
		customOptions.push(option);

		if(SAVE)
		{
			localStorage.customOptions = JSON.stringify(customOptions);
		}

		var optionSpan = $(customSpanText + option.name + "</span>");
		
		optionSpan.attr({
			"id": "tag_" + option.id,
			"class": "label label-success choice_label unselectable",
			"active": "true", 
			"tagId": option.id,
			"tagDs": option.ds,
			"tagRationale": option.rationale
		});

		$("#custom_options").append(optionSpan);
	}
	else if(!idInArray(option.id, options))
	{
		// If the tool thing is added, we want to record that one thing was added because of that, and the others weren't
		if(lookupTool != "" && addLinked)
		{
			option.toolView = lookupTool;
		}

		if(lookupOption != "" && addLinked)
		{
			option.optionView = lookupOption;
			option.optionViewProportion = percentages[option.id];
			// TODO Need to get that value out of the jaccard index 
		}

		options.push(option);
		
		if(SAVE)
		{
			localStorage.options = JSON.stringify(options);
		}
		
		if(addLinked && HEURISTICS)
			addLinkedOptions(option);
		
		var optionSpan = $(optionSpanText + option.name + "</span>");

		optionSpan.attr({
			"id": "tag_" + option.id,
			"class": "label label-important choice_label unselectable",
			"active": "true", 
			"tagId": option.id,
			"tagDs": option.ds,
			"tagRationale": option.rationale
		});

		
		$("#" + option.dsCode + "_options").append(optionSpan);
		
		$("#overlay").modal("hide");
	}
	else
	{
		// I don't think this should happen...
	}

	redraw();
}

function showRationale(node, custom)
{
	var option;

	var oldOption = getOptionFromArray(node.id, unChosenOptions)

	$("#rationale_status_bar").css({ "display": "none" });
	$("#custom_description_text").css({ "border": "1px solid #ccc" });
	$("#rationale_text").css({ "border": "1px solid #ccc" });
	
	if(oldOption !== null)
	{
		option = oldOption;
		unChosenOptions.splice(getOptionIndexFromArray(option.id, unChosenOptions), 1);

		// Now I need to remove the tag from the list
		var containerKids = $("#unChosen").children();
		for(var i = 0; i < containerKids.length; i++)
		{
			if(option.id == $(containerKids[i]).attr("tagId"))
			{
				$(containerKids[i]).remove();
				break;
			}
		}
	} 
	else if(custom)
	{
		option = new DesignOption(-1, node, "Custom Option", CUSTOM);
	}
	else
	{
		option = new DesignOption(node.id, node.name, node.data.ds, node.data.dsCode);
		option.decision = findAcrossDS(node.data.parent).name;
		option.description = node.data.description;
	}

	var rationaleType = $("#rationale_type");

	if(custom)
	{
		// Make the description box visible
		$("#custom_description_container").css({ "display": "block" });
		$("#rationale_description_container").css({ "display": "none" });
		$("#rationale_description_name").html(option.name);
		rationaleType.html("Custom option");
		rationaleType.attr({ "class": "label label-success overlay_label" });
	}
	else
	{
		// Make the description box invisible
		$("#custom_description_container").css({ "display": "none" });
		$("#rationale_description_container").css({ "display": "block" });
		$("#rationale_description").html(option.description);
		rationaleType.html("Option");
		rationaleType.attr({ "class": "label label-info overlay_label" });
	}

	$("#rationale_title").html("Choose: <i style='font-weight:normal;'>" + option.name + "</i>");


	currentOption = option;
	$("#rationale_name").html(option.name);


	if(option.rationale === "")
		$("#rationale_text").val("");
	else
		$("#rationale_text").val(option.rationale);
	
	$("#rationale_modal").modal();

	if(custom)
	{
		$("#custom_description_text").val("");
		window.setTimeout(function(){
			$("#custom_description_text").filter(":visible").focus();	
		}, 800);
	}
	else
	{
		window.setTimeout(function(){
			$("#rationale_text").filter(":visible").focus();	
		}, 800);
	}
}

function setRationale()
{
	var rationaleBar = $("#rationale_status_bar");

	// TODO Set the description if it's a custom one
	if($("#custom_description_container").css("display") == "block")
	{
		var descriptionText = $("#custom_description_text");

		// Then we need to get the value of the description
		var description = descriptionText.val();

		if(description == "")
		{
			rationaleBar.css({ "display": "block" });
			rationaleBar.html("<b>Fail</b> You need to add a description for custom options!");
			descriptionText.css({ "border": "2px solid #b94a48" });
			return;
		}

		currentOption.description = description;
	}

	var rationaleText = $("#rationale_text");
	var rationale = rationaleText.val();
	

	if(rationale == "")
	{
		rationaleBar.css({ "display": "block" });
		rationaleBar.html("<b>Fail</b> You haven't entered a rationale!");
		rationaleText.css({ "border": "2px solid #b94a48" });
		return;
	}

	currentOption.rationale = rationale;
	$("#rationale_modal").modal("hide");
	
	

	addToOptions(currentOption, true);
	currentOption = null;
}

function lookup(node)
{

	var id = node.id.substring(node.data.type.length);
	var jc = jaccardURL + id;
	colours = new Array();
	percentages = new Array();
	
	$.ajax({
		url: jc
	}).done(function(data)
	{
		if(data == "-1")
		{
			// Do nothing		
		}
		else
		{
			eval(data);
			
			for(var i = 0; i < ret.length; i++)
			{
				var elem = ret[i];
				
				if(elem == undefined)
					continue;
				
				colours["option" + elem[0]] = elem[1];
				percentages["option" + elem[0]] = elem[2];
			}
			
			setStatus(PREVIEW_OPTION, node.name, node.id.substring(6));
			$("#overlay").modal("hide");

			redraw();
		}
	});
}

function lookupForTool(event)
{
	colours = new Array();
	var jc = getForToolURL + event.data.toolId

	$.ajax({
		url: jc
	}).done(function(data)
	{
		if(data == "-1")
		{
			// Do nothing		
		}
		else
		{
			eval(data);
			
			for(var i = 0; i < ret.length; i++)
			{
				var elem = ret[i];
				
				if(elem == undefined)
					continue;
				
				colours["option" + elem[0]] = elem[1];
			}

			setStatus(PREVIEW_TOOL, event.data.toolName);
			$("#overlay").modal("hide");

			redraw();
		}
	});
}

function setStatus(type, name, id)
{
	if(type == PREVIEW_TOOL)
	{
		lookupTool = name;
		$("#status_text").html("Showing design decisions for <b>" + name + "</b>");

		if(SAVE)
		{
			localStorage.toolName = name;

			var bob = new Array();
			for(key in colours)
			{
				bob.push("[\"" + key + "\",\"" + colours[key] + "\"]");
			}

			localStorage.colours = JSON.stringify(bob);
		}
	}
	else if(type == PREVIEW_OPTION)
	{
		lookupOption = name;
		lookupOptionId = id;

		$("#status_text").html("Showing related design options to <b>" + name + "</b>");

		if(SAVE)
		{
			localStorage.optionName = name;
			localStorage.optionId = id;

			var bob = new Array();
			for(key in colours)
			{
				bob.push("[\"" + key + "\",\"" + colours[key] + "\"]");
			}

			localStorage.colours = JSON.stringify(bob);
		}
	}
		
	$("#status_bar").css({ "display": "block" });
}

function clearStatus()
{
	if(SAVE)
	{
		localStorage.toolName = "";
		localStorage.optionName = "";
	}

	$("#status_bar").css({ "display": "none" });
	
	// clearing the colours array
	colours = new Array();
	lookupTool = "";
	lookupOption = "";
	lookupOptionId = -1;
	redraw();
}

function lookupTools(node)
{
	var id = node.id.substring(node.data.type.length);
	var tool = toolURL + id;
	
	$.ajax({
		url: tool
	}) .done(function(data)
	{
		if(data == "-1")
		{
			// Do nothing
		}
		else
		{
			eval(data);
			
			var container = $("#overlay_tools");
			container.children().remove();
			
			for(var i = 0; i < t.length; i++)
			{
				var toolSpan = $("<span id='tool" + t[i][0] + "' name='" + t[i][1] + "' class='label choice_label label_purple'>" + t[i][1] + "</span>");

				toolSpan.click({ toolId: t[i][0], toolName: t[i][1] }, lookupForTool);
				container.append(toolSpan);
			}
			
			for(var i = 0; i < tNot.length; i++)
			{
				var toolSpan = $("<span id='tool" + tNot[i][0] + "' name='" + tNot[i][1] + "' class='label choice_label'>" + tNot[i][1] + "</span>");
				toolSpan.click({ toolId: tNot[i][0], toolName: tNot[i][1]}, lookupForTool);
				
				container.append(toolSpan);
			}
		}
	});
}