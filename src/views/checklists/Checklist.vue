<!--
@component:         ChecklistsView
@environment:       Hyprid
@description:       This component handle the visibility for mobile and desktop devices
                    as well as the data storage for child components.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-08-09
@modified:          2018-10-11
-->
<template>
    <div class="view__checklists main-container" v-loading="loading">
        <div class="checklists__inner">
            <div class="checklist">

                <!-- Pagetitle -->
                <el-row class="m-t-1--sm">
                    <el-col :xs="24">
                        <h1 class="headline p-l-half--sm" v-if="checklist.name">&lt;{{checklist.name}}&gt; {{ $t('edit') }}</h1>
                        <h1 class="headline p-l-half--sm" v-else>{{ $t('EDIT_CHECKLIST') }}</h1>
                    </el-col>
                </el-row>

                <el-row>
                    <el-col :xs="24">
                        <el-card>
                            <el-collapse class="el-collapse--transparent">
                                <el-collapse-item name="1">
                                    <template slot="title">
                                        {{ $t('GENERAL_SETTINGS') }}
                                    </template>

                                    <FormEditChecklist
                                        :is-edit="true"
                                        :data="checklist" v-on:updatecheckpoint="updateCheckpointData('done')">
                                    </FormEditChecklist>
                                </el-collapse-item>
                            </el-collapse>
                        </el-card>
                    </el-col>
                </el-row>


                <div v-if="!elements.length" class="text-center m-t-3 color-gray">
                    <span class="circle">
                        <q-icon name="check_box" size="4rem"></q-icon>
                    </span>
                    <p class="m-t-1">{{ $t('NO_ITEMS_IN_CHECKLIST') }}</p>
                </div>

                <div class="w-100 m-t-1" :class="{'m-t-3': elements.length}">
                    <!-- Header -->
                    <div class="flex space-between valign-center" v-if="elements.length">
                        <h4 class="headline m-b-0 p-l-half--sm font--regular">{{ $t('CHECKLIST_ELEMENTS') }}</h4>

                        <div>
                            <q-btn
                                flat
                                color="primary"
                                @click="onClickEditOrder">
                                <span v-if="editOrder">
                                    <q-icon name="done"></q-icon>
                                    {{$t('SAVE_ORDER')}}
                                </span>
                                <span v-if="!editOrder">
                                    <q-icon name="drag_indicator"></q-icon>
                                    {{$t('EDIT_ORDER')}}
                                </span>
                            </q-btn>

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
                    </div>

                    <!-- Main -->
                    <div class="checklist__main">
                        <!-- Element -->
                        <draggable
                            :list="elements"
                            :move="checkEditOrder"
                            :options="drapOptions">
                            <ElementContainer
                                v-for="element in elements" :key="element.id"
                                :data="element"
                                :open="toggleAll">
                            </ElementContainer>
                        </draggable>

                        <div class="m-t-2 m-b-2 text-center">
                            <ButtonAddChecklistElement :context="context"></ButtonAddChecklistElement>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import { getObjectSize } from '@/services/utils';
import { getElements } from '@/shared/checklistElements';
import { Checklist } from '@/shared/classes/Checklist';
import {
	Extension,
	LocationExtension,
	NotefieldExtension,
	ParticipantExtension,
	PictureExtension,
	SignatureExtension,
	TextfieldExtension
} from '@/shared/classes/Extension';
import FormEditChecklist from '@/components/Form/FormEditChecklist';
import commonMixin from '@/shared/mixins/common';
import ButtonAddChecklistElement from '@/components/Button/ButtonAddChecklistElement';
import { Section } from '@/shared/classes/Section';

import { Checkpoint } from '@/shared/classes/Checkpoint';
import draggable from 'vuedraggable';

