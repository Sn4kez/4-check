export class Invitation {
	constructor(obj) {
		this.id = obj.id || null;
		this.email = obj.email;
		this.company = obj.company;
	}
}
