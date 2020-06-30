export class Task {
	constructor(obj) {
		this.id = obj.id || null;
		this.name = obj.name;
		this.doneAt = obj.doneAt || new Date();
		this.assignee = obj.assignee;
		this.giveNotice = obj.giveNotice || 0;

		if (obj.assignedAt) {
			this.assignedAt = obj.assignedAt || new Date();
		}

		if (obj.company) {
			this.company = obj.company;
		}

		if (obj.issuer) {
			this.issuer = obj.issuer;
		}

		if (obj.source_b64) {
			this.source_b64 = obj.source_b64 || null;
		}

		this.description = obj.description || null;
		this.location = obj.location || null;
		this.priority = obj.priority || null;
		this.type = obj.type || null;
		this.state = obj.state || null;
		this.image = obj.image || null;
	}
}
