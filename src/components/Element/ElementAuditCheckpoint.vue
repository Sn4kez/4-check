<!--
@component:         ElementAuditCheckpoint
@environment:       Hyprid
@description:       -
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-10-02
@modified:          2018-10-18
-->
<template>
    <div class="checklist__element-checkpoint" v-loading="loading">
        <!-- Scores -->
        <div class="p-1 p-t-2">
            <p v-if="!isDeviceGreaterSM">{{data.object.prompt}}</p>

            <ChooseScore v-if="data.object.evaluationSchemeType === 'App\\ChoiceScheme'"
                :items="scores"
                :active-item="activeScore"
                :disabled="!isDraft"
                @change="onChangeScore">
            </ChooseScore>

            <div v-if="data.object.evaluationSchemeType === 'App\\ValueScheme'">
                <div class="flex valign-center">
                    <span>{{minValue}}</span>

                    <div style="flex: 1 0 auto;">
                        <q-slider
                            v-model="selectedValue"
                            :min="minValue"
                            :max="maxValue"
                            :step="0.1"
                            label-always
                            @input="onChangeValue"
                            :readonly="!isDraft"
                            :disable="!isDraft"
                        />
                    </div>

                    <span>{{maxValue}}</span>
                </div>

                <div class="text-right m-t-1">
                    <q-btn v-if="isDraft"
                        color="primary"
                        :label="$t('SAVE')"
                        @click="onBtnClickSaveValue"
                        :loading="loadingUpdate">
                    </q-btn>
                </div>
            </div>
        </div>

        <!-- Extensions -->
        <div class="p-1 m-t-2" v-if="entries.length">
            <!-- <ElementAuditContainer
                v-for="(element, index) in entries" :key="element.id"
                :data="element"
                :audit="audit"
                :class="{'is-first': index === 0}">
            </ElementAuditContainer> -->

            <ElementAuditExtension v-for="(element, index) in entries" :key="element.id"
                :data="element"
                :audit="audit"
                :class="{'is-first': index === 0}">
            </ElementAuditExtension>
        </div>

    </div>
</template>

<script>
import axios from 'axios';
import { Check } from '@/shared/classes/Check';
import ChooseScore from '@/components/ChooseScore';
import auditMixins from '@/shared/mixins/audits';
import checkpointMixins from '@/shared/mixins/checkpoints';
import scoringschemesMixins from '@/shared/mixins/scoringschemes';
import ElementAuditExtension from '@/components/Element/ElementAuditExtension';

export let auditCheckpointId = []

