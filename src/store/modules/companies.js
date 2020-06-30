import Vue from 'vue';
import QS from 'qs';
import API from '@/config/api';
import { transformForSelect, transformCompaniesForSelect } from '@/shared/transformers';
import scoringschemes from './scoringschemes';

export default {
	namespaced: true,

	state: {
		addresses: [],
		companies: [],
		corporateIdentity: { image: '' },
		groups: [],
		invitations: [],
		scoringschemes: [],
		users: [],
		subscription: {
			package: '',
			start: new Date(),
			end: new Date()
		}
	},

	getters: {
		companyOptions: function(state, getters) {
			return transformCompaniesForSelect(state.companies);
		},

		getCompanyById: state => id => {
			return state.companies.find(state => state.id === id);
		},

		getGroupById: state => id => {
			return state.groups.find(group => group.id === id);
		},

		groupOptions: function(state, getters) {
			return transformForSelect(state.groups);
		}
	},

	mutations: {
		SET_COMPANIES: function(state, payload) {
			state.companies = payload;
		},

		SET_COMPANY_GROUPS: function(state, payload) {
			state.groups = payload;
		},

		SET_COMPANY_CORPORATE_IDENTITY: function(state, payload) {
			state.corporateIdentity.image = payload.image;
			state.corporateIdentity.brand_primary = payload.brand_primary;
			state.corporateIdentity.link_color = payload.link_color;

			const CI = {
				image: payload.image,
				brand_primary: payload.brand_primary,
				link_color: payload.link_color,
				image: payload.image
			};

			let stringifed = JSON.stringify(CI);
			Vue.prototype.$localStorage.set('corporateIdentity', stringifed);
		}
	},

	actions: {
		CREATE_ADDRESS: function({ commit, state }, payload) {
			const data = QS.stringify(payload.data);

			return API.post('/companies/' + payload.id + '/addresses', data, {
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				}
			})
				.then(response => {
					state.addresses.push(response.data.data);
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		CREATE_COMPANY: function({ commit, state }, payload) {
			const data = QS.stringify(payload.data);

			return API.post('/companies', data, {
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				}
			})
				.then(response => {
					if (response.status === 201) {
						state.companies.push(response.data.data);
					}

					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		CREATE_GROUP: function({ commit, state }, payload) {
			const data = QS.stringify(payload);

			return API.post('/companies/' + payload.id + '/groups', data, {
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

		CREATE_SCORING_SCHEME: function({ commit, state }, payload) {
			const data = QS.stringify(payload);

			return API.post('/companies/' + payload.id + '/scoringschemes', data, {
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

		GET_COMPANY: function({ commit, state }, payload) {
			return API.get('/companies/' + payload.id)
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		GET_COMPANY_LOGIN: function({ commit, state }, payload) {
			return API.get('/login/ci/' + payload.id)
				.then(response => {
					commit('SET_COMPANY_CORPORATE_IDENTITY', response.data.data);
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		GET_COMPANY_ADDRESSES: function({ commit, state }, payload) {
			return API.get('/companies/' + payload.id + '/addresses')
				.then(response => {
					state.addresses = response.data.data;
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		GET_COMPANY_DESIGN_PREFERENCES: function({ commit, state }, payload) {
			return API.get('/companies/preferences/ci/' + payload.id)
				.then(response => {
					commit('SET_COMPANY_CORPORATE_IDENTITY', response.data.data);
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		GET_COMPANY_REPORT_PREFERENCES: function({ commit, state }, payload) {
			return API.get('/companies/' + payload.id + '/reportsettings')
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		GET_COMPANY_SCORING_SCHEMES: function({ commit, state }, payload) {
			return API.get('/companies/' + payload.id + '/scoringschemes')
				.then(response => {
					state.scoringschemes = response.data.data;
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		GET_COMPANY_SUBSCRIPTION: function({ commit, state }, payload) {
			return API.get('/companies/' + payload.id + '/subscription')
				.then(response => {
					state.subscription = response.data.data;
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		GET_GROUPS: function({ commit, state }, payload) {
			return API.get('/companies/' + payload.id + '/groups')
				.then(response => {
					state.groups = response.data.data;
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		GET_INVITATIONS: function({ commit, state }, payload) {
			return API.get('/users/invitations/company/' + payload.id)
				.then(response => {
					state.invitations = response.data.data;
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		GET_COMPANIES: function({ commit, state }, payload) {
			return API.get('/companies')
				.then(response => {
					state.companies = response.data.data;
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		GET_USERS: function({ commit, state }, payload) {
			return API.get('/companies/' + payload.id + '/users')
				.then(response => {
					state.users = response.data.data;
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		RESET_COMPANY: function({ commit, state }, payload) {
			return API.get('/hardreset/' + payload.id)
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		UPDATE_COMPANY: function({ commit, state }, payload) {
			const data = QS.stringify(payload);

			return API.patch('/companies/' + payload.id, data, {
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

		UPDATE_COMPANY_DESIGN_PREFERENCES: function({ commit, state }, payload) {
			const data = QS.stringify(payload);

			console.log('design', payload, data);

			return API.patch('/companies/preferences/ci/' + payload.company, data, {
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				}
			})
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		UPDATE_COMPANY_REPORT_PREFERENCES: function({ commit, state }, payload) {
			const data = QS.stringify(payload);

			return API.patch('/companies/' + payload.companyId + '/reportsettings', data, {
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				}
			})
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		}
	}
};
