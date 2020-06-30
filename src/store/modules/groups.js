import QS from 'qs';
import API from '@/config/api';
import { filter } from 'lodash';
import { transformForSelect } from '@/shared/transformers';

export default {
	namespaced: true,

	state: {
		groups: []
	},

	getters: {
		getGroupById: state => id => {
			return state.groups.find(group => group.id === id);
		},

		groupOptions: function(state, getters) {
			return transformForSelect(state.groups);
		}
	},

	mutations: {
		SET_GROUPS: function(state, payload) {
			state.groups = payload;
		}
	},

	actions: {
		CREATE_GROUP: function({ commit, state }, payload) {
			const data = QS.stringify(payload);

			return API.post('/groups', data, {
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

		CREATE_GROUP_MEMBER: function({ commit, state }, payload) {
			const data = QS.stringify(payload.data);

			return API.patch('/groups/' + payload.id + '/members', data, {
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

		DELETE_GROUP: function({ commit, state }, payload) {
			return API.delete('/groups/' + payload.id)
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

		DELETE_GROUP_MEMBER: function({ commit, state }, payload) {
			const data = QS.stringify(payload.data);

			return API.delete('/groups/' + payload.id + '/members', { data: { id: payload.data.id } })
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

		GET_GROUP: function({ commit, state }, payload) {
			return API.get('/groups/' + payload.id)
				.then(response => {
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		GET_GROUP_ACCESS_GRANTS: function({ commit, state }, payload) {
			return API.get('/groups/' + payload.id + '/grants')
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		GET_GROUP_MEMBERS: function({ commit, state }, payload) {
			return API.get('/groups/' + payload.id + '/members')
				.then(response => {
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		GET_GROUPS: function({ commit, state }, payload) {
			return API.get('/groups')
				.then(response => {
					commit('SET_GROUPS', response.data.data);
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		UPDATE_GROUP: function({ commit, state }, payload) {
			const data = QS.stringify(payload);

			return API.patch('/groups/' + payload.id, data, {
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
