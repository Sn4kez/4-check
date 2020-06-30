<!--
@component:         FormFilterBarAudits
@environment:       Hyprid
@description:       Filter audits and make requests to the backend.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-09-27
@modified:          2018-10-05
-->
<template>
    <el-form ref="FormFilterBarAudits" class="form-filter-bar">

        <div class="flex wrap a-right bg-color-lighter-gray p-t-1">
            <div>
                <q-field class="m-b-1 q-field--bg-white">
                    <q-select
                        v-model="form.state"
                        :stack-label="$t('STATE')"
                        radio
                        clearable
                        :options="auditStateOptions"
                        ref="state"
                        @input="onChangeInput" />
                </q-field>
            </div>
            <div>
                <q-field class="m-b-1 q-field--bg-white">
                    <q-datetime
                        :stack-label="$t('FROM')"
                        v-model="form.start"
                        minimal
                        type="date"
                        clearable
                        ref="start"
                        @input="onChangeInput" />
                </q-field>
            </div>
            <div>
                <q-field class="m-b-1 q-field--bg-white">
                    <q-datetime
                        :stack-label="$t('TO')"
                        v-model="form.end"
                        minimal
                        type="date"
                        clearable
                        ref="end"
                        @input="onChangeInput" />
                </q-field>
            </div>
            <div>
                <q-field class="m-b-1 q-field--bg-white">
                    <q-select
                        v-model="form.user"
                        :stack-label="$t('SELECT_USER')"
                        radio
                        clearable
                        :options="usersOptions"
                        ref="user"
                        @input="onChangeInput" />
                </q-field>
            </div>
            <div>
                <q-field class="m-b-1 q-field--bg-white">
                    <q-select
                        v-model="form.location"
                        :stack-label="$t('SELECT_LOCATION')"
                        radio
                        clearable
                        :options="locationOptions"
                        ref="location"
                        @input="onChangeInput" />
                </q-field>
            </div>
            <div class="text-center">
                <q-btn
                    color="primary"
                    no-ripple
                    :label="$t('APPLY_FILTER')"
                    class="color-dark"
                    @click="onApplyFilter()">
                </q-btn>
            </div>
            <div class="text-center">
                <q-btn
                    flat
                    no-ripple
                    :label="$t('RESET_FILTER')"
                    class="color-dark"
                    @click="resetForm()">
                </q-btn>
            </div>
        </div>

    </el-form>
</template>

<script>
export default {
	name: 'FormFilterBarAudits',

	props: {
		data: {
			type: Object,
			required: false,
			default: () => {
				return {};
			}
		}
	},

	computed: {
		auditStateOptions() {

			let astates = this.$store.getters['audits/auditStates'];


			let engGerTranslation = {
				"finished": "Abgeschlossen",
				"approved": "Genehmigt",
				"sync needed": "Synchronisation erforderlich",
				"draft": "Entwurf",
				"awaiting approval": "Genehmigung ausstehend"
			};


			let toSend = []

			let locale = this.$store.state.user.data.locale;

            if(locale.indexOf('de') != -1){
                astates.map((item) => {
                    let temp = {}
                    temp['label'] = engGerTranslation[item['name']];
                    temp['value'] = item['id'];
                    toSend.push(temp);
                })
            }else{
                astates.map((item) => {
                    let temp = {}
                    temp['label'] = item['name'];
                    temp['value'] = item['id'];
                    toSend.push(temp);
                })
            }

            return toSend;
		},

		dateStartToUnix() {
			return Math.floor(new Date(this.form.start).getTime() / 1000);
		},

		dateEndToUnix() {
			return Math.floor(new Date(this.form.end).getTime() / 1000);
		},

		isDeviceGreaterSM() {
			return this.$store.getters.IS_DEVICE_GREATER_SM;
		},

		user() {
			return this.$store.state.user.data;
		},

		usersOptions() {
			return this.$store.getters['users/usersOptions'];
		},

		locationOptions() {
			return this.$store.getters['locations/locationOptions'];
		}
	},

	data() {
		return {
			form: {
				state: '',
				user: '',
				location: '',
				checklist: '',
				start: null,
				end: null
			}
		};
	},

	mounted() {
		console.log('FormFilterBarAudits mounted');
		this.init();
	},

	methods: {
		init() {
			this.form = Object.assign({}, this.form, this.data);
		},

		onApplyFilter() {
			this.$emit('refresh');
		},

		onChangeInput(value) {
			console.log('onChangeInput', value);

			const date = {
				start: this.dateStartToUnix,
				end: this.dateEndToUnix
			};

			const data = Object.assign({}, this.form, date);

			this.$store.commit('audits/SET_FILTER', data);
		},

		resetForm() {
			_.forEach(this.$refs, item => {
				// Reset only input fields with clear method
				if (typeof item.clear === 'function') {
					item.clear();
					this.onApplyFilter();
				}
			});
		}
	}
};
</script>

<style lang="scss">
.form-filter-bar {
	.flex {
		> * {
			width: calc((100% / 2) - 6%);
			margin: 0 3%;

			@media screen and (min-width: $screen-sm) {
				width: calc((100% / 4) - 2%);
				margin: 0 1%;
			}

			@media screen and (min-width: $screen-lg) {
				width: calc((100% / 6) - 1%);
				margin: 0 0.5%;
			}
		}
	}
}
</style>
