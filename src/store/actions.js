import Vue from 'vue';
import axios from 'axios';
import API from '@/config/api';
import config from '@/config/index';
import { date } from 'quasar';
import { b64EncodeUnicode } from '@/services/utils';
import i18n from '@/i18n/index';

export default {
	DO_LOGIN: function({ commit, state }, payload) {
		if (Vue.prototype.$q.platform.is.cordova) {
			Vue.prototype.$localStorage.set('username', b64EncodeUnicode(payload.email));
			Vue.prototype.$localStorage.set('password', b64EncodeUnicode(payload.password));
		}

		/*return API.post('/login', {
			email: payload.email,
			password: payload.password
		})
			.then(result => {
				return result;
			})
			.catch(err => {
				return err;
			});*/

		return API.post('/auth/token', {
			username: payload.email,
			password: payload.password,
			grant_type: config.api.grant_type,
			client_id: config.api.client_id,
			client_secret: config.api.client_secret
		})
			.then(result => {
				return result;
			})
			.catch(err => {
				return err;
			});
	},

	GET_CURRENT_USER({ commit, state }) {
		return API.get('/users/me')
			.then(response => {
				commit('SET_USER', response.data.data);
                const locale = response.data.data.locale ? response.data.data.locale.substr(0, 2) : 'de';
                i18n.locale = locale;
                localStorage.setItem('locale', locale);

				return response;
			})
			.catch(err => {
				return err;
			});
	}
};
