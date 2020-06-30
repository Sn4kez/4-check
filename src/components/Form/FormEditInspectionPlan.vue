<!--
@component:         FormEditInspectionPlan
@description:       Form to create and edit task
                    There are more attributes available but we start with a smaller set.
                    In further development only remove the comments in markup and javascript.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-10-04
@modified:          2018-10-08
-->
<template>
    <el-form ref="FormEditInspectionPlan">
        <el-row :gutter="30">
            <el-col :xs="24" :sm="12">
                <q-field
                    class="m-b-1"
                    :error="$v.form.name.$error"
                    :error-label="$t('PLEASE_FILL_OUT_ALL_REQUIRED_FIELDS')">
                    <q-input
                        v-model.trim="$v.form.name.$model"
                        :stack-label="$t('NAME')"
                        ref="name">
                    </q-input>
                </q-field>
            </el-col>

            <el-col :xs="24" :sm="12">
                <q-field class="m-b-1">
                    <q-select
                        v-model="selectedUsers"
                        radio
                        :stack-label="$t('SELECT_PARTICIPANT')"
                        :options="usersOptions"
                        @input="onChangeUser"
                        ref="user" />
                </q-field>
            </el-col>
        </el-row>

        <el-row :gutter="30" class="m-t-1">
            <el-col :xs="24" :sm="12">
                <q-field class="m-b-1 m-t-2"
                    :error="$v.form.type.$error"
                    :error-label="$t('PLEASE_FILL_OUT_ALL_REQUIRED_FIELDS')">
                    <q-select
                        radio
                        v-model="$v.form.type.$model"
                        :stack-label="$t('REPEATATION')"
                        :options="repeatationOptions"
                        ref="repeatation" />
                </q-field>
            </el-col>

            <el-col :xs="24" :sm="12">
                <q-field class="m-b-1"
                    :label="$t('FACTOR')"
                    :label-width="12"
                    :error="$v.form.factor.$error"
                    :error-label="$t('PLEASE_FILL_OUT_ALL_REQUIRED_FIELDS')">
                    <el-input-number
                        v-model="$v.form.factor.$model"
                        :min="1"
                        :max="10"
                        ref="factor">
                    </el-input-number>
                </q-field>
            </el-col>
        </el-row>

        <el-row :gutter="30" class="m-t-2">
            <el-col :xs="24">
                <q-checkbox v-model="form.monday" :label="$t('MONDAY')" :true-value="1" :false-value="0" class="m-r-1 m-b-half" ref="weekday" />
                <q-checkbox v-model="form.tuesday" :label="$t('TUESDAY')" :true-value="1" :false-value="0" class="m-r-1 m-b-half" ref="weekday" />
                <q-checkbox v-model="form.wednesday" :label="$t('WEDNESDAY')" :true-value="1" :false-value="0" class="m-r-1 m-b-half" ref="weekday" />
                <q-checkbox v-model="form.thursday" :label="$t('THURSDAY')" :true-value="1" :false-value="0" class="m-r-1 m-b-half" ref="weekday" />
                <q-checkbox v-model="form.friday" :label="$t('FRIDAY')" :true-value="1" :false-value="0" class="m-r-1 m-b-half" ref="weekday" />
                <q-checkbox v-model="form.saturday" :label="$t('SATURDAY')" :true-value="1" :false-value="0" class="m-r-1 m-b-half" ref="weekday" />
                <q-checkbox v-model="form.sunday" :label="$t('SUNDAY')" :true-value="1" :false-value="0" class="m-r-1 m-b-half" ref="weekday" />
            </el-col>
        </el-row>

        <el-row :gutter="30" class="m-t-2">
            <el-col :xs="24" :sm="12">
                <q-field class="m-b-1"
                    :error="$v.form.startDate.$error"
                    :error-label="$t('PLEASE_FILL_OUT_ALL_REQUIRED_FIELDS')">
                    <q-datetime v-model="$v.form.startDate.$model" minimal format24h :stack-label="$t('START_AT')" type="date" ref="startDate" />
                </q-field>
            </el-col>

            <el-col :xs="24" :sm="12">
                <q-field class="m-b-1"
                    :error="$v.form.endDate.$error"
                    :error-label="$t('PLEASE_FILL_OUT_ALL_REQUIRED_FIELDS')">
                    <q-datetime v-model="$v.form.endDate.$model" minimal format24h :stack-label="$t('END_AT')" type="date" ref="endDate" />
                </q-field>
            </el-col>
        </el-row>

        <el-row :gutter="30">
            <el-col :xs="24" :sm="12">
                <q-field class="m-b-1"
                    :error="$v.form.startTime.$error"
                    :error-label="$t('PLEASE_FILL_OUT_ALL_REQUIRED_FIELDS')">
                    <q-datetime v-model="$v.form.startTime.$model" minimal format="HH:mm" format24h :stack-label="$t('START_TIME')" type="time" ref="startTime" />
                </q-field>
            </el-col>
        </el-row>

        <el-row :gutter="30">
            <el-col :xs="24" :sm="12">
                <q-field class="m-b-1"
                    :error="$v.form.endTime.$error"
                    :error-label="$t('PLEASE_FILL_OUT_ALL_REQUIRED_FIELDS')">
                    <q-datetime v-model="$v.form.endTime.$model" format="HH:mm" minimal format24h :stack-label="$t('END_TIME')" type="time" ref="endTime" />
                </q-field>
            </el-col>

            <el-col :xs="24" :sm="12" class="text-right">
                <q-btn
                    outline
                    rounded
                    no-ripple
                    color="primary"
                    :label="$t('RESET_PLAN')"
                    @click="resetForm()">
                </q-btn>
            </el-col>
        </el-row>

        <el-row class="m-t-2">
            <el-col>
                <div class="text-right">
                    <q-btn
                        :label="$t('CANCEL')"
                        @click="onCancel"
                        v-if="isDeviceGreaterSM"
                        flat
                        no-ripple
                        class="m-r-1">
                    </q-btn>
                    <q-btn
                        :label="$t('SAVE')"
                        class="w-100--sm"
                        color="primary"
                        @click="onSubmit"
                        no-ripple
                        :loading="loading">
                    </q-btn>
                </div>
            </el-col>
        </el-row>
    </el-form>
