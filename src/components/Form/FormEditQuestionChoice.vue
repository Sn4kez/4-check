<!--
@component:         FormEditQuestionChoice
@description:       Form to create and edit question
                    There are more attributes available but we start with a smaller set.
                    In further development only remove the comments in markup and javascript.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-09-10
@modified:          2018-10-20
-->
<template>
    <el-form ref="FormEditQuestionChoice">
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
                <p>{{ $t('RATING_SYSTEMS') }}:</p>
                <q-field v-if="companyScoringSchemes.length"
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
                <div v-if="!companyScoringSchemes.length">
                    <p>{{ $t('PLEASE_CREATE_SCORING_SCHEME') }}</p>
                    <q-btn
                        color="primary"
                        flat
                        :label="$t('CREATE_SCORING_SCHEME')"
                        :to="{path: '/checklists/rating-systems'}">
                    </q-btn>
                </div>
            </el-col>
        </el-row>

        <!-- Additional Settings -->
        <el-collapse class="el-collapse--transparent m-t-2">
            <el-collapse-item name="1">
                <template slot="title">
                    {{ $t('ADVANCED_SETTINGS') }}
                </template>


                <el-row :gutter="40" class="m-t-1 flex a-center">
                    <el-col :xs="24" :sm="8">
                       <q-field class="q-field--overflow q-field--wrap m-b-1">
                            <q-toggle v-model="form.mandatory" :label="$t('MANDATORY_QUESTION')" :true-value="1" :false-value="0" />
                            <p class="m-t-1 font--small color-gray">{{$t('QUESTION_IS_MANDATORY_TO_COMPLETE_AUDIT')}}</p>
                        </q-field>
                    </el-col>

                    <el-col :xs="24" :sm="8">
                        <!-- <q-btn
                            flat
                            no-ripple
                            color="primary"
                            @click="onClickBtnCreateNotification"
                            :label="$t('NOTIFY')"
                            class="m-t-1" />
                        <p class="m-t-1 font--small color-gray">{{$t('GET_NOTIFICATION_FOR_SPECIFIC_RATING')}}</p> -->
                    </el-col>

                    <el-col :xs="24" :sm="8">
                        <q-field class="m-b-1">
                            <p>{{$t('FACTOR')}}</p>
                            <el-input-number v-model="form.factor" :min="0" :step="0.1"></el-input-number>
                        </q-field>
                    </el-col>
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
import { required } from 'vuelidate/lib/validators';
import { Checkpoint } from '@/shared/classes/Checkpoint';
import ChooseScoringScheme from '@/components/ChooseScoringScheme';

export default {
	name: 'FormEditQuestionChoice',

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
					type: 'choice',
					data: []
				},
				evaluationSchemeType: 'choice'
			}
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
			evaluationScheme: { type: { required } }
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
				// checkpoint = new Checkpoint({
				// 	prompt: this.form.prompt,
				// 	scoringSchemeId: this.form.scoringSchemeId,
				// 	mandatory: this.form.mandatory,
				// 	factor: this.form.factor,
				// 	index: this.form.index,
				// 	evaluationScheme: this.form.evaluationScheme,
				// 	evaluationSchemeType: this.evaluationSchemeType
				// });
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

		init() {
			if (!this.data.id) {
				this.edit = false;
			} else {
				this.form = Object.assign({}, this.form, this.data);
				this.form.mandatory = this.form.mandatory === true ? 1 : 0;
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
			this.form.scoringSchemeId = _.cloneDeep(scoringScheme.id);
			console.log('onChangeScoringScheme', scoringScheme);
		},

		onClickBtnCreateNotification() {
			if (this.isDeviceGreaterSM) {
				this.$store.commit('OPEN_DIALOG', {
					title: this.$t('NOTIFY'),
					loadComponent: 'Form/FormCheckpointNotification',
					width: '40%',
					data: { checkpoint: this.data }
				});
			} else {
				this.$store.commit('OPEN_MODAL', {
					title: this.$t('NOTIFY'),
					loadComponent: 'Form/FormCheckpointNotification',
					maximized: true,
					data: { checkpoint: this.data }
				});
			}
		},

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
