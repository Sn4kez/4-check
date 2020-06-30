import axios from 'axios';

export default {
	methods: {
		doCreateScore(score) {
			return this.$store
				.dispatch('scoringschemes/CREATE_SCORING_SCHEME_SCORE', score)
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		doCreateScoreCondition(scoreCondition) {
			return this.$store
				.dispatch('valueschemes/CREATE_VALUE_SCHEME_CONDITION', scoreCondition)
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		doCreateScoreConditions(scoreConditions, schemeId) {
			const REQUEST = [];

			scoreConditions.forEach(condition => {
				if (condition.id) {
					REQUEST.push(this.doUpdateScoreCondition(condition));
				} else {
					condition.schemeId = schemeId;
					REQUEST.push(this.doCreateScoreCondition(condition));
				}
			});

			return axios
				.all(REQUEST)
				.then(
					axios.spread((...results) => {
						return results;
					})
				)
				.catch(err => {
					return err;
				});
		},

		doUpdateScore(score) {
			return this.$store
				.dispatch('scores/UPDATE_SCORE', score)
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		doUpdateScoreCondition(scoreCondition) {
			return this.$store
				.dispatch('conditions/UPDATE_CONDITION', scoreCondition)
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		doSaveScores(scores, schemeId) {
			const REQUEST = [];

			scores.forEach(score => {
				if (score.id) {
					REQUEST.push(this.doUpdateScore(score));
				} else {
					score.schemeId = schemeId;
					REQUEST.push(this.doCreateScore(score));
				}
			});

			return axios
				.all(REQUEST)
				.then(
					axios.spread((...results) => {
						return results;
					})
				)
				.catch(err => {
					return err;
				});
		},

		requestScoringSchemeScores(id) {
			return this.$store
				.dispatch('scoringschemes/GET_SCORING_SCHEME_SCORES', { id: id })
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		}
	}
};
