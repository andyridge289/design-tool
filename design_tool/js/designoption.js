function DesignOption(id, name, ds, dsCode, rationale, addedFrom)
{
	this.id = id;
	this.name = name;
	this.rationale = "";
	this.description = "";
	this.ds = ds;
	this.dsCode = dsCode;

	this.decisionLink = null;
	this.decision = null;
}
