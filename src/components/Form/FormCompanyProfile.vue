<!--
@component:         FormCompanyProfile
@environment:       Hyprid
@description:       This component handle the company address information.
                    Also the component is used during registration process and emit form to the eventbus.

@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-07-29
@modified:          2018-10-04
-->
<template>
    <div class="form-company-profile">
        <!-- First row -->
        <el-row :gutter="20">
            <el-col :xs="24" :md="8">
                <q-field
                    class="m-b-1"
                    :error="$v.form.company.name.$error"
                    :error-label="$t('PLEASE_FILL_OUT_ALL_REQUIRED_FIELDS')">
                    <q-input
                        v-model.trim="$v.form.company.name.$model"
                        :float-label="$t('COMPANY')"
                        @input="onInput">
                    </q-input>
                </q-field>
            </el-col>

            <el-col :xs="24" :md="8">
                <q-field
                    class="m-b-1"
                    :error="$v.form.address.line1.$error"
                    :error-label="$t('PLEASE_FILL_OUT_ALL_REQUIRED_FIELDS')">
                    <q-input
                        v-model.trim="$v.form.address.line1.$model"
                        :float-label="$t('STREET')"
                        @input="onInput">
                    </q-input>
                </q-field>
            </el-col>

            <el-col :xs="24" :md="8">
                <q-field class="m-b-1"
                    :error="$v.form.address.line2.$error"
                    :error-label="$t('PLEASE_FILL_OUT_ALL_REQUIRED_FIELDS')">
                    <q-input
                        v-model.trim="$v.form.address.line2.$model"
                        :float-label="$t('NUMBER')"
                        @input="onInput">
                    </q-input>
                </q-field>
            </el-col>
        </el-row>
        <!-- Second row -->
        <el-row :gutter="20">
            <el-col :xs="24" :md="8">
                <q-field class="m-b-1"
                    :error="$v.form.address.postalCode.$error"
                    :error-label="$t('PLEASE_FILL_OUT_ALL_REQUIRED_FIELDS')">
                    <q-input
                        v-model.trim="$v.form.address.postalCode.$model"
                        :float-label="$t('POSTAL_CODE')"
                        @input="onInput">
                    </q-input>
                </q-field>
            </el-col>

            <el-col :xs="24" :md="8">
                <q-field class="m-b-1"
                    :error="$v.form.address.city.$error"
                    :error-label="$t('PLEASE_FILL_OUT_ALL_REQUIRED_FIELDS')">
                    <q-input
                        v-model.trim="$v.form.address.city.$model"
                        :float-label="$t('CITY')"
                        @input="onInput">
                    </q-input>
                </q-field>
            </el-col>

            <el-col :xs="24" :md="8">
                <q-field class="m-b-1"
                    :error="$v.form.address.country.$error"
                    :error-label="$t('PLEASE_FILL_OUT_ALL_REQUIRED_FIELDS')">
                    <!-- <q-select
                        v-model.trim="$v.form.address.country.$model"
                        :float-label="$t('COUNTRY')"
                        @input="onInput"
                        :options="countryOptions" /> -->
                    <q-input
                        v-model.trim="$v.form.address.country.$model"
                        :float-label="$t('COUNTRY')">
                            <q-autocomplete
                                @search="searchCountry"
                                @selected="selectCountry"
                                :min-characters="2"
                                value-field="label"
                             />
                    </q-input>
                </q-field>
            </el-col>
        </el-row>
        <!-- Third row -->
        <el-row class="m-t-1" v-if="showSubmitButton">
            <el-col :xs="24" class="text-right">
                <q-btn
                    color="primary"
                    :loading="loading"
                    @click="onSubmit"
                    class="w-100--sm"
                    no-ripple
                    :label="$t('SAVE')">
                </q-btn>
            </el-col>
        </el-row>

    </div>
</template>

<script>
import { Address } from '@/shared/classes/Address';
import axios from 'axios';
import { cloneDeep } from 'lodash';
import { required, email, minLength } from 'vuelidate/lib/validators';
import countriesMixin from '@/shared/mixins/countries';
import { getCountries } from '@/services/country';

