export default {
	methods: {
		requestScoreNotice(scoreId, checklistId) {
			return this.$store
				.dispatch('scores/GET_SCORE_NOTICE', { id: scoreId, checklistId: checklistId })
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		}
	}
};
