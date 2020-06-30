export class Score {
	constructor(obj) {
		this.id = obj.id || null;
		this.name = obj.name;
		this.value = obj.value || 0;
		this.color = obj.color || '#ffffff';
	}
}
