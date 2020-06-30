import QS from 'qs';
import API from '@/config/api';

export default {
	namespaced: true,

	state: {
		grants: []
	},

	getters: {},

	mutations: {},

	actions: {
		CREATE_GRANT: function({ commit, state }, payload) {
			const data = QS.stringify(payload);
			console.log('CREATE_GRANT', data, payload);

			return API.post('/grants', data, {
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				}
			})
				.then(response => {
					// if (response.status === 201) {
					// 	state.grants.push(response.data.data);
					// }

					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		DELETE_GRANT: function({ commit, state }, payload) {
			return API.delete('/grants/' + payload.id)
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		DELETE_GRANTS: function({ commit, state }, payload) {
			// ToDo: Allow Array with IDs and the grants models
			// const grantsIds = [];
			// payload.forEach(item => {
			//     if(item.id) {
			//         grantsIds.push(item.id);
			//     }
			// });

			return API.patch('/grants/delete', { items: payload })
				.then(response => {
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		GET_GRANT: function({ commit, state }, payload) {
			return API.get('/grants/' + payload.id)
				.then(response => {
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		UPDATE_GRANT: function({ commit, state }, payload) {
			const data = QS.stringify(payload);

			return API.patch('/grants/' + payload.id, data, {
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
