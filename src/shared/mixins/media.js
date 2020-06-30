export default {
	methods: {
		requestMedia(name, output_b64 = true) {
			return this.$http
				.get('/media', {
					params: {
						name: name,
						output_b64: output_b64
					}
				})
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		}
	}
};
