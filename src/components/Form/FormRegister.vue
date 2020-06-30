<!--
@component:         FormRegister
@environment:       Hyprid
@description:       This component handle the registration of a new company or new user.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-06-09
@modified:          2018-10-04
-->
<template>
	<el-form ref="form-register" :model="form" :rules="rules" @submit.native.prevent="onSubmit" class="register__form">

		<el-row :gutter="20">
			<el-col :xs="24" :sm="12">
				<el-form-item :label="$t('COMPANY')" prop="company.name">
					<el-input
                        type="text"
                        :placeholder="$t('COMPANY')"
                        v-model="form.company.name"
                        :disabled="validInvitation">
                    </el-input>
				</el-form-item>
			</el-col>
            <el-col :xs="24" :sm="12" v-if="!validInvitation">
                <el-form-item :label="$t('SECTOR')" prop="company.sector">
                    <el-select v-model="form.company.sector" :placeholder="$t('SECTOR')">
                        <el-option v-for="sector in options.sectors" :key="sector.value"
                            :label="sector.name"
                            :value="sector.value"
                            :disabled="validInvitation">
                        </el-option>
                    </el-select>
                </el-form-item>
            </el-col>
		</el-row>

		<el-row :gutter="20">
            <el-col :xs="24" :sm="8">
                <el-form-item :label="$t('GENDER')" prop="gender">
                    <el-select v-model="form.gender" :placeholder="$t('GENDER')">
                        <el-option v-for="gender in options.gender" :key="gender.value"
                            :label="gender.name"
                            :value="gender.value">
                        </el-option>
                    </el-select>
                </el-form-item>
            </el-col>

			<el-col :xs="24" :sm="8">
				<el-form-item :label="$t('FIRSTNAME')" prop="firstName">
					<el-input type="text" :placeholder="$t('FIRSTNAME')" v-model="form.firstName"></el-input>
				</el-form-item>
			</el-col>

			<el-col :xs="24" :sm="8">
				<el-form-item :label="$t('LASTNAME')" prop="lastName">
					<el-input type="text" :placeholder="$t('LASTNAME')" v-model="form.lastName"></el-input>
				</el-form-item>
			</el-col>
		</el-row>

		<el-row :gutter="20">
			<el-col :xs="24" :sm="12">
				<el-form-item :label="$t('EMAIL_BUSINESS')" prop="email">
					<el-input
                        type="email"
                        placeholder="me@company.com"
                        v-model="form.email">
                    </el-input>
				</el-form-item>
			</el-col>

			<el-col :xs="24" :sm="12">
                <el-row :gutter="20">
                    <el-form-item :label="$t('PHONE')" prop="phone.nationalNumber">
                        <!-- <el-col :xs="24" :sm="7">
                            <el-input  type="text" :placeholder="$t('PHONE')" v-model="form.phone.countryCode">
                                <template slot="prepend">+</template>
                            </el-input>
                        </el-col>

                        <el-col :xs="24" :sm="17">
                            <el-input type="text" :placeholder="$t('PHONE')" v-model="form.phone.nationalNumber"></el-input>
                        </el-col> -->
                        <vue-phone-number-input v-model="form.phone.userNumber" color="red" valid-color="green" @update="onUpdate"></vue-phone-number-input>
                    </el-form-item>
                </el-row>
			</el-col>
		</el-row>

		<el-row :gutter="20">
			<el-col :xs="24" :sm="12">
				<el-form-item :label="$t('PASSWORD')" prop="password">
					<el-input type="password" :placeholder="$t('PASSWORD')" v-model="form.password"></el-input>
				</el-form-item>
			</el-col>

			<el-col :xs="24" :sm="12">
				<el-form-item :label="$t('PASSWORD_REPEAT')" prop="password_repeat">
					<el-input type="password" :placeholder="$t('PASSWORD_REPEAT')" v-model="form.password_repeat"></el-input>
				</el-form-item>
			</el-col>
		</el-row>

		<el-row>
			<el-col :xs="24">
				<el-form-item class="text-left" prop="terms_of_service">
					<el-checkbox v-model="form.terms_of_service" class="el-checkbox--nowrap">{{ $t('TEXT_ACCEPT_TERMS_OF_SERVICE') }}</el-checkbox>
					<a href="http://4-check.com/agb/" target="_blank" class="m-l-1 el-button el-button--text">
                        ({{ $t('TERMS_OF_SERVICE') }})
                    </a>
				</el-form-item>
			</el-col>
		</el-row>

		<el-row>
			<el-col :xs="24">
				<el-form-item>
					<!-- <el-button
                        type="primary"
                        class="w-100"
                        native-type="submit"
                        :loading="loading"
                        :disabled="form.id">
                        {{ $t('REGISTER') }}
                    </el-button> -->
                    <el-button
                        type="primary"
                        class="w-100"
                        native-type="submit"
                        :loading="loading"
                        v-if="!form.id">
                        {{ $t('REGISTER') }}
                    </el-button>
				</el-form-item>
			</el-col>
		</el-row>

	</el-form>
