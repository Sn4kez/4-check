export class ScoreCondition {
	constructor(obj) {
		this.id = obj.id || null;
		this.to = obj.to || 0;
		this.from = obj.from || 0;
		this.scoreId = obj.scoreId || '';
	}
}
