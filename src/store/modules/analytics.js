import QS from 'qs';
import API from '@/config/api';

export default {
	namespaced: true,

	state: {
		filter: {
			location: null,
			checklist: null,
			directory: null,
			start: null,
			end: null
		}
	},

	getters: {},

	mutations: {
		SET_FILTER: function(state, payload) {
			state.filter.location = payload.location || null;
			state.filter.start = payload.start || null;
			state.filter.end = payload.end || null;
			state.filter.checklist = payload.checklist || null;
			state.filter.directory = payload.directory || null;
		}
	},

	actions: {
		GET_ANALYTICS: function({ commit, state }, payload) {
			return API.get('/analytics', {
				params: state.filter
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
