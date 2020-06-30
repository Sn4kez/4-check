export default {
	name: '4check App',
	version: '0.1.0',
	api: {
		url: process.env.VUE_APP_API_URL,
		token: localStorage.getItem('access_token'),
		grant_type: 'password',
		client_id: 2,
		client_secret: process.env.VUE_APP_API_CLIENT_SECRET,
	}
};
