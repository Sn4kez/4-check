export class Checkpoint {
	constructor(obj) {
		this.id = obj.id || null;
		this.prompt = obj.prompt;
		this.scoringSchemeId = obj.scoringSchemeId;
		this.mandatory = obj.mandatory || 0;
		this.factor = obj.factor || '0';
		this.index = obj.index || '0';

		/**
		 * Update evaluationScheme
		 */
		if (obj.evaluationScheme) {
			this.evaluationScheme = obj.evaluationScheme;
		}

		/**
		 * Create new value choice
		 *
		 * The attribute 'evaluationSchemeType' come from frontend
		 */
		if (obj.evaluationSchemeType === 'value') {
			this.evaluationScheme.type = 'value';

			this.evaluationScheme.data = {
				unit: obj.evaluationScheme.data.unit,
				scoreConditions: obj.evaluationScheme.data.scoreConditions
			};
		}

		/**
		 * Create new choice
		 *
		 * The attribute 'evaluationSchemeType' come from frontend
		 */
		if (obj.evaluationSchemeType === 'choice') {
			this.evaluationScheme.type = 'choice';
			this.evaluationScheme.data = {
				multiselect: 0
			};
		}
	}
}
