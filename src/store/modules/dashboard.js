import QS from 'qs';
import API from '@/config/api';

export default {
	namespaced: true,

	state: {
		data: {
			last_audits: [],
			next_audits: [],
			tasks: []
		},
		lastAuditFilter: {
			start: '',
			end: '',
			page: null
		},
		nextAuditFilter: {
			page: null
		},
		tasksFilter: {
			page: null
		}
	},

	getters: {},

	mutations: {
		SET_LAST_AUDIT_FILTER: function(state, payload) {
			state.lastAuditFilter.start = payload.start || '';
			state.lastAuditFilter.end = payload.end || '';
			state.lastAuditFilter.page = payload.page || null;
		},

		SET_NEXT_AUDITS_FILTER: function(state, payload) {
			state.nextAuditFilter.page = payload.page || 1;
		},

		SET_TASKS_FILTER: function(state, payload) {
			state.tasksFilter.page = payload.page || 1;
		}
	},

	actions: {
		GET_DATA: function({ commit, state }, payload) {
			return API.get('/dashboard')
				.then(response => {
					state.data = response.data.data;
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		GET_LAST_AUDITS: function({ commit, state }, payload) {
			return API.get('/dashboard/last_audits', {
				params: state.lastAuditFilter
			})
				.then(response => {
					state.data = Object.assign({}, state.data, response.data.data);
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		GET_NEXT_AUDITS: function({ commit, state }, payload) {
			return API.get('/dashboard/next_audits', {
				params: state.nextAuditFilter
			})
				.then(response => {
					state.data = Object.assign({}, state.data, response.data.data);
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		GET_TASKS: function({ commit, state }, payload) {
			return API.get('/dashboard/tasks', {
				params: state.tasksFilter
			})
				.then(response => {
					state.data = Object.assign({}, state.data, response.data.data);
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		}
	}
};
