import QS from 'qs';
import API from '@/config/api';

export default {
	namespaced: true,

	state: {
		phones: []
	},

	getters: {},

	mutations: {},

	actions: {
		DELETE_PHONE: function({ commit, state }, payload) {
			return API.delete('/phones/' + payload.id)
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

		GET_PHONE: function({ commit, state }, payload) {
			return API.get('/phones/' + payload.id)
				.then(response => {
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		UPDATE_PHONE: function({ commit, state }, payload) {
			const data = QS.stringify(payload);

			return API.patch('/phones/' + payload.id, data, {
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
