delete Array.prototype.toJSON

// TODO turn this back on!!!!
//Disable right click context menu - don't give it the event!
$(document).ready(function(){ 
  document.oncontextmenu = function() {return false;};
});

$(document).click(function(e) 
{ 
    if (e.button == 0) {
        if(SAVE)
        {
        	if(localStorage.clickCount > 0)
        		localStorage.clickCount++;
        	else
        		localStorage.clickCount = 1;
        }
    }
});

HSLtoRGB = function(hsl) {
	// in JS 1.7 use: var [h, s, l] = hsl;
	var h = hsl[0], s = hsl[1], l = hsl[2], r, g, b, hue2rgb = function(p, q, t) {
		if (t < 0) {
			t += 1;
		}
		if (t > 1) {
			t -= 1;
		}
		if (t < 1 / 6) {
			return p + (q - p) * 6 * t;
		}
		if (t < 1 / 2) {
			return q;
		}
		if (t < 2 / 3) {
			return p + (q - p) * (2 / 3 - t) * 6;
		}
		return p;
	};

	if (s === 0) {
		r = g = b = l;
		// achromatic
	} else {
		var q = l < 0.5 ? l * (1 + s) : l + s - l * s, p = 2 * l - q;
		r = hue2rgb(p, q, h + 1 / 3);
		g = hue2rgb(p, q, h);
		b = hue2rgb(p, q, h - 1 / 3);
	}

	return [r * 0xFF, g * 0xFF, b * 0xFF];
};

function mysql_escape(string)
{
	string = string.replace("'", "\'");

	return string;
}

function canStore() 
{
	try 
	{
		return 'localStorage' in window && window['localStorage'] !== null;
	}
	catch (e) 
	{
		return false;
	}
}

var Log = {
	elem: false,
	write: function(text) {
		if(!this.elem)
			this.elem = document.getElementById("log");
		this.elem.innerHTML = text;
	}
};

var timer;

