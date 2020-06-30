export class Checklist {
	constructor(obj) {
		this.id = obj.id || null;
		this.name = obj.name;
		this.description = obj.description || '';
		this.parentId = obj.parentId;
		this.numberQuestions = obj.numberQuestions || 0;
	}
}
