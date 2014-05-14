function TextCombo(idNameArray)
{
	this.sources = idNameArray;

	this.selected = null;

	this.container = $("<div></div>");
	this.container.css({

	});

	this.input = $("<input type='text'>");
	this.input.css({

	});

	this.results = $("<ul></ul>");
	this.results.css({
		"height": "100px",
		"overflow": "scroll"
	});

	this.getSelected = function()
	{
		if(this.selected == null) 
		{
			if(this.results.children().length > 0)
			{
				// Pick the first one
				this.selected = this.getThing($(this.results.children()[0]).attr("thing_id"));
			}
			else
			{
				return null;
			}
		}

		return this.selected;
	}

	this.getThing = function(id)
	{
		for(var i = 0; i < this.sources.length; i++)
		{
			if(this.sources[i][0] == id)
			{
				return this.sources[i];
			}
		}
	}

	this.filter = function(event)
	{
		var input = event == null ? "" : $(event.target).val();
		var things = this.sources;

		var myResults = [];

		if(input == "")
		{
			myResults = things;
		}
		else
		{
			for(var i = 0; i < things.length; i++)
			{
				if(things[i][1].toLowerCase().indexOf(input.toLowerCase()) != -1 || input == "")
				{
					myResults.push(things[i]);
				}
			}
		}

		this.results.empty();
		var parent = this;
		for(var i = 0; i < myResults.length; i++)
		{
			var result = $("<li class='result' thing_id='" + myResults[i][0] + "'>" + myResults[i][1] + "</li>");
			
			liclick = function(event)
			{
				var li = $(this);
				if(li.attr("class") == "result")
				{
					parent.selected = parent.getThing(li.attr("thing_id"));

					for(var j = 0; j < parent.results.children().length; j++)
					{
						parent.results.children()[j].className = "result";
					}

					li.attr({ "class": "chosen"});

					parent.input.val(li.html());
				}
				else
				{
					// Dno, maybe don't do anything?
				}
			};

			result.bind("click", {	parent: parent }, liclick);

			result.css({
				"cursor": "pointer"
			});
			
			this.results.append(result);
		}

		
	}

	this.input.bind("keyup", { parent: this }, this.filter);

	this.container.append(this.input);
	this.container.append(this.results);
	this.filter(null);

	return this;
}