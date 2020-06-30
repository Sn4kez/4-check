import QS from 'qs';
import API from '@/config/api';

export default {
	namespaced: true,

	state: {
		notifications: []
	},

	getters: {},

	mutations: {},

	actions: {
		GET_NOTIFICATIONS: function({ commit, state }, payload) {
			return API.get('/notifications')
				.then(response => {
					state.notifications = response.data.data;
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		MARK_AS_READ: function({ commit, state }, payload) {
			return API.patch('/notifications/read/' + payload.id, {
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
