<template>
    <el-form @submit.native.prevent v-loading="loading" class="form__edit-score-scheme">
        <!-- Scoring Scheme -->
        <q-field
            class="m-b-1"
            :error="$v.form.name.$error"
            :error-label="$t('PLEASE_FILL_OUT_ALL_REQUIRED_FIELDS')">
            <q-input
                v-model.trim="$v.form.name.$model"
                :float-label="$t('NAME')"> 
            </q-input>
        </q-field>

        <!-- Scores -->
        <div class="m-t-2">
            <el-row>
                <el-col :xs="24">
                    <h4 class="headline">{{ $t('RATINGS') }}</h4>
                </el-col>
            </el-row>
            <el-row :gutter="20"
                v-for="(score, index) in $v.scores.$each.$iter" :key="index"
                class="form__edit-score-scheme__score">
                
                <!-- Name -->
                <el-col :xs="10" :sm="10">
                    <q-field class="m-b-1"
                        :error="score.name.$error"
                        :error-label="$t('PLEASE_FILL_OUT_ALL_REQUIRED_FIELDS')">
                        <q-input
                            v-model.trim="score.name.$model"
                            :placeholder="$t('GREEN')"
                            :stack-label="$t('NAME')">
                        </q-input>
                    </q-field>
                </el-col>

                <!-- Icon/Color -->
                <el-col :xs="6" :sm="4">
                    <div class="text-center pos-r">
                        <q-color
                            v-model="score.color.$model"
                            :format-model="format"
                            @input="val => onChangeColor(val, index)"
                            :ref="'color-picker-' + index"
                            class="no-padding"
                            style="visibility: hidden; height: 0;" />
                        <button
                            class="form__edit-score-scheme__score-color"
                            :style="'background-color:' + score.color.$model"
                            @click="onClickBtnColor(index)">
                        </button>
                    </div>
                </el-col>

                <!-- Rating -->
                <el-col :xs="8" :sm="8">
                    <q-field class="m-b-1"
                        :error="score.value.$error"
                        :error-label="$t('PLEASE_FILL_OUT_ALL_REQUIRED_FIELDS')">
                        <q-input
                            v-model.number="score.value.$model"
                            type="number"
                            min="0"
                            :float-label="$t('RATING')">
                        </q-input>
                    </q-field>
                </el-col>

                <!-- Button -->
                <el-col :xs="24" :sm="2" class="text-center">
                    <q-btn
                        icon="delete"
                        :disable="scores.length < 2"
                        @click="onRemoveScore(scores[index], index)"
                        flat
                        no-ripple
                        class="w-100--sm">
                    </q-btn>
                </el-col>
            </el-row>

            <div class="m-t-2 m-b-1 text-center">
                <q-btn
                    :disable="scores.length > 9"
                    color="primary"
                    flat
                    no-ripple
                    icon="add_circle_outline"
                    @click="onAddScore"
                    :label="$t('ADD_ELEMENT')">
                </q-btn>
            </div>

        </div>

        <!-- Save/Cancel -->
        <div class="text-right">
            <q-btn
                :label="$t('CANCEL')"
                v-if="isDeviceGreaterSM"
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
import { required } from 'vuelidate/lib/validators';
import { Score } from '@/shared/classes/Score';
import { ScoringScheme } from '@/shared/classes/ScoringScheme';
import commonMixins from '@/shared/mixins/common';
import scoringSchemesMixins from '@/shared/mixins/scoringschemes';