function trimDS(ds)
{
	if(ds.data.type == "category")
	{
		for(var i = 0; i < ds.children.length; i++)
		{
			trimDS(ds.children[i]);
		}
	}
	else if(ds.data.type == "decision")
	{
		for(var i = 0; i < ds.children.length; i++)
		{
			var child = ds.children[i];

			if(child.data.type == "decision")
			{
				trimDS(child);
			}
			else if(child.data.type == "option")
			{
				// alert(JSON.stringify(child));
				ds.children.splice(i, 1);
				i--;
			}
		}
	}
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
		height: $(window).height() - 140,
        
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
			// onRightClick: function(node, eventInfo, e)
			// {
				// nodeRightClick(node);
			// },
		},
		
		onCreateLabel: function(label, node)
		{
			label.id = node.id;
			label.innerHTML = node.name;			
			
			/*label.ondblclick = function()
			{
				$(this).data("double", 2);
				nodeClick(node);
			};*/

			label.oncontextmenu = function(e)
			{
				nodeRightClick(node);
				return false;
			}
			
			label.onclick = function(e)
			{
				nodeClick(node);
				return false;
				
				// setTimeout(function()
				// {
					// var dblClick = parseInt($(that).data("double"), 10);
					// if(dblClick > 0)
					// {
						// $(that).data("double", dblClick - 1);
					// }
					// else
					// {
						// nodeRightClick(node);
					// }
				// }, 300);
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

function findInDS(dsList, searchTerm)
{
	for(var i = 0; i < dsList.length; i++)
	{
		if(dsList[i].name == searchTerm)
		{
			return dsList[i];
		}	
	}
	
	return null;
}

function findAcrossDS(searchTerm)
{
	var result = find2(ds1, searchTerm);
	if(result != false)
		return result;

	result = find2(ds2, searchTerm);
	if(result != false)
		return result;

	result = find2(ds3, searchTerm);
	if(result != false)
		return result;

	return find2(ds4, searchTerm);

}

function find2(dsElement, searchTerm)
{
	if(dsElement.id === searchTerm)
	{
		return dsElement;
	}

	for(var i = 0; i < dsElement.children.length; i++)
	{
		var result = find2(dsElement.children[i], searchTerm);

		if(result !== false)
		{
			return result;
		}
	}

	return false;
}

function findById(nodeID)
{
	var node = funST.graph.getNode(nodeID);
	if(node == undefined)
		node = nonST.graph.getNode(nodeID);
		
	if(node == undefined)
		node = strST.graph.getNode(nodeID);
		
	if(node == undefined)
		node = serST.graph.getNode(nodeID);
		
	return node;
}

function findDSById(nodeID)
{
	var node = funST.graph.getNode(nodeID);
	if(node == undefined)
		node = nonST.graph.getNode(nodeID);
	else return funST;
		
	if(node == undefined)
		node = strST.graph.getNode(nodeID);
	else return nonST;
		
	if(node == undefined)
		node = serST.graph.getNode(nodeID);
	else return strST;
	
	if(node != undefined)
		return serST;
}

function find()
{
	search = new Array();
	
	var searchTerm = $("#search").val();
	
	var dsFound = -1;
	var found = findInDS(opt1, searchTerm);
	
	if(found === null)
		found = findInDS(opt2, searchTerm);
	else if(dsFound == -1)
		dsFound = FUNCTIONAL;
	
	if(found === null)
		found = findInDS(opt3, searchTerm);
	else if(dsFound == -1)
		dsFound = NONFUNCTIONAL;
		
	if(found === null)
		found = findInDS(opt4, searchTerm);
	else if(dsFound == -1)
		dsFound = STRUCTURAL;
		
	if(found === null)
		return;
	else if(dsFound == -1)
		dsFound = SERVICE;
	
	search.push(found.id);	
	setDS(dsFound, -1);
	
	
	// TODO Move the canvas to the position of that node
	// st.graph.getNode(found.id);
}

function idInArray(id, array)
{
	for(var i = 0; i < array.length; i++)
	{
		if(array[i].id === id)
			return true;
	}
	
	return false;
}

function optionInArray(option, array)
{
	var id = option.id;
	
	for(var i = 0; i < array.length; i++)
	{
		if(array[i].id === id)
			return true;
	}
	
	return false;
}

function getOptionFromArray(id, array)
{
	for(var i = 0; i < array.length; i++)
	{
		if(array[i].id === id)
			return array[i];
	}
	
	return null;
}

function getOptionIndexFromArray(id, array)
{
	for(var i = 0; i < array.length; i++)
	{
		if(array[i].id === id)
			return i;
	}

	return -1;
}

function getOptionFromCustom(custom)
{
	for(var i = 0; i < customOptions.length; i++)
	{
		if(custom === customOptions[i].name)
			return customOptions[i];
	}

	return null;
}

function getIndexFromCustom(custom)
{
	for(var i = 0; i < customOptions.length; i++)
	{
		if(custom === customOptions[i].name)
			return i;
	}

	return -1;
}

function myEscape(dsList)
{
	for(var i = 0; i < dsList.length; i++)
	{
		var item = dsList[i];
		item.description = mysql_escape(item.description);
		item.rationale = mysql_escape(item.rationale);
	}

	return dsList;
}

function exportDS()
{
	// Collect the data
	// Participant number, click count
	stuff = {};
	stuff.p = participantNum;
	stuff.c = SAVE ? localStorage.clickCount : -1;
	stuff.time = timeTaken
	stuff.options = myEscape(options);
	stuff.unChosenOptions = myEscape(unChosenOptions);
	stuff.customOptions = myEscape(customOptions);
	stuff.unChosenCustomOptions = myEscape(unChosenCustomOptions);
	// stuff.sql = exportSQL();

	// MAke the string
	var outputString = JSON.stringify(stuff);

	// Need to POST it to a php page that will save it in the database
	$.ajax({
		url: "save_ds.php",
		type: "post",
		data: { stuff: outputString }
	}).done(function( msg ){
		alert(msg);
	});

	// Need to then save it somewhere useful



}



function exportJSON()
{
	var outputString = "<h4>JSON Output</h4>";

	outputString += "<br /><p>Options</p>" + JSON.stringify(options);
	outputString += "<br /><p>Un-chosen Options</p>" + JSON.stringify(unChosenOptions);
	outputString += "<br /><p>Custom options</p>" + JSON.stringify(customOptions);
	outputString += "<br /><p>Custom Un-chosen options</p>" + JSON.stringify(unChosenCustomOptions);

	return outputString;
}

function exportSQL()
{
	var outputString = "<h4>SQL Output</h4>"; 

	for(var i = 0; i < options.length; i++)
	{
		outputString += makeOptionQuery(options[i], false);
	}

	for(var i = 0; i < unChosenOptions.length; i++)
	{
		outputString += makeOptionQuery(unChosenOptions[i], true);
	}

	for(var i = 0; i < customOptions.length; i++)
	{
		outputString += makeCustomQuery(customOptions[i], false, "");
	}

	for(var i = 0; i < unChosenCustomOptions.length; i++)
	{
		outputString += makeCustomQuery(unChosenCustomOptions[i], true, "");
	}

	return outputString;
}

var escapeHTML = (function () {
    'use strict';
    var chr = { '"': '&quot;', '&': '&amp;', '<': '&lt;', '>': '&gt;' };
    return function (text) {
        return text.replace(/[\"&<>]/g, function (a) { return chr[a]; });
    };
}());