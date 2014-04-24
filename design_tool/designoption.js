function DesignOption(id, name, ds, dsCode, rationale, addedFrom)
{
	this.id = id;
	this.name = name;
	this.rationale = "";
	this.description = "";
	this.ds = ds;
	this.dsCode = dsCode;

	this.addedFromId = -1;
	this.addedFromName = null;

	this.toolView = null;
	this.optionView = -1;
	this.optionViewProportion = 0;

	this.decision = null;
}
