export class Group {
	constructor(obj) {
		this.id = obj.id || null;
		this.name = obj.name;

		if (obj.description) {
			this.description = obj.description;
		}

		this.image = obj.image || null;

		if (obj.source_b64) {
			this.source_b64 = obj.source_b64;
		}
	}
}
