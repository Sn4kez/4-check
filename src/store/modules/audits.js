import QS from 'qs';
import API from '@/config/api';
import { transformForSelect } from '@/shared/transformers';

export default {
	namespaced: true,

	state: {
		audit: [],
		audits: [],
		auditStates: [],
		checks: [],
		filter: {},
		inspectionPlans: []
	},

	getters: {
		auditStates: function(state, getters) {
			return transformForSelect(state.auditStates);
		},

		getAuditsByChecklistId: state => id => {
			return state.audits.filter(audit => audit.checklist === id);
		},

		getAuditsByDirectoryId: state => id => {
			return state.audits.filter(audit => audit.directory === id);
		},

		getAuditStateById: state => id => {
			return state.auditStates.find(state => state.id === id);
		},

		getAuditStateByName: state => name => {
			return state.auditStates.find(state => state.name === name);
		}
	},

	mutations: {
		SET_AUDIT: function(state, payload) {
			// Payload muss be the response
			if (payload.data.data.id) {
				let found = false;
				state.audits.forEach(audit => {
					if (audit.id === payload.data.data.id) {
						audit = Object.assign({}, payload.data.data);
						found = true;
					}
				});

				if (!found) {
					state.audits.push(payload.data.data);
				}
			}
		},

		SET_CHECK: function(state, payload) {
			// Payload muss be the response
			if (payload.data.data.id) {
				let found = false;
				state.checks.forEach(audit => {
					if (check.id === payload.data.data.id) {
						check = Object.assign({}, payload.data.data);
						found = true;
					}
				});

				if (!found) {
					state.checks.push(payload.data.data);
				}
			}
		},

		SET_FILTER: function(state, payload) {
			// state.filter.auditId = payload.auditId || null;
			state.filter.checklist = payload.checklist;
			state.filter.location = payload.location;
			state.filter.user = payload.user || null;
			state.filter.state = payload.state || null;
			state.filter.start = payload.start || null;
			state.filter.end = payload.end || null;
		},

		SET_PLAN: function(state, payload) {
			// Payload muss be the response
			if (payload.data.data.id) {
				let found = false;
				state.inspectionPlans.forEach(audit => {
					if (plan.id === payload.data.data.id) {
						plan = Object.assign({}, payload.data.data);
						found = true;
					}
				});

				if (!found) {
					state.inspectionPlans.push(payload.data.data);
				}
			}
		}
	},

	actions: {
		CREATE_AUDIT: function({ commit, state }, payload) {
			const data = QS.stringify(payload);

			return API.post('/audits', data, {
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				}
			})
				.then(response => {
					commit('SET_AUDIT', response);

					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		CREATE_AUDIT_PLAN: function({ commit, state }, payload) {
			const data = QS.stringify(payload);

			return API.post('/audits/plans', data, {
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				}
			})
				.then(response => {
					commit('SET_PLAN', response);
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		DELETE_AUDIT: function({ commit, state }, payload) {
			return API.delete('/audits/' + payload.id)
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		DELETE_AUDIT_PLAN: function({ commit, state }, payload) {
			return API.delete('/audits/plans/' + payload.id)
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		GET_AUDIT: function({ commit, state }, payload) {
			return API.get('/audits/' + payload.id)
				.then(response => {
					commit('SET_AUDIT', response);
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		GET_AUDIT_CHECK: function({ commit, state }, payload) {
			return API.get('/audits/checks/' + payload.id)
				.then(response => {
					commit('SET_CHECK', response);
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		GET_AUDIT_ENTRIES: function({ commit, state }, payload) {
			return API.get('/audits/entries/' + payload.id, {
				params: {
					sectionId: payload.sectionId
				}
			})
				.then(response => {
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		GET_AUDIT_PLANS: function({ commit, state }, payload) {
			console.log("PLEASE hAVE A LOOK AT ME")
			console.log(payload)
			return API.get('/audits/plans/company/' + payload.id + '?checklist=' + payload.checklist_id)
				.then(response => {
					state.inspectionPlans = response.data.data;
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		GET_AUDIT_STATES: function({ commit, state }, payload) {
			return API.get('/audits/states/company/' + payload.id)
				.then(response => {
					state.auditStates = response.data.data;
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		// Scope: company
		GET_AUDITS: function({ commit, state }, payload) {
			return API.get('/audits/company/' + payload.id, {
				params: state.filter
			})
				.then(response => {
					// Order audits descending by create date
					state.audits = _.sortBy(response.data.data, audit => {
						return audit.createdAt;
					});
					state.audits.reverse();
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		GET_DIRECTORY_AUDITS: function({ commit, state }, payload) {
			return API.get('/audits/directory/' + payload.id, {
				params: state.filter
			})
				.then(response => {
					state.audits = response.data.data;
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		GET_DIRECTORY_RESULTS: function({ commit, state }, payload) {
			return API.get('/audits/directory/' + payload.id + '/results')
				.then(response => {
					state.results = response.data.data;
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		START_AUDIT: function({ commit, state }, payload) {
			return API.get('/audits/start/' + payload.id)
				.then(response => {
					state.checks = response.data.data;
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		UPDATE_AUDIT: function({ commit, state }, payload) {
			const data = QS.stringify(payload);

			return API.patch('/audits/' + payload.id, data, {
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				}
			})
				.then(response => {
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		UPDATE_AUDIT_CHECK: function({ commit, state }, payload) {
			const data = QS.stringify(payload);

			return API.patch('/audits/checks/' + payload.id, data, {
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				}
			})
				.then(response => {
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		UPDATE_AUDIT_PLAN: function({ commit, state }, payload) {
			const data = QS.stringify(payload);

			return API.patch('/audits/plans/' + payload.id, data, {
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				}
			})
				.then(response => {
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		}
	}
};
