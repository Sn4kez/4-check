import { Directory } from '@/shared/classes/Directory';

export default {
	methods: {
		doCreateDirectory(directory) {
			const newDirectory = new Directory(directory);

			return this.$store
				.dispatch('directories/CREATE_DIRECTORY', newDirectory)
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		getDirectoryIdFromRoute($route) {
			if (!$route.path.length) {
				return false;
			}

			const arrPathSegments = $route.path.split('/');
			const id = arrPathSegments[arrPathSegments.length - 1];

			console.log('getDirectoryIdFromRoute', arrPathSegments, id);

			return id;
		},

		getEntriesByDirectoryId(id) {
			return this.$store.getters['directories/getEntriesByDirectoryId'](id);
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
		},

		promptCreateDirectory(parentId) {
			if (this.isDeviceGreaterSM) {
				this.$prompt(this.$t('CREATE_DIRECTORY'), {
					confirmButtonText: this.$t('OK'),
					cancelButtonText: this.$t('CANCEL'),
					inputErrorMessage: this.$t('PLEASE_FILL_OUT_MANDATORY_FIELDS'),
					inputValidator(value) {
						return value !== null;
					}
				})
					.then(data => {
						console.log('neussel', data);

						this.doCreateDirectory({
							name: data.value,
							parentId: parentId
						});
					})
					.catch(() => {});
			} else {
				this.$q
					.dialog({
						title: this.$t('CREATE_DIRECTORY'),
						message: '',
						prompt: {
							model: '',
							type: 'text' // optional
						},
						ok: this.$t('OK'),
						cancel: this.$t('CANCEL')
					})
					.then(data => {
						if (!data) {
							return;
						}

						this.doCreateDirectory({
							name: data,
							parentId: parentId
						});
					})
					.catch(() => {});
			}
		},

		requestDirectoryEntries(id) {
			return this.$store
				.dispatch('directories/GET_DIRECTORY_ENTRIES', { id: id })
				.then(response => {
					this.$store.commit('directories/SET_DIRECTORY_ENTRIES', response.data.data);
					return response;
				})
				.catch(err => {
					this.loading = false;
					return err;
				});
		}
	}
};
