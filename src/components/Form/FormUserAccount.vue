<template>
    <el-form class="form-user-account" v-show="visible">
        <el-row :gutter="20">
            <el-col :xs="24" :md="8">
                <q-field class="m-b-1"
                    :error="$v.form.email.$error"
                    :error-label="$t('PLEASE_FILL_OUT_ALL_REQUIRED_FIELDS')">
                    <q-input
                        v-model.trim="$v.form.email.$model"
                        :stack-label="$t('EMAIL')"
                        @input="onInput">
                    </q-input>
                </q-field>
            </el-col>
        </el-row>

        <el-row :gutter="20">
            <el-col :xs="24" :md="8">
                <q-field class="m-b-1"
                    :error="$v.form.password_old.$error"
                    :error-label="$t('PLEASE_FILL_OUT_ALL_REQUIRED_FIELDS')">
                    <q-input
                        v-model.trim="$v.form.password_old.$model"
                        :stack-label="$t('PASSWORD_OLD')"
                        type="password"
                        @input="onInput">
                    </q-input>
                </q-field>
            </el-col>
        </el-row>

        <el-row :gutter="20">
            <el-col :xs="24" :md="8">
                <q-field class="m-b-1"
                    :error="$v.form.password.$error"
                    :error-label="$t('PLEASE_FILL_OUT_ALL_REQUIRED_FIELDS')">
                    <q-input
                        v-model.trim="$v.form.password.$model"
                        :stack-label="$t('PASSWORD')"
                        type="password"
                        @input="onInput">
                    </q-input>
                </q-field>
            </el-col>

            <el-col :xs="24" :md="8">
                <q-field class="m-b-1"
                    :error="!$v.form.password_repeat.sameAsPassword"
                    :error-label="$t('PASSWORD_DO_NOT_MATCH')">
                    <q-input
                        v-model.trim="$v.form.password_repeat.$model"
                        :stack-label="$t('PASSWORD_REPEAT')"
                        type="password"
                        @input="onInput">
                    </q-input>
                </q-field>
            </el-col>
        </el-row>

        <el-row class="m-t-1">
            <el-col :xs="24" :md="16">
                <el-alert
                    :title="$t('HINT')"
                    type="info"
                    :description="$t('YOU_NEED_TO_LOGIN_AFTER_CHANGING_PASSWORD')"
                    :closable="false"
                    show-icon>
                </el-alert>
            </el-col>
        </el-row>

        <el-row class="m-t-1">
            <el-col :xs="24" :md="24" class="text-right">
                <q-btn class="w-100--sm"
                    color="primary"
                    :label="$t('CHANGE_PASSWORD_EMAIL')"
                    @click="onSubmit"
                    :disable="!inputsHasChanged"
                    :loading="loading">
                </q-btn>
            </el-col>
        </el-row>

    </el-form>
</template>

<script>
import { required, requiredIf, email, minLength, sameAs } from 'vuelidate/lib/validators';
import { User } from '@/shared/classes/User';
import commonMixins from '@/shared/mixins/common';

export default {
	name: 'FormUserAccount',

	mixins: [commonMixins],

	props: {
		data: {
			type: Object,
			required: true
		}
	},

	computed: {
		isOwnProfile() {
			return this.data.id === this.user.id;
		},

		user() {
			return this.$store.state.user.data;
		}
	},

	data() {
		return {
			form: {
				email: '',
				password: '',
				password_repeat: '',
				password_old: ''
			},
			inputsHasChanged: false,
			loading: false,
            visible: false,
		};
	},

	validations: {
		form: {
			email: {
				required: requiredIf(function(model) {
					console.log(model, this.form.email !== this.data.email);
					return this.form.email !== this.data.email;
				}),
				email
			},
			password: {
				minLength: minLength(8),
				required: requiredIf(function(model) {
					return this.form.password_old;
				})
			},
			password_repeat: {
				required: requiredIf(function(model) {
					return this.form.password_old;
				}),
				minLength: minLength(8),
				sameAsPassword: sameAs('password')
			},
			password_old: {
				required: requiredIf(function(model) {
					return this.form.password;
				}),
				minLength: minLength(8)
			}
		}
	},

	mounted() {
		this.init();
	},

	methods: {
		/**
		 * Update user
		 *
		 * @returns Promise
		 */
		doSave() {
			this.loading = true;

			let data = {
				id: this.data.id
			};

			if (this.form.password.length) {
				data.currentPassword = this.form.password_old;
				data.newPassword = this.form.password;
			}

			if (this.form.email !== this.data.email) {
				data.email = this.form.email;
			}

			return this.$store
				.dispatch('users/UPDATE_USER', data)
				.then(response => {
					this.loading = false;

					if (response.status === 204) {
						this.$q.notify({
							message: this.$t('SAVE_SUCCESS'),
							type: 'positive'
						});

						// Log off user to validate credentials
						if (this.isOwnProfile) {
							this.$store.commit('SET_APP_LOADING', { loading: true });

							// Log off user
							setTimeout(() => {
								this.$router.push({ path: '/logout' });
							}, 1500);
						}
					} else {
						this.handleErrors(response);
					}

					return response;
				})
				.catch(err => {
					this.$q.notify({
						message: this.$t('ERROR'),
						type: 'negative'
					});
					this.loading = false;
					return err;
				});
		},

		init() {
			this.form.email = _.cloneDeep(this.data.email);

			setTimeout(()=> {
				this.visible = true;
			}, 500)
		},

		/**
		 * Check if input fields are empty.
		 *
		 * @returns {void}
		 */
		onInput(value) {
			_.forEach(this.form, item => {
				if (item !== '') {
					this.inputsHasChanged = true;
				}
			});
		},

		/**
		 * Handle form validation
		 *
		 * @returns void
		 */
		onSubmit() {
			this.$v.$touch();
			if (this.$v.$invalid) {
				return false;
			} else {
				this.doSave();
			}
		}
	},

	watch: {
		data(newValue, oldValue) {
			this.init();
		}
	}
};
</script>

<style lang="scss">
</style>
