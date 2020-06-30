<!--
@component:         FormEditLocation
@description:       Form to create and edit task
                    There are more attributes available but we start with a smaller set.
                    In further development only remove the comments in markup and javascript.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-07-09
@modified:          2018-10-18
-->
<template>
    <el-form v-loading="loading">

        <el-row :gutter="20">
            <el-col :xs="24" :md="12">
                <q-field class="m-b-1"
                    :error="$v.form.type.$error"
                    :error-label="$t('PLEASE_FILL_OUT_ALL_REQUIRED_FIELDS')">
                    <q-select
                        v-model.trim="$v.form.type.$model"
                        :float-label="$t('TYPE')"
                        :options="locationTypes"
                        clearable />
                </q-field>
            </el-col>

            <el-col :xs="24" :md="12">
                <q-btn
                    flat
                    no-ripple
                    :label="$t('CREATE_ADDITIONAL_TYPE')"
                    @click="promptLocationType">
                </q-btn>
            </el-col>
        </el-row>

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

        <el-row :gutter="20">
            <el-col :xs="24">
                <q-field class="m-b-1">
                    <q-input
                        v-model.trim="form.description"
                        :float-label="$t('DESCRIPTION')"
                        type="textarea">
                    </q-input>
                </q-field>
            </el-col>
        </el-row>

        <el-row :gutter="20" class="m-t-1">
            <el-col :xs="24" :md="18">
                <q-field class="m-b-1">
                    <q-input
                        v-model.trim="form.street"
                        :float-label="$t('STREET')">
                    </q-input>
                </q-field>
            </el-col>

            <el-col :xs="24" :md="6">
                <q-field class="m-b-1">
                    <q-input
                        v-model.trim="form.streetNumber"
                        :float-label="$t('NUMBER')">
                    </q-input>
                </q-field>
            </el-col>
        </el-row>

        <el-row :gutter="20">
            <el-col :xs="24" :sm="4">
                <q-field class="m-b-1">
                    <q-input
                        v-model.trim="form.postalCode"
                        :float-label="$t('POSTAL_CODE')">
                    </q-input>
                </q-field>
            </el-col>

            <el-col :xs="24" :sm="10">
                <q-field class="m-b-1">
                    <q-input
                        v-model.trim="form.city"
                        :float-label="$t('CITY')">
                    </q-input>
                </q-field>
            </el-col>

            <el-col :xs="24" :sm="10">
                <q-field class="m-b-1">
                    <q-input
                        v-model.trim="selectedCountry"
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

        <el-row :gutter="20">

        </el-row>

        <el-row :gutter="20" class="m-t-1" v-if="edit">
            <el-col :xs="24" :md="12">
                <q-field class="q-field--overflow m-b-1">
                    <q-toggle
                        v-model="form.state"
                        :true-value="'active'"
                        :false-value="'inactive'">
                        <span class="m-l-1">
                            <span v-if="form.state === 'active'">{{$t('ACTIVE')}}</span>
                            <span v-else>{{$t('INACTIVE')}}</span>
                        </span>
                    </q-toggle>
                </q-field>
            </el-col>
        </el-row>

        <!-- Additional Settings -->
        <el-collapse class="el-collapse--transparent m-t-2" v-if="this.locations.length">
            <el-collapse-item name="1">
                <template slot="title">
                    {{ $t('ADVANCED_SETTINGS') }}
                </template>
                <el-row>
                    <el-col :xs="24">
                        <h3 class="headline font--regular-plus text-uppercase">{{$t('LINK_WITH')}}</h3>
                        <p>
                            Verknüpfen Sie Standorte, um Hierarchien und Abhängigkeiten darzustellen.
                        </p>
                        <p class="m-t-1">
                            <strong class="color-gray">Beispiel:</strong><br>
                            Ein Raum kann mit einer Etage oder einem Gebäude verknüpft werden und ist diesem als kleine
                            Einheit untergeordnet.
                        </p>
                    </el-col>
                </el-row>

                <el-row :gutter="20" class="m-t-1 flex a-center">
                    <el-col :xs="24" :sm="8">
                        <q-field class="m-b-1">
                            <q-input disable
                                v-model="form.name"
                                :float-label="$t('NAME')">
                            </q-input>
                        </q-field>
                    </el-col>
                    <el-col :xs="24" :sm="5">
                        <p class="text-center">{{ $t('LINK_WITH') }}</p>
                    </el-col>
                    <el-col :xs="24" :sm="11">
                        <q-field class="m-b-1">
                            <q-search
                                v-model="selectedParentLocation"
                                :debounce="600"
                                clearable
                                icon="place"
                                :placeholder="$t('LOCATION')"
                                :stack-label="$t('SEARCH_LOCATIONS')"
                                ref="searchString"
                                @clear="form.parentId = null">
                                <q-autocomplete
                                    @search="searchLocation"
                                    @selected="selectLocation"
                                    :value-field="v => `${v.name} - ${v.street} ${v.streetNumber} `" />
                            </q-search>
                        </q-field>
                    </el-col>
                </el-row>
            </el-collapse-item>
        </el-collapse>


        <!-- Actions -->
        <el-row class="m-t-2">
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
import { required, integer, minLength, maxLength } from 'vuelidate/lib/validators';
import { cloneDeep, forEach } from 'lodash';
import locationsMixin from '@/shared/mixins/locations';
import countriesMixin from '@/shared/mixins/countries';
import { getCountries } from '@/services/country';

