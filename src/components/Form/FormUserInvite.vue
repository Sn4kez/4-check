<template>
    <el-form v-loading="loading">
        <q-field
            :error="$v.form.email.$error"
            :error-label="$t('PLEASE_ENTER_VALID_EMAIL')">
            <q-input
                :stack-label="$t('EMAIL')"
                placeholder="name@company.com"
                v-model.trim="$v.form.email.$model">
            </q-input>
        </q-field>

        <div class="m-t-2">
            <el-alert
                :title="$t('HINT')"
                :description="$t('TEXT_INVITE_USER_INCREASE_QUANTITY')"
                type="info"
                show-icon
                :closable="false">
            </el-alert>
        </div>

        <div class="text-right m-t-2">
            <q-btn
                :label="$t('CANCEL')"
                @click="onCancel"
                v-if="isDeviceGreaterSM"
                flat
                no-ripple
                class="m-r-1">
            </q-btn>
            <q-btn
                :label="$t('INVITE_USER')"
                class="w-100--sm"
                @click="onSubmit"
                color="primary"
                no-ripple>
            </q-btn>
        </div>
    </el-form>
</template>

<script>
import { required, email } from 'vuelidate/lib/validators';
import { Invitation } from '@/shared/classes/Invitation';
import commonMixins from '@/shared/mixins/common';

export default {
	name: 'FormUserInvite',

	mixins: [commonMixins],

	computed: {
		company() {
			return this.$store.state.user.company;
		},

		isDeviceGreaterSM() {
			return this.$store.getters.IS_DEVICE_GREATER_SM;
		}
	},

	data() {
		return {
			form: {
				company: '',
				email: ''
			},
			loading: false
		};
	},

	validations: {
		form: {
			email: { required, email }
		}
	},

	mounted() {
		this.init();
	},

	methods: {
		doInviteUser(invitation) {
			console.log('doInviteUser', invitation);

			this.loading = true;

			this.$store
				.dispatch('users/CREATE_INVITATION', invitation)
				.then(response => {
					console.log({ response }, response.status);

					if (response.status === 201) {
						this.$q.notify({
							message: this.$t('SAVE_SUCCESS'),
							type: 'positive'
						});

						this.$store.dispatch('companies/GET_INVITATIONS', { id: this.company.id });

						this.onCancel();
					} else {
						this.handleErrors(response);
					}

					this.loading = false;
				})
				.catch(err => {
					this.handleErrors(response);
					this.loading = false;
				});
		},

		init() {
			console.log('FormInviteUser');
			this.form.company = this.company.id;
		},

		onCancel() {
			this.$emit('cancel');
		},

		onSubmit() {
			console.log(this.$v);
			this.$v.$touch();
			if (this.$v.$invalid) {
				return false;
			} else {
				const invitation = new Invitation(this.form);
				this.doInviteUser(invitation);
			}
		}
	}
};
</script>
