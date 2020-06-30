<!--
@component:         AnalysisView
@environment:       Hyprid
@description:       This component handle the visibility for mobile and desktop devices
                    as well as the data storage for child components.

                    ToDo: This file is a mess and need to be extracted in more files.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-09-09
@modified:          2018-10-23
-->
<template>
    <div class="view__analysis main-container">
        <div class="view__analysis-inner flex">
            <!-- +++++++++++++++ ASIDE +++++++++++++++ -->
            <aside class="view__analysis__aside p-r-2 b-r-1">
                <div>
                    <p class="color-gray font--regular-plus m-b-half">{{ $t('SELECT_TIMERANGE') }}</p>
                    <q-datetime-picker
                        class="bg-color-white"
                        v-model="form.startDate"
                        minimal
                        type="date"
                        @input="changeFilter" />

                    <q-datetime-picker
                        class="bg-color-white"
                        v-model="form.endDate"
                        minimal
                        type="date"
                        @input="changeFilter" />
                </div>

                <div class="m-t-1">
                    <q-field class="q-field--bg-white" style="max-width:20rem;">
                        <q-search
                            v-model="form.selectedLocation"
                            :debounce="600"
                            clearable
                            icon="place"
                            :placeholder="$t('LOCATION')"
                            :stack-label="$t('SEARCH_LOCATIONS')"
                            ref="searchString"
                            @clear="form.selectedLocation = ''">
                            <q-autocomplete
                                @search="searchLocation"
                                @selected="selectLocation"
                                :value-field="v => `${v.name} - ${v.street} ${v.streetNumber} `" />
                        </q-search>
                    </q-field>
                </div>

                <!-- <div class="m-t-1 text-center">
                    <q-btn
                        :label="$t('RESET_FILTER')"
                        color="secondary"
                        no-ripple
                        @click="clearFilter">
                    </q-btn>
                </div> -->

                <div class="m-t-3">
                    <q-field class="q-field--bg-white">
                        <q-input
                            v-model="form.text"
                            :stack-label="$t('PLEASE_ENTER_TEXT')">
                        </q-input>
                    </q-field>
                </div>

                <div class="m-t-2">
                    <q-field class="q-field--bg-white">
                        <q-input
                            v-model="form.comment"
                            type="textarea"
                            :stack-label="$t('FREE_TEXT')"
                            :max-height="100"
                            rows="4" />
                    </q-field>
                </div>

                <div class="m-t-3 flex flex-column">
                    <p class="color-gray font--regular-plus m-b-half">{{ $t('SETTINGS') }}</p>
                    <q-checkbox v-model="form.settings.total" :label="$t('SHOW_TOTAL_RESULT')" class="m-t-half" />
                    <q-checkbox v-model="form.settings.focal_point" :label="$t('SHOW_FOCAL_POINT')" class="m-t-half" />
                    <q-checkbox v-model="form.settings.group_rating" :label="$t('SHOW_GROUP_RATING')" class="m-t-half" />
                    <q-checkbox v-model="form.settings.media" :label="$t('SHOW_PICTURES')" class="m-t-half" />
                    <q-checkbox v-model="form.settings.checkpoints" :label="$t('SHOW_CHECKPOINTS')" class="m-t-half" />

                    <div class="m-l-2 flex flex-column" v-if="form.settings.checkpoints">
                        <q-checkbox v-model="form.settings.checkpoint.date" :label="$t('DATE')" class="m-t-half" />
                        <q-checkbox v-model="form.settings.checkpoint.directory" :label="$t('DIRECTORY')" class="m-t-half" />
                        <q-checkbox v-model="form.settings.checkpoint.title" :label="$t('TITLE')" class="m-t-half" />
                        <q-checkbox v-model="form.settings.checkpoint.group" :label="$t('GROUP')" class="m-t-half" />
                        <q-checkbox v-model="form.settings.checkpoint.question" :label="$t('QUESTION')" class="m-t-half" />
                        <q-checkbox v-model="form.settings.checkpoint.notefield" :label="$t('NOTEFIELD')" class="m-t-half" />
                        <q-checkbox v-model="form.settings.checkpoint.actualValue" :label="$t('OPTIMUM_ACTUAL_VALUE')" class="m-t-half" />
                        <q-checkbox v-model="form.settings.checkpoint.factor" :label="$t('FACTOR')" class="m-t-half" />
                        <q-checkbox v-model="form.settings.checkpoint.rating" :label="$t('RATING')" class="m-t-half" />
                    </div>
                </div>
            </aside>

            <!-- +++++++++++++++ MAIN +++++++++++++++ -->
            <main class="view__analysis__main p-l-2--md p-half--sm" v-loading="loading">
                <div class="flex valign-center space-between m-b-1">
                    <p class="color-gray font--regular-plus">{{$t('PREVIEW')}}</p>
                    <div>
                        <q-btn
                            color="primary"
                            no-ripple
                            flat
                            :label="$t('CANCEL')"
                            @click="$router.go(-1)"
                            class="m-r-half">
                        </q-btn>
                        <q-btn
                            color="primary"
                            no-ripple
                            :label="$t('EXPORT')"
                            @click="onExportPDF">
                        </q-btn>
                    </div>
                </div>

                <div class="view__analysis__main-inner p-t-1" id="view__analysis__export-area">
                    <!-- First page -->
                    <section class="analysis__section">
                        <!-- Logo -->
                        <div class="m-b-5" :style="{'text-align': reportPreferences.logoPosition}">
                            <img v-if="corporateIdentity.image"
                                :src="corporateIdentity.image"
                                alt="Logo"
                                class="analysis__section-logo analysis__section-logo--custom" />
                            <img v-else
                                src="/img/4-check-logo.jpg"
                                alt="Logo"
                                class="analysis__section-logo analysis__section-logo--default">
                        </div>

                        <!-- Text -->
                        <h2 class="analysis__section-title" v-if="form.text">{{form.text}}</h2>

                        <!-- Details -->
                        <div class="analysis__section-details">
                            <dl class="flex--md w-100">
                                <dt>{{ $t('DATE') }}</dt>
                                <dd>{{ $d(new Date(exportDate), 'long')}}</dd>
                            </dl>
                            <dl class="flex--md w-100" v-if="form.comment">
                                <dt>{{ $t('COMMENT') }}</dt>
                                <dd>{{ form.comment }}</dd>
                            </dl>
                            <dl class="flex--md w-100" v-if="form.selectedLocation">
                                <dt>{{ $t('LOCATION') }}</dt>
                                <dd>
                                    {{ form.selectedLocation }}
                                </dd>
                            </dl>
                        </div>

                        <!-- Charts -->
                        <div class="analysis__section-charts m-t-4" v-loading="loadingCharts">
                            <!-- Total -->
                            <div class="analysis__section-chart" v-if="form.settings.total">
                                <h3 class="analysis__section-chart__headline">
                                    <strong>{{ $t('OVERALL_EVALUATION') }}:</strong>
                                </h3>
                                <div class="analysis__section-chart--">
                                    <BarChart
                                        :chart-data="charts.chart_one_average_value_of_all_choices"
                                        :options="chartBarOptions">
                                    </BarChart>
                                </div>
                            </div>
                            <!-- Focal points -->
                            <div class="analysis__section-chart" v-if="form.settings.focal_point">
                                <h3 class="analysis__section-chart__headline">
                                    <strong>{{ $t('FOCAL_POINT') }}:</strong>
                                </h3>
                                <div class="analysis__section-chart--">
                                     <PieChart
                                        :chart-data="charts.chart_two_count_of_yes_and_no"
                                        :options="chartOptions">
                                    </PieChart>
                                </div>
                            </div>
                            <!-- Group ratings -->
                            <div class="analysis__section-chart" v-if="form.settings.group_rating">
                                <h3 class="analysis__section-chart__headline">
                                    <strong>{{ $t('GROUP_RATING') }}:</strong>
                                </h3>
                                <div class="analysis__section-chart--">
                                    <BarChart
                                        :chart-data="charts.chart_three_average_values_of_all_choices_grouped_by_section"
                                        :options="chartBarOptions">
                                    </BarChart>
                                </div>
                            </div>
                        </div>

                        <div class="page-num-footer">
                            <div>
                                <p v-if="reportPreferences.showCompanyName == 1">
                                    {{ companyData['name'] }}
                                </p>
                                <p v-if="reportPreferences.showCompanyAddress == 1">
                                    <span v-if="companyAddress && companyAddress.line2">{{ companyAddress.line2 }}</span> <span v-if="companyAddress && companyAddress.line1">{{ companyAddress.line1 }}</span>
                                </p>
                                <p v-if="reportPreferences.showCompanyAddress == 1">
                                    <span v-if="companyAddress && companyAddress.postalCode">{{ companyAddress.postalCode }}</span> <span v-if="companyAddress && companyAddress.city">{{ companyAddress.city }}</span>
                                </p>
                                <p v-if="reportPreferences.text != null">
                                    {{ reportPreferences.text }}
                                </p>
                            </div>
                            <div>
                                <p v-if="reportPreferences.showUsername == 1">
                                    {{ user['firstName'] }}
                                </p>
                                <p v-if="reportPreferences.showExportDate == 1">
                                    Export: {{ currentDate }}
                                </p>
                                <p v-if="reportPreferences.showVersion == 1">
                                    Version: 1.0
                                </p>
                            </div>
                            <div>
                                <p>&nbsp;</p>
                                <p>&nbsp;</p>
                                <p class="pagenum" v-if="reportPreferences.showPageNumbers == 1">
                                    1/{{ getNumPages(transformedResults) + 3 }}
                                </p>
                            </div>
                        </div>

                    </section>

                    <!-- Second page -->
                    <div class="html2pdf__page" v-for="page in getNumPages(transformedResults)" :key="page">
                        <section class="analysis__section" v-loading="loadingCheckpointResults" v-if="form.settings.checkpoints" :data-attr-page="page">
                            <!-- Logo -->
                            <div class="m-b-1" :style="{'text-align': reportPreferences.logoPosition}">
                                <img v-if="corporateIdentity.image"
                                    :src="corporateIdentity.image"
                                    alt="Logo"
                                    class="analysis__section-logo analysis__section-logo--custom" />
                                <img v-else
                                    src="/img/4-check-logo.jpg"
                                    alt="Logo"
                                    class="analysis__section-logo analysis__section-logo--default">
                            </div>
                            <div class="table--overflow" style="min-height:533px;">
                                <table class="w-100 analysis__section-table" cellspacing="0" cellpadding="0" v-if="transformedResults">
                                    <thead style="display:table-header-group;">
                                        <tr>
                                            <th v-if="form.settings.checkpoint.date">{{$t('DATE')}}</th>
                                            <th v-if="form.settings.checkpoint.directory">{{$t('DIRECTORY')}}</th>
                                            <th v-if="form.settings.checkpoint.title">{{$t('TITLE')}}</th>
                                            <th v-if="form.settings.checkpoint.group">{{$t('GROUP')}}</th>
                                            <th v-if="form.settings.checkpoint.notefield">{{$t('COMMENT')}}</th>
                                            <th v-if="form.settings.checkpoint.question">{{$t('QUESTION')}}</th>
                                            <th v-if="form.settings.checkpoint.actualValue" v-html="$t('OPTIMUM_ACTUAL_VALUE')"></th>
                                            <th v-if="form.settings.checkpoint.factor">{{$t('FACTOR')}}</th>
                                            <th v-if="form.settings.checkpoint.rating">{{$t('RATING')}}</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <tr
                                            v-for="row in transformedResults.slice((page-1)*10, page*10)" :key="row.id">
                                            <td v-if="form.settings.checkpoint.date">{{ $d(new Date(row.createdAt), 'short')}}</td>
                                            <td v-if="form.settings.checkpoint.directory">
                                                <ul class="list list--horizontal">
                                                    <li v-for="(item, index) in row.checklistPath" :key="index">
                                                        <span v-if="row.checklistPath.length">{{item}}</span>
                                                        <span v-if="index + 1 < row.checklistPath.length" class="m-l-half">/</span>
                                                    </li>
                                                    <li v-if="!row.checklistPath.length">Home</li>
                                                </ul>
                                            </td>
                                            <td v-if="form.settings.checkpoint.title">{{row.checklistName}}</td>
                                            <td v-if="form.settings.checkpoint.group">
                                                <span v-if="row.section">{{row.section.title}}</span>
                                            </td>
                                            <td v-if="form.settings.checkpoint.notefield">
                                                <span>{{ idNotefieldRel[row.audit_id+row.object.id] }}</span>
                                                
                                            </td>
                                            <td v-if="form.settings.checkpoint.question">{{row.object.prompt}}</td>
                                            <td v-if="form.settings.checkpoint.actualValue">
                                                <span v-if="row.valueType === 'value'">
                                                    <span>{{row.conditions[0].from}} {{$t('TO')}} {{row.conditions[0].to}} {{row.evaluatingScheme.unit}}</span>
                                                    <span> / </span>
                                                    <span>{{row.value.value}} {{row.evaluatingScheme.unit}}</span>
                                                </span>
                                            </td>
                                            <td v-if="form.settings.checkpoint.factor">{{row.object.factor}}</td>
                                            <td v-if="form.settings.checkpoint.rating">
                                                <span v-if="row.score"
                                                    class="rating__circle"
                                                    :style="{'background-color': row.score.color}">
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="page-num-footer">
                                <div>
                                    <p v-if="reportPreferences.showCompanyName == 1">
                                        {{ companyData['name'] }}
                                    </p>
                                    <p v-if="reportPreferences.showCompanyAddress == 1">
                                        <span v-if="companyAddress && companyAddress.line2">{{ companyAddress.line2 }}</span> <span v-if="companyAddress && companyAddress.line1">{{ companyAddress.line1 }}</span>
                                    </p>
                                    <p v-if="reportPreferences.showCompanyAddress == 1">
                                        <span v-if="companyAddress && companyAddress.postalCode">{{ companyAddress.postalCode }}</span> <span v-if="companyAddress && companyAddress.city">{{ companyAddress.city }}</span>
                                    </p>
                                    <p v-if="reportPreferences.text != null">
                                        {{ reportPreferences.text }}
                                    </p>
                                </div>
                                <div>
                                    <p v-if="reportPreferences.showUsername == 1">
                                        {{ user['firstName'] }}
                                    </p>
                                    <p v-if="reportPreferences.showExportDate == 1">
                                        Export: {{ currentDate }}
                                    </p>
                                    <p v-if="reportPreferences.showVersion == 1">
                                        Version: 1.0
                                    </p>
                                </div>
                                <div>
                                    <p>&nbsp;</p>
                                    <p>&nbsp;</p>
                                    <p class="pagenum" v-if="reportPreferences.showPageNumbers == 1">
                                        {{ page + 1 }}/{{ getNumPages(transformedResults) + 3 }}
                                    </p>
                                </div>
                            </div>

                        </section>
                        
                    </div>

                    <!-- <div class="html2pdf__page-break"></div> -->

                    <!-- Third section -->
                    <section class="analysis__section analysis__section-images html2pdf__page" v-if="images.length > 0"
                        v-loading="loadingCheckpointResults">
                        <!-- Logo -->
                        <div class="m-b-1" :style="{'text-align': reportPreferences.logoPosition}">
                            <img v-if="corporateIdentity.image"
                                :src="corporateIdentity.image"
                                alt="Logo"
                                class="analysis__section-logo analysis__section-logo--custom" />
                            <img v-else
                                src="/img/4-check-logo.jpg"
                                alt="Logo"
                                class="analysis__section-logo analysis__section-logo--default">
                        </div>

                        <ul class="list list--horizontal"  style="min-height:533px;">
                            <li v-for="(item, index) in images" :key="index" style="padding:0 20px;">
                                <img v-if="item.extension_type == 'media'" :src="item.base64" alt="" class="analysis__section-images__image">
                                <div v-if="item.extension_type == 'media'">
                                    <span>Datum: {{ $d(new Date(item.created), 'short') }}</span>
                                    <br>
                                    <span>Benutzer: {{ item.user_name }}</span>
                                </div>
                            </li>
                        </ul>

                        <div class="page-num-footer">
                            <div>
                                <p v-if="reportPreferences.showCompanyName == 1">
                                    {{ companyData['name'] }}
                                </p>
                                <p v-if="reportPreferences.showCompanyAddress == 1">
                                    <span v-if="companyAddress && companyAddress.line2">{{ companyAddress.line2 }}</span> <span v-if="companyAddress && companyAddress.line1">{{ companyAddress.line1 }}</span>
                                </p>
                                <p v-if="reportPreferences.showCompanyAddress == 1">
                                    <span v-if="companyAddress && companyAddress.postalCode">{{ companyAddress.postalCode }}</span> <span v-if="companyAddress && companyAddress.city">{{ companyAddress.city }}</span>
                                </p>
                                <p v-if="reportPreferences.text != null">
                                    {{ reportPreferences.text }}
                                </p>
                            </div>
                            <div>
                                <p v-if="reportPreferences.showUsername == 1">
                                    {{ user['firstName'] }}
                                </p>
                                <p v-if="reportPreferences.showExportDate == 1">
                                    Export: {{ currentDate }}
                                </p>
                                <p v-if="reportPreferences.showVersion == 1">
                                    Version: 1.0
                                </p>
                            </div>
                            <div>
                                <p>&nbsp;</p>
                                <p>&nbsp;</p>
                                <p class="pagenum" v-if="reportPreferences.showPageNumbers == 1">
                                    {{ getNumPages(transformedResults) + 2 }}/{{ getNumPages(transformedResults) + 3 }}
                                </p>
                            </div>

                        </div>

                    </section>

                    <!-- Fourth section -->
                    <section class="analysis__section analysis__section-images html2pdf__page" v-if="sigs.length > 0"
                        v-loading="loadingCheckpointResults">
                        <!-- Logo -->
                        <div class="m-b-1" :style="{'text-align': reportPreferences.logoPosition}">
                            <img v-if="corporateIdentity.image"
                                :src="corporateIdentity.image"
                                alt="Logo"
                                class="analysis__section-logo analysis__section-logo--custom" />
                            <img v-else
                                src="/img/4-check-logo.jpg"
                                alt="Logo"
                                class="analysis__section-logo analysis__section-logo--default">
                        </div>

                        <ul class="list list--horizontal"  style="min-height:533px;">
                            <li v-for="(item, index) in sigs" :key="index" style="width:300px">
                                <img v-if="item.extension_type == 'signature'" :src="item.base64" alt="" class="analysis__section-images__image">
                                <div v-if="item.extension_type == 'signature'">
                                    <span>Datum: {{ $d(new Date(item.created), 'short') }}</span>
                                    <br>
                                    <span>Benutzer: {{ item.user_name }}</span>
                                </div>
                            </li>
                        </ul>

                        <div class="page-num-footer">
                            <div>
                                <p v-if="reportPreferences.showCompanyName == 1">
                                    {{ companyData['name'] }}
                                </p>
                                <p v-if="reportPreferences.showCompanyAddress == 1">
                                    <span v-if="companyAddress && companyAddress.line2">{{ companyAddress.line2 }}</span> <span v-if="companyAddress && companyAddress.line1">{{ companyAddress.line1 }}</span>
                                </p>
                                <p v-if="reportPreferences.showCompanyAddress == 1">
                                    <span v-if="companyAddress && companyAddress.postalCode">{{ companyAddress.postalCode }}</span> <span v-if="companyAddress && companyAddress.city">{{ companyAddress.city }}</span>
                                </p>
                                <p v-if="reportPreferences.text != null">
                                    {{ reportPreferences.text }}
                                </p>
                            </div>
                            <div>
                                <p v-if="reportPreferences.showUsername == 1">
                                    {{ user['firstName'] }}
                                </p>
                                <p v-if="reportPreferences.showExportDate == 1">
                                    Export: {{ currentDate }}
                                </p>
                                <p v-if="reportPreferences.showVersion == 1">
                                    Version: 1.0
                                </p>
                            </div>
                            <div>
                                <p>&nbsp;</p>
                                <p>&nbsp;</p>
                                <p class="pagenum" v-if="reportPreferences.showPageNumbers == 1">
                                    {{ getNumPages(transformedResults) + 3 }}/{{ getNumPages(transformedResults) + 3 }}
                                </p>
                            </div>

                        </div>

                    </section>
                </div>
            </main>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import auditMixins from '@/shared/mixins/audits';
