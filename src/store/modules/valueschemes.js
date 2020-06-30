import QS from 'qs';
import API from '@/config/api';

export default {
	namespaced: true,

	state: {
		valueschemes: []
	},

	getters: {},

	mutations: {},

	actions: {
		CREATE_VALUE_SCHEME_CONDITION: function({ commit, state }, payload) {
			const data = QS.stringify(payload);

			return API.post('/valueschemes/' + payload.schemeId + '/conditions', data, {
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

		DELETE_VALUE_SCHEME: function({ commit, state }, payload) {
			return API.delete('/valueschemes/' + payload.id)
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

		GET_VALUE_SCHEME: function({ commit, state }, payload) {
			return API.get('/valueschemes/' + payload.id)
				.then(response => {
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		GET_VALUE_SCHEME_CONDITIONS: function({ commit, state }, payload) {
			return API.get('/valueschemes/' + payload.id + '/conditions')
				.then(response => {
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		UPDATE_VALUE_SCHEME: function({ commit, state }, payload) {
			const data = QS.stringify(payload);

			return API.patch('/valueschemes/' + payload.id, data, {
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
