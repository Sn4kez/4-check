export default {
	methods: {
		getUserById(id) {
			return this.$store.getters['users/getUserById'](id);
		},

		requestUserAccessGrants(id) {
			return this.$store
				.dispatch('users/GET_USER_ACCESS_GRANTS', { id: id })
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		}
	}
};
