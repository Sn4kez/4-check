export class Company {
	constructor(obj) {
		this.id = obj.id || null;
		this.name = obj.name;
		this.sector = obj.sector;
		this.subscription = obj.subscription;

		this.isActive = obj.isActive;
		// if (obj.isActive) {
		// }
	}
}

export class ReportSettings {
	constructor(obj) {
		this.id = obj.id || '';
		this.showCompanyName = obj.showCompanyName || 0;
		this.showCompanyAddress = obj.showCompanyAddress || 0;
		this.showUsername = obj.showUsername || 0;
		this.showPageNumbers = obj.showPageNumbers || 0;
		this.showExportDate = obj.showExportDate || 0;
		this.showVersion = obj.showVersion || 0;
		this.text = obj.text || '';
		this.logoPosition = obj.logoPosition || 'left';
	}
}

export class CorporateIdentity {
	constructor(obj) {
		this.id = obj.id || '';
		this.company = obj.company;
		this.brand_primary = obj.brand_primary || '';
		this.brand_secondary = obj.brand_secondary || '';
		this.link_color = obj.link_color || '';

		this.image = obj.image || null;

		if (obj.source_b64) {
			this.source_b64 = obj.source_b64;
		}
	}
}
