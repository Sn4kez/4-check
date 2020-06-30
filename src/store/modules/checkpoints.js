import QS from 'qs';
import API from '@/config/api';

export default {
	namespaced: true,

	state: {
		checkpoints: []
	},

	getters: {},

	mutations: {},

	actions: {
		CREATE_EXTENSION: function({ commit, state }, payload) {
			const data = QS.stringify(payload);

			return API.post('/checkpoints/' + payload.id + '/extensions', data, {
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

		DELETE_CHECKPOINT: function({ commit, state }, payload) {
			return API.delete('/checkpoints/' + payload.id)
				.then(response => {
					// Remove group from groups
					// _.filter(state.phones, group => {
					// 	return payload.id !== group.id;
					// });

					return response;
				})
				.catch(err => {
					return err;
				});
		},

		GET_CHECKPOINT: function({ commit, state }, payload) {
			return API.get('/checkpoints/' + payload.id)
				.then(response => {
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		GET_ENTRIES: function({ commit, state }, payload) {
			return API.get('/checkpoints/' + payload.id + '/entries')
				.then(response => {
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		UPDATE_CHECKPOINT: function({ commit, state }, payload) {
			const data = QS.stringify(payload);

			return API.patch('/checkpoints/' + payload.id, data, {
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
