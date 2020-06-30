export class User {
	constructor(obj) {
		this.id = obj.id || null;
		this.email = obj.email;
		this.gender = obj.gender || 'male';
		this.password = obj.password;
		this.firstName = obj.firstName;
		this.lastName = obj.lastName;
		this.middleName = obj.middleName || '';
		this.locale = obj.locale || 'de-DE';
		this.timezone = obj.timezone || 'Europe/Berlin';
		this.company = obj.company;
		this.phone = obj.phone;

		this.image = obj.image || null;

		if (obj.source_b64) {
			this.source_b64 = obj.source_b64 || null;
		}

		if (obj.role) {
			this.role = obj.role;
		}

		if ((typeof obj.isActive === 'string' && obj.isActive.toLowerCase() === 'true')
            || obj.isActive === '1'
            || obj.isActive === true) {
		    this.isActive = 1;
        } else if ((typeof obj.isActive === 'string' && obj.isActive.toLowerCase() === 'false')
            || obj.isActive === '0'
            || obj.isActive === false) {
            this.isActive = 0;
        } else {
            this.isActive = obj.isActive ? 1 : 0;
        }
	}
}