export default {
	name: 'ElementAuditCheckpoint',

	mixins: [auditMixins, checkpointMixins, scoringschemesMixins],

	components: {
		ChooseScore,
		ElementAuditExtension
	},

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
		}
	},

	computed: {
		isDeviceGreaterSM() {
			return this.$store.getters.IS_DEVICE_GREATER_SM;
		},

		isDraft() {
			if (!this.audit) {
				return false;
			}

			return this.getAuditStateById(this.audit.state).name === 'draft';
		},

		maxValue() {
			if (!this.conditions.length) {
				return 1;
			}

			let sorted = _.sortBy(this.conditions, condition => {
				return condition.to;
			});

			sorted = sorted.reverse();

			return sorted[0].to;
		},

		minValue() {
			if (!this.conditions.length) {
				return 0;
			}

			let sorted = _.sortBy(this.conditions, condition => {
				return condition.from;
			});

			return sorted[0].from;
		}
	},

	data() {
		return {
			activeScore: null,
			check: {},
			conditions: [],
			entries: [],
			context: {
				id: this.data.object.id,
				type: 'checkpoints'
			},
			loading: false,
			loadingUpdate: false,
			scores: [],
			selectedValue: null
		};
	},

	mounted() {
		this.init();
	},

	methods: {
		getScoreById(id) {
			let result = {};

			this.scores.filter(score => {
				if (score.id === id) {
					result = score;
				}
			});

			return result;
		},

		getScoreConditionByValue(value) {
			let myCondition = null;
			let keepGoing = true;
			value = parseFloat(value);

			const SORTED_CONDITIONS = _.sortBy(this.conditions, condition => {
				return condition.from;
			});

			SORTED_CONDITIONS.forEach(condition => {
				if (keepGoing) {
					if (value >= parseFloat(condition.from) && value <= parseFloat(condition.to)) {
						myCondition = condition;
					} else {
						keepGoing = false;
					}
				}
			});

			if (myCondition === null) {
				let sorted = _.sortBy(this.conditions, condition => {
					return condition.to;
				});
				sorted = sorted.reverse();

				myCondition = sorted[0];
			}

			return myCondition;
		},

		handlePrefillValue() {
			if (this.data.object.evaluationSchemeType === 'App\\ChoiceScheme') {
				const SCORE = this.getScoreById(this.data.value.scoreId);
				this.activeScore = SCORE.name;
			} else {
				if (_.isObject(this.data.value)) {
					this.selectedValue = this.data.value.value;
				}
			}
		},

		init() {
			this.check = Object.assign({}, this.data);
			this.requestInitalData();

			console.log('ElementAuditCheckpoint mounted', this.data);
		},

		onBtnClickSaveValue() {
			const CHECK = new Check(this.check);

			this.loadingUpdate = true;
			this.doUpdateCheck(CHECK)
				.then(response => {
					this.loadingUpdate = false;
					console.log('doUpdateCheck', response);
				})
				.catch(err => {
					this.loadingUpdate = false;
					console.log('doUpdateCheck ---- err', err);
				});

			console.log('onChangeValue', CHECK);
		},

		onChangeScore(value) {
			this.check.value = value.value;
			this.check.scoreId = value.id;
			this.check.scoringScheme = value.schemeId;

			const CHECK = new Check(this.check);

			this.doUpdateCheck(CHECK)
				.then(response => {
					console.log('doUpdateCheck', response);
				})
				.catch(err => {
					console.log('doUpdateCheck ---- err', err);
				});

			auditCheckpointId.push(CHECK.checkpoint);
			console.log('onChangeScore in checkpoint', value, CHECK);
		},

		onChangeValue(value) {
			const condition = this.getScoreConditionByValue(value);
			this.check.value = value;
			this.check.scoreId = condition.scoreId;
			this.check.valueScheme = this.data.object.evaluationSchemeId;
		},

		requestInitalData() {
			this.loading = true;

			if (this.data.object.evaluationSchemeType === 'App\\ChoiceScheme') {
				this.requestScoringSchemeScores(this.data.object.scoringSchemeId)
					.then(response => {
						this.loading = false;
						let resp = response.data.data;

						resp.sort(function(x, y) {
							if (x.value > y.value) {
								return -1;
							}
							if (x.value < y.value) {
								return 1;
							}
							return 0;
						});

						this.scores = resp;

						this.handlePrefillValue();
					})
					.catch(err => {
						this.loading = false;
					});
			} else {
				this.requestValueSchemeConditions(this.data.object.evaluationSchemeId)
					.then(response => {
						this.loading = false;
						this.conditions = response.data.data;

						this.handlePrefillValue();
					})
					.catch(err => {
						this.loading = false;
					});
			}

			this.loading = true;
			this.requestCheckpointEntries(this.data.checkpoint)
				.then(response => {
					this.loading = false;
					// this.entries = response.data.data;
					
					let dd = response.data.data;

					let sortByIndex = false;

					let someElem = dd.length > 0 ? dd[0] : {};

					dd.map(elem => {
						if(elem.object.index != someElem.object.index){
							sortByIndex = true;
						}
					})

					if(sortByIndex){
						dd = _.sortBy(dd, element => {
					        return element.object.index;
					    })
					}else{
						dd = _.sortBy(dd, element => {
					        return element.object.createdAt;
					    })
					}

					this.entries = dd;

					return response;
					
				})
				.catch(err => {
					this.loading = false;
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

<style lang="scss">
.checklist__element-checkpoint {
	.checklist__element {
		margin-top: 0.4rem;

		&:first-of-type {
			margin-top: 0;
		}
	}
}
</style>
