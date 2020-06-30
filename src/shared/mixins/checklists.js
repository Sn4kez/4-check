export default {
	methods: {
		promptCreateChecklist() {
			if (this.isDeviceGreaterSM) {
				this.$prompt(this.$t('CREATE_CHECKLIST'), {
					confirmButtonText: this.$t('OK'),
					cancelButtonText: this.$t('CANCEL'),
					inputErrorMessage: this.$t('PLEASE_FILL_OUT_MANDATORY_FIELDS'),
					inputValidator(value) {
						return value !== null;
					}
				})
					.then(data => {
						// this.doCreateFolder(data.value);
					})
					.catch(() => {});
			} else {
				this.$q
					.dialog({
						title: this.$t('CREATE_CHECKLIST'),
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
						// this.doCreateFolder(data);
					})
					.catch(() => {});
			}
		},

		requestSectionEntries(sectionId) {
			return this.$store
				.dispatch('sections/GET_ENTRIES', { id: sectionId })
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		}
	}
};
