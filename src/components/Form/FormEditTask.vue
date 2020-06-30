<!--
@component:         FormEditTask
@description:       Form to create and edit task
                    There are more attributes available but we start with a smaller set.
                    In further development only remove the comments in markup and javascript.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-07-09
@modified:          2018-10-16
-->
<template>
    <el-form v-loading="loading" class="form__edit-task">

        <el-row :gutter="20">
            <el-col :xs="24">
                <q-field
                    class="m-b-1"
                    :error="$v.form.name.$error"
                    :error-label="$t('PLEASE_FILL_OUT_ALL_REQUIRED_FIELDS')">
                    <q-input
                        v-model.trim="$v.form.name.$model"
                        :float-label="$t('NAME')">
                    </q-input>
                </q-field>
            </el-col>
        </el-row>

        <el-row :gutter="20">
            <el-col :xs="24">
                <q-field class="m-b-1">
                    <q-input
                        v-model="form.description"
                        :float-label="$t('DESCRIPTION')"
                        type="textarea"
                        clearable>
                    </q-input>
                </q-field>
            </el-col>
        </el-row>

        <el-row :gutter="20">
            <el-col :xs="24" :md="12">
                <q-field class="m-b-1">
                    <q-datetime
                        :float-label="$t('DUE_DATE')"
                        v-model="form.doneAt"
                        type="date"
                        clearable />
                </q-field>
            </el-col>

            <el-col :xs="24" :md="12">
                <q-field
                    class="m-b-1"
                    :error="$v.form.assignee.$error"
                    :error-label="$t('PLEASE_FILL_OUT_ALL_REQUIRED_FIELDS')">
                    <q-select
                        v-model.trim="$v.form.assignee.$model"
                        :float-label="$t('TO_BE_DONE_BY')"
                        :options="users"
                        radio
                        clearable />
                </q-field>
            </el-col>
        </el-row>

        <el-row :gutter="20">
            <el-col :xs="24" :md="12">
                <q-field class="m-b-1">
                    <q-select
                        v-model="form.type"
                        :float-label="$t('TYPE_OF_TASK')"
                        :options="taskTypes"
                        clearable />
                </q-field>
            </el-col>

            <el-col :xs="24" :md="12">
                <q-field class="m-b-1">
                    <q-select
                        v-model.trim="form.priority"
                        :float-label="$t('PRIORITY')"
                        :options="taskPriorities"
                        clearable />
                </q-field>
            </el-col>
        </el-row>

        <!-- Preview Image -->
        <el-row v-if="showPreview" class="m-t-2">
            <el-col :xs="24">
                <h3 class="headline font--regular">{{$t('ATTACHED_IMAGE')}}</h3>
            </el-col>
            <el-col :xs="24" :sm="24" class="pos-r">
                <q-btn
                    icon="zoom_out_map"
                    color="secondary"
                    no-ripple
                    round
                    @click="showPreviewLarge = !showPreviewLarge"
                    class="form__edit-task__btn-image-preview">
                </q-btn>

                <img
                    :src="form.source_b64"
                    :class="{'form__edit-task__image-preview--large': showPreviewLarge}"
                    class="form__edit-task__image-preview">

                <div class="m-t-half">
                    <q-btn
                        icon="delete"
                        :label="$t('REMOVE_IMAGE')"
                        flat
                        no-ripple
                        @click="onRemoveImage">
                    </q-btn>
                </div>
            </el-col>
        </el-row>

        <!-- <el-row :gutter="20" v-if="locations.length">
            <el-col :xs="24" :md="12">
                <q-field class="m-b-1">
                    <q-select
                        v-model.trim="form.location"
                        :float-label="$t('LOCATION')"
                        :options="locations"
                        clearable />
                </q-field>
            </el-col>
        </el-row> -->

        <!-- <el-row :gutter="20" class="m-t-1">
            <el-col :xs="24" :md="24">
                <q-field class="q-field--overflow m-b-1">
                    <q-toggle
                        v-model="form.giveNotice"
                        :label="$t('NOTIFY_ME')"
                        :true-value="1"
                        :false-value="0">
                    </q-toggle>
                </q-field>
            </el-col>
        </el-row> -->

        <el-row class="m-t-2">
            <el-col :xs="24">
                <h3 class="headline">{{$t('ADDITIONAL_INFORMATION')}}</h3>
            </el-col>

            <el-col :xs="24">
                <q-list link no-border>
                    <q-item>
                        <q-item-side left icon="add_a_photo"></q-item-side>
                        <q-item-main>
                            <q-uploader
                                :multiple="false"
                                url=""
                                @add="onChangeUpload"
                                hide-upload-button
                                :float-label="$t('ADD_PICTURE')"
                                hide-underline />
                        </q-item-main>
                    </q-item>
                    <!-- <q-item>
                        <q-item-side left icon="keyboard_voice"></q-item-side>
                        <q-item-main>
                            {{$t('ADD_VOICE_RECORD')}}
                        </q-item-main>
                    </q-item> -->
                    <!-- <q-item>
                        <q-item-side left icon="insert_link"></q-item-side>
                        <q-item-main>
                            {{$t('ADD_LINK_DOCUMENT')}}
                        </q-item-main>
                    </q-item> -->
                </q-list>
            </el-col>
        </el-row>

        <el-row :gutter="20" class="m-t-2">
            <el-col :xs="24" :sm="12">
                <p class="p-t-half font--small">
                    <span v-if="edit && !loading">
                        {{$t('CREATED_AT')}} {{ $d(new Date(form.createdAt), 'long') }} {{$t('BY')}} {{getUserById(form.issuer).lastName}}, {{getUserById(form.issuer).firstName}}
                    </span>
                </p>
            </el-col>

            <el-col :xs="24" :sm="12">
                <div class="text-right">
                    <q-btn v-if="isDeviceGreaterSM"
                        :label="$t('CANCEL')"
                        @click="onCancel"
                        flat
                        no-ripple
                        class="m-r-1">
                    </q-btn>

                    <q-btn
                        :label="$t('SAVE')"
                        color="primary"
                        no-ripple
                        @click="onSubmit"
                        class="w-100--sm m-t-1--sm"
                        :loading="loading">
                    </q-btn>
                </div>
            </el-col>
        </el-row>
    </el-form>
