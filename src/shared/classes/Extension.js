export class Extension {
	constructor(obj) {
		this.id = obj.id || null;
		this.type = obj.type;
		this.data = obj.data || {};
		this.index = obj.index || 12;
	}
}

export class TextfieldExtension extends Extension {
	constructor(obj) {
		super(obj);
		this.type = 'textfield';
		this.data = obj.data || {
			text: '',
			fixed: 1
		};
	}
}

export class NotefieldExtension extends Extension {
	constructor(obj) {
		super(obj);
		this.type = 'textfield';
		this.data = obj.data || {
			text: '',
			fixed: 0
		};
	}
}

export class PictureExtension extends Extension {
	constructor(obj) {
		super(obj);
		this.type = 'picture';
		this.data = obj.data || {
			image: '',
			type: 'media'
		};
	}
}

export class SignatureExtension extends Extension {
	constructor(obj) {
		super(obj);
		this.type = 'picture';
		this.data = obj.data || {
			image: '',
			type: 'signature'
		};
	}
}

export class ParticipantExtension extends Extension {
	constructor(obj) {
		super(obj);
		this.type = 'participant';
		this.data = obj.data || {
			userId: '',
			external: '',
			fixed: 0
		};
	}
}

export class LocationExtension extends Extension {
	constructor(obj) {
		super(obj);
		this.type = 'location';
		this.data = obj.data || {
			locationId: '',
			fixed: 0
		};
	}
}
