import Vue from 'vue';
import axios from 'axios';
import config from './index';
import router from './../router/index';

const api = axios.create({
	// baseURL: config.api.isHttps ? 'https://' + config.api.host + config.api.path : 'http://' + config.api.host + config.api.path,
	baseURL: config.api.url,
	headers: {
		'Content-Type': 'application/json; charset=utf-8'
	}
});

/*
 * Interceptor to handle request
 */
api.interceptors.request.use(
	function(config) {
		Vue.prototype.$Progress.start();

		return config;
	},
	function(error) {
		Vue.prototype.$Progress.fail();

		return Promise.reject(error);
	}
);

/*
 * Interceptor to handle unauthorized requests
 */
api.interceptors.response.use(
	function(response) {
		// All fine
		Vue.prototype.$Progress.finish();

		return response;
	},
	function(error) {

		Vue.prototype.$Progress.fail();
		console.log('interceptors', { error });

		// Redirect to login if authentication is required
		if (error.response.status === 401 && !error.config.url.includes('/auth/token')) {
			console.log('status', error);

			Vue.prototype.$q.notify({ message: error.response.data.message, type: 'negative' });

			// Redirect to login
			router.push({ path: '/login' });

			return Promise.reject(error);
		}

		// Show message for all kinds of errors
		// Vue.prototype.$notify({
		// 	type: 'error',
		// 	title: error.request.statusText,
		// 	message: error.message,
		// 	duration: 0,
		// 	showClose: true
		// });

		return Promise.reject(error);
	}
);

Vue.prototype.$http = api;

export default api;
