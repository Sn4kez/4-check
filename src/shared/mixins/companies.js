export default {
	methods: {
		getCompanyById(id) {
			return this.$store.getters['companies/getCompanyById'](id);
		},

		requestCompanySubscription(id) {
			return this.$store
				.dispatch('companies/GET_COMPANY_SUBSCRIPTION', { id: id })
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		}
	}
};
