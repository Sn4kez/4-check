<template>
    <el-form ref="form-forgot-password" :model="form" :rules="rules" @submit.native.prevent="onSubmit">
        <el-form-item :label="$t('EMAIL')" prop="email">
            <el-input type="text" :placeholder="$t('EMAIL')" v-model="form.email"></el-input>
        </el-form-item>

        <el-form-item class="m-b-0">
            <button
                class="el-button w-100 el-button--primary"
                type="submit"
                v-loading="loading">
                {{$t('RESET_PASSWORD')}}
            </button>
        </el-form-item>

    </el-form>
</template>

<script>
import commonMixins from '@/shared/mixins/common';

export default {
	name: 'FormForgotPassword',

	mixins: [commonMixins],

	data() {
		return {
			form: {
				email: ''
			},
			loading: false,
			rules: {
				email: [
					{ required: true, message: this.$t('PLEASE_FILL_OUT_MANDATORY_FIELDS'), trigger: 'blur' },
					{ type: 'email', message: this.$t('PLEASE_ENTER_VALID_EMAIL'), trigger: 'blur' }
				]
			}
		};
	},

	methods: {
		doResetPasswort() {
			this.loading = true;

			this.$http
				.post('/users/password/token', {
					email: this.form.email
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
					console.log('doResetPasswort err', { err });
					this.loading = false;

					this.handleErrors(err);
				});
		},

		handleSuccess(result) {
			console.log('handleSuccess result', result);
			this.$q.notify({
				message: this.$t('SAVE_SUCCESS'),
				type: 'positive'
			});

			this.$emit('reset-completed');
		},

		onSubmit() {
			console.log('onSubmit');

			this.$refs['form-forgot-password'].validate(valid => {
				if (valid) {
					this.doResetPasswort();
				} else {
					return false;
				}
			});
		}
	}
};
</script>

