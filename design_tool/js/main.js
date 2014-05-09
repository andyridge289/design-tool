

var labelType, useGradients, nativeTextSupport, animate;
var baseURL = "php/";
var jaccardURL = baseURL + "jaccard.php?id=";
var optionIdURL = baseURL + "get_option_id.php?o=";
var linkedOptionURL = baseURL + "get_linked_options.php?o=";
var getForToolURL = baseURL + "get_for_tool.php?id="

var importURL = "http://localhost/htdocs/thesis_stuff/study2/json_participant.php?p=";

var optionSpanText = "<span oncontextmenu='tagInfo(event, options); return false;' ondblclick='tagDoubleClick(event);'>";
var unchosenSpanText = "<span oncontextmenu='tagInfo(event, unChosenOptions); return false;' ondblclick='tagUnmake(event);'>";
var customSpanText = "<span oncontextmenu='tagInfo(event, customOptions); return false;' ondblclick='customUnmake(event);'>";

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

var linkDecisionCombo = null;

var startTime = -1;
var timeTaken = -1;

var showingHeat = false;

// var sliderValues = ["Some decisions", "Some decisions, and their solutions", "All decisions", "All decisions, some options", "All decisions & options"];

function init()
{	
	// lookupOptions();

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

		$('#decision_slider').slider({
			min: 0,
			max: 10, 
			step: 1,
			orientation: "horizontal",
			value: 10,
			tooltip: "hide"
		});

		$("#option_slider").slider({
			min: 0,
			max: 10, 
			step: 1,
			orientation: "horizontal",
			value: 10,
			tooltip: "hide"
		})

		$("#decision_slider").on('slide', function(slideEvt) {
			var dVal = slideEvt.value;
			var oVal = $("#option_slider").data("slider").getValue();
			$("#decision_value").html((dVal * 10) + "%");
			trimDS(dVal, oVal);
		});

		$("#option_slider").on("slide", function(slideEvt){
			var dVal = $("#decision_slider").data("slider").getValue();
			var oVal = slideEvt.value;
			$("#option_value").html((oVal * 10) + "%");
			trimDS(dVal, oVal);
		});
	}
	
	if(SAVE)
	{	
		if(localStorage.options)
		{
			var dsList = JSON.parse(localStorage.options);

			if(Object.prototype.toString.call(dsList) !== "[object Array]")
			{
				console.log("Local storage options not an array, what have you done...");
			}
			else
			{
				options = dsList[DS_ID];

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
		}

		if(localStorage.unChosenOptions)
		{
			var dsList = JSON.parse(localStorage.unChosenOptions);

			if(Object.prototype.toString.call(dsList) !== "[object Array]")
			{
				console.log("Local storage options not an array, what have you done...");
			}
			else
			{
				unChosenOptions = dsList[DS_ID];

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
		}


		if(localStorage.customOptions)
		{
			var dsList = JSON.parse(localStorage.customOptions);

			if(Object.prototype.toString.call(dsList) !== "[object Array]")
			{
				console.log("Local storage options not an array, what have you done...");
			}
			else
			{
				customOptions = dsList[DS_ID];

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
	}

	
}

function heatmap(btn)
{
	showingHeat = showingHeat ? false : true;

	if(showingHeat)
		btn.className = "btn active";
	else
		btn.className = "btn";

	setDS(currentDS, -1);
}

function trimDS(dValue, oValue)
{
	dValue /= 10;
	oValue /= 10;

	console.log(dValue + " :: " + oValue);
	// For each of the DSs, look at the original, and apply the relevant filter

	// Put the DS back to its original state, and then recursively remove elements
	ds1 = jQuery.extend(true, {}, orig1);
	ds2 = jQuery.extend(true, {}, orig2);
	ds3 = jQuery.extend(true, {}, orig3);
	ds4 = jQuery.extend(true, {}, orig4);

	trimElement(ds1, dValue, oValue, false);
	trimElement(ds2, dValue, oValue, false);
	trimElement(ds3, dValue, oValue, false);
	trimElement(ds4, dValue, oValue, false);

	setDS(currentDS, -1);
}

function trimElement(elem, dValue, oValue, parent)
{
	if(elem.data.type == "category")
	{
		for(var i = 0; i < elem.children.length; i++)
		{
			trimElement(elem.children[i], dValue, oValue, false);
		}
	}
	else if(elem.data.type == "decision")
	{
		for(var i = 0; i < elem.children.length; i++)
		{
			var child = elem.children[i];

			if(child.data.type == "decision")
			{
				var chosen = false;
				var prop = child.data.pCount / TOOL_COUNT;

				if(prop <= 1 - dValue)
				{
					console.log("Removing " + child.name);
					elem.children.splice(i, 1);
					i--;
				}
				else
				{
					console.log("Keeping " + child.name + ": " + prop + " > " + (1 - dValue));
					chosen = true;
				}

				trimElement(child, dValue, oValue, chosen);
			}
			else if(child.data.type == "option")
			{
				var prop = child.data.pCount / TOOL_COUNT;

				if(prop <= 1 - oValue)
				{
					elem.children.splice(i, 1);
					i--;
				}
			}
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
		saveAll();

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
}

function clearData()
{
	options = [];
	unChosenOptions = [];
	customOptions = [];
	saveAll();
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
		saveAll();

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
		saveAll();
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
		saveAll();

	tag.remove();
}

function saveAll()
{
	if(options == null || options == "") options = [];
	if(unChosenOptions == null || options == "") unChosenOptions = [];
	if(customOptions == null  || options == "") customOptions = [];


	if(localStorage.options) {
		var list = JSON.parse(localStorage.options);
		list[DS_ID] = options;
		localStorage.options = JSON.stringify(list);
	} else {
		var list = [];
		list[DS_ID] = options;
		localStorage.options = JSON.stringify(list);
	}

	if(localStorage.unChosenOptions) {
		var list = JSON.parse(localStorage.unChosenOptions);
		list[DS_ID] = unChosenOptions;
		localStorage.unChosenOptions = JSON.stringify(list);
	} else {
		var list = [];
		list[DS_ID] = unChosenOptions;
		localStorage.unChosenOptions = JSON.stringify(list);
	}

	if(localStorage.customOptions) {
		var list = JSON.parse(localStorage.customOptions);
		list[DS_ID] = customOptions;
		localStorage.customOptions = JSON.stringify(list);
	} else {
		var list = [];
		list[DS_ID] = customOptions;
		localStorage.customOptions = JSON.stringify(list);
	}
}

function tagDoubleClick(e)
{
	// Do nothing
}

// This one unmakes the decision under the hood, we then need to reflect this in the UI
function unmakeDecision(option)
{
	if(option.dsCode == "custom")
	{
		var index = getIndexFromCustom(option.name);
		options.splice(index, 1);

		if(SAVE) 
			saveAll();
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
			saveAll();
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
		$("#tag_prior").css({ "display": "none" });

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

	if(option.decisionLink == null)
	{
		$("#tag_link").css({ "display": "none" });
	}
	else
	{
		$("#tag_link").css({ "display": "block" });
		$("#link_label").html(option.decisionLink[1]);
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
	lookupTools(node);
	if(HEURISTICS)
	{
		
		getLinkedOptions(node);
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

	}
	else
	{
		choose.css({ "display": "none" });
		preview.css({ "display": "none" });
	}
}

function addToOptions(option, addLinked)
{
	if(option.id == -1)
	{
		// This is when it's a custom option
		customOptions.push(option);

		if(SAVE)
			saveAll();

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

		if(options == null || options == "")
			options = [];

		options.push(option);
		
		if(SAVE)
			saveAll();
		
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

		$.ajax({
			url: "../lib/get_decisions.php",
			type: "get",
			data: {
				ds: DS_ID
			}
		}).done(function(msg){

			eval("var decisions = " + msg);

			var data = [];
			for(var i = 0; i < decisions.length; i++)
			{
				data.push([decisions[i].id, decisions[i].name]);
			}

			$("#link_text").css({ "display": "block" });
			$("#link_placeholder").css({ "display": "block" });
			linkDecisionCombo = new TextCombo(data);

			$("#link_placeholder").empty();
			$("#link_placeholder").append(linkDecisionCombo.container);
			

		}).fail(function ( jqXHR, textStatus, errorThrown ){ alert("Fail " + textStatus + ", " + errorThrown)});;
	}
	else
	{
		// Make the description box invisible
		$("#custom_description_container").css({ "display": "none" });
		$("#rationale_description_container").css({ "display": "block" });
		$("#rationale_description").html(option.description);
		rationaleType.html("Option");
		rationaleType.attr({ "class": "label label-info overlay_label" });

		$("#link_text").css({ "display": "none" });
		$("#link_placeholder").css({ "display": "none" });
		linkDecisionCombo = null;
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

	// TODO This is where we need to lookup what they've linked it to

	if(linkDecisionCombo != null)
	{
		var chosen = linkDecisionCombo.getSelected() == null ? null : linkDecisionCombo.getSelected();
		currentOption.decisionLink = chosen;
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

function lookupTools(node)
{
	console.log(node.id);

	var tool = 
	
	$.ajax({
		url: "../lib/get_tools_for_element.php",
		type: "get",
		data:
		{
			ds: DS_ID,
			id: node.id
		}
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
				var toolSpan = $("<span class='label choice_label label_purple'>" + t[i] + "</span>");

				// toolSpan.click({ toolId: t[i][0], toolName: t[i][1] }, lookupForTool);
				container.append(toolSpan);
			}
		}
	});
}