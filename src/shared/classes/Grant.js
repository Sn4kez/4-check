export class Grant {
	constructor(obj) {
		this.id = obj.id || null;
		this.subjectId = obj.subjectId;

		this.view = obj.view || 0;
		this.index = obj.index || 0;
		this.update = obj.update || 0;
		this.delete = obj.delete || 0;
	}
}
