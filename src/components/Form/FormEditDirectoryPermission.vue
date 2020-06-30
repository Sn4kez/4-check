<!--
@component:         FormEditDirectoryPermission
@description:       Create and update permission for both directory and checklist
                    as well user and group.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-08-23
@modified:          2018-08-23
-->
<template>
    <el-form ref="FormEditDirectoryPermission">
        <el-row :gutter="20">
            <!-- Users -->
            <el-col :xs="24" :md="7">
                <q-select
                    v-model="form.subjectId"
                    :options="users"
                    :placeholder="$t('SELECT_USER')"
                    :float-label="$t('SELECT_USER')"
                    @input="onChangeSelection"
                    clearable />
            </el-col>

            <el-col :xs="24" :md="3">
                <p class="text-center m-t-2 m-b-0">{{ $t('OR') }}</p>
            </el-col>
            <!-- Groups -->
            <el-col :xs="24" :md="7">
                <q-select
                    v-model="form.subjectId"
                    :options="groups"
                    :placeholder="$t('SELECT_GROUP')"
                    :float-label="$t('SELECT_GROUP')"
                    @input="onChangeSelection"
                    clearable />
            </el-col>
        </el-row>

        <el-row>
            <el-col :xs="24">
                <q-list link no-border class="m-t-1">
                    <!-- Index -->
                    <q-item tag="label">
                        <q-item-side>
                            <q-checkbox
                                v-model="form.index"
                                :true-value="1"
                                :false-value="0"
                                :disable="!this.form.subjectId"
                                ref="index" />
                        </q-item-side>
                        <q-item-main>
                            <q-item-tile title>Auflisten</q-item-tile>
                            <q-item-tile sublabel>Benutzer/Gruppe sieht Ordner/Prüfliste</q-item-tile>
                        </q-item-main>
                    </q-item>
                    <!-- View -->
                    <q-item tag="label">
                        <q-item-side>
                            <q-checkbox
                                v-model="form.view"
                                :true-value="1"
                                :false-value="0"
                                :disable="!this.form.subjectId"
                                ref="view" />
                        </q-item-side>
                        <q-item-main>
                            <q-item-tile label>Einsehen</q-item-tile>
                            <q-item-tile sublabel>Benutzer/Gruppe kann Inhalt von Ordner/Prüfliste einsehen</q-item-tile>
                        </q-item-main>
                    </q-item>
                    <!-- Update -->
                    <q-item multiline tag="label">
                        <q-item-side>
                            <q-checkbox
                                v-model="form.update"
                                :true-value="1"
                                :false-value="0"
                                :disable="!this.form.subjectId"
                                ref="update" />
                        </q-item-side>
                        <q-item-main>
                            <q-item-tile label>Bearbeiten</q-item-tile>
                            <q-item-tile sublabel lines="2">Benutzer/Gruppe kann Ordner/Prüfliste bearbeiten</q-item-tile>
                        </q-item-main>
                    </q-item>
                    <!-- Delete -->
                    <q-item multiline tag="label">
                        <q-item-side>
                            <q-checkbox
                                v-model="form.delete"
                                :true-value="1"
                                :false-value="0"
                                :disable="!this.form.subjectId"
                                ref="delete" />
                        </q-item-side>
                        <q-item-main>
                            <q-item-tile label>Löschen</q-item-tile>
                            <q-item-tile sublabel lines="2">Benutzer/Gruppe kann Ordner/Prüfliste löschen</q-item-tile>
                        </q-item-main>
                    </q-item>
                </q-list>
            </el-col>
        </el-row>

        <el-row class="m-t-1 p-b-small text-right">
            <el-col :xs="24">
                <q-btn
                    color="primary"
                    :disable="!this.form.subjectId"
                    :loading="loading"
                    @click="doSavePermission">
                    {{ $t('SAVE') }}
                </q-btn>
            </el-col>
        </el-row>

    </el-form>
</template>

<script>
import { Grant } from '@/shared/classes/Grant';
import axios from 'axios';
import commonMixins from '@/shared/mixins/common';

export default {
	name: 'FormEditDirectoryPermission',

	mixins: [commonMixins],

	props: {
		data: {
			type: Object,
			required: false
		},

		grants: {
			type: Array,
			required: true
		},

		isDirectory: {
			type: Boolean,
			required: false,
			default: true
		}
	},

	computed: {
		company() {
			return this.$store.state.user.company;
		},

		groups() {
			return this.$store.getters['companies/groupOptions'];
		},

		users() {
			return this.$store.getters['users/usersOptions'];
		}
	},

	data() {
		return {
			edit: false,
			form: {
				delete: 0,
				index: 0,
				subjectId: '',
				update: 0,
				view: 0
			},
			loading: false,
			selectedItem: ''
		};
	},

	mounted() {
		this.init();
	},

	methods: {
		doSavePermission() {
			let dispatcherName = this.isDirectory
				? 'directories/CREATE_DIRECTORY_GRANT'
				: 'checklists/CREATE_CHECKLIST_GRANT';

			let data = {};

			if (this.edit) {
				data = new Grant(this.form);
				dispatcherName = 'grants/UPDATE_GRANT';
			} else {
				data.directoryId = this.data.object.id;
				data.data = new Grant({
					delete: this.form.delete,
					index: this.form.index,
					subjectId: this.form.subjectId,
					update: this.form.update,
					view: this.form.view
				});
			}

			this.loading = true;

			console.log('data', data, this.edit);

			this.$store
				.dispatch(dispatcherName, data)
				.then(response => {
					this.loading = false;

					if (response.status === 201 || response.status === 204) {
						this.$q.notify({
							message: this.$t('SAVE_SUCCESS'),
							type: 'positive'
						});

						this.resetForm(true);
						this.$emit('refresh');
					} else {
						this.handleErrors(response);
					}
				})
				.catch(err => {
					this.loading = false;
				});
		},

		init() {
			const REQUESTS = [];

			if (!this.users.length) {
				REQUESTS.push(this.requestUsers());
			}
			if (!this.groups.length) {
				REQUESTS.push(this.requestGroups());
			}

			if (REQUESTS.length) {
				this.requestInitialData(REQUESTS);
			}
		},

		onChangeSelection(value) {
			let foundGrant = false;
			if (value) {
				this.grants.forEach(grant => {
					if (grant.subjectId === value) {
						foundGrant = true;
						this.edit = true;
						this.form = _.cloneDeep(grant);
						this.form.delete = this.form.delete ? 1 : 0;
						this.form.index = this.form.index ? 1 : 0;
						this.form.update = this.form.update ? 1 : 0;
						this.form.view = this.form.view ? 1 : 0;
					}
				});
			}

			if (!foundGrant) {
				this.resetForm();
			}
		},

		requestGroups() {
			return this.$store
				.dispatch('companies/GET_GROUPS', { id: this.company.id })
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		requestInitialData(REQUESTS) {
			this.loading = true;

			axios
				.all(REQUESTS)
				.then(
					axios.spread((...results) => {
						this.loading = false;
					})
				)
				.catch(err => {
					this.loading = false;
				});
		},

		requestUsers() {
			return this.$store
				.dispatch('users/GET_USERS')
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		resetForm(full = false) {
			if (full) {
				this.form.subjectId = '';
			}
			this.form.delete = 0;
			this.form.index = 0;
			this.form.update = 0;
			this.form.view = 0;
		}
	},

	watch: {
		grants(newValue, oldValue) {
			this.resetForm();
		}
	}
};
</script>

<style lang="scss">
</style>
