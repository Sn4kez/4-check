<!--
@component:         ElementLocation
@environment:       Hyprid
@description:       This component is used to build a checklist as well as to create an audit.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-09-13
@modified:          2018-10-28
-->
<template>
    <div class="checklist__element-participant" :class="{'p-1': audit.id}">
        <!-- +++++++++++++++++++ CHECKLIST +++++++++++++++++++ -->
        <div v-if="!audit.id">
            <el-row :gutter="20">
                <el-col :xs="24" :sm="12">
                    <q-field class="q-field--overflow">
                        <q-radio class="m-b-half" v-model="form.fixed" :val="1" :label="$t('ASSIGN_FIXED_LOCATION')" /> <br>
                        <q-radio v-model="form.fixed" :val="0" :label="$t('CHOOSE_LOCATION_DURING_AUDIT')" />
                    </q-field>
                </el-col>

                <el-col :xs="24" :sm="12" v-if="form.fixed">
                    <q-field>
                        <q-search
                            v-model="selectedChecklistLocation"
                            :debounce="600"
                            clearable
                            icon="place"
                            :placeholder="$t('LOCATION')"
                            :stack-label="$t('ASSIGN_LOCATION')"
                            ref="searchString"
                            @clear="selectedChecklistLocation = ''">
                            <q-autocomplete
                                @search="searchLocation"
                                @selected="selectLocation"
                                :value-field="v => `${v.name} - ${v.street} ${v.streetNumber} `" />
                        </q-search>
                    </q-field>

                    <q-btn
                        color="primary"
                        flat
                        :label="$t('CREATE_LOCATION')"
                        @click="onBtnClickCreateLocation">
                    </q-btn>
                </el-col>
            </el-row>
        </div>

        <!-- +++++++++++++++++++ AUDIT +++++++++++++++++++ -->
        <div v-if="audit.id">
            <el-row :gutter="20">
                <el-col :xs="24">
                    <div v-if="data.object.fixed">
                        <p>
                            {{location.name}} <br>
                            <span class="color-gray font--small">{{location.description}}</span>
                        </p>
                        <address>
                            <p class="m-b-small">
                                {{location.street}} {{location.streetNumber}}
                            </p>
                            <p class="m-t-0 m-b-0">
                                {{location.postalCode}} {{location.city}}
                            </p>
                        </address>
                    </div>

                    <q-field class="m-b-0" v-if="!data.object.fixed">
                        <q-search
                            v-model="selectedLocation"
                            :debounce="600"
                            clearable
                            icon="place"
                            :placeholder="$t('LOCATION')"
                            :stack-label="$t('ASSIGN_LOCATION')"
                            :readonly="!isDraft"
                            ref="searchString"
                            @clear="selectedLocation = ''">
                            <q-autocomplete
                                @search="searchLocation"
                                @selected="selectLocation"
                                :value-field="v => `${v.name} - ${v.street} ${v.streetNumber} `" />
                        </q-search>
                    </q-field>
                </el-col>
            </el-row>
        </div>
    </div>
</template>

<script>
import { Check } from '@/shared/classes/Check';
import { LocationExtension } from '@/shared/classes/Extension';
import auditMixins from '@/shared/mixins/audits';
import commonMixin from '@/shared/mixins/common';
import locationsMixin from '@/shared/mixins/locations';

