<template>
    <el-form ref="form-reset-password" :model="form" :rules="rules" @submit.native.prevent="onSubmit" class="">

        <el-row :gutter="20">
			<el-col :xs="24">
				<el-form-item :label="$t('PASSWORD')" prop="password">
					<el-input type="password" :placeholder="$t('PASSWORD')" v-model="form.password"></el-input>
				</el-form-item>
			</el-col>

			<el-col :xs="24">
				<el-form-item :label="$t('PASSWORD_REPEAT')" prop="password_repeat">
					<el-input type="password" :placeholder="$t('PASSWORD_REPEAT')" v-model="form.password_repeat"></el-input>
				</el-form-item>
			</el-col>
		</el-row>

        <el-row class="m-t-1">
			<el-col :xs="24">
				<el-form-item>
					<el-button type="primary" class="w-100" native-type="submit" v-loading="loading">{{ $t('RESET_PASSWORD') }}</el-button>
				</el-form-item>
			</el-col>
		</el-row>

    </el-form>
</template>

<script>
export default {
	name: 'FormResetPassword',

	props: {
		token: {
			type: String,
			required: true
		}
	},

	data() {
		const validatePass = (rule, value, callback) => {
			if (value === '') {
				callback(new Error(this.$t('PLEASE_FILL_OUT_MANDATORY_FIELDS')));
			} else {
				if (this.form.password_repeat !== '') {
					this.$refs['form-reset-password'].validateField('password_repeat');
				}
				callback();
			}
		};

		const validatePass2 = (rule, value, callback) => {
			if (value === '') {
				callback(new Error(this.$t('PLEASE_FILL_OUT_MANDATORY_FIELDS')));
			} else if (value !== this.form.password) {
				callback(new Error(this.$t('PASSWORD_DO_NOT_MATCH')));
			} else {
				callback();
			}
		};

		return {
			form: {
				password: '',
				password_repeat: ''
			},
			hasToken: false,
			loading: false,
			rules: {
				password: [
					{ required: true, message: this.$t('PLEASE_FILL_OUT_MANDATORY_FIELDS'), trigger: 'blur' },
					{ min: 6, message: this.$t('PASSWORD_TOO_SHORT'), trigger: 'blur' },
					{ validator: validatePass, trigger: 'blur' }
				],
				password_repeat: [
					{ required: true, message: this.$t('PLEASE_FILL_OUT_MANDATORY_FIELDS'), trigger: 'blur' },
					{ validator: validatePass2, trigger: 'blur' }
				]
			}
		};
	},

	mounted() {
		this.init();
	},

	methods: {
		doResetPassword() {
			this.loading = true;

			this.$http
				.post('/users/password/reset', {
					password: this.form.password,
					token: this.token
				})
				.then(result => {
					this.loading = false;

					if (result.status === 200 || result.status === 204) {
						this.handleSuccess(result);
					} else {
						this.handleErrors(result);
					}
				})
				.catch(err => {
					this.loading = false;
				});
		},

		handleErrors(response) {
			console.log('handleError', response);

			if (response.response.data.message.length) {
				this.$q.notify({
					message: response.response.data.message,
					type: 'negative'
				});
			}

			if (response.response.data.errors) {
				_.forEach(response.response.data.errors, (value, key) => {
					console.log('invalid fields:', value, key);
					this.$q.notify({
						message: value[0],
						type: 'negative'
					});
				});
			}
		},

		handleSuccess(response) {
			this.$q.notify({
				message: this.$t('SAVE_SUCCESS'),
				type: 'positive'
			});

			this.$router.push({ path: '/login' });
		},

		init() {
			console.log('FormResetPassword');
		},

		onSubmit() {
			console.log('onSubmit');

			this.$refs['form-reset-password'].validate(valid => {
				if (valid) {
					this.doResetPassword();
				} else {
					return false;
				}
			});
		}
	}
};
</script>

<style lang="scss">
</style>
