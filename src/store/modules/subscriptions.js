import QS from 'qs';
import API from '@/config/api';

export default {
	namespaced: true,

	state: {
		subscriptions: []
	},

	getters: {},

	mutations: {},

	actions: {
		GET_SUBSCRIPTION: function({ commit, state }, payload) {
			return API.get('/subscriptions/' + payload.id)
				.then(response => {
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		UPDATE_SUBSCRIPTION: function({ commit, state }, payload) {
			const data = QS.stringify(payload);

			return API.patch('/subscriptions/' + payload.id, data, {
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
