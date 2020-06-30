export default {
	methods: {
		getIdFromRoute($route) {
			if (!$route.path.length) {
				return false;
			}

			const arrPathSegments = $route.path.split('/');
			const id = arrPathSegments[arrPathSegments.length - 1];

			console.log('getIdFromRoute', arrPathSegments, id);

			return id;
		},

		handleErrors(response) {
			if (response.response.data.message.length) {
				this.$q.notify({
					message: response.response.data.message,
					type: 'negative'
				});
			}

			if (response.response.data.errors) {
				_.forEach(response.response.data.errors, (value, key) => {
					console.log('invalid fields:', value, key);
					this.$q.notify({
						message: value[0],
						type: 'negative'
					});
				});
			}
		}
	}
};
