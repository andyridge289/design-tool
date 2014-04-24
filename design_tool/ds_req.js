function go()
{
	var container = $("#container");
	getElement(dsreq1, container);
	getElement(dsreq2, container);
	getElement(dsreq3, container);
	getElement(dsreq4, container);
}

function getElement(element, parent)
{
	var monkey;

	monkey = $("<div>" + element.id + ":    " + element.name + "  (" +  parent.attr("elementId") + ")</div>");
	
	monkey.attr("id", (element.data.type + "_" + element.id));
	monkey.attr("elementId", element.id);
	
	if(element.data.type != "requirement")
		monkey.css("font-weight", "bold");
		
	monkey.attr("elementName", element.name);
	monkey.addClass(element.data.type);
	
	if(element.data.type == "option" && parent.attr("class") == "decision" && parent.children().length == 0)
	{
		$("<br />").appendTo(parent);
	}
	
	if(element.data.type == "code")
	{
		if(element.requirements == null)
		{
			console.log("Not adding code " + element.name);
		}
		else if(element.requirements.length == 0)
		{
			console.log("Not adding code " + element.name);
		}
		else
		{
			monkey.appendTo(parent);
		}
	}
	else
	{
		monkey.appendTo(parent);
	}
	
	if(element.data.type != "code" && element.data.type != "requirement")
	{
		for(var i = 0; i < element.codes.length; i++)
		{
			getElement(element.codes[i], monkey);
		}
	
		for(var i = 0; i < element.children.length; i++)
		{
			getElement(element.children[i], monkey);
		}
	}
	
	if(element.data.type == "code")
	{
		for(var i = 0; i < element.requirements.length; i++)
		{
			getElement(element.requirements[i], monkey);
		}
	}
	
	
}