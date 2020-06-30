export default {
	methods: {
		requestCheckpointEntries(checkpointId) {
			return this.$store
				.dispatch('checkpoints/GET_ENTRIES', { id: checkpointId })
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		updateCheckpoint(checkpoint) {
			return this.$store
				.dispatch('checkpoints/UPDATE_CHECKPOINT', checkpoint)
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		}
	}
};