export default {
	name: 'ChecklistView',

	mixins: [commonMixin],

	components: {
		ButtonAddChecklistElement,
		draggable, 
		FormEditChecklist
	},

	computed: {
		company() {
			return this.$store.state.user.company;
		},

		directories() {
			return this.$store.state.directories.directories;
		},

		isDeviceGreaterSM() {
			return this.$store.getters.IS_DEVICE_GREATER_SM;
		},

		groups() {
			return this.$store.state.companies.groups;
		},

		parentId() {
			return this.$route.query.parent || '0';
		},

		rootDirectory() {
			return this.$store.state.directories.rootDirectory;
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
			checklist: {},
			context: {
				id: '', // Set during initialization
				type: 'checklists'
			},
			drapOptions: {
				handle: '.drag__handler'
			},
			editOrder: false,
			elements: [],
			form: {},
			loading: false,
			toggleAll: false
		};
	},

	mounted() {
		this.init();
	},

	methods: {
		updateCheckpointData: function(arg){
			this.requestChecklist(this.getIdFromRoute(this.$route));
		},
		checkEditOrder(evt) {
			return this.editOrder;
		},

		doUpdateCheckpoint(checkpoint) {
			return this.$store
				.dispatch('checkpoints/UPDATE_CHECKPOINT', checkpoint)
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		doUpdateExtension(extension) {
			return this.$store
				.dispatch('extensions/UPDATE_EXTENSION', extension)
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		doUpdateSection(section) {
			return this.$store
				.dispatch('sections/UPDATE_SECTION', section)
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		init() {
			console.log('ChecklistsView mounted', this.$route, this.parentId, this.rootDirectory);

			this.requestInitialData();

			this.registerEvents();
		},

		handleAddElement(data) {
			switch (data.item.name) {
				case 'section':
					this.handleCreateSection(data);
					break;
				case 'question':
					this.handleCreateQuestion(data);
					break;
				case 'value-question':
					this.handleCreateQuestionValue(data);
					break;
				case 'textfield':
					this.handleCreateTextfield(data);
					break;
				case 'notefield':
					this.handleCreateNotefield(data);
					break;
				case 'picture':
					this.handleCreatePicture(data);
					break;
				case 'signature':
					this.handleCreateSignature(data);
					break;
				case 'location':
					this.handleCreateLocation(data);
					break;
				case 'participant':
					this.handleCreateParticipant(data);
					break;
			}
			console.log('handleAddElement', data);
		},

		handleChangeOrder() {
			const REQUEST = [];
			
			this.elements.forEach((element, index) => {
				if (element.objectType === 'checkpoint') {
					const CHECKPOINT = new Checkpoint(element.object);
					CHECKPOINT.mandatory = CHECKPOINT.mandatory === 'true' ? 1 : 0;
					CHECKPOINT.index = index;
					REQUEST.push(this.doUpdateCheckpoint(CHECKPOINT));
				}

				if (element.objectType === 'section') {
					const SECTION = new Section(element.object);
					SECTION.index = index;
					REQUEST.push(this.doUpdateSection(SECTION));
				}

				if (element.objectType === 'extension') {
					let EXTENSION = {};
					const data = Object.assign({}, element.object);
					data.index = index;
					data.data = Object.assign({}, element.object.object);

					switch (element.object.type) {
						case 'textfield':
							if (element.object.object.fixed) {
								EXTENSION = new TextfieldExtension(data);
							} else {
								EXTENSION = new NotefieldExtension(data);
							}
							break;
						case 'picture':
							if (element.object.object.type === 'signature') {
								EXTENSION = new SignatureExtension(data);
							} else {
								EXTENSION = new PictureExtension(data);
							}
							break;
						case 'location':
							data.data.fixed = data.data.fixed ? 1 : 0;
							EXTENSION = new LocationExtension(data);
							break;
						case 'participant':
							EXTENSION = new ParticipantExtension(data);
							break;
					}

					REQUEST.push(this.doUpdateExtension(EXTENSION));
				}
			});
			
			axios
				.all(REQUEST)
				.then(
					axios.spread((...results) => {
						//
					})
				)
				.catch(err => {
					//
				});
		},

		handleCreateLocation(data) {
			console.log('handleCreateLocation', data);
			const LOCATION = new LocationExtension({});
			LOCATION.id = data.context.id;

			console.log('handleCreateLocation', data, LOCATION);
			this.$store
				.dispatch(data.context.type + '/CREATE_EXTENSION', LOCATION)
				.then(response => {
					console.log('handleCreateLocation response', response);
					this.requestChecklistEntries(this.checklist.id);
					this.$eventbus.$emit('checkpoint:' + data.context.id + ':refresh');
					this.$eventbus.$emit('section:' + data.context.id + ':refresh');
				})
				.catch(err => {
					console.log('handleCreateLocation err', err);
				});
		},

		handleCreateNotefield(data) {
			const NOTEFIELD = new NotefieldExtension({});
			NOTEFIELD.id = data.context.id;

			this.$store
				.dispatch(data.context.type + '/CREATE_EXTENSION', NOTEFIELD)
				.then(response => {
					console.log('handleCreateNotefield response', response);
					this.requestChecklistEntries(this.checklist.id);
					this.$eventbus.$emit('checkpoint:' + data.context.id + ':refresh');
					this.$eventbus.$emit('section:' + data.context.id + ':refresh');
				})
				.catch(err => {
					console.log('handleCreateNotefield err', err);
				});
		},

		handleCreateParticipant(data) {
			console.log('handleCreateParticipant', data);
			const PARTICIPANT = new ParticipantExtension({});
			PARTICIPANT.id = data.context.id;

			console.log('handleCreateParticipant', data, PARTICIPANT);
			this.$store
				.dispatch(data.context.type + '/CREATE_EXTENSION', PARTICIPANT)
				.then(response => {
					console.log('handleCreateParticipant response', response);
					this.requestChecklistEntries(this.checklist.id);
					this.$eventbus.$emit('checkpoint:' + data.context.id + ':refresh');
					this.$eventbus.$emit('section:' + data.context.id + ':refresh');
				})
				.catch(err => {
					console.log('handleCreateParticipant err', err);
				});
		},

		handleCreatePicture(data) {
			const PICTURE = new PictureExtension({});
			PICTURE.id = data.context.id;

			console.log('handleCreatePicture', data, PICTURE);
			this.$store
				.dispatch(data.context.type + '/CREATE_EXTENSION', PICTURE)
				.then(response => {
					console.log('handleCreatePicture response', response);
					this.requestChecklistEntries(this.checklist.id);
					this.$eventbus.$emit('checkpoint:' + data.context.id + ':refresh');
					this.$eventbus.$emit('section:' + data.context.id + ':refresh');
				})
				.catch(err => {
					console.log('handleCreatePicture err', err);
				});
		},

		handleCreateQuestion(data) {
			console.log('handlecreatequestion', data);
			if (this.isDeviceGreaterSM) {
				this.$store.commit('OPEN_DIALOG', {
					title: this.$t('CREATE_QUESTION'),
					loadComponent: 'Form/FormEditQuestionChoice',
					width: '50%',
					data: data
				});
			} else {
				this.$store.commit('OPEN_MODAL', {
					title: this.$t('CREATE_QUESTION'),
					loadComponent: 'Form/FormEditQuestionChoice',
					maximized: true,
					data: data
				});
			}
		},

		handleCreateQuestionValue(data) {
			console.log('handleCreateQuestionValue', data);
			if (this.isDeviceGreaterSM) {
				this.$store.commit('OPEN_DIALOG', {
					title: this.$t('CREATE_QUESTION'),
					loadComponent: 'Form/FormEditQuestionValue',
					width: '50%',
					data: data
				});
			} else {
				this.$store.commit('OPEN_MODAL', {
					title: this.$t('CREATE_QUESTION'),
					loadComponent: 'Form/FormEditQuestionValue',
					maximized: true,
					data: data
				});
			}
		},

		handleCreateSection(item) {
			let element;

			this.$q
				.dialog({
					title: this.$t('CREATE_SECTION'),
					message: this.$t('PLEASE_ENTER_TITLE'),
					ok: this.$t('OK'),
					cancel: this.$t('CANCEL'),
					prompt: {
						model: '',
						type: 'text'
					}
				})
				.then(value => {
					// Create new section
					element = new Section({ title: value });
					element.id = this.checklist.id;

					this.$store
						.dispatch('checklists/CREATE_SECTION', element)
						.then(response => {
							this.requestChecklistEntries(this.checklist.id);
						})
						.catch(err => {
							console.log('ERR CREATE SECTION');
						});

					console.log(value);
				})
				.catch(err => {});
		},

		handleCreateSignature(data) {
			const SIGNATURE = new SignatureExtension({});
			SIGNATURE.id = data.context.id;

			console.log('handleCreateSignature', data, SIGNATURE);
			this.$store
				.dispatch(data.context.type + '/CREATE_EXTENSION', SIGNATURE)
				.then(response => {
					console.log('handleCreateSignature response', response);
					this.requestChecklistEntries(this.checklist.id);
					this.$eventbus.$emit('checkpoint:' + data.context.id + ':refresh');
					this.$eventbus.$emit('section:' + data.context.id + ':refresh');
				})
				.catch(err => {
					console.log('handleCreateSignature err', err);
				});
		},

		handleCreateTextfield(data) {
			this.$q
				.dialog({
					title: this.$t('CREATE_TEXT'),
					message: this.$t('PLEASE_ENTER_TEXT'),
					ok: this.$t('OK'),
					cancel: this.$t('CANCEL'),
					prompt: {
						model: '',
						type: 'text'
					}
				})
				.then(value => {
					// Create new textfield
					const TEXTFIELD = new TextfieldExtension({
						data: {
							text: value,
							fixed: 1
						}
					});
					TEXTFIELD.id = data.context.id;

					this.$store
						.dispatch(data.context.type + '/CREATE_EXTENSION', TEXTFIELD)
						.then(response => {
							console.log('handleCreateTextfield response', response);
							this.requestChecklistEntries(this.checklist.id);
							this.$eventbus.$emit('checkpoint:' + data.context.id + ':refresh');
							this.$eventbus.$emit('section:' + data.context.id + ':refresh');
						})
						.catch(err => {
							console.log('handleCreateTextfield err', err);
						});
				})
				.catch(err => {});
		},

		onClickEditOrder() {
			if (this.editOrder) {
				this.handleChangeOrder();
			}

			this.editOrder = !this.editOrder;
			
		},

		registerEvents() {
			this.$eventbus.$on('checklist:add:element', data => {
				console.log('Event checklist:add:element', data);

				if (this.isDeviceGreaterSM) {
					this.$store.commit('CLOSE_DIALOG');
				} else {
					this.$store.commit('CLOSE_MODAL');
				}

				this.handleAddElement(data);
			});

			this.$eventbus.$on('checklist:refresh', () => {
				console.log('Event checklist:refresh');

				this.requestChecklistEntries(this.checklist.id);
			});
		},

		requestChecklist(id) {
			return this.$store
				.dispatch('checklists/GET_CHECKLIST', { id: id })
				.then(response => {
					this.checklist = response.data.data;
					this.context.id = this.checklist.id;
					
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		requestChecklistEntries(id) {
			return this.$store
				.dispatch('checklists/GET_CHECKLIST_ENTRIES', { id: id })
				.then(response => {
					
					let dd = response.data.data
					// this.elements = response.data.data
					let sortByIndex = false;
					dd.map(elem => {
						if(elem.object.index != "0"){
							sortByIndex = true;
						}
					})
					console.log('sortByIndex', sortByIndex);
					if(sortByIndex){
						/*dd = _.sortBy(dd, element => {
							return element.object.index;
						})*/
						dd.sort(function(a, b){
							return parseInt(a.object.index) > parseInt(b.object.index) ? 1 : -1;
						})
					}else{
						/*dd = _.sortBy(dd, element => {
							return element.object.createdAt;
						})*/
						dd.sort(function(a, b){
							return a.object.createdAt > b.object.createdAt ? 1 : -1;
						})
					}
					
					this.elements = dd;
					console.log('CHECK ME')
					console.log(dd)

					return response;
				})
				.catch(err => {
					return err;
				});
		},

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

		requestInitialData() {
			if (!this.users.length) {
				this.$store.dispatch('users/GET_USERS');
			}

			if (!this.groups.length) {
				this.$store.dispatch('companies/GET_GROUPS', { id: this.company.id });
			}

			const CHECKLIST_ID = this.getIdFromRoute(this.$route);
			const REQUEST = [];

			REQUEST.push(this.requestChecklist(CHECKLIST_ID));
			REQUEST.push(this.requestChecklistEntries(CHECKLIST_ID));
			REQUEST.push(this.requestCompanyScoringSchemes());

			this.loading = true;
			axios
				.all(REQUEST)
				.then(
					axios.spread((...results) => {
						console.log('ready');
						this.loading = false;
					})
				)
				.catch(err => {
					this.loading = false;
					return err;
				});
		},

		requestCompanyScoringSchemes() {
			return this.$store
				.dispatch('companies/GET_COMPANY_SCORING_SCHEMES', { id: this.company.id })
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

		unregisterEvents() {
			this.$eventbus.$off('checklist:add:element');
		}
	},

	destroyed() {
		this.unregisterEvents();
	},

	watch: {
		$route(to, from) {
			this.init();
		}
	}
};
</script>

<style lang="scss">
.checklist {
	&__main {
		padding: 0 0.6rem;
		max-width: 100vw;
		width: 100%;

		.el-card__header {
			padding: 0.5rem 0 0.5rem 1rem;
		}

		@media screen and (min-width: $screen-md) {
			flex: 1 0 auto;
			padding: 0;
		}
	}
}

.circle {
	border: 1px solid $c-light-gray;
	border-radius: 50%;
	background-color: #ffffff;
	height: 7rem;
	width: 7rem;
	display: inline-flex;
	align-items: center;
	justify-content: center;
}
</style>
