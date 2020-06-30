export class Address {
	constructor(obj) {
		this.id = obj.id || null;
		this.type = obj.type || 'billing';
		this.line1 = obj.line1 || '';
		this.line2 = obj.line2 || '';
		this.city = obj.city || '';
		this.postalCode = obj.postalCode || '';
		this.province = obj.province || '';
		this.country = obj.country || 'de';
	}
}
