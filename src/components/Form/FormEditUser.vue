<template>
    <div>
        <q-field
            class="m-b-1"
            :error="$v.form.firstName.$error"
            :error-label="$t('PLEASE_FILL_OUT_ALL_REQUIRED_FIELDS')">
            <q-input
                v-model.trim="$v.form.firstName.$model"
                :float-label="$t('FIRSTNAME')">
            </q-input>
        </q-field>

        <q-field class="m-b-1"
            :error="$v.form.lastName.$error"
            :error-label="$t('PLEASE_FILL_OUT_ALL_REQUIRED_FIELDS')">
            <q-input
                v-model.trim="$v.form.lastName.$model"
                :float-label="$t('LASTNAME')">
            </q-input>
        </q-field>

        <q-field class="m-b-1"
            :error="$v.form.email.$error"
            :error-label="$t('PLEASE_ENTER_VALID_EMAIL')">
            <q-input
                :float-label="$t('EMAIL')"
                v-model.trim="$v.form.email.$model">
            </q-input>
        </q-field>

        <!-- <q-field class="m-b-2"
            :error="$v.form.password.$error"
            :error-label="$t('PASSWORD_TOO_SHORT')">
            <q-input
                v-model.trim="$v.form.password.$model"
                type="password"
                min-length="8"
                :float-label="$t('PASSWORD')">
            </q-input>
        </q-field> -->

        <!-- <q-field class="m-b-1"
            :error="$v.form.company.$error"
            :error-label="$t('PLEASE_FILL_OUT_ALL_REQUIRED_FIELDS')">
            <q-select
                v-model.trim="$v.form.company.$model"
                :placeholder="$t('COMPANY')"
                :options="companies" />
        </q-field> -->

        <div class="text-right">
            <q-btn :label="$t('CANCEL')" @click="onCancel"></q-btn>
            <q-btn :label="$t('SAVE')" @click="onSubmit"></q-btn>
        </div>
    </div>
</template>

<script>
import QS from 'qs';
import axios from 'axios';
import { cloneDeep } from 'lodash';
import { required, email, minLength } from 'vuelidate/lib/validators';

export default {
	name: 'FormEditUser',

	props: {
		data: {
			type: Object,
			required: false
		}
	},

	data() {
		return {
			form: {
				firstName: '',
				lastName: '',
				email: '',
				password: '',
				company: {}
			},
			loading: false
		};
	},

	validations: {
		form: {
			firstName: { required },
			email: { required, email },
			lastName: { required }
		}
	},

	mounted() {
		this.init();
	},

	methods: {
		doSave() {
			this.loading = true;

			this.$store
				.dispatch('users/UPDATE_USER', this.form)
				.then(response => {
					this.loading = false;
					this.onCancel();

					this.$q.notify({
						message: this.$t('SAVE_SUCCESS'),
						type: 'positive'
					});
				})
				.catch(err => {
					this.loading = false;
					this.onCancel();
				});
		},

		init() {
			this.form = _.cloneDeep(this.data);
			// this.requestUserDetails();

			console.log('FormEditUser', this.data);
		},

		onCancel() {
			this.$emit('cancel');
		},

		onSubmit() {
			this.$v.$touch();
			if (this.$v.$invalid) {
				return false;
			} else {
				this.doSave();
			}
		},

		requestUserPhones() {
			this.$http
				.get('/users/' + this.data.id + '/phones')
				.then(response => {
					console.log(response);
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		requestUserDetails() {
			const requests = [];
			requests.push(this.requestUserPhones());

			axios.all(requests).then(
				axios.spread((...results) => {
					// this.form.phones = results[0].data.data;
					console.log(results);
				})
			);
		},

		requestCompany() {
			this.$http
				.get('/companies/' + this.data.companyId)
				.then(response => {
					console.log(response);
					this.company = response.data.data;
				})
				.catch(err => {});
		},

		resetForm(formName) {
			this.$refs[formName].resetFields();
		}
	}
};
</script>

<style lang="scss">
</style>
