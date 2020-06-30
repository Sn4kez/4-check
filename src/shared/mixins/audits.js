export default {
	methods: {
		doUpdateCheck(check) {
			return this.$store
				.dispatch('audits/UPDATE_AUDIT_CHECK', check)
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		getAuditsByChecklistId(id) {
			return this.$store.getters['audits/getAuditsByChecklistId'](id);
		},

		getAuditStateById(id) {
			return this.$store.getters['audits/getAuditStateById'](id);
		},

		getAuditStateByName(name) {
			return this.$store.getters['audits/getAuditStateByName'](name);
		},

		requestAuditStates(companyId) {
			return this.$store
				.dispatch('audits/GET_AUDIT_STATES', { id: companyId })
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		requestSectionEntries(auditId, sectionId = null) {
			return this.$store
				.dispatch('audits/GET_AUDIT_ENTRIES', { id: auditId, sectionId: sectionId })
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		}
	}
};