</template>

<script>
import { required, integer, minLength, maxLength } from 'vuelidate/lib/validators';
import { InspectionPlan } from '@/shared/classes/InspectionPlan';
import { getRepeatations } from '@/shared/repeatations';
import commonMixin from '@/shared/mixins/common';

export default {
	name: 'FormEditInspectionPlan',

	mixins: [commonMixin],

	props: {
		data: {
			type: Object,
			required: true
		}
	},

	computed: {
		company() {
			return this.$store.state.user.company;
		},

		isDeviceGreaterSM() {
			return this.$store.getters.IS_DEVICE_GREATER_SM;
		},

		edit() {
			return this.data.plan.id ? true : false;
		},

		user() {
			return this.$store.state.user.data;
		},

		usersOptions() {
			return this.$store.getters['users/usersOptions'];
		}
	},

	data() {
		return {
			form: {
				name: '',
				type: '',
				user: ''
			},
			loading: false,
			repeatationOptions: getRepeatations(),
			selectedUsers: []
		};
	},

	validations: {
		form: {
			name: { required },
			factor: { required },
			type: { required },
			startDate: { required },
			endDate: { required },
			endTime: { required },
			startTime: { required }
		}
	},

	mounted() {
		this.init();
	},

	methods: {
		doSubmit() {
			let inspectionPlan = new InspectionPlan(this.form);
			inspectionPlan.startTime = this.getTimeFromDate(inspectionPlan.startTime);
			inspectionPlan.endTime = this.getTimeFromDate(inspectionPlan.endTime);
			inspectionPlan.startDate = this.getDateStringFromDate(inspectionPlan.startDate);
			inspectionPlan.endDate = this.getDateStringFromDate(inspectionPlan.endDate);

			let dispatcherName = 'audits/UPDATE_AUDIT_PLAN';

			if (this.edit) {
			} else {
				dispatcherName = 'audits/CREATE_AUDIT_PLAN';
			}

			console.log('doSubmit insperction plan', inspectionPlan);

			this.loading = true;

			this.$store
				.dispatch(dispatcherName, inspectionPlan)
				.then(response => {
					this.loading = false;

					if (response.status === 201 || response.status === 204) {
						this.$q.notify({
							message: this.$t('SAVE_SUCCESS'),
							type: 'positive'
						});

						this.onCancel();
					} else {
						this.handleErrors(response);
					}
				})
				.catch(err => {
					this.loading = false;
					this.handleErrors(err);
				});
		},

		/**
		 * Return only Date object for time as text in format HH:MM:SS.
		 *
		 * @param  {String}       value
		 * @returns {Date}
		 */
		getDateFromTimeString(value) {
			let date = new Date();
			const hours = parseInt(value.split(':')[0]);
			const minutes = parseInt(value.split(':')[1]);
			const seconds = parseInt(value.split(':')[2]);
			date.setHours(hours);
			date.setMinutes(minutes);
			date.setSeconds(seconds);

			console.log(value, hours, minutes, seconds, date);
			return date.toISOString();
		},

		/**
		 *
		 * @param    {String}    value
		 * @returns  {String}
		 */
		getDateStringFromDate(value) {
			const splitted = value.split('T');

			return splitted[0];
		},

		/**
		 * Return only HH:MM:SS from a Date object
		 *
		 * @param  {Date}       value
		 * @returns {String}
		 */
		getTimeFromDate(value) {
			let date = new Date(value);
			date = date.toTimeString();

			return date.split(' ')[0];
		},

		init() {
			if (this.edit) {
				this.form = Object.assign({}, new InspectionPlan(this.data.plan));
				this.selectedUsers = this.data.plan.user;

				this.form.startTime = this.getDateFromTimeString(this.data.plan.startTime);
				this.form.endTime = this.getDateFromTimeString(this.data.plan.endTime);

				// this.form.startTime = this.getTimeFromDate(this.data.plan.startTime);
				// this.form.endTime = this.getTimeFromDate(this.data.plan.endTime);
			} else {
				this.form = Object.assign({}, new InspectionPlan());
				this.form.company = this.company.id;
				this.form.checklist = this.data.checklist.id;
			}
		},

		onCancel() {
			this.$emit('cancel');
		},

		onChangeUser(value) {
			this.form.user = value;
		},

		onSubmit() {
			this.$v.$touch();
			if (this.$v.$invalid) {
				return false;
			} else {
				this.doSubmit();
			}
		},

		resetForm() {
			_.forEach(this.$refs, item => {
				// Reset only input fields with clear method
				if (typeof item.clear === 'function') {
					item.clear();
				}
			});
		}
	}
};
</script>
