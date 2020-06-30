<template>
    <el-form v-loading="loading">

        <el-row :gutter="20" class="m-t-1">
            <el-col :xs="24">
                <q-field
                    class="m-b-1"
                    :error="$v.form.name.$error"
                    :error-label="$t('PLEASE_FILL_OUT_ALL_REQUIRED_FIELDS')">
                    <q-input
                        v-model.trim="$v.form.name.$model"
                        :float-label="$t('NAME')">
                    </q-input>
                </q-field>
            </el-col>
        </el-row>

        <!-- Actions -->
        <el-row class="m-t-1">
            <el-col :xs="24">
                <div class="text-right">
                    <q-btn v-if="isDeviceGreaterSM"
                        :label="$t('CANCEL')"
                        @click="onCancel"
                        flat
                        no-ripple
                        class="m-r-1">
                    </q-btn>

                    <q-btn
                        :label="$t('SAVE')"
                        color="primary"
                        no-ripple
                        @click="onSubmit"
                        class="w-100--sm m-t-1--sm"
                        :loading="loading">
                    </q-btn>
                </div>
            </el-col>
        </el-row>
    </el-form>
</template>

<script>
import axios from 'axios';
import { Location } from '@/shared/classes/Location';
import { LocationType } from '@/shared/classes/LocationType';
import { required, integer, minLength, maxLength } from 'vuelidate/lib/validators';
import { cloneDeep, forEach } from 'lodash';
import locationsMixin from '@/shared/mixins/locations';

export default {
	name: 'FormEditLocationType',

	mixins: [locationsMixin],

	props: {
		data: {
			type: Object,
			required: false
		}
	},

	computed: {
		edit() {
			return this.data.id ? true : false;
		},

		isDeviceGreaterSM() {
			return this.$store.getters.IS_DEVICE_GREATER_SM;
		},

		locationTypes() {
			return this.$store.getters['locations/locationTypes'];
		},

		user() {
			return this.$store.state.user;
		}
	},

	data() {
		return {
			form: {
				name: ''
			},
			loading: false
		};
	},

	validations: {
		form: {
			name: { required }
		}
	},

	mounted() {
		this.init();
	},

	methods: {
		doSubmit() {
			let locationType = {};
			let dispatcherName = 'locations/CREATE_LOCATION_TYPE';

			if (this.edit) {
				locationType = new LocationType(this.form);
				dispatcherName = 'locations/UPDATE_LOCATION_TYPE';
			} else {
				locationType = new LocationType({
					company: this.user.company.id,
					name: this.form.name
				});
			}

			console.log('doSubmit new locationtype', locationType);

			this.loading = true;

			this.$store
				.dispatch(dispatcherName, locationType)
				.then(response => {
					this.loading = false;

					if (response.status === 201 || response.status === 204) {
						this.onCancel();

						this.$q.notify({
							message: this.$t('SAVE_SUCCESS'),
							type: 'positive'
						});

						// Refresh
						this.requestLocationTypes();
					} else {
						this.handleErrors(response);
					}
				})
				.catch(err => {
					this.loading = false;
				});
		},

		handleErrors(response) {
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

		init() {
			console.log('init formeditlocationtypes', this.edit);

			if (this.edit) {
				this.form = _.cloneDeep(this.data);
			}
		},

		onCancel() {
			this.$emit('cancel');
		},

		onSubmit() {
			this.$v.$touch();
			if (this.$v.$invalid) {
				return false;
			} else {
				this.doSubmit();
			}
		},

		requestLocationTypes() {
			return this.$store
				.dispatch('locations/GET_LOCATION_TYPES', { id: this.user.company.id })
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		}
	}
};
</script>
