import QS from 'qs';
import API from '@/config/api';

export default {
	namespaced: true,

	state: {
		extensions: []
	},

	getters: {},

	mutations: {},

	actions: {
		DELETE_EXTENSION: function({ commit, state }, payload) {
			return API.delete('/extensions/' + payload.id)
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

		GET_EXTENSION: function({ commit, state }, payload) {
			return API.get('/extensions/' + payload.id)
				.then(response => {
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		UPDATE_EXTENSION: function({ commit, state }, payload) {
			const data = QS.stringify(payload);

			return API.patch('/extensions/' + payload.id, data, {
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
