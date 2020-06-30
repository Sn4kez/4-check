<!--
@component:         CreateAudit
@environment:       Hyprid
@description:       This component handle the visibility for mobile and desktop devices
                    as well as the data storage for child components.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-09-25
@modified:          2018-10-11
-->
<template>
    <div class="view__audit main-container p-t-1--sm p-b-2--sm">
        <!-- New Audit -->
        <div v-if="!edit" class="a-center valign-center flex-column">
            <p class="font-brand font--regular-plus color-gray m-b-2">
                {{ $t('PLEASE_WAIT_AUDIT_WILL_BE_CREATED') }}
            </p>
            <!-- <div v-loading="loading"></div> -->
        </div>

        <!-- State -->
        <div class="view__audit-header" v-if="edit">
            <div class="valign-center w-100--sm">
                <p class="color-gray m-r-half m-b-0">{{ $t('STATE') }}:</p>
                <div v-if="auditStates.length && !loading && audit && audit.state" class="m-r-3">
                    <el-tag
                        v-if="getAuditStateById(audit.state).name === 'draft'">
                        {{ $t(getAuditStateById(audit.state).name) }}
                    </el-tag>

                    <el-tag type="success"
                        v-if="getAuditStateById(audit.state).name === 'approved'">
                        {{ $t(getAuditStateById(audit.state).name) }}
                    </el-tag>

                    <el-tag type="warning"
                        v-if="getAuditStateById(audit.state).name === 'sync needed'">
                        {{ $t(getAuditStateById(audit.state).name) }}
                    </el-tag>

                    <el-tag type="info"
                        v-if="getAuditStateById(audit.state).name === 'finished'">
                        {{ $t(getAuditStateById(audit.state).name) }}
                    </el-tag>

                    <el-tag type="danger"
                        v-if="getAuditStateById(audit.state).name === 'awaiting approval'">
                        {{ $t(getAuditStateById(audit.state).name) }}
                    </el-tag>
                </div>
            </div>
        </div>

        <!-- Collapse -->
        <div class="flex space-between valign-center m-t-2" v-if="edit">
            <h4 class="headline m-b-0 p-l-half--sm font--regular">{{ $t('CHECKPOINTS') }}</h4>

            <q-btn
                flat
                color="primary"
                @click="toggleAll = !toggleAll">
                <span v-if="toggleAll">
                    <q-icon name="expand_less"></q-icon>
                    {{$t('COLLAPSE_ALL')}}
                </span>
                <span v-if="!toggleAll">
                    <q-icon name="expand_more"></q-icon>
                    {{$t('EXPAND_ALL')}}
                </span>
            </q-btn>
        </div>

        <!-- Elements -->
        <div v-loading="loading">
            <ElementAuditContainer
                v-for="entry in entries.checks" :key="entry.id"
                :data="entry"
                :audit="audit"
                :open="toggleAll"
                @change-score="onChangeScore">
            </ElementAuditContainer>

            <!-- <ElementAuditContainer
                v-for="section in entries.sections" :key="section.id"
                :data="section"
                :audit="audit"
                :open="toggleAll"
                @change-score="onChangeScore">
            </ElementAuditContainer> -->
        </div>


        <!-- Buttons -->
        <div class="view__audit-footer" v-if="edit">
            <div class="form__complete-audit m-t-2">
                <div v-if="audit.state && getAuditStateById(audit.state).name === 'draft'">
                    <q-btn
                        :label="$t('DISCARD_AUDIT')"
                        color="secondary"
                        outline
                        @click="onDiscard"
                        no-ripple
                        class="m-r-1--md m-b-half--sm w-100--sm">
                    </q-btn>

                    <q-btn
                        :label="$t('COMPLETE_AUDIT')"
                        color="primary"
                        @click="onSaveComplete"
                        no-ripple
                        class="w-100--sm">
                    </q-btn>

                    <!-- Only if audit awaiting for approval -->
                    <q-btn
                        v-if="getAuditStateById(audit.state).name === 'awaiting approval'"
                        :label="$t('APPROVE')"
                        color="primary"
                        @click="onApprove"
                        no-ripple>
                    </q-btn>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import axios from 'axios';
    import auditMixins from '@/shared/mixins/audits';
    import checklistsMixin from '@/shared/mixins/checklists';
    import commonMixins from '@/shared/mixins/common';
    import {Audit} from '@/shared/classes/Audit';
    import { auditCheckpointId } from '@/components/Element/ElementAuditCheckpoint';

    export default {
	name: 'CreateAudit',

	mixins: [auditMixins, checklistsMixin, commonMixins],

	computed: {
		auditStates() {
			return this.$store.state.audits.auditStates;
		},

		company() {
			return this.$store.state.user.company;
		},

		isDeviceGreaterSM() {
			return this.$store.getters.IS_DEVICE_GREATER_SM;
		},

		user() {
			return this.$store.state.user.data;
		}
	},

	data() {
		return {
			audit: {},
            auditCheckpointId: auditCheckpointId,
			checklist: {},
			checklistId: null,
			checkpoints: [],
            currentState: {},
			edit: false,
			entries: [],
			loading: false,
			scoringschemes: [],
			sections: [],
			toggleAll: false
		};
	},

	mounted() {
		this.init();
	},

	methods: {

        init() {
            console.log('CreateAudit mounted');

            if (this.$route.query.checklist) {
                // New Audit
                this.checklistId = this.$route.query.checklist;
                this.loading = true;

                this.requestChecklist(this.checklistId)
                    .then(response => {
                        this.requestInitialChecklistData();
                    })
                    .catch(err => {
                        // No valid checklist, redirect
                        this.loading = false;
                    });
            } else if (this.$route.params.id) {
                // Continue Audit
                this.edit = true;
                this.loading = true;

                // To make all checkpoint entries offline available
                // we need to open all checkpoints to make requests to there entries
                this.toggleAll = !this.isDeviceGreaterSM;

                this.requestAudit(this.$route.params.id)
                    .then(response => {
                        this.requestInitialAuditData();
                    })
                    .catch(err => {
                        // No valid audit, redirect
                        this.loading = false;
                    });

                console.log('Continue Audit', this.$route.params);
            } else {
                // Redirect
            }
        },

		doCreateAudit() {
			const STATE_DRAFT = this.getAuditStateByName('draft');
			const AUDIT = new Audit({
				executionDue: new Date(),
				checklist: this.checklistId,
				company: this.company.id,
				user: this.user.id,
				state: STATE_DRAFT.id
			});

			this.$store
				.dispatch('audits/CREATE_AUDIT', AUDIT)
				.then(response => {
					if (response.status === 201 || response.status === 204) {
						console.log('ok audit');
						this.audit = response.data.data;

						// Redirect to edit view
						this.doStartAudit(this.audit.id).then(response => {
							this.loading = false;
							this.$router.push({ name: 'EditAuditView', params: { id: this.audit.id } });
						});
					} else {
						this.loading = false;
						this.handleErrors(response);
					}
				})
				.catch(err => {
					this.handleErrors(err);
				});
		},

        doDeleteAudit() {
            this.$store
                .dispatch('audits/DELETE_AUDIT', { id: this.audit.id })
                .then(response => {
                    this.onCancel();

                    // Redirect to home directory
                    this.$router.push({ path: '/checklists/directories' });
                })
                .catch(err => {
                    this.onCancel();
                    this.handleErrors(err);
                });
        },

		doStartAudit(id) {
			return this.$store
				.dispatch('audits/START_AUDIT', { id: id })
				.then(response => {
					console.log('doStartAudit', id);
					return response;
				})
				.catch(err => {
					this.handleErrors(err);
					return err;
				});
		},

        doUpdateAudit() {
            const AUDIT = new Audit(this.audit);
            AUDIT.state = this.currentState.id;

            console.log('doUpdateAudit', AUDIT);

            this.$store
                .dispatch('audits/UPDATE_AUDIT', AUDIT)
                .then(response => {
                    // if (response.status !== 204) {
                    if(response.status !== 200){
                        this.handleErrors(response);
                        return;
                    }

                    this.$q.notify({
                        message: this.$t('SAVE_SUCCESS'),
                        type: 'positive'
                    });

                    // this.onCancel();

                    // Redirect to home directory
                    this.$router.push({ path: '/checklists/directories' });
                })
                .catch(err => {
                    this.onCancel();
                    this.handleErrors(err);
                });
        },

        onApprove() {
            if (this.isDeviceGreaterSM) {
                this.$confirm(this.$t('TEXT_BEFORE_APPROVE_AUDIT'), {
                    type: 'warning',
                    confirmButtonText: this.$t('APPROVE'),
                    cancelButtonText: this.$t('CANCEL')
                })
                    .then(() => {
                        this.currentState = this.getAuditStateByName('approved');
                        this.doUpdateAudit();
                    })
                    .catch(() => {
                        console.log('not deleted');
                        this.onCancel();
                    });
            } else {
                const confirmObj = {
                    title: this.$t('CONFIRM'),
                    message: this.$t('TEXT_BEFORE_APPROVE_AUDIT'),
                    ok: this.$t('APPROVE'),
                    cancel: this.$t('CANCEL')
                };

                this.$q
                    .dialog(confirmObj)
                    .then(response => {
                        this.currentState = this.getAuditStateByName('approved');
                        this.doUpdateAudit();
                    })
                    .catch(err => {
                        console.log('cancel', row);
                        this.onCancel();
                    });
            }
        },

        onCancel() {
            this.$emit('cancel');
        },

		onChangeScore(value) {
			console.log('onChangeScore in CreateAudit', value);
		},

		onCompleteAudit() {
			if (this.isDeviceGreaterSM) {
				this.$store.commit('OPEN_DIALOG', {
					title: this.$t('COMPLETE_AUDIT'),
					loadComponent: 'Form/FormCompleteAudit',
					data: this.audit,
					width: '50%'
				});
			} else {
				this.$store.commit('OPEN_MODAL', {
					title: this.$t('COMPLETE_AUDIT'),
					loadComponent: 'Form/FormCompleteAudit',
					maximized: true,
					data: this.audit
				});
			}
		},

        onDiscard() {
            if (this.isDeviceGreaterSM) {
                this.$confirm(this.$t('TEXT_BEFORE_DISCARD_AUDIT'), {
                    type: 'warning',
                    confirmButtonText: this.$t('OK'),
                    cancelButtonText: this.$t('CANCEL')
                })
                    .then(() => {
                        this.doDeleteAudit();
                    })
                    .catch(() => {
                        console.log('not deleted');
                        this.onCancel();
                    });
            } else {
                const confirmObj = {
                    title: this.$t('CONFIRM'),
                    message: this.$t('TEXT_BEFORE_DISCARD_AUDIT'),
                    ok: this.$t('OK'),
                    cancel: this.$t('CANCEL')
                };

                this.$q
                    .dialog(confirmObj)
                    .then(response => {
                        this.doDeleteAudit();
                    })
                    .catch(err => {
                        console.log('cancel', row);
                        this.onCancel();
                    });
            }
        },

        onSaveComplete() {

            if(!navigator.onLine){
                this.$q.notify({
                    message: "Bitte stellen Sie eine Internetverbindung her um die Prüfung zu speichern",
                    type: 'negative'
                });
                return false;
            }

            let showMandatoryError = false;

            // check if mandatory fields are filled or not
            this.audit.results.map((elem) => {

                if(elem.object.mandatory){
                    if(this.auditCheckpointId.indexOf(elem.object.id) === -1){
                        showMandatoryError = true;
                    }
                }

            });

            if(showMandatoryError){
                this.$q.notify({
                    message: "Bitte füllen Sie alle Pflichtfelder aus.",
                    type: 'negative'
                });
                return false;
            }


            if (this.isDeviceGreaterSM) {
                this.$confirm(this.$t('TEXT_BEFORE_COMPLETE_AUDIT'), {
                    type: 'warning',
                    confirmButtonText: this.$t('OK'),
                    cancelButtonText: this.$t('CANCEL')
                })
                    .then(() => {
                        if (this.checklist.needsApproval) {
                            this.currentState = this.getAuditStateByName('awaiting approval');
                        } else {
                            this.currentState = this.getAuditStateByName('finished');
                        }

                        this.doUpdateAudit();
                    })
                    .catch(() => {
                        console.log('not deleted');
                        this.onCancel();
                    });
            } else {
                const confirmObj = {
                    title: this.$t('CONFIRM'),
                    message: this.$t('TEXT_BEFORE_COMPLETE_AUDIT'),
                    ok: this.$t('OK'),
                    cancel: this.$t('CANCEL')
                };

                this.$q
                    .dialog(confirmObj)
                    .then(response => {
                        if (this.checklist.needsApproval) {
                            this.currentState = this.getAuditStateByName('awaiting approval');
                        } else {
                            this.currentState = this.getAuditStateByName('finished');
                        }

                        this.doUpdateAudit();
                    })
                    .catch(err => {
                        console.log('cancel', row);
                        this.onCancel();
                    });
            }
        },

        onSaveDraft() {
            this.currentState = this.getAuditStateByName('draft');
            this.doUpdateAudit();
        },

		requestAudit(id) {
			return this.$store
				.dispatch('audits/GET_AUDIT', { id: id })
				.then(response => {
					this.audit = response.data.data;
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		requestAuditEntries(id) {
			return this.$store
				.dispatch('audits/GET_AUDIT_ENTRIES', { id: id })
				.then(response => {
					this.entries = response.data;

					let dd = response.data.checks.concat(response.data.sections);

					let sortByIndex = false;
					dd.map(elem => {
						/*if(elem.object.index != "0"){
							sortByIndex = true;
						}*/

						if(elem.object.index == undefined){
							sortByIndex = true;
							elem['index_custom'] = elem.index;
						}else{
							sortByIndex = true;
							elem['index_custom'] = elem.object.index;
						}

					})

					if(sortByIndex){
						/*dd = _.sortBy(dd, element => {
							return element['index_custom'];
						})*/
						dd.sort(function(a, b){
							return parseInt(a['index_custom']) > parseInt(b['index_custom']) ? 1 : -1;
						})
					}else{
						/*dd = _.sortBy(dd, element => {
							return element.object.createdAt;
						})*/
						dd.sort(function(a, b){
							return a.object.createdAt > b.object.createdAt ? 1 : -1;
						})
					}

					this.entries.checks = dd;

					/*this.entries.checks = _.sortBy(response.data.checks, element => {
						return element.createdAt;
					})

					this.entries.checks = _.sortBy(response.data.checks, element => {
						return element.object.index;
					})*/

					return response;
				})
				.catch(err => {
					return err;
				});
		},

		requestChecklist(id) {
			return this.$store
				.dispatch('checklists/GET_CHECKLIST', { id: id })
				.then(response => {
					this.checklist = response.data.data;
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		requestChecklistCheckpoints(id) {
			return this.$store
				.dispatch('checklists/GET_CHECKLIST_CHECKPOINTS', { id: id })
				.then(response => {
					this.checkpoints = response.data.data;
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		// requestChecklistEntries(id) {
		// 	return this.$store
		// 		.dispatch('checklists/GET_CHECKLIST_ENTRIES', { id: id })
		// 		.then(response => {
		// 			this.entries = response.data.data;
		// 			return response;
		// 		})
		// 		.catch(err => {
		// 			return err;
		// 		});
		// },

		requestChecklistExtensions(id) {
			return this.$store
				.dispatch('checklists/GET_CHECKLIST_EXTENSIONS', { id: id })
				.then(response => {
					this.extensions = response.data.data;
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		requestChecklistScoringSchemes(id) {
			return this.$store
				.dispatch('checklists/GET_CHECKLIST_SCORING_SCHEMES', { id: id })
				.then(response => {
					this.scoringschemes = response.data.data;
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		requestChecklistSections(id) {
			return this.$store
				.dispatch('checklists/GET_CHECKLIST_SECTIONS', { id: id })
				.then(response => {
					this.sections = response.data.data;
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		requestInitialAuditData() {
			const REQUEST = [];

			// REQUEST.push(this.doStartAudit(this.audit.id));
			REQUEST.push(this.requestAuditEntries(this.audit.id));
			REQUEST.push(this.requestChecklist(this.audit.checklist));
			// REQUEST.push(this.requestAuditStates(this.company.id));

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

		requestInitialChecklistData() {
			const REQUEST = [];

			REQUEST.push(this.requestChecklistScoringSchemes(this.checklistId));
			REQUEST.push(this.requestAuditStates(this.company.id));

			axios
				.all(REQUEST)
				.then(
					axios.spread((...results) => {
						// this.loading = false;
						this.doCreateAudit();
					})
				)
				.catch(err => {
					this.loading = false;
					console.log('err', err);
				});
		},
	},

	watch: {
		$route(to, from) {
			this.init();
		}
	}
};
</script>

<style lang="scss">
.view__audit {
	&-header {
		padding-right: $distance-1;
		padding-left: $distance-1;

		@media screen and (min-width: $screen-md) {
			display: flex;
			justify-content: space-between;
			align-items: center;
			padding: 0;
		}
	}

    &-footer {
        padding-right: $distance-1;
        padding-left: $distance-1;

        @media screen and (min-width: $screen-md) {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            padding: 0;
        }
    }
}
</style>