export default {
	name: 'ElementLocation',

	mixins: [auditMixins, commonMixin, locationsMixin],

	props: {
		audit: {
			type: Object,
			required: false,
			default: function() {
				return {};
			}
		},

		data: {
			type: Object,
			required: true
		},

		open: {
			type: Boolean,
			required: false,
			default: false
		}
	},

	computed: {
		company() {
			return this.$store.state.user.company;
		},

		fixed() {
			return this.form.fixed;
		},

		isDeviceGreaterSM() {
			return this.$store.getters.IS_DEVICE_GREATER_SM;
		},

		isDraft() {
			if (!this.audit.id) {
				return false;
			}

			return this.getAuditStateById(this.audit.state).name === 'draft';
		},

		locationOptions() {
			return this.$store.getters['locations/locationOptions'];
		}
	},

	data() {
		return {
			form: {
				fixed: 1,
				locationId: null
			},
			selectedChecklistLocation: '',
			selectedLocation: '',
			loading: false,
			location: {}
		};
	},

	mounted() {
		// this.init();
	},

	methods: {
		init() {
			console.log('inint location', this.data);
			this.form = Object.assign({}, this.data.object);
			this.form.fixed = this.data.object.fixed ? (this.form.fixed = 1) : 0;

			if (this.data.object.object) {
				this.form.locationId = this.data.object.object.locationId;
			} else if (this.data.value) {
				this.form.locationId = this.data.value.locationId;
			} else if (this.data.object.locationId) {
				this.form.locationId = this.data.object.locationId;
				this.selectedLocation = this.data.object.location.name;
				this.selectedChecklistLocation = this.data.object.location.name;
				this.locationId = this.data.object.location;
			}

			if (!this.locationOptions.length && !this.data.object.location) {
				this.$store.dispatch('locations/GET_LOCATIONS', { id: this.company.id }).then(response => {
					// Get fixed location in audit
					if (this.data.object.fixed) {
						this.location = this.getLocationById(this.data.object.locationId);
						if (this.form.locationId) {
							// this.selectedLocation = this.location.name;
						}
					} else if (this.form.locationId) {
						this.location = this.getLocationById(this.form.locationId);
						this.selectedLocation = this.location.name;
					}
				});
			} else {
				console.log('loc', this.locationOptions);
				// Get fixed location in audit
				if (this.data.object.fixed) {
					this.location = this.getLocationById(this.data.object.locationId);
					console.log('loc 2', this.location, this.data.object.locationId);
					if (this.form.locationId) {
						this.selectedLocation = this.location.name;
					}
				} else if (this.form.locationId) {
					this.location = this.getLocationById(this.form.locationId);
					this.selectedLocation = this.location.name;
				}
			}

			// Prefill location in audit
			if (_.isObject(this.data.value)) {
				this.form.value = this.data.value.locationId;
			}
		},

		doSubmit(CHECK) {
			this.loading = true;
			this.doUpdateCheck(CHECK)
				.then(response => {
					this.loading = false;
					if (response.status === 204) {
						console.log('doUpdateCheck', response);
					} else {
						this.handleErrors(response);
					}
				})
				.catch(err => {
					this.loading = false;
					this.handleErrors(err);
					console.log('doUpdateCheck err', err);
				});
		},

		doUpdateExtension(EXTENSION) {
			return this.$store
				.dispatch('extensions/UPDATE_EXTENSION', EXTENSION)
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		onBlurTextInput() {
			const CHECK = new Check(this.data);

			this.doSubmit(CHECK);
		},

		onBtnClickCreateLocation() {
			if (this.isDeviceGreaterSM) {
				this.$store.commit('OPEN_DIALOG', {
					title: this.$t('CREATE_LOCATION'),
					loadComponent: 'Form/FormEditLocation',
					width: '50%'
				});
			} else {
				this.$store.commit('OPEN_MODAL', {
					title: this.$t('CREATE_LOCATION'),
					loadComponent: 'Form/FormEditLocation',
					maximized: true
				});
			}
		},

		onChangeInput(value) {
			const CHECK = new Check(this.data);
			CHECK.value = value;

			this.doSubmit(CHECK);
		},

		onChecklistChangeInput(value) {
			const EXTENSION = new LocationExtension(this.data);
			EXTENSION.data = Object.assign({}, this.form);

			this.doUpdateExtension(EXTENSION);
		},

		selectLocation(item) {
			this.form.locationId = item.id;

			if (this.audit.id) {
				this.onChangeInput(item.id);
			} else {
				this.onChecklistChangeInput(item.id);
			}
		}
	},

	watch: {
		fixed(newVale, oldValue) {
			if (!newVale && !this.audit.id) {
				const EXTENSION = new LocationExtension(this.data);
				EXTENSION.data = Object.assign({}, this.form);

				this.doUpdateExtension(EXTENSION);
			}
		},

		open(newVale, oldValue) {
			if (newVale) {
				this.init();
			}
		}
	}
};
</script>