</template>

<script>
import { Phone } from '@/shared/classes/Phone';
import { User } from '@/shared/classes/User';
import { getTimezone, getLocale } from '@/services/browser';
import commonMixins from '@/shared/mixins/common';

let storeUserPhoneInfo = {};

export default {
	name: 'FormRegister',

	mixins: [commonMixins],

	props: {
		data: {
			type: Object,
			required: false,
			default: function() {
				return {};
			}
		},

		validInvitation: {
			type: Boolean,
			required: false,
			default: false
		}
	},

	data() {
		const checkTermsOfService = (rule, value, callback) => {
			if (!value) {
				callback(new Error(this.$t('PLEASE_FILL_OUT_MANDATORY_FIELDS')));
			} else {
				callback();
			}
		};

		const validatePass = (rule, value, callback) => {
			if (value === '') {
				callback(new Error(this.$t('PLEASE_FILL_OUT_MANDATORY_FIELDS')));
			} else {
				if (this.form.password_repeat !== '') {
					this.$refs['form-register'].validateField('password_repeat');
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
				company: {
					name: '',
					sector: ''
				},
				email: '',
				firstName: '',
				gender: 'male',
				lastName: '',
				locale: '',
				phone: {},
				password: '',
				password_repeat: '',
				terms_of_service: false,
				timezone: '',
				userNumber: {},
				nationalNumber: {}
			},
			loading: false,
			rules: {
				company: {
					name: [{ required: true, message: this.$t('PLEASE_FILL_OUT_MANDATORY_FIELDS'), trigger: 'blur' }],
					sector: [{ required: true, message: this.$t('PLEASE_FILL_OUT_MANDATORY_FIELDS'), trigger: 'blur' }]
				},

				email: [
					{ required: true, message: this.$t('PLEASE_FILL_OUT_MANDATORY_FIELDS'), trigger: 'blur' },
					{ type: 'email', message: this.$t('PLEASE_ENTER_VALID_EMAIL'), trigger: 'blur' }
				],
				password: [
					{ required: true, message: this.$t('PLEASE_FILL_OUT_MANDATORY_FIELDS'), trigger: 'blur' },
					{ min: 6, message: this.$t('PASSWORD_TOO_SHORT'), trigger: 'blur' },
					{ validator: validatePass, trigger: 'blur' }
				],
				password_repeat: [
					{ required: true, message: this.$t('PLEASE_FILL_OUT_MANDATORY_FIELDS'), trigger: 'blur' },
					{ validator: validatePass2, trigger: 'blur' }
				],
				phone: {
					nationalNumber: [
						{ required: true, message: this.$t('PLEASE_FILL_OUT_MANDATORY_FIELDS'), trigger: 'blur' }
					]
				},
				terms_of_service: [
					{ required: true, message: this.$t('PLEASE_FILL_OUT_MANDATORY_FIELDS'), trigger: 'change' },
					{ validator: checkTermsOfService, trigger: 'change' }
				]
			},
			options: {
				gender: [
					{
						id: 1,
						name: this.$t('MRS'),
						value: 'female'
					},
					{
						id: 2,
						name: this.$t('MR'),
						value: 'male'
					}
				],
				sectors: [
					{
						id: 1,
						name: this.$t('CONSTRUCTION_INDUSTRY'),
						value: 'building'
					},
					{
						id: 2,
						name: this.$t('FOOD_INDUSTRY'),
						value: 'food'
					},
					{
						id: 3,
						name: this.$t('HEALTH_AND_CARE'),
						value: 'health'
					},
					{
						id: 4,
						name: this.$t('TRANSPORT'),
						value: 'transport'
					},
					{
						id: 5,
						name: this.$t('INDUSTRY'),
						value: 'industry'
					},
					{
						id: 6,
						name: this.$t('CLEANING'),
						value: 'cleaning'
					},
					{
						id: 7,
						name: this.$t('HOTEL_INDUSTRY_GASTRONOMY'),
						value: 'catering'
					},
					{
						id: 8,
						name: this.$t('OTHERS'),
						value: 'misc'
					}
				]
			}
		};
	},

	mounted() {
		this.init();
	},

	methods: {
		doRegister() {
			this.loading = true;

			const USER = new User(this.form);
			storeUserPhoneInfo['cCode'] = storeUserPhoneInfo['cCode'].replace('+', '');
			this.form.phone['countryCode'] = storeUserPhoneInfo['cCode'];
			this.form.phone['nationalNumber'] = storeUserPhoneInfo['nationalNumber'];
			this.form.phone['type'] = 'work';
			
			this.$store
				.dispatch('users/CREATE_USER', USER)
				.then(result => {
					this.loading = false;

					this.$emit('register', [result, this.form]);

					if (result.status === 201) {
						this.handleSuccessRegistration(result);
					} else {
						this.handleErrors(result);
					}
				})
				.catch(err => {
					this.loading = false;
					this.$emit('register', [err, this.form]);
					this.handleErrors(err);
				});
		},

		doRegisterUser() {
			this.loading = true;
			storeUserPhoneInfo['cCode'] = storeUserPhoneInfo['cCode'].replace('+', '');
			this.form.phone['countryCode'] = storeUserPhoneInfo['cCode'];
			this.form.phone['nationalNumber'] = storeUserPhoneInfo['nationalNumber'];
			this.form.phone['type'] = 'work';
			
			const USER = new User(this.form);
			const DATA = {
				data: new User(this.form),
				token: this.data.token
			};

			USER.token = this.data.token;

			this.$store
				.dispatch('invitations/CREATE_USER', DATA)
				.then(result => {
					this.loading = false;

					if (result.status === 201) {
						this.handleSuccessRegistration(result);
					} else {
						this.handleErrors(result);
					}
				})
				.catch(err => {
					this.loading = false;
				});
		},

		init() {
			this.form.phone = new Phone({
				countryCode: '49',
				nationalNumber: null,
				type: 'work'
			});

			this.form.timezone = getTimezone();
			this.form.locale = getLocale();

			if (this.data) {
				this.form.email = this.data.email;
				this.form.company.name = this.data.companyName;
			}

			this.handleDataDuringRegistration();
		},

		/**
		 * If user enter data during registration fill out all fields with this data
		 */
		handleDataDuringRegistration() {
			if (this.$session.get('user')) {
				const USER = this.$session.get('user');
				this.form = Object.assign({}, this.form, USER);
				this.$store.commit('SET_USER', USER);
			}

			if (this.$session.get('company')) {
				const COMPANY = this.$session.get('company');
				this.form.company = Object.assign({}, this.form.company, COMPANY);
				this.$store.commit('SET_USER_COMPANY', COMPANY);
			}
		},

		handleSuccessRegistration(result) {
			console.log('handleSuccessRegistration', { result });

			// Create user in store
			this.$store.commit('SET_USER', result.data.data);
			this.$store.commit('SET_USER_PASSWORD', { password: this.form.password });

			// Only for user invitation
			if (this.validInvitation) {
				this.$q.notify({
					message: this.$t('REGISTRATION_SUCCESSFULL'),
					type: 'positive'
				});

				// Redirect to login page
				setTimeout(() => {
					this.$router.push({ path: '/login' });
				}, 300);
			}
		},

		onSubmit() {
			console.log('onSubmit');

			this.$refs['form-register'].validate(valid => {
				if (valid) {
					if (this.validInvitation) {
						this.doRegisterUser();
					} else {
						this.doRegister();
					}
				} else {
					return false;
				}
			});
		},

		resetForm(formName) {
			this.$refs[formName].resetFields();
		},

		onUpdate(payload){
			// console.log(this.form);
			this.form.phone.nationalNumber = payload.nationalNumber;
			storeUserPhoneInfo = payload;
			storeUserPhoneInfo['cCode'] = payload['formatInternational'] != undefined ? payload['formatInternational'].split(' ')[0] : "0";
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
.register__form {
	.el-form-item {
		&__label {
			display: block;
			float: none;
			text-align: left;
		}
		&__content {
			.el-select {
				width: 100%;
			}
		}
	}
}
label[for="VuePhoneNumberInput_country_selector"], label[for="VuePhoneNumberInput_phone_number"]{
	top:-7px!important;
}
.country-selector-arrow{
	top: calc(50% - 18px)!important;
}
</style>
