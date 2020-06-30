import QS from 'qs';
import API from '@/config/api';

export default {
	namespaced: true,

	state: {
		breadcrumbs: {},
		directories: [],
		rootDirectory: {}
	},

	getters: {},

	mutations: {
		ADD_BREADCRUMB: function(state, payload) {
			state.breadcrumbs = payload;
		},

		RESET_BREADCRUMBS: function(state, payload) {
			state.breadcrumbs = {};
		},

		SET_DIRECTORIES: function(state, payload) {
			state.directories = payload;
		},

		SET_ROOT_DIRECTORY: function(state, payload) {
			state.rootDirectory = payload;
		}
	},

	actions: {
		CREATE_ARCHIVE: function({ commit, state }, payload) {
			const data = QS.stringify(payload);

			return API.post('/archives', data, {
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

		CREATE_ARCHIVE_GRANT: function({ commit, state }, payload) {
			const archiveId = payload.archiveId;
			const data = QS.stringify(payload.data);

			return API.post('/archives/' + archiveId + '/grants', data, {
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

		DELETE_ARCHIVE: function({ commit, state }, payload) {
			return API.delete('/archives/' + payload.id)
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		GET_ARCHIVE: function({ commit, state }, payload) {
			return API.get('/archives/' + payload.id)
				.then(response => {
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		GET_ARCHIVE_ENTRIES: function({ commit, state }, payload) {
			return API.get('/archives/' + payload.id + '/entries')
				.then(response => {
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		GET_ARCHIVE_GRANTS: function({ commit, state }, payload) {
			return API.get('/archives/' + payload.id + '/grants')
				.then(response => {
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		UPDATE_ARCHIVE: function({ commit, state }, payload) {
			const data = QS.stringify(payload);

			return API.patch('/archives/' + payload.id, data, {
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