</template>

<script>
import axios from 'axios';
import { Task } from '@/shared/classes/Task';
import { required, email, minLength } from 'vuelidate/lib/validators';
import { cloneDeep, forEach } from 'lodash';
import { getBase64, getCompressedImage } from '@/services/utils';
import tasksMixin from '@/shared/mixins/tasks';
import commonMixins from '@/shared/mixins/common';
import usersMixin from '@/shared/mixins/users';

export default {
	name: 'FormEditTask',

	mixins: [commonMixins, tasksMixin, usersMixin],

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
		},

		locations() {
			return this.$store.getters['locations/locationOptions'];
		},

		taskPriorities() {
			// return this.$store.getters['tasks/taskPriorities'];
			let engGerTranslation = {
                "low": "niedrig",
                "medium": "Mittel",
                "high": "hoch"
            };

			let toSend = []

			let tpro = this.$store.getters['tasks/taskPriorities'];

			let locale = this.user.data.locale;
            
            if(locale.indexOf('de') != -1){
                tpro.map((item) => {
                    let temp = {}
                    temp['label'] = engGerTranslation[item['name']];
                    temp['value'] = item['id'];
                    toSend.push(temp);
                })
            }else{
                tpro.map((item) => {
                    let temp = {}
                    temp['label'] = item['name'];
                    temp['value'] = item['id'];
                    toSend.push(temp);
                })
            }

            return toSend;
		},

		taskStates() {
			return this.$store.getters['tasks/taskStates'];
		},

		taskTypes() {
			// return this.$store.getters['tasks/taskTypes'];

			let engGerTranslation = {
                "removal": "Entfernung",
				"reworking": "Überarbeitung",
				"instruction": "Anweisung",
				"miscellaneous": "Verschiedenes",
				"cleaning": "Reinigung",
				"call": "Anruf",
				"offer": "Angebot",
				"revision": "Revision",
				"e-mail": "Email",
				"maintenance": "Instandhaltung",
				"inspection": "Inspektion",
				"overhauling": "Überholung",
				"disinfection": "Desinfektion",
				"repairing": "Reparatur"
            };

			let toSend = []

			let ttypes = this.$store.getters['tasks/taskTypes'];

			let locale = this.user.data.locale;
            
            if(locale.indexOf('de') != -1){
                ttypes.map((item) => {
                    let temp = {}
                    temp['label'] = engGerTranslation[item['name']];
                    temp['value'] = item['id'];
                    toSend.push(temp);
                })
            }else{
                ttypes.map((item) => {
                    let temp = {}
                    temp['label'] = item['name'];
                    temp['value'] = item['id'];
                    toSend.push(temp);
                })
            }

            return toSend;

		},

		user() {
			return this.$store.state.user;
		},

		users() {
			return this.$store.getters['users/usersOptions'];
		}
	},

	data() {
		return {
			form: {
				doneAt: new Date(),
				giveNotice: 0,
				location: null,
				priority: '',
				assignee: '',
				state: '',
				name: ''
			},
			loading: false,
			showPreview: false,
			showPreviewLarge: false
		};
	},

	validations: {
		form: {
			name: { required },
			assignee: { required }
		}
	},

	mounted() {
		this.init();
	},

	methods: {
		doSubmit() {
			let task = {};
			let dispatcherName = 'tasks/CREATE_TASK';

			if (this.edit) {
				task = new Task(this.form);
				dispatcherName = 'tasks/UPDATE_TASK';
			} else {
				task = new Task({
					assignee: this.form.assignee,
					description: this.form.description,
					company: this.company.id,
					doneAt: this.form.doneAt,
					issuer: this.user.data.id,
					name: this.form.name,
					priority: this.form.priority,
					state: this.form.state,
					type: this.form.type,
					source_b64: this.form.source_b64
				});

				// Set 'todo' state as default value
				task.state = this.getTaskStateByName('todo').id;
			}

			console.log('doSubmit new task', task);

			this.loading = true;

			this.$store
				.dispatch(dispatcherName, task)
				.then(response => {
					this.loading = false;

					if (response.status === 201 || response.status === 204) {
						this.requestTasks(this.company.id);

						this.$q.notify({
							message: this.$t('SAVE_SUCCESS'),
							type: 'positive'
						});

						this.onCancel();
					} else {
						this.handleErrors(response);
					}
				})
				.catch(err => {
					this.loading = false;
				});
		},

		handleInitialData() {
			if (this.edit) {
				this.form = Object.assign({}, this.form, this.data);
				// this.form.giveNotice = this.data.giveNotice ? 1 : 0;
			} else {
				this.form.priority = this.taskPriorities[1].value;
			}
		},

		init() {
			this.requestInitalData();

			if (this.data.image) {
				this.form.source_b64 = this.data.image;
				this.showPreview = true;
			}
		},

		onCancel() {
			this.$emit('cancel');
		},

		/**
		 * Transform the current image into base64 to send it to the backend if user save the form.
		 * This method is quite the same as we use for the equivalent Element UI component.
		 *
		 * @param {Array} files
		 * @returns {void}
		 */
		onChangeUpload(files) {
			const file = files[0];

			const isJPG = file.type === 'image/jpeg';
			const isPNG = file.type === 'image/png';
			const isGIF = file.type === 'image/gif';
			const isLt2M = file.size / 1024 / 1024 < 2;

			if (!isJPG && !isPNG && !isGIF) {
				this.$q.notify({
					message: this.$t('FORMAT_NOT_SUPPORTED'),
					type: 'negative'
				});
				return;
			}

			// 	if (!isLt2M) {
			// 		this.$q.notify({
			// 			message: this.$t('FILE_SIZE_EXCEEDED'),
			// 			type: 'negative'
			// 		});
			// 		return;
			// 	}

			getBase64(file).then(response => {
				getCompressedImage(response, file.type, 0.2).then(result => {
					this.form.source_b64 = result;
					this.showPreview = true;
				});
			});
		},

		onRemoveImage() {
			this.showPreview = false;
			this.form.image = null;
			delete this.form.source_b64;
		},

		onSubmit() {
			this.$v.$touch();
			if (this.$v.$invalid) {
				return false;
			} else {
				this.doSubmit();
			}
		},

		requestInitalData() {
			this.loading = true;
			const REQUEST = [this.requestLocations()];

			if (!this.taskPriorities.length) {
				REQUEST.push(this.requestTasksPriorities(this.company.id));
			}

			if (!this.taskStates.length) {
				REQUEST.push(this.requestTasksStates(this.company.id));
			}

			axios.all(REQUEST).then(
				axios.spread((...results) => {
					this.loading = false;

					this.handleInitialData();
				})
			);
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
		}
	}
};
</script>

<style lang="scss">
.form__edit-task__image-preview {
	max-height: 14rem;
	max-width: 100%;

	&--large {
		max-height: 40rem;
	}
}

.form__edit-task__btn-image-preview {
	position: absolute;
	top: 0.3rem;
	left: 0.1rem;
	z-index: 1;
}
</style>