export default {
	name: 'FormEditScoringScheme',

	mixins: [commonMixins, scoringSchemesMixins],

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

		isDeviceGreaterSM() {
			return this.$store.getters.IS_DEVICE_GREATER_SM;
		}
	},

	data() {
		return {
			form: {
				name: ''
			},
			format: 'hex',
			loading: false,
			scores: [],
			color: ''
		};
	},

	validations: {
		form: {
			name: { required }
		},
		scores: {
			$each: {
				color: {},
				name: { required },
				value: { required }
			}
		}
	},

	mounted() {
		this.init();
	},

	methods: {
		/**
		 * Create/Update scoring schemes as well as corresponding scores.
		 * We cannot update or create multiple scores by one request
		 * so we need the handle this by a mixin function after scoring scheme has been created/updated.
		 *
		 * @returns {void}
		 */
		doSubmit() {
			this.loading = true;

			let scoringScheme = {};
			let dispatcherName = 'companies/CREATE_SCORING_SCHEME';

			if (this.edit) {
				scoringScheme = new ScoringScheme(this.form);
				dispatcherName = 'scoringschemes/UPDATE_SCORING_SCHEME';
			} else {
				scoringScheme = new ScoringScheme({
					id: this.company.id,
					name: this.form.name
				});
			}

			this.$store
				.dispatch(dispatcherName, scoringScheme)
				.then(response => {
					this.loading = false;
					if (response.status === 201 || response.status === 204) {
						/**
						 * Create/Update Scores
						 */
						let schemeId = scoringScheme.id;
						if (!this.edit) {
							schemeId = response.data.data.id;
						}

						this.doSaveScores(this.scores, schemeId)
							.then(response => {
								if (response.status !== 201 || response.status !== 204) {
									this.handleErrors(response);
								}
							})
							.catch(err => {
								this.handleErrors(err);
							});

						// Close Modal
						this.onCancel();

						// Notify User
						this.$q.notify({
							message: this.$t('SAVE_SUCCESS'),
							type: 'positive'
						});

						// Refresh
						this.$store.dispatch('companies/GET_COMPANY_SCORING_SCHEMES', { id: this.company.id });
					} else {
						this.handleErrors(response);
					}
				})
				.catch(err => {
					this.loading = false;
				});
		},

		init() {
			if (this.edit) {
				this.form = Object.assign({}, this.data);

				this.loading = true;
				this.requestScoringSchemeScores(this.data.id)
					.then(response => {
						this.loading = false;

						if (response.status !== 200) {
							this.handleErrors(response);
							return;
						}

						//const sorted = _.sortBy(response.data.data, score => {
						//	return score.value;
						//});

						//this.scores = sorted.reverse();

						this.scores = response.data.data;
						
						// Dummy score if no score has been saved
						if (!this.scores.length) {
							this.scores.push(
								new Score({
									name: '',
									value: 100
								})
							);
						}
					})
					.catch(err => {
						this.loading = false;
					});
			} else {
				// Dummy score if no score has been saved
				this.scores.push(
					new Score({
						name: '',
						value: 100
					})
				);
				this.scores.push(
					new Score({
						name: '',
						value: 50
					})
				);
				this.scores.push(
					new Score({
						name: '',
						value: 0
					})
				);
			}
		},

		/**
		 * Add a new score
		 */
		onAddScore() {
			this.scores.push(
				new Score({
					name: ''
				})
			);
		},

		/**
		 * Close the modal/dialog
		 */
		onCancel() {
			this.$emit('cancel');
		},

		/**
		 * Because the validation object is not reactive we need to reset the changed score object
		 *
		 * @param {String} Color
		 * @param {Integer} Index of score in Array
		 */
		onChangeColor(color, index) {
			this.$set(this.scores, index, this.scores[index]);
		},

		/**
		 * Toogle the color picker
		 */
		onClickBtnColor(index) {
			this.$refs['color-picker-' + index][0].toggle();
		},

		/**
		 * Remove score from scores if the current score has not been saved yet
		 * Otherwhise hit the backend and delete the current score and refresh all scores
		 *
		 * @param {Object} score
		 * @param {Integer} index
		 * @returns {void}
		 */
		onRemoveScore(score, index) {
			if (score.id) {
				this.loading = true;
				this.$store
					.dispatch('scores/DELETE_SCORE', { id: score.id })
					.then(response => {
						this.requestScoringSchemeScores(this.data.id)
							.then(response => {
								this.loading = false;
								this.scores = response.data.data;
							})
							.catch(err => {
								this.loading = false;
							});
					})
					.catch(err => {});
			} else {
				this.scores.splice(index, 1);
			}
		},

		/**
		 * Trigger form validation and in case of success save the scoring scheme and the corresponding scores
		 *
		 * @returns {void}
		 */
		onSubmit() {
			this.$v.$touch();
			if (this.$v.$invalid) {
				return false;
			} else {
				this.doSubmit();
			}
		}
	}
};
</script>

<style lang="scss">
.form__edit-score-scheme__score {
	border-bottom: 1px solid $c-light-gray;
	padding-top: 1rem;
	margin-bottom: 1rem;

	@media screen and(min-width: $screen-md) {
		margin-top: 0;
		padding-top: 0;
		border-bottom: none;
	}

	&-color {
		border-radius: 50%;
		border: 1px solid $c-light-gray;
		display: inline-block;
		height: 40px;
		width: 40px;
		outline: 0;

		&:focus {
			box-shadow: 0 0 5px 0 transparentize($c-navi-blue, 0.1);
		}
	}
}
</style>