export default {
	name: 'FormEditLocation',

	mixins: [countriesMixin, locationsMixin],

	props: {
		data: {
			type: Object,
			required: false
		}
	},

	computed: {
		company() {
			return this.$store.state.user.company;
		},

		edit() {
			return this.data.id ? true : false;
		},

		locations() {
			return this.$store.state.locations.locations;
		},

		locationOptions() {
			return this.$store.getters['locations/locationOptions'];
		},

		locationTypes() {
            let engGerTranslation = {
                "room": "Zimmer",
                "machine": "Maschine",
                "building": "Gebäude",
                "floor": "Etage",
                "Land": "Land",
                "customer": "Kunde",
                "area": "Bereich"
            };

            let toSend = []


			let ltypes = this.$store.getters['locations/locationTypes'];
            
            let locale = this.user.data.locale;
            
            if(locale.indexOf('de') != -1){
                ltypes.map((item) => {
                    let temp = {}
                    temp['label'] = engGerTranslation[item['name']] != undefined ? engGerTranslation[item['name']] : item['name'];
                    temp['value'] = item['value'];
                    toSend.push(temp);
                })
            }else{
                ltypes.map((item) => {
                    let temp = {}
                    temp['label'] = item['name'];
                    temp['value'] = item['value'];
                    toSend.push(temp);
                })
            }

            return toSend
		},

		locationStates() {
			return this.$store.getters['locations/locationStates'];
		}
	},

	data() {
		return {
			countries: getCountries(),
			form: {
				city: null,
				country: null,
				description: null,
				name: '',
				parentId: '',
				postalCode: null,
				province: null,
				state: '',
				street: null,
				streetNumber: null,
				type: ''
			},
			loading: false,
			selectedCountry: null,
			selectedParentLocation: null
		};
	},

	validations: {
		form: {
			name: { required },
			type: { required }
		}
	},

	mounted() {
		this.init();
	},

	methods: {
		doSubmit() {
			let location = {};
			let dispatcherName = 'locations/CREATE_LOCATION';

			if (this.edit) {
				this.form.state = this.getLocationStateByName(this.form.state).id;
				location = new Location(this.form);
				dispatcherName = 'locations/UPDATE_LOCATION';
			} else {
				// Set state to active
				this.form.state = this.getLocationStateByName('active').id;

				location = new Location({
					city: this.form.city,
					company: this.user.company.id,
					country: this.form.country,
					description: this.form.description,
					postalCode: this.form.postalCode,
					street: this.form.street,
					streetNumber: this.form.streetNumber,
					name: this.form.name,
					state: this.form.state,
					type: this.form.type
				});

				if (this.form.parentId) {
					location.parentId = this.form.parentId;
				}
			}

			console.log('doSubmit new location', location);

			this.loading = true;

			this.$store
				.dispatch(dispatcherName, location)
				.then(response => {
					this.loading = false;
                    console.log('CREATE NEW LOCATION: ', response);
					if (response.status === 201 || response.status === 204) {
						this.onCancel();

						this.$q.notify({
							message: this.$t('SAVE_SUCCESS'),
							type: 'positive'
						});

						// Refresh
						this.requestLocations();
					} else {
						this.handleErrors(response);
					}
				})
				.catch(err => {
					this.loading = false;
				});
		},

		init() {
			if (!this.locationTypes.length) {
				this.$store.dispatch('locations/GET_LOCATION_TYPES', { id: this.company.id });
			}

			if (!this.locationStates.length) {
				this.$store.dispatch('locations/GET_LOCATION_STATES', { id: this.company.id });
			}

			if (this.edit) {
				this.form = Object.assign({}, this.data);
				this.form.state = this.getLocationStateById(this.form.state).name;
				this.form.country = this.getCountryByValue(this.form.country).value;

				this.selectedCountry = this.getCountryByValue(this.form.country).label;
				this.selectedParentLocation = this.form.parent;
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

		requestLocations() {
			return this.$store
				.dispatch('locations/GET_LOCATIONS', {
					id: this.company.id
					// filter: {
					// 	name: this.selectedParentLocation
					// }
				})
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		searchCountry(terms, done) {
			const searchString = terms.toUpperCase();
			let result = [];

			result = _.filter(this.countries, country => {
				const name = country.label.toUpperCase();
				return name.includes(searchString);
			});

			done(result);
		},

		selectCountry(item) {
			this.form.country = item.value;
		},

		selectLocation(item) {
			this.form.parentId = item.id;
		}
	}
};
</script>
