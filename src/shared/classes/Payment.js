export class Payment {
	constructor(obj) {
		this.token = obj.token || null;
		// this.package = obj.package || 'PREMIUM_YEARLY'; //BASIC_MONTHLY, BASIC_YEARLY, DELUXE_BASIC, DELUXE_YEARLY, PREMIUM_MONTHLY, PREMIUM_YEARLY
		this.package = obj.package || 'DELUXE_MONTHLY';
		this.method = obj.method || null; //sepa, invoice
		this.qty = obj.qty || 1;
		this.reference = obj.reference || '';
	}
}
