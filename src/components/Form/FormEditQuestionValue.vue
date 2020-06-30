<!--
@component:         FormEditQuestionValue
@description:       Form to create and edit question
                    There are more attributes available but we start with a smaller set.
                    In further development only remove the comments in markup and javascript.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-09-11
@modified:          2018-10-15
-->
<template>
    <el-form ref="FormEditQuestionValue" v-loading="loading">
        <el-row :gutter="20">
            <el-col :xs="24">
                <q-field
                    class="m-b-1"
                    :error="$v.form.prompt.$error"
                    :error-label="$t('PLEASE_FILL_OUT_ALL_REQUIRED_FIELDS')">
                    <q-input
                        v-model.trim="$v.form.prompt.$model"
                        :stack-label="$t('QUESTION')">
                    </q-input>
                </q-field>
            </el-col>
        </el-row>

        <el-row :gutter="20" class="m-t-1">
            <el-col :xs="24">
                <p class="m-b-half">{{ $t('RATING_SYSTEMS') }}:</p>
                <q-field
                    class="m-b-1 q-field--overflow-auto"
                    :error="$v.form.scoringSchemeId.$error"
                    :error-label="$t('PLEASE_FILL_OUT_ALL_REQUIRED_FIELDS')">
                    <ChooseScoringScheme
                        :active-item="getScoringSchemeById(data.scoringSchemeId).name"
                        :items="companyScoringSchemes"
                        :disabled="edit"
                        @change="onChangeScoringScheme">
                    </ChooseScoringScheme>
                </q-field>
            </el-col>
        </el-row>

        <!-- Setpoints -->
        <div v-loading="loadingScores">
            <el-row :gutter="20" class="m-t-2" v-if="form.evaluationScheme.data.scoreConditions.length">
                <el-col :xs="24" :sm="18">
                    <!-- Header -->
                    <el-row :gutter="20">
                        <el-col :xs="24" :sm="12">
                            <p class="m-b-half">{{ $t('SETPOINTS') }} ({{ $t('ONE_PER_ROW') }}):</p>
                        </el-col>
                        <el-col :xs="24" :sm="12">
                            <p class="m-b-half">{{ $t('RATING') }}:</p>
                        </el-col>
                    </el-row>

                    <!-- Loop -->
                    <el-row :gutter="20" v-for="(item, index) in $v.form.evaluationScheme.data.scoreConditions.$each.$iter" :key="index">
                        <!-- From/To -->
                        <el-col :xs="24" :sm="12">
                            <el-row :gutter="20">
                                <el-col :xs="24" :sm="12">
                                    <q-field class="m-b-1"
                                        :error="item.from.$error"
                                        :error-label="$t('PLEASE_FILL_OUT_ALL_REQUIRED_FIELDS')">
                                        <q-input type="number"
                                            v-model.trim="item.from.$model"
                                            :stack-label="$t('FROM')"
                                            :disable="edit">
                                        </q-input>
                                    </q-field>
                                </el-col>

                                <el-col :xs="24" :sm="12">
                                    <q-field class="m-b-1"
                                        :error="item.to.$error"
                                        :error-label="$t('PLEASE_FILL_OUT_ALL_REQUIRED_FIELDS')">
                                        <q-input type="number"
                                            v-model.trim="item.to.$model"
                                            :stack-label="$t('TO')"
                                            :disable="edit">
                                        </q-input>
                                    </q-field>
                                </el-col>
                            </el-row>
                        </el-col>

                        <!-- Ratings -->
                        <el-col :xs="24" :sm="12">
                            <q-field class="m-b-1"
                                :error="item.scoreId.$error"
                                :error-label="$t('PLEASE_FILL_OUT_ALL_REQUIRED_FIELDS')">
                                <q-select
                                    radio
                                    v-model="item.scoreId.$model"
                                    :stack-label="$t('RATING')"
                                    :options="scoreOptions"
                                    :disable="edit"
                                />
                            </q-field>
                        </el-col>
                    </el-row>
                </el-col>

                <!-- Units -->
                <el-col :xs="24" :sm="6">
                    <p class="m-b-half">{{ $t('UNIT') }}:</p>
                    <q-field class="m-b-1"
                        :error="$v.form.evaluationScheme.data.unit.$error"
                        :error-label="$t('PLEASE_FILL_OUT_ALL_REQUIRED_FIELDS')">
                        <q-select
                            radio
                            v-model="$v.form.evaluationScheme.data.unit.$model"
                            :stack-label="$t('UNIT')"
                            :options="unitOptions"
                            :disable="edit" />
                    </q-field>
                </el-col>
            </el-row>
        </div>

        <!-- Additional Settings -->
        <el-collapse class="el-collapse--transparent m-t-2">
            <el-collapse-item name="1">
                <template slot="title">
                    {{ $t('ADVANCED_SETTINGS') }}
                </template>


                <el-row :gutter="40" class="m-t-1 flex a-center">
                    <el-col :xs="24" :sm="12">
                       <q-field class="q-field--overflow q-field--wrap m-b-1">
                            <q-toggle v-model="form.mandatory" :label="$t('MANDATORY_QUESTION')" :true-value="1" :false-value="0" />
                            <p class="m-t-1 font--small color-gray">{{$t('QUESTION_IS_MANDATORY_TO_COMPLETE_AUDIT')}}</p>
                        </q-field>
                    </el-col>

                    <!-- <el-col :xs="24" :sm="8">
                        <q-field class="q-field--overflow q-field--wrap m-b-1">
                            <q-toggle v-model="form.notify" :label="$t('NOTIFY')" />
                            <p class="m-t-1 font--small color-gray">Coming soon</p>
                        </q-field>
                    </el-col> -->

                    <el-col :xs="24" :sm="12">
                        <q-field class="m-b-1">
                            <p>{{$t('FACTOR')}}</p>
                            <el-input-number v-model="form.factor" :min="0" :step="0.1"></el-input-number>
                        </q-field>
                    </el-col>
                </el-row>

                <el-row>

                </el-row>
            </el-collapse-item>
        </el-collapse>

        <!-- Save/Cancel -->
        <div class="text-right m-t-2">
            <q-btn
                :label="$t('CANCEL')"
                v-if="isDeviceGreaterSM && !edit"
                @click="onCancel"
                flat
                no-ripple
                class="m-r-1">
            </q-btn>
            <q-btn
                :label="$t('SAVE')"
                class="w-100--sm m-t-1--sm"
                @click="onSubmit"
                color="primary"
                no-ripple>
            </q-btn>
        </div>
    </el-form>
