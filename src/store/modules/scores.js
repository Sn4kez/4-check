import QS from 'qs';
import API from '@/config/api';

export default {
	namespaced: true,

	state: {
		scores: []
	},

	getters: {},

	mutations: {},

	actions: {
		CREATE_SCORE_NOTICE: function({ commit, state }, payload) {
			const data = QS.stringify(payload);

			return API.post('/scores/' + payload.id + '/notice', data, {
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

		DELETE_SCORE: function({ commit, state }, payload) {
			return API.delete('/scores/' + payload.id)
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

		DELETE_SCORE_NOTICE: function({ commit, state }, payload) {
			return API.delete('/scores/' + payload.id + '/notice')
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		GET_SCORE: function({ commit, state }, payload) {
			return API.get('/scores/' + payload.id)
				.then(response => {
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		GET_SCORE_NOTICE: function({ commit, state }, payload) {
			return API.get('/scores/' + payload.id + '/notice/' + payload.checklistId)
				.then(response => {
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		UPDATE_SCORE: function({ commit, state }, payload) {
			const data = QS.stringify(payload);

			return API.patch('/scores/' + payload.id, data, {
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
