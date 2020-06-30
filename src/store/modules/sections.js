import QS from 'qs';
import API from '@/config/api';

export default {
	namespaced: true,

	state: {
		sections: []
	},

	getters: {},

	mutations: {},

	actions: {
		CREATE_CHECKPOINT: function({ commit, state }, payload) {
			const data = QS.stringify(payload);

			return API.post('/sections/' + payload.id + '/checkpoints', data, {
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

			return API.post('/sections/' + payload.id + '/extensions', data, {
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

		DELETE_SECTION: function({ commit, state }, payload) {
			return API.delete('/sections/' + payload.id)
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

		GET_ENTRIES: function({ commit, state }, payload) {
			return API.get('/sections/' + payload.id + '/entries')
				.then(response => {
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		GET_SECTION: function({ commit, state }, payload) {
			return API.get('/sections/' + payload.id)
				.then(response => {
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		UPDATE_SECTION: function({ commit, state }, payload) {
			const data = QS.stringify(payload);

			return API.patch('/sections/' + payload.id, data, {
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
