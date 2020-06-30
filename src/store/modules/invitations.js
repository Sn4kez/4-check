import QS from 'qs';
import API from '@/config/api';

export default {
	namespaced: true,

	state: {
		invitations: []
	},

	getters: {},

	mutations: {},

	actions: {
		CREATE_USER: function({ commit, state }, payload) {
			const data = QS.stringify(payload.data);

			return API.post('/invitation/' + payload.token, data, {
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

		GET_INVITATION: function({ commit, state }, payload) {
			console.log(payload);
			return API.get('/invitation/' + payload.token)
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
