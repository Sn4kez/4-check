<template>
    <el-form
        ref="form-renew-password"
        :model="form"
        :rules="rules"
        @submit.native.prevent="onSubmit">

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
					<el-button type="primary" class="w-100" native-type="submit" v-loading="loading">{{ $t('RENEW_PASSWORD') }}</el-button>
				</el-form-item>
			</el-col>
		</el-row>

    </el-form>
</template>

<script>
import commonMixins from '@/shared/mixins/common';

export default {
	name: 'FormRenewPassword',

	mixins: [commonMixins],

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
					this.$refs['form-renew-password'].validateField('password_repeat');
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
		doSave() {
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
					this.handleErrors(err);
				});
		},

		handleSuccess(response) {
			this.$q.notify({
				message: this.$t('SAVE_SUCCESS'),
				type: 'positive'
			});

			this.$router.push({ path: '/login' });
		},

		init() {
			console.log('FormRenewPassword mounted');
		},

		onSubmit() {
			console.log('onSubmit');

			this.$refs['form-renew-password'].validate(valid => {
				if (valid) {
					this.doSave();
				} else {
					return false;
				}
			});
		}
	}
};
</script>
