import QS from 'qs';
import API from '@/config/api';

export default {
	namespaced: true,

	state: {
		scoringschemes: []
	},

	getters: {},

	mutations: {},

	actions: {
		CREATE_SCORING_SCHEME_SCORE: function({ commit, state }, payload) {
			const data = QS.stringify(payload);

			return API.post('/scoringschemes/' + payload.schemeId + '/scores', data, {
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

		DELETE_SCORING_SCHEME: function({ commit, state }, payload) {
			return API.delete('/scoringschemes/' + payload.id)
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

		GET_SCORING_SCHEME: function({ commit, state }, payload) {
			return API.get('/scoringschemes/' + payload.id)
				.then(response => {
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		GET_SCORING_SCHEME_SCORES: function({ commit, state }, payload) {
			return API.get('/scoringschemes/' + payload.id + '/scores')
				.then(response => {
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		UPDATE_SCORING_SCHEME: function({ commit, state }, payload) {
			const data = QS.stringify(payload);

			return API.patch('/scoringschemes/' + payload.id, data, {
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