import locationMixins from '@/shared/mixins/locations';
import html2pdf from 'html2pdf.js';
import jsPDF from 'jspdf';
import BarChart from '@/views/analysis/BarChart';
import PieChart from '@/views/analysis/PieChart';

export default {
	name: 'AnalysisView',

	mixins: [auditMixins, locationMixins],

	components: {
		BarChart,
		PieChart
	},

	computed: {
		auditStates() {
			return this.$store.state.audits.auditStates;
		},

		baseUrl() {
			return process.env.BASE_URL;
		},

		company() {
			return this.$store.state.user.company;
		},

		companyScoringSchemes() {
			return this.$store.state.companies.scoringschemes;
		},

		corporateIdentity() {
			return this.$store.state.companies.corporateIdentity;
		},

		dateStartToUnix() {
			// return Math.floor(new Date(this.form.startDate).getTime() / 1000);
            return this.form.startDate == null ? 0 : this.formatDate(this.form.startDate);
		},

		dateEndToUnix() {
			// return Math.floor(new Date(this.form.endDate).getTime() / 1000);
            return this.form.endDate == null ? 0 : this.formatDate(this.form.endDate);
		},

		isDeviceGreaterSM() {
			return this.$store.getters.IS_DEVICE_GREATER_SM;
		},

		locationOptions() {
			return this.$store.getters['locations/locationOptions'];
		},

		user() {
			return this.$store.state.user.data;
		},

		users() {
			return this.$store.state.users.users;
		}
	},

	data() {
		return {
			audits: [],
			charts: {
				totalRating: {},
				focalPoint: {},
				groupRating: {},
				chart_one_average_value_of_all_choices: {},
				chart_two_count_of_yes_and_no: {},
				chart_three_average_values_of_all_choices_grouped_by_section: {}
			},
			chartBarOptions: {
				scales: {
					yAxes: [
						{
							ticks: {
								beginAtZero: true
							}
						}
					],
                    xAxes: [{
                        ticks: {
                            autoSkip: false
                        }
                    }]
				}
			},
			chartOptions: {},
			media: [],
            images: [],
            sigs: [],
			transformedResults: [],
			checklist: {},
			checklistId: null,
			directory: {},
			directoryId: null,
            idNotefieldRel: {},
			exportDate: new Date(),
			form: {
				locationId: null,
				selectedLocation: '',
				text: '',
				comment: '',
				startDate: null,
				endDate: null,
				settings: {
					total: true,
					focal_point: true,
					group_rating: true,
					media: true,
					checkpoints: true,
					checkpoint: {
						date: true,
						group: true,
						notefield: false,
						factor: true,
						rating: true,
						question: true,
						directory: true,
						title: true,
						actualValue: true
					}
				}
			},
			loading: false,
			loadingCharts: false,
			loadingCheckpointResults: false,
			showAside: true,
			worker: null,
            reportPreferences: {},
            companyData: {},
            companyAddress: {},
            currentDate: ""
		};
	},

	mounted() {
		this.init();
	},

	methods: {
		changeFilter() {
			const data = {
				start: this.dateStartToUnix,
				end: this.dateEndToUnix,
				checklist: this.checklistId,
				directory: this.directoryId,
				location: this.form.locationId
			};

			this.$store.commit('analytics/SET_FILTER', data);

			this.requestAnalytics();
		},

		clearFilter() {
			this.form.startDate = '';
			this.form.endDate = '';
			this.form.locationId = '';

			const data = {
				start: '',
				end: '',
				checklist: '',
				directory: '',
				location: ''
			};

			this.$store.commit('analytics/SET_FILTER', data);
			this.requestAnalytics();
		},

		init() {
			console.log('AnalysisView mounted');

			if (this.$route.query.checklist) {
				this.checklistId = this.$route.query.checklist;
			}

			if (this.$route.query.directory) {
				this.directoryId = this.$route.query.directory;
			}

            this.currentDate = new Date().toLocaleDateString();

			this.changeFilter();
			this.requestInitalMetaData();
		},

		onChangeDate(value) {
			console.log('onChangeDate', value);

			const date = {
				start: this.dateStartToUnix,
				end: this.dateEndToUnix
			};

			const data = Object.assign({}, this.form, date);

			this.$store.commit('audits/SET_FILTER', data);
		},

		onExportPDF() {
			console.log('onExportPDF');
			this.loading = true;
			const exportArea = document.querySelector('#view__analysis__export-area');
			const opt = {
				margin: 0,
				filename: 'statistic.pdf',
				image: { type: 'jpeg', quality: 0.98 },
				html2canvas: { 
                    dpi: 192,
                    letterRendering: true
                },
				jsPDF: {
					unit: 'in',
					format: 'letter',
					orientation: 'landscape'
				},

			};

			this.worker = html2pdf()
				.from(exportArea)
				.set(opt)
				.toPdf()
                .get('pdf')
				.then(pdf => {
					this.loading = false;
                    /*console.log('ToTal pages: ', pdf.internal.getNumberOfPages())
                    var totalPages = pdf.internal.getNumberOfPages();

  for (i = 1; i <= totalPages; i++) {
    pdf.setPage(i);
    pdf.setFontSize(10);
    pdf.setTextColor(150);
    pdf.text(pdf.internal.pageSize.width - 100, pdf.internal.pageSize.height - 30, 'Page '+i+' of '+totalPages);
  }*/
				})
				.catch(err => {
					this.loading = false;
				})
				.save();
		},

		refreshPage(done) {
			this.requestTasks()
				.then(() => {
					done();
				})
				.catch(() => {
					done();
				});
		},

		requestAnalytics() {
			this.loadingCharts = true;
			this.loadingCheckpointResults = true;

			return this.$store
				.dispatch('analytics/GET_ANALYTICS')
				.then(response => {
					console.log('requestAnalytics', response.data);
					this.audits = response.data.audits;
					this.charts = response.data.charts;
					this.media = response.data.media;

                    this.images = this.media.filter(item => item.extension_type == "media");
                    this.sigs = this.media.filter(item => item.extension_type == "signature");

                    this.audits.map(row => {
                        row['results'].map(elem => {
                            let allNotefields = [];
                            if(elem.valueType == "textfield" && elem.object.fixed == false && Object.keys(elem.value).length > 0 && elem.parentId != null){
                                if(elem.value.value != ""){
                                    allNotefields.push(elem.value.value);
                                }
                            }

                            if(elem.parentId != null){
                                this.idNotefieldRel[elem.audit+elem.parentId] = allNotefields.join(', ');
                            }

                        })
                        
                    })

                    // console.log(this.idNotefieldRel)

					this.transformCharts();

					// this.transformedResults = this.transformAudits(response.data.audits);

                    this.transformAudits(response.data.audits).then(result => {
                        this.transformedResults = result
                    }).catch(err => {
                        console.log(err)
                    })
                    
					this.loadingCharts = false;
					this.loadingCheckpointResults = false;
					return response;
				})
				.catch(err => {
					this.loadingCharts = false;
					this.loadingCheckpointResults = false;
					return err;
				});
		},

        getNumPages(ts){
            return Math.ceil(ts.length / 10);
        },

		requestAudits() {
			console.log('request audit...');

			return this.$store
				.dispatch('audits/GET_AUDITS', { id: this.company.id })
				.then(response => {
					this.audits = response.data.data;
					// This array will be used
					// this.transformedResults = this.transformAudits(this.audits);
                    
					return response;
				})
				.catch(err => {
					console.log(err);
					return err;
				});
		},

		requestChecklist(id) {
			console.log('request checklist...', id);

			return this.$store
				.dispatch('checklists/GET_CHECKLIST', { id: id })
				.then(response => {
					this.checklist = response.data.data;
					return response;
				})
				.catch(err => {
					console.log(err);
					return err;
				});
		},

		requestDirectoryResults(id) {
			console.log('request GET_DIRECTORY_RESULTS...', id, this.checklist);

			return this.$store
				.dispatch('audits/GET_DIRECTORY_RESULTS', { id: id })
				.then(response => {
					this.results = response.data.data;
					return response;
				})
				.catch(err => {
					console.log(err);
					return err;
				});
		},

		requestCorporateIdentity(companyId) {
			return this.$store
				.dispatch('companies/GET_COMPANY_DESIGN_PREFERENCES', { id: companyId })
				.then(response => {
					this.$store.commit('SET_CUSTOM_DESIGN', { isCustomDesign: true });
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		requestDirectory(id) {
			return this.$store
				.dispatch('directories/GET_DIRECTORY', { id: id })
				.then(response => {
					this.directory = response.data.data;
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		requestInitalData() {
			this.loading = true;

			const REQUEST = [];
			REQUEST.push(this.requestAnalytics());
            REQUEST.push(this.requestReportPreferences());

			if (this.checklistId) {
				REQUEST.push(this.requestChecklist(this.checklistId));
			}

			if (this.directoryId) {
				// REQUEST.push(this.requestDirectoryResults(this.directoryId));
			}

			if (this.company.id) {
                this.companyData = this.company;
                REQUEST.push(this.requestCompanyAddresses())
				// REQUEST.push(this.requestCorporateIdentity(this.company.id));
			}

			axios
				.all(REQUEST)
				.then(
					axios.spread((...results) => {
						this.loading = false;
					})
				)
				.catch(err => {
					this.loading = false;
					console.log('err', err);
				});
		},

		requestInitalMetaData() {
			this.loading = true;

			const REQUEST = [];
			// REQUEST.push(this.requestAuditStates());
			REQUEST.push(this.requestLocations());

			axios
				.all(REQUEST)
				.then(
					axios.spread((...results) => {
						// this.loading = false;

						// Request more data because we now have the state for finished audits
						this.requestInitalData();
					})
				)
				.catch(err => {
					this.loading = false;
					console.log('err', err);
				});
		},

        requestCompanyAddresses() {
            this.loading = true;
            this.$store
                .dispatch('companies/GET_COMPANY_ADDRESSES', { id: this.company.id })
                .then(response => {                    
                    this.companyAddress = response.data.data[1]
                })
                .catch(err => {
                    this.loading = false;
                });
        },

		requestLocations() {
			return this.$store
				.dispatch('locations/GET_LOCATIONS', { id: this.company.id })
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		requestScore(id) {
			return this.$store
				.dispatch('scores/GET_SCORE', { id: id })
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		requestScoreConditions(schemeId) {
			return this.$store
				.dispatch('valueschemes/GET_VALUE_SCHEME_CONDITIONS', { id: schemeId })
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		requestSection(id) {
			return this.$store
				.dispatch('sections/GET_SECTION', { id: id })
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		requestUsers() {
			return this.$store.dispatch('users/GET_USERS').then(response => {
				return response;
			});
		},

		selectLocation(item) {
			this.form.locationId = item.id;
			this.changeFilter();
		},

		toggleAside() {
			this.showAside = !this.showAside;
		},

        /**
         * Report preferences
         */
        requestReportPreferences() {
            return this.$store
                .dispatch('companies/GET_COMPANY_REPORT_PREFERENCES', { id: this.company.id })
                .then(response => {
                    this.reportPreferences = response.data.data;
                    return response;
                })
                .catch(err => {
                    return err;
                });
        },

		transformAudits(audits) {
            this.loadingCheckpointResults = true;
            const ROWS = [];

            return new Promise(resolve => {
                audits.forEach(audit => {
                    audit.results.forEach(result => {
                        // console.log('result', result.objectType, result, result.section);
                        
                        result.conditions.sort(function(a, b){
                            return parseInt(a.from) < parseInt(b.from) ? 1 : -1;
                        })

                        // Checkpoints
                        if (result.objectType === 'checkpoint') {
                            let obj = {
                                id: result.id,
                                createdAt: audit.createdAt,
                                executionAt: audit.executionAt,
                                executionDue: audit.executionDue,
                                checklistName: audit.checklistName,
                                audit_id: audit.id,
                                checklistPath: audit.checklistPath,
                                checklistDescription: audit.checklistDescription,
                                value: Object.assign({}, result.value),
                                object: Object.assign({}, result.object),
                                section: result.section,
                                valueType: result.valueType,
                                score: result.score,
                                conditions: result.conditions,
                                evaluatingScheme: result.evaluatingScheme,
                                scoringScheme: result.scoringScheme
                            };

                            const REQUEST = [];
                            if (result.section) {
                                REQUEST.push(this.requestSection(result.section));
                            }

                            axios
                                .all(REQUEST)
                                .then(
                                    axios.spread((...results) => {
                                        this.loadingCheckpointResults = false;

                                        if (result.section) {
                                            obj.section = results[0].data.data;
                                        }

                                        // console.log('results', results);

                                        ROWS.push(obj);
                                    })
                                )
                                .catch(err => {
                                    this.loadingCheckpointResults = false;
                                    ROWS.push(obj);
                                    return err;
                                });
                        }

                        // Textfield
                        // ToDo: Textfields und Checkpoints sind eigenständige Ergbenisse und werden
                        // deshalb untereinander ausgegeben. Jedoch sollen diese nach Section/Checklist kategoriesiert werden.
                        if (result.objectType === 'textfield') {
                            let obj = {
                                id: result.id,
                                createdAt: audit.createdAt,
                                executionAt: audit.executionAt,
                                executionDue: audit.executionDue,
                                checklistName: audit.checklistName,
                                audit_id: audit.id,
                                checklistDescription: audit.checklistDescription,
                                value: Object.assign({}, result.value),
                                object: Object.assign({}, result.object),
                                section: result.section,
                                valueType: result.valueType
                            };

                            if (result.section) {
                                this.requestSection(result.section)
                                    .then(response => {
                                        obj.section = response.data.data;
                                        ROWS.push(obj);
                                    })
                                    .catch(err => {
                                        ROWS.push(obj);
                                    });
                            }
                        }
                    });
                });

                return resolve(ROWS)
                
            })


            // return ROWS;
        },

		transformCharts() {
			// Overall evaluation
			this.charts.chart_one_average_value_of_all_choices.datasets[0].label = this.$t('OVERALL_EVALUATION');
			this.charts.chart_one_average_value_of_all_choices.datasets[0].backgroundColor = '#4cae4e';
			
            // Focal point
			this.charts.chart_two_count_of_yes_and_no.datasets[0].label = this.$t('FOCAL_POINT');

            /*
                Over here we're gonna change the color of pie chart according to what user has set in the rating system
            */

			// this.charts.chart_two_count_of_yes_and_no.datasets[0].backgroundColor = '#f87979';

            var scoreColorGraph = {}
            this.audits.map((item) => {
                if(item.results.length > 0){
                    item.results.map((elem) => {

                        if(elem['score'] != undefined){
                            if(!scoreColorGraph[elem['score']['name']]){
                                scoreColorGraph[elem['score']['name']] = elem['score']['color']
                            }
                        }

                    })
                }
            })

            var piecharData = this.charts.chart_two_count_of_yes_and_no
            var backgroundColor = [];

            this.charts.chart_two_count_of_yes_and_no['labels'].map((item) => {
                backgroundColor.push(scoreColorGraph[item])
            })

            this.charts.chart_two_count_of_yes_and_no['datasets'][0]['backgroundColor'] = backgroundColor



			// Group
			this.charts.chart_three_average_values_of_all_choices_grouped_by_section.datasets[0].label = this.$t(
				'GROUP_RATING'
			);
			this.charts.chart_three_average_values_of_all_choices_grouped_by_section.datasets[0].backgroundColor =
				'#4cae4e';
			// Labels for group rating
			if (_.isObject(this.charts.chart_three_average_values_of_all_choices_grouped_by_section.labels)) {
				const LABELS = [];
				_.forEach(
					this.charts.chart_three_average_values_of_all_choices_grouped_by_section.labels,
					(value, key) => {
						LABELS.push(value);
					}
				);
				this.charts.chart_three_average_values_of_all_choices_grouped_by_section.labels = LABELS;
			}
		},

        formatDate(date) {
            var d = new Date(date),
                month = '' + (d.getMonth() + 1),
                day = '' + d.getDate(),
                year = d.getFullYear();

            if (month.length < 2) month = '0' + month;
            if (day.length < 2) day = '0' + day;

            return [year, month, day].join('-');
        }
	},

	watch: {
		$route(to, from) {
			this.init();
		}
	},

	destroyed() {
		this.clearFilter();
	}
};
</script>

<style lang="scss">
.view__analysis-inner {
}

.view__analysis {
	.q-datetime {
		&:first-of-type {
			.q-datetime-content {
				border-bottom-width: 0 !important;
			}
		}
	}

	.q-datetime-content {
		@media screen and (min-width: $screen-md) {
			border-left-width: 1px !important;
		}
	}

	&__aside {
		overflow: hidden;

		@media screen and (max-width: $screen-md) {
			width: 100%;
		}

		@media screen and (min-width: $screen-md) {
			border-right: 1px solid $c-light-gray;
		}
	}

	&__main {
		@media screen and (max-width: $screen-md) {
			max-width: 100%;
			width: 100%;
		}

		@media screen and (min-width: $screen-md) {
			flex: 1 0 auto;
			// min-height: calc(100vh - 9rem);
		}

		&-inner {
			// max-height: 90vh;
			// overflow-y: auto;
		}
	}

	.q-if-inner {
		@media screen and (min-width: $screen-md) {
			max-width: 20rem !important;
		}
	}
}

.analysis__section {
	display: block;
	margin: 0 auto;
	margin-bottom: 0.5cm;
	box-shadow: 0 0 0.5cm rgba(0, 0, 0, 0.5);

	width: 100%;
	height: calc((297mm / 0.707));

	max-width: 297mm;
	max-height: 210mm;
	min-height: max-content;

	background-color: #ffffff;
	padding: 1rem;

	font-size: 12pt;
	line-height: 15pt;

	@media print {
		width: 29.7cm;
		height: 21cm;
	}

	@media screen and (min-width: $screen-md) {
		// width: 29.7cm;
		// height: 21cm;
	}

	&-logo {
		max-height: 4rem;
		text-align: left;

		height: 3rem;
		max-width: 100%;

		&--default {
		}

		&--custom {
		}
	}

	&-title {
		font-size: 16pt;
		line-height: 21pt;
		font-weight: 700;
	}

	&-details {
		@media screen and (min-width: $screen-md) {
			display: flex;
			flex-direction: column;
		}

		dl {
			margin-top: 0.5rem;
			margin-bottom: 0;

			&:first-child {
				margin-top: 0;
			}
		}

		dt {
			@media screen and (min-width: $screen-md) {
				margin-right: 2rem;
				min-width: 7rem;
			}
		}
	}

	&-charts {
		@media screen and (min-width: $screen-md) {
			display: flex;
			flex-wrap: wrap;
			margin-right: -0.5rem;
			margin-left: -0.5rem;
		}
	}

	&-chart {
		padding: 0 0.5rem;

		@media screen and (min-width: $screen-md) {
			width: calc(100% / 3);
		}

		&__headline {
			font-size: 12pt;
		}
	}

	&-table {
		th {
			text-align: left;
		}

		thead {
			th {
				border-bottom: 1px solid $c-gray;
				color: $c-gray;
				font-size: 11pt;
				padding: 0 0 0.3rem 0;
			}
		}

		tbody {
			td {
				font-size: 10pt;
				hyphens: auto;
				padding: 0.5rem 0.3rem;
				max-width: 5rem;
				vertical-align: top;
				border-top: 1px solid $c-light-gray;
			}

			// First row
			tr {
				&:first-child {
					td {
						border-top: none;
					}
				}
			}
		}
	}
}

.rating__circle {
	border-radius: 50%;
	display: block;
	height: 2rem;
	width: 2rem;
}

.analysis__section-images {
	&__image {
		max-width: 100%;
		height: 200px;
	}
}
.analysis__section{
    position:relative;
}
.page-num-footer{
    position:absolute;
    bottom:0;
    padding-bottom:5px;
    text-align: center;
    display: flex;
    width: 97%;
    border-top: 1px solid #f5f5f5;
    padding-top: 5px;
}
.page-num-footer span{
    display: table-cell;
    text-align: center;
}
.page-num-footer > div{
    flex: 1;
}
.page-num-footer p{
    margin:0;
    font-size: 10px;
    line-height:14px;
}
.page-num-footer .pagenum{
    float:right;
}
.page-num-footer div:first-child, .page-num-footer div:nth-child(2){
    text-align:left;
}
@media print {
	.el-header,
	.el-aside,
	.view__analysis__aside {
		display: none;
	}

	body,
	.analysis__section {
		margin: 0;
		box-shadow: 0;
	}

	.analysis__section {
		border: initial;
		border-radius: initial;
		width: initial;
		min-height: initial;
		box-shadow: initial;
		background: initial;
	}
}
</style>
