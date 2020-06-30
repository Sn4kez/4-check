export class Check {
	constructor(obj) {
		this.id = obj.id || null;
		this.audit = obj.audit;
		this.checklist = obj.checklist;
		this.checkpoint = obj.checkpoint;
		this.section = obj.section;
		this.value = obj.value;
		this.valueScheme = obj.valueScheme;
		this.scoringScheme = obj.scoringScheme;
		this.rating = obj.rating;
		this.scoreId = obj.scoreId;
		this.objectType = obj.objectType;
		this.objectId = obj.objectId;
		this.object = obj.object;
	}
}