</template>

<script>
import axios from 'axios';
import { required, decimal } from 'vuelidate/lib/validators';
import { Checkpoint } from '@/shared/classes/Checkpoint';
import { ScoreCondition } from '@/shared/classes/ScoreCondition';
import ChooseScoringScheme from '@/components/ChooseScoringScheme';
import scoringschemesMixins from '@/shared/mixins/scoringschemes';
import { transformForSelect } from '@/shared/transformers';
import { getUnits } from '@/shared/units';

export default {
	name: 'FormEditQuestionValue',

	mixins: [scoringschemesMixins],

	props: {
		data: {
			type: Object,
			required: true
		}
	},

	components: {
		ChooseScoringScheme
	},

	computed: {
		company() {
			return this.$store.state.user.company;
		},

		companyScoringSchemes() {
			return this.$store.state.companies.scoringschemes;
		},

		isDeviceGreaterSM() {
			return this.$store.getters.IS_DEVICE_GREATER_SM;
		}
	},

	data() {
		return {
			edit: true,
			form: {
				prompt: '',
				scoringSchemeId: '',
				mandatory: 0,
				factor: 1,
				index: 0,
				evaluationScheme: {
					type: 'value',
					data: {
						unit: '',
						scoreConditions: []
					}
				},
				evaluationSchemeType: 'value'
			},
			loading: false,
			loadingScores: false,
			scores: [],
			scoreOptions: [],
			unitOptions: getUnits()
		};
	},

	validations: {
		form: {
			prompt: { required },
			scoringSchemeId: { required },
			mandatory: {},
			factor: {},
			index: {},
			notify: false,
			evaluationScheme: {
				type: { required },
				data: {
					unit: { required },
					scoreConditions: {
						$each: {
							to: { required, decimal },
							from: { required, decimal },
							scoreId: { required }
						}
					}
				}
			}
		}
	},

	mounted() {
		this.init();
	},

	methods: {
		doSubmit() {
			let checkpoint = {};
			let dispatcherName = 'checkpoints/UPDATE_CHECKPOINT';

			if (this.edit) {
				checkpoint = new Checkpoint(this.form);
			} else {
				checkpoint = new Checkpoint(this.form);
				checkpoint.id = this.data.context.id;
				dispatcherName = this.data.context.type + '/CREATE_CHECKPOINT';
			}

			console.log('doSubmit new checkpoint', checkpoint);

			this.loading = true;

			this.$store
				.dispatch(dispatcherName, checkpoint)
				.then(response => {
					this.loading = false;

					if (response.status === 201 || response.status === 204) {
						if (response.status === 201) {
							// Create score condition for each score because the backend do not create the conditions by
							// creating a new checkpont
							this.handleCheckpointResponse(response.data.data.evaluationSchemeId);
						}

						this.$q.notify({
							message: this.$t('SAVE_SUCCESS'),
							type: 'positive'
						});

						this.$eventbus.$emit('checklist:refresh');
						this.$eventbus.$emit('section:' + this.data.context.id + ':refresh');

						this.onCancel();
					} else {
						this.handleErrors(response);
					}
				})
				.catch(err => {
					this.loading = false;
				});
		},

		getScoringSchemeById(id) {
			let result = '';

			this.companyScoringSchemes.forEach(scheme => {
				if (scheme.id === id) {
					result = scheme;
				}
			});

			return result;
		},

		generateEvaluationSchemeData(scores) {
			let result = [];

			scores.forEach(score => {
				result.push(new ScoreCondition({}));
			});

			return result;
		},

		handleCheckpointResponse(evaluationSchemeId) {
			this.doCreateScoreConditions(this.form.evaluationScheme.data.scoreConditions, evaluationSchemeId)
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		init() {
			if (!this.data.id) {
				this.edit = false;
			} else {
				this.form = Object.assign({}, this.form, this.data);
				this.form.mandatory = this.form.mandatory === true ? 1 : 0;

				this.requestInitialDataOnEdit();
			}
			console.log('FormEditQuestion', this.data);
		},

		/**
		 * Close the modal/dialog
		 */
		onCancel() {
			this.$emit('cancel');
		},

		onChangeScoringScheme(scoringScheme) {
			this.loadingScores = true;
			this.requestScoringSchemeScores(scoringScheme.id)
				.then(response => {

					// this.scores = response.data.data;
					const sorted = _.sortBy(response.data.data, score => {
						return score.value;
					});

					this.scores = sorted.reverse();

					this.form.evaluationScheme.data.scoreConditions = this.generateEvaluationSchemeData(this.scores);
					this.scoreOptions = transformForSelect(this.scores);
					this.loadingScores = false;
				})
				.catch(err => {
					this.loadingScores = false;
				});

			this.form.scoringSchemeId = _.cloneDeep(scoringScheme.id);
			console.log('onChangeScoringScheme', scoringScheme);
		},

		onSubmit() {
			this.$v.$touch();
			if (this.$v.$invalid) {
				return false;
			} else {
				this.doSubmit();
			}
		},

		requestInitialDataOnEdit() {
			const REQUEST = [];

			REQUEST.push(this.requestScoringSchemeScores(this.data.scoringSchemeId));
			REQUEST.push(this.requestValueScheme(this.data.evaluationSchemeId));
			REQUEST.push(this.requestValueSchemeConditions(this.data.evaluationSchemeId));

			this.loadingScores = true;
			axios
				.all(REQUEST)
				.then(
					axios.spread((...results) => {
						this.loadingScores = false;

						this.scores = results[0].data.data;
						this.scoreOptions = transformForSelect(this.scores);

						this.form.evaluationScheme.data.unit = results[1].data.data.unit;

						let scc = results[0].data.data;
						let evSc = results[2].data.data;

						let evSccRelation = {};
						scc.map((item) => {
							evSccRelation[item['id']] = item['label']
						})

						evSc.sort(function(a, b){
							return parseInt(evSccRelation[a.scoreId]) > parseInt(evSccRelation[b.scoreId]) ? 1 : -1;
						})

						// this.form.evaluationScheme.data.scoreConditions = results[2].data.data;
						this.form.evaluationScheme.data.scoreConditions = evSc;
					})
				)
				.catch(err => {
					this.loadingScores = false;
					return err;
				});
		},

		requestValueScheme(schemeId) {
			return this.$store
				.dispatch('valueschemes/GET_VALUE_SCHEME', { id: schemeId })
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		requestValueSchemeConditions(schemeId) {
			return this.$store
				.dispatch('valueschemes/GET_VALUE_SCHEME_CONDITIONS', { id: schemeId })
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
