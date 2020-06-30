export class Audit {
	constructor(obj) {
		this.id = obj.id || null;
		this.executionDue = obj.executionDue;
		this.checklist = obj.checklist;
		this.user = obj.user;
		this.company = obj.company;
		this.state = obj.state;
	}
}
