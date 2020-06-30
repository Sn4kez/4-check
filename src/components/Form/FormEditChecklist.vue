<!--
@component:         FormEditChecklist
@environment:       Hyprid
@description:       We use this component in both the checklist view component as well as inside a dialog/modal.
                    In the dialog the form should render vertically and horizontal in the checklist view component.

                    Watcher for selectedUsers is used instead of an event because we need to get the old value to remove/add
                    a user.

                    ToDo: Better UX to add a group as approvers.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-08-07
@modified:          2018-10-22
-->
<template>
    <el-form class="checklist-form">
        <el-row :gutter="20">
            <el-col :xs="24" :md="12" class="flex--md1" :class="[{'flex--md':isEdit}, {'w-100--md':!isEdit}]">
                <q-field class="m-b-1 w-50--md1" :class="{'w-50--md':isEdit}"
                    :error="$v.form.name.$error"
                    :error-label="$t('PLEASE_FILL_OUT_ALL_REQUIRED_FIELDS')">
                    <q-input
                        :stack-label="$t('NAME')"
                        v-model.trim="$v.form.name.$model">
                    </q-input>
                </q-field>

                <q-field class="p-l-1--md1 m-b-1 w-50--md1" :class="{'w-50--md p-l-1--md':isEdit}"
                    :error="$v.form.description.$error"
                    :error-label="$t('PLEASE_FILL_OUT_ALL_REQUIRED_FIELDS')">
                    <q-input
                        :stack-label="$t('DESCRIPTION')"
                        v-model.trim="$v.form.description.$model">
                    </q-input>
                </q-field>
            </el-col>

            <el-col :xs="24" :md="12" :class="[{'w-100--md':!isEdit}]">
                <div class="text-right">
                    <q-btn
                        class="w-100--sm m-l-1--md m-t-1--sm"
                        color="primary"
                        @click="onClickBtnSave"
                        :label="$t('SAVE')"
                        v-loading="loading" />
                </div>
            </el-col>
        </el-row>

        <el-row v-if="isEdit" class="m-t-1">
            <!-- Additional Settings -->
            <el-collapse class="el-collapse--transparent">
                <el-collapse-item name="1">
                    <template slot="title">
                        {{ $t('ADVANCED_SETTINGS') }}
                    </template>

                    <el-row :gutter="40" class="flex p-t-1">
                        <el-col :xs="24" :sm="8">
                            <!-- Inspection Plans -->
                            <ul class="list">
                                <li v-for="plan in inspectionPlans" :key="plan.id"
                                    class="flex valign-center space-between">
                                    <span class="m-r-11">{{plan.name}} ({{ $t(plan.type.toUpperCase()) }})</span>
                                    <div>
                                        <el-tooltip class="item" effect="dark" :content="$t('EDIT')" placement="top">
                                            <q-btn
                                                no-ripple
                                                flat
                                                round
                                                size="0.8rem"
                                                icon="edit"
                                                @click="onClickEditPlan(plan)"
                                                :loading="loadingPlans">
                                            </q-btn>
                                        </el-tooltip>

                                        <el-tooltip class="item" effect="dark" :content="$t('DELETE')" placement="top">
                                            <q-btn
                                                no-ripple
                                                flat
                                                round
                                                size="0.8rem"
                                                icon="delete"
                                                @click="onClickRemovePlan(plan)"
                                                :loading="loadingPlans">
                                            </q-btn>
                                        </el-tooltip>
                                    </div>
                                </li>
                            </ul>

                            <!-- Create new inspection plan -->
                            <q-btn v-if="!inspectionPlans.length"
                                flat
                                no-ripple
                                color="primary"
                                @click="onClickBtnCreateInspectionPlan"
                                :label="$t('CREATE_RECURRING_CHECK')"
                                class="m-t-1" />
                        </el-col>

                        <el-col :xs="24" :sm="8" class="m-t-2--sm">
                            <!-- <q-field class="q-field--overflow q-field--wrap m-b-1">
                                <q-toggle v-model="form.appovalRequired" :label="$t('APPROVAL_FOR_FINAL_RELEASE_REQUIRED')" :true-value="1" :false-value="0" />
                            </q-field>

                            <q-field v-if="form.appovalRequired">
                                <q-select
                                    :placeholder="$t('SELECT_USER')"
                                    multiple
                                    checkbox
                                    chips
                                    v-model="selectedUsers"
                                    :options="usersOptions" />
                            </q-field> -->
                            <!-- ToDo: Find a better UX for when a group has been added.
                                Because currently only the users within a group will be added and not the group himself.
                             -->
                            <!-- <q-field v-if="form.appovalRequired" class="m-t-1">
                                <q-select
                                    :placeholder="$t('SELECT_GROUP')"
                                    multiple
                                    checkbox
                                    chips
                                    v-model="selectedGroups"
                                    :options="groupOptions" />
                            </q-field> -->
                        </el-col>

                        <el-col :xs="24" :sm="8" class="m-t-2--sm">
                            <q-field class="q-field--overflow q-field--wrap m-b-1">
                                <q-toggle
                                    v-model="form.chooseRandom"
                                    :label="$t('USE_RANDOM_GENERATOR')"
                                    :true-value="1" :false-value="0"
                                    @input="onChangeRandomNumber" />
                                <p class="m-t-1 font--small color-gray">{{$t('QUESTIONS_WILL_BE_SELECTED_RANDOMLY_IN_CASE_OF_ACTIVATED_RANDOM_GENERATOR')}}</p>
                            </q-field>

                            <q-field v-if="form.chooseRandom">
                                <p>{{$t('NUMBER_QUESTIONS')}}</p>
                                <el-input-number v-model.number="form.numberQuestions" :min="0" :step="1"></el-input-number>
                            </q-field>
                        </el-col>
                    </el-row>
                </el-collapse-item>
            </el-collapse>
        </el-row>
    </el-form>
