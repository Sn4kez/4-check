<!--
@component:         ElementParticipant
@environment:       Hyprid
@description:       This component is used to build a checklist as well as to create an audit.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-09-13
@modified:          2018-10-06
-->
<template>
    <div class="checklist__element-participant" :class="{'p-1': audit.id}">
        <el-form ref="ElementParticipant">
            <!-- Checklist -->
            <p v-if="!audit.id" class="color-gray m-b-0">{{ $t('SELECTION_WILL_BE_AVAILABLE_DURING_AUDIT') }}</p>

            <!-- Audit -->
            <div v-if="audit.id">
                <el-row :gutter="20">
                    <el-col :xs="24" :sm="11">
                        <q-field class="m-b-0">
                            <q-select
                                v-model="form.userParticipant"
                                :float-label="$t('SEARCH_PARTICIPANT')"
                                :placeholder="$t('PARTICIPANT')"
                                :options="users"
                                @input="onChangeInput"
                                clearable
                                radio
                                ref="participants"
                                :readonly="!isDraft" />
                        </q-field>
                    </el-col>

                    <el-col :xs="24" :sm="2" class="valign-center a-center m-t-1--sm m-b-1--sm">
                        {{ $t('OR') }}
                    </el-col>

                    <el-col :xs="24" :sm="11">
                        <q-field>
                            <q-input
                                v-model="form.externalParticipant"
                                :stack-label="$t('ENTER_PARTICIPANT')"
                                @blur="onBlurTextInput"
                                :loading="loading"
                                :readonly="!isDraft">
                            </q-input>
                        </q-field>
                    </el-col>
                </el-row>
            </div>
        </el-form>
    </div>
</template>

<script>
import { Check } from '@/shared/classes/Check';
import auditMixins from '@/shared/mixins/audits';
import commonMixin from '@/shared/mixins/common';

export default {
	name: 'ElementParticipant',

	mixins: [auditMixins, commonMixin],

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
		isDraft() {
			if (!this.audit) {
				return false;
			}

			return this.getAuditStateById(this.audit.state).name === 'draft';
		},

		users() {
			return this.$store.getters['users/usersOptions'];
		}
	},

	data() {
		return {
			form: {
				externalParticipant: null,
				participants: 'participants',
				userParticipant: null
			},
			loading: false
		};
	},

	mounted() {
		this.init();
	},

	methods: {
		init() {
			this.form = Object.assign({}, this.data.object);

			// Prefill values
			if (this.data.value) {
				this.form.externalParticipant = this.data.value.externalParticipant;
				this.form.userParticipant = this.data.value.participantId;
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

		onBlurTextInput() {
			const CHECK = new Check(this.data);
			CHECK.externalParticipant = this.form.externalParticipant;
			CHECK.participants = this.form.participants;

			this.doSubmit(CHECK);
		},

		onChangeInput(value) {
			const CHECK = new Check(this.data);
			CHECK.userParticipant = value;
			CHECK.participants = this.form.participants;

			this.doSubmit(CHECK);
		}
	}
};
</script>
