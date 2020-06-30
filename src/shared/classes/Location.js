export class Location {
	constructor(obj) {
		this.name = obj.name;
		this.type = obj.type;
		this.state = obj.state;
		this.company = obj.company;
		this.parentId = obj.parentId || null;
		this.id = obj.id || null;
		this.country = obj.country || 'de';

		if (obj.description) {
			this.description = obj.description || null;
		}

		if (obj.street) {
			this.street = obj.street || null;
		}

		if (obj.streetNumber) {
			this.streetNumber = obj.streetNumber || null;
		}

		if (obj.city) {
			this.city = obj.city || null;
		}

		if (obj.postalCode) {
			this.postalCode = obj.postalCode || null;
		}

		if (obj.province) {
			this.province = obj.province || null;
		}
	}
}
