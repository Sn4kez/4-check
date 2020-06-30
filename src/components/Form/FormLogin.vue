<template>
    <el-form ref="form" :model="form" :rules="rules" class="login__form" @submit.native.prevent="onSubmit">
        <el-form-item :label="$t('EMAIL')" prop="email">
            <el-input type="email" :placeholder="$t('EMAIL')" v-model="form.email"></el-input>
        </el-form-item>

        <el-form-item :label="$t('PASSWORD')" prop="password">
            <el-input type="password" :placeholder="$t('PASSWORD')" v-model="form.password"></el-input>
        </el-form-item>

        <el-form-item class="m-b-0">
            <el-button type="primary" class="w-100" :loading="loading" native-type="submit">{{$t('LOGIN')}}</el-button>
        </el-form-item>
    </el-form>
</template>

<script>
import { b64DecodeUnicode } from '@/services/utils';
import { date } from 'quasar';

export default {
	name: 'FormLogin',

	data() {
		return {
			form: {
				email: '',
				password: ''
			},
			loading: false,
			rules: {
				email: [
					{ required: true, message: this.$t('PLEASE_FILL_OUT_MANDATORY_FIELDS'), trigger: 'blur' },
					{ type: 'email', message: this.$t('PLEASE_ENTER_VALID_EMAIL'), trigger: 'blur' }
				],
				password: [{ required: true, message: this.$t('PLEASE_FILL_OUT_MANDATORY_FIELDS'), trigger: 'blur' }]
			}
		};
	},

	computed: {
		network() {
			return this.$store.state.network;
		},

		user() {
			return this.$store.state.user.data;
		}
	},

	mounted() {
		console.log('FormLogin mounted', this.$route);

		if (this.network.state === 'offline') {
			this.handleOfflineLogin();
		} else {
			this.handleAutoLogin();
		}
	},

	methods: {
		doLogin(obj) {
			this.loading = true;
			this.$store
				.dispatch('DO_LOGIN', {
					email: obj.email,
					password: obj.password
				})
				.then(response => {
					console.log('doLogin response', { response });
					this.loading = false;

					if (response.status === 200) {
						this.handleSuccessLogin(response);
					} else {
						this.handleFailureLogin(response);
					}
				})
				.catch(err => {
					console.log('doLogin err', { err });
					this.loading = false;
				});
		},

		handleAutoLogin() {
			if (this.$q.platform.is.cordova) {
				if (this.$localStorage.get('username') && this.$localStorage.get('password')) {
					console.log('email and password');
					this.doLogin({
						email: b64DecodeUnicode(this.$localStorage.get('username')),
						password: b64DecodeUnicode(this.$localStorage.get('password'))
					});
				}
			}

			if (this.user.email && this.user.password) {
				console.log('email and password');
				this.doLogin({
					email: this.user.email,
					password: this.user.password
				});
			}

		},

		handleOfflineLogin() {
			console.log('handleOfflineLogin');

			if (this.$q.platform.is.cordova) {
				if (this.$localStorage.get('expiry_date') && this.$localStorage.get('access_token')) {
					const expiry_date = new Date(this.$localStorage.get('expiry_date'));
					const today = new Date();
					const isExpired = expiry_date > today ? false : true;

					if (isExpired) {
						return;
					}

					console.log('handleAutoLogin', expiry_date, today, expiry_date > today, isExpired);

					// Set authorization header
					this.$http.defaults.headers.common['Authorization'] =
						`${this.$localStorage.get('token_type')} ` + this.$localStorage.get('access_token');

					// Redirect to start page
					this.$router.push('/');
				}
			}
		},

		handleFailureLogin(result) {
			this.$message({
				type: 'error',
				message: result.response.data.message,
				showClose: true,
				duration: 10000
			});
		},

		handleSuccessLogin(result) {
			console.log('loginForm: login successful.');

			// Set authorization header
			this.$http.defaults.headers.common['Authorization'] = `${result.data.token_type} ${
				result.data.access_token
			}`;

			// Calculate expiry date
			const expiryIn = result.data.expires_in;
			const now = new Date();
			const expiryDate = date.addToDate(now, { seconds: expiryIn });

			// Store token
			this.$localStorage.set('access_token', result.data.access_token);
			this.$localStorage.set('expiry_date', expiryDate);
			this.$localStorage.set('expires_in', result.data.expires_in);
			this.$localStorage.set('token_type', result.data.token_type);

			this.$session.set('access_token', result.data.access_token);
			// this.$session.set('refresh_token', result.data.refresh_token);
			this.$session.set('expires_in', result.data.expires_in);
			this.$session.set('token_type', result.data.token_type);
			this.$session.set('expiry_date', expiryDate);

			// Redirect to main page
			this.$router.push({ path: '/' });
		},

		onSubmit() {
			console.log('onSubmit');
			if (this.network.state === 'offline') {
				this.handleOfflineLogin();
			}

			this.$refs['form'].validate(valid => {
				if (valid) {
					this.doLogin({
						email: this.form.email,
						password: this.form.password
					});
				} else {
					return false;
				}
			});
		}
	}
};
</script>
