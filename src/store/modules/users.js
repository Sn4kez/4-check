import QS from 'qs';
import axios from 'axios';
import config from '@/config/index';
import API from '@/config/api';
import { transformUsersForSelect } from '@/shared/transformers';

export default {
	namespaced: true,

	state: {
		groups: [],
		invitations: [],
		phones: [],
		users: []
	},

	getters: {
		getUserById: state => id => {
			return state.users.find(user => user.id === id);
		},

		usersOptions: function(state, getters) {
			return transformUsersForSelect(state.users);
		}
	},

	mutations: {
		SET_GROUPS: function(state, payload) {
			state.groups = payload;
		},

		SET_PHONES: function(state, payload) {
			state.phones = payload;
		},

		SET_USERS: function(state, payload) {
			state.users = payload;
		}
	},

	actions: {
		CREATE_PHONE: function({ commit, state }, payload) {
			const data = QS.stringify(payload.data);

			return API.post('/users/' + payload.id + '/phones', data, {
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				}
			})
				.then(response => {
					state.phones.push(response.data.data);
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		CREATE_USER: function({ commit, state }, payload) {
			const data = QS.stringify(payload);

			return API.post('/users', data, {
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				}
			})
				.then(response => {
					// if (response.status === 201) {
					// 	state.users.push(response.data.data);
					// }

					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		CREATE_INVITATION: function({ commit, state }, payload) {
			const data = QS.stringify(payload);

			return API.post('/users/invitations', data, {
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

		DELETE_USER: function({ commit, state }, payload) {
			return API.delete('/users/' + payload.id)
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		DELETE_INVITATION: function({ commit, state }, payload) {
			return API.delete('/users/invitations/' + payload.token)
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		GET_GROUPS: function({ commit, state }, payload) {
			return API.get('/users/' + payload.id + '/groups')
				.then(response => {
					commit('SET_GROUPS', response.data.data);
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		GET_PHONES: function({ commit, state }, payload) {
			return API.get('/users/' + payload.id + '/phones')
				.then(response => {
					commit('SET_PHONES', response.data.data);
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		GET_USER: function({ commit, state }, payload) {
			return API.get('/users/' + payload.id)
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		GET_USER_ACCESS_GRANTS: function({ commit, state }, payload) {
			return API.get('/users/' + payload.id + '/grants')
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		GET_USER_NOTIFICATION_PREFERENCES: function({ commit, state }, payload) {
			return API.get('/users/preferences/notifications/' + payload.id)
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		GET_USERS: function({ commit, state }, payload) {
			return API.get('/users')
				.then(response => {
					state.users = response.data.data;
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		UPDATE_INVITATION: function({ commit, state }, payload) {
			const data = QS.stringify(payload);

			return API.patch('/users/invitations/' + payload.token, data, {
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				}
			})
				.then(response => {
					console.log(response);
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		UPDATE_USER: function({ commit, state }, payload) {
			const data = QS.stringify(payload);

			return API.patch('/users/' + payload.id, data, {
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				}
			})
				.then(response => {
					console.log(response);
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		UPDATE_USER_ACCESS_GRANTS: function({ commit, state }, payload) {
			const data = QS.stringify(payload);

			return API.patch('/users/' + payload.id + '/grants', data, {
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				}
			})
				.then(response => {
					console.log(response);
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		UPDATE_USER_NOTIFICATION_PREFERENCES: function({ commit, state }, payload) {
			const data = QS.stringify(payload);

			return API.patch('/users/preferences/notifications/' + payload.userId, data, {
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				}
			})
				.then(response => {
					console.log(response);
					return response;
				})
				.catch(err => {
					return err;
				});
		}
	}
};