</template>

<script>
import { required, minLength } from 'vuelidate/lib/validators';
import { Checklist } from '@/shared/classes/Checklist';
import commonMixin from '@/shared/mixins/common';

export default {
	name: 'FormEditChecklist',

	mixins: [commonMixin],

	props: {
		data: {
			type: Object,
			required: false,
			default: function() {
				return {};
			}
		},

		isEdit: {
			type: Boolean,
			required: false,
			default: false
		}
	},

	computed: {
		appovalRequired() {
			return this.form.appovalRequired;
		},

		company() {
			return this.$store.state.user.company;
		},

		inspectionPlans() {
			return this.$store.state.audits.inspectionPlans;
		},

		isDeviceGreaterSM() {
			return this.$store.getters.IS_DEVICE_GREATER_SM;
		},

		groupOptions() {
			return this.$store.getters['companies/groupOptions'];
		},

		rootDirectory() {
			return this.$store.state.directories.rootDirectory;
		},

		usersOptions() {
			return this.$store.getters['users/usersOptions'];
		}
	},

	data() {
		return {
			edit: false,
			form: {
				appovalRequired: 0,
				name: '',
				description: '',
				numberQuestions: 0,
				chooseRandom: 0
			},
			loading: false,
			loadingPlans: false,
			selectedGroups: [],
			selectedUsers: []
		};
	},

	validations: {
		form: {
			name: { required },
			description: { required }
		}
	},

	mounted() {
		this.init();
	},

	methods: {
		// doAddApproverGroup(items) {
		// 	const DATA = {
		// 		id: this.data.id,
		// 		groupId: items[0]
		// 	};

		// 	this.$store.dispatch('checklists/UPDATE_CHECKLIST_APPROVERS_GROUP', DATA).then(response => {
		// 		console.log('selectedUsers', response);
		// 	});
		// },

		doAddApproverUser(items) {
			const DATA = {
				id: this.data.id,
				userId: items[0]
			};

			this.$store.dispatch('checklists/UPDATE_CHECKLIST_APPROVER', DATA).then(response => {
				console.log('selectedUsers', response);
			});
		},

		// doRemoveApproverGroup(items) {
		// 	const DATA = {
		// 		id: this.data.id,
		// 		groupId: items[0]
		// 	};

		// 	this.$store.dispatch('checklists/DELETE_CHECKLIST_APPROVERS_GROUP', DATA).then(response => {
		// 		console.log('selectedUsers', response);
		// 	});
		// },

		onChangeRandomNumber(value) {
			console.log(value);
			if (!this.form.chooseRandom) {
				this.form.numberQuestions = 0;
			}
		},

		onClickEditPlan(plan) {
			if (this.isDeviceGreaterSM) {
				this.$store.commit('OPEN_DIALOG', {
					title: this.$t('EDIT_INSPECTION_PLAN'),
					loadComponent: 'Form/FormEditInspectionPlan',
					width: '50%',
					data: { checklist: this.data, plan: plan },
					refreshAfterClose: true
				});
			} else {
				this.$store.commit('OPEN_MODAL', {
					title: this.$t('EDIT_INSPECTION_PLAN'),
					loadComponent: 'Form/FormEditInspectionPlan',
					maximized: true,
					data: { checklist: this.data, plan: plan },
					refreshAfterClose: true
				});
			}
		},

		onClickRemovePlan(plan) {
			this.$store
				.dispatch('audits/DELETE_AUDIT_PLAN', { id: plan.id })
				.then(response => {
					this.loading = false;
					this.requestInspectionPlans();

					this.$q.notify({
						message: this.$t('DELETE_SUCCESS'),
						type: 'positive'
					});
				})
				.catch(err => {
					this.loading = false;
					this.handleErrors(err);
				});
		},

		doRemoveApproverUser(items) {
			const DATA = {
				id: this.data.id,
				userId: items[0]
			};

			this.$store.dispatch('checklists/DELETE_CHECKLIST_APPROVER', DATA).then(response => {
				console.log('selectedUsers', response);
			});
		},

		doSave() {
			
			let checklist = {};
			let dispatcherName = 'checklists/CREATE_CHECKLIST';

			if (this.edit) {
				checklist = new Checklist(this.form);
				dispatcherName = 'checklists/UPDATE_CHECKLIST';
			} else {
				checklist = new Checklist({
					description: this.form.description,
					name: this.form.name,
					parentId: this.parentId,
					numberQuestions: this.form.numberQuestions
				});
			}

			this.loading = true;

			this.$store
				.dispatch(dispatcherName, checklist)
				.then(response => {
					this.loading = false;

					if (response.status === 201 || response.status === 204) {
						this.$q.notify({
							message: this.$t('SAVE_SUCCESS'),
							type: 'positive'
						});

						this.$emit('updatecheckpoint');

						if (!this.edit) {
							this.$router.push({ path: '/checklists/checklist/' + response.data.data.id });

							if (this.isDeviceGreaterSM) {
								this.$store.commit('CLOSE_DIALOG');
							} else {
								this.$store.commit('CLOSE_MODAL');
							}
						}
					} else {
						this.handleErrors(response);
					}
				})
				.catch(err => {
					this.loading = false;
				});
		},

		init() {
			this.edit = this.isEdit;
			this.edit = this.data.id ? true : false;
			this.parentId = this.data.parentId || null;

			if (!this.isEdit) {
				this.form = Object.assign({}, this.form, this.data.object);
			}

			this.registerEvents();
		},

		onClickBtnCreateInspectionPlan() {
			if (this.isDeviceGreaterSM) {
				this.$store.commit('OPEN_DIALOG', {
					title: this.$t('CREATE_INSPECTION_PLAN'),
					loadComponent: 'Form/FormEditInspectionPlan',
					width: '50%',
					data: { checklist: this.data, plan: {} }
				});
			} else {
				this.$store.commit('OPEN_MODAL', {
					title: this.$t('CREATE_INSPECTION_PLAN'),
					loadComponent: 'Form/FormEditInspectionPlan',
					maximized: true,
					data: { checklist: this.data, plan: {} }
				});
			}
		},

		onClickBtnSave() {
			this.$v.$touch();
			if (this.$v.$invalid) {
				return false;
			} else {
				this.doSave();
			}
		},

		registerEvents() {
			this.$eventbus.$on('dialog:closed', () => {
				this.requestInspectionPlans();
			});

			this.$eventbus.$on('modal:closed', () => {
				this.requestInspectionPlans();
			});
		},

		requestChecklist(id) {
			return this.$store
				.dispatch('checklists/GET_CHECKLIST', { id: id })
				.then(response => {
					this.form = response.data.data;
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		requestChecklistApprovers(id) {
			return this.$store
				.dispatch('checklists/GET_CHECKLIST_APPROVERS', { id: id })
				.then(response => {
					if (response.data.data.length) {
						response.data.data.forEach(item => {
							this.selectedUsers.push(item.id);
						});
						this.form.appovalRequired = 1;
					} else {
						this.selectedUsers = [];
					}
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		requestInspectionPlans() {
			this.loadingPlans = true;

			return this.$store
				.dispatch('audits/GET_AUDIT_PLANS', { id: this.company.id, checklist_id: this.data.id })
				.then(response => {
					this.loadingPlans = false;
					return response;
				})
				.catch(err => {
					this.loadingPlans = false;
					return err;
				});
		},

		unregisterEvents() {
			this.$eventbus.$off('dialog:closed');
			this.$eventbus.$off('modal:closed');
		}
	},

	destroyed() {
		this.unregisterEvents();
	},

	watch: {
		data(newValue, oldValue) {
			this.edit = this.data.id ? true : false;
			this.parentId = this.data.parentId || null;
			this.form = Object.assign({}, this.form, this.data);

			this.requestChecklistApprovers(this.data.id);
			this.requestInspectionPlans();
		},

		/**
		 * Refresh users and groups if checklist need to be approved by user(s).
		 * If checklist don´t need approval delete all approvers.
		 */
		appovalRequired(newValue, oldValue) {
			if (newValue) {
				this.$store.dispatch('users/GET_USERS');
				this.$store.dispatch('companies/GET_GROUPS', { id: this.company.id });
			} else {
				// this.$store.dispatch('checklists/DELETE_CHECKLIST_APPROVERS', { id: this.data.id });
				if (this.selectedUsers.length) {
					this.form.appovalRequired = 1;
				}
			}
		},

		// selectedGroups(newValue, oldValue) {
		// 	console.log('watch selectedGroups', newValue, oldValue);

		// 	if (this.selectedGroups.length > oldValue.length) {
		// 		const addGroup = this.selectedGroups.filter(x => !oldValue.includes(x));
		// 		console.log('group hinzugefügt', addGroup);

		// 		this.doAddApproverGroup(addGroup);
		// 	}

		// 	if (this.selectedGroups.length < oldValue.length) {
		// 		const removeGroup = oldValue.filter(x => !this.selectedGroups.includes(x));
		// 		console.log('group entfernt', removeGroup);

		// 		this.doRemoveApproverGroup(removeGroup);
		// 	}
		// },

		selectedUsers(newValue, oldValue) {
			if (this.selectedUsers.length > oldValue.length) {
				const addUser = this.selectedUsers.filter(x => !oldValue.includes(x));
				this.doAddApproverUser(addUser);
			}

			if (this.selectedUsers.length < oldValue.length) {
				const removeUser = oldValue.filter(x => !this.selectedUsers.includes(x));
				this.doRemoveApproverUser(removeUser);
			}
		}
	}
};
</script>
