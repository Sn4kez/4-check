import QS from 'qs';
import API from '@/config/api';

export default {
	namespaced: true,

	state: {
		addresses: []
	},

	getters: {},

	mutations: {
		SET_ADDRESSES: function(state, payload) {
			state.addresses = payload;
		}
	},

	actions: {
		CREATE_ADDRESS: function({ commit, state }, payload) {
			const data = QS.stringify(payload);

			return API.post('/addresses', data, {
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				}
			})
				.then(response => {
					if (response.status === 201) {
						state.groups.push(response.data.data);
					}

					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		DELETE_ADDRESS: function({ commit, state }, payload) {
			return API.delete('/addresses/' + payload.id)
				.then(response => {
					// Remove group from groups
					// _.filter(state.groups, group => {
					// 	return payload.id !== group.id;
					// });

					return response;
				})
				.catch(err => {
					return err;
				});
		},

		GET_ADDRESS: function({ commit, state }, payload) {
			return API.get('/addresses/' + payload.id)
				.then(response => {
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		GET_ADDRESSES: function({ commit, state }, payload) {
			return API.get('/addresses')
				.then(response => {
					commit('SET_ADDRESSES', response.data.data);
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		UPDATE_ADDRESS: function({ commit, state }, payload) {
			const data = QS.stringify(payload);

			return API.patch('/addresses/' + payload.id, data, {
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