export default {
	name: 'FormCompanyProfile',

	mixins: [countriesMixin],

	props: {
		data: {
			type: Object,
			required: true
		},

		addresses: {
			type: Array,
			required: true
		},

		addressType: {
			type: String,
			required: false,
			default: 'postal'
		},

		showSubmitButton: {
			type: Boolean,
			required: false,
			default: true
		}
	},

	data() {
		return {
			countryOptions: [
				{
					label: this.$t('GERMANY'),
					value: 'de'
				},
				{
					label: this.$t('USA'),
					value: 'us'
				}
			],
			form: {
				company: {},
				address: {}
			},
			loading: false,
			selectedCountry: null,
			countries: getCountries()
		};
	},

	validations: {
		form: {
			company: {
				name: { required }
			},
			address: {
				city: { required },
				country: { required },
				postalCode: { required },
				line1: { required },
				line2: { required }
			}
		}
	},

	mounted() {
		this.init();
	},

	methods: {
		doSave() {
			this.loading = true;

			axios
				.all([this.saveAddress(), this.saveCompany()])
				.then(
					axios.spread((...results) => {
						this.$q.notify({
							message: this.$t('SAVE_SUCCESS'),
							type: 'positive'
						});
						this.loading = false;
					})
				)
				.catch(err => {
					this.$q.notify({
						message: this.$t('ERROR'),
						type: 'negative'
					});
					this.loading = false;
				});
		},

		doValidate() {
			this.$v.$touch();
			this.$emit('validation', this.$v.$invalid);
			console.log('doValidate', this.form, this.$v.$invalid);
		},

		init() {
			if (this.addresses.length) {
				// ToDo: Keep in mind, that a company can have both a billing and a postal address
				this.form.address = Object.assign({}, this.addresses[0]);
				let countryFromDb = this.form.address.country;
				this.form.address.country = this.getCountryByValue(countryFromDb).label;
			}
			this.form.company = _.cloneDeep(this.data);


			this.registerEvents();
		},

		onInput(value) {
			this.$emit('input', this.form);
		},

		onSubmit() {
			this.$v.$touch();
			console.log('onsubmut', this.form, this.$v.$invalid);

			if (this.$v.$invalid) {
				return false;
			} else {
				this.doSave();
			}
		},

		registerEvents() {
			this.$eventbus.$on('form-company-profile:validate', () => {
				this.doValidate();
			});
			this.$eventbus.$on('form-company-profile:submit', () => {
				this.onSubmit();
			});
		},

		saveAddress() {
			const ADDRESS = new Address(this.form.address);
			ADDRESS.type = this.addressType;
			let country = ADDRESS.country;
			let countryCode = '';
			let countryCodeArr = this.countries.filter(item => item.label == country);
			if(countryCodeArr.length > 0){
				countryCode = countryCodeArr[0]['value'];
			}else{
				this.$q.notify({
					message: "Please select country from dropdown.",
					type: 'negative'
				});
				return false;
			}
			
			ADDRESS.country = countryCode;

			console.log(ADDRESS);

			if (this.addresses.length) {
				// Update Address
				return this.$store.dispatch('addresses/UPDATE_ADDRESS', ADDRESS).then(response => {
					return response;
				});
			} else {
				// Create new address
				return this.$store
					.dispatch('companies/CREATE_ADDRESS', {
						id: this.data.id,
						data: ADDRESS
					})
					.then(response => {
						return response;
					})
					.catch(err => {
						return err;
					});
			}
		},

		saveCompany() {
			return this.$store
				.dispatch('companies/UPDATE_COMPANY', this.form.company)
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		unregisterEvents() {
			this.$eventbus.$off('form-company-profile:validate');
			this.$eventbus.$off('form-company-profile:submit');
		},
		searchCountry(terms, done) {
			if(terms.length > 0){
				const searchString = terms.toUpperCase();
				let result = [];

				result = _.filter(this.countries, country => {
					const name = country.label.toUpperCase();
					return name.includes(searchString);
				});

				done(result);
			}else{
				done();
			}
		},

		selectCountry(item) {
			this.form.country = item.value;
		}
	},

	destroyed() {
		this.unregisterEvents();
	},

	watch: {
		addresses(newValue, oldValue) {
			this.init();
		},

		data(newValue, oldValue) {
			this.init();
		}
	}
};
</script>

<style lang="scss">
</style>
