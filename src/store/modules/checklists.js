import QS from 'qs';
import API from '@/config/api';

export default {
	namespaced: true,

	state: {
		checklists: []
	},

	getters: {},

	mutations: {},

	actions: {
		CREATE_CHECKLIST: function({ commit, state }, payload) {
			const data = QS.stringify(payload);
			console.log('CREATE_CHECKLIST', data, payload);

			return API.post('/checklists', data, {
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

		CREATE_CHECKPOINT: function({ commit, state }, payload) {
			const data = QS.stringify(payload);

			return API.post('/checklists/' + payload.id + '/checkpoints', data, {
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

		CREATE_EXTENSION: function({ commit, state }, payload) {
			const data = QS.stringify(payload);

			return API.post('/checklists/' + payload.id + '/extensions', data, {
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

		CREATE_GRANT: function({ commit, state }, payload) {
			const checklistId = payload.checklistId;
			const data = QS.stringify(payload.data);
			console.log('CREATE_CHECKLIST_GRANT', data, payload);

			return API.post('/checklists/' + checklistId + '/grants', data, {
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

		CREATE_SECTION: function({ commit, state }, payload) {
			const data = QS.stringify(payload);

			return API.post('/checklists/' + payload.id + '/sections', data, {
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

		CREATE_SCORING_SCHEME: function({ commit, state }, payload) {
			const data = QS.stringify(payload);

			return API.post('/checklists/' + payload.id + '/scoringschemes', data, {
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

		DELETE_CHECKLIST: function({ commit, state }, payload) {
			return API.delete('/checklists/' + payload.id)
				.then(response => {
					// Remove group from groups
					// _.filter(state.DIRECTORYs, group => {
					// 	return payload.id !== group.id;
					// });

					return response;
				})
				.catch(err => {
					return err;
				});
		},

		DELETE_CHECKLIST_APPROVER: function({ commit, state }, payload) {
			return API.delete('/checklists/' + payload.id + '/approvers/' + payload.userId)
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		DELETE_CHECKLIST_APPROVER_GROUP: function({ commit, state }, payload) {
			return API.delete('/checklists/' + payload.id + '/approvers/group/' + payload.groupId)
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		DELETE_CHECKLIST_APPROVERS: function({ commit, state }, payload) {
			return API.delete('/checklists/' + payload.id + '/approvers', {
				params: {
					approvers: payload
				}
			})
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		DELETE_CHECKLISTS: function({ commit, state }, payload) {
			return API.delete('/checklists', {
				params: {
					checklists: payload
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

		GET_CHECKLIST: function({ commit, state }, payload) {
			return API.get('/checklists/' + payload.id)
				.then(response => {
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		GET_CHECKLIST_ACCESS_GRANTS: function({ commit, state }, payload) {
			return API.get('/checklists/' + payload.id + '/grants')
				.then(response => {
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		GET_CHECKLIST_APPROVERS: function({ commit, state }, payload) {
			return API.get('/checklists/' + payload.id + '/approvers')
				.then(response => {
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		GET_CHECKLIST_CHECKPOINTS: function({ commit, state }, payload) {
			return API.get('/checklists/' + payload.id + '/checkpoints')
				.then(response => {
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		GET_CHECKLIST_ENTRIES: function({ commit, state }, payload) {
			return API.get('/checklists/' + payload.id + '/entries')
				.then(response => {
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		GET_CHECKLIST_EXTENSIONS: function({ commit, state }, payload) {
			return API.get('/checklists/' + payload.id + '/extensions')
				.then(response => {
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		GET_CHECKLIST_SCORING_SCHEMES: function({ commit, state }, payload) {
			return API.get('/checklists/' + payload.id + '/scoringschemes')
				.then(response => {
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		GET_CHECKLIST_SECTIONS: function({ commit, state }, payload) {
			return API.get('/checklists/' + payload.id + '/sections')
				.then(response => {
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		GET_CHECKLISTS: function({ commit, state }, payload) {
			return API.get('/checklists')
				.then(response => {
					state.directories.push(response.data.data);
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		UPDATE_CHECKLIST: function({ commit, state }, payload) {
			const data = QS.stringify(payload);

			return API.patch('/checklists/' + payload.id, data, {
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

		UPDATE_CHECKLIST_APPROVER: function({ commit, state }, payload) {
			const data = QS.stringify(payload);

			return API.patch('/checklists/' + payload.id + '/approvers/' + payload.userId, data, {
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

		UPDATE_CHECKLIST_APPROVERS_GROUP: function({ commit, state }, payload) {
			const data = QS.stringify(payload);

			return API.patch('/checklists/' + payload.id + '/approvers/group/' + payload.groupId, data, {
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

		UPDATE_CHECKLIST_APPROVERS: function({ commit, state }, payload) {
			const data = QS.stringify(payload);

			return API.patch('/checklists/' + payload.id + '/approvers', data, {
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
