<!--
@component:         TableDirectories
@environment:       Hyprid
@description:       This component handle the visibility for mobile and desktop devices
                    as well as the data storage for child components.
                    On desktop we show a normal table while on mobile a list is rendered.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-08-21
@modified:          2018-10-14
-->
<template>
    <div>
        <!-- ############# Desktop ############# -->
        <div class="w-100" v-if="isDeviceGreaterSM">
            <el-table ref="TableDirectories" class="w-100"
                v-loading="isLoading"
                @selection-change="handleSelectionChange"
                :empty-text="$t('NO_DATA_AVAILABLE')"
                :data="data">

                <el-table-column
                    type="selection"
                    width="55">
                </el-table-column>

                <el-table-column
                    :label="$t('NAME')"
                    show-overflow-tooltip>
                    <template slot-scope="scope">
                        <div class="flex" v-if="scope.row.objectType === 'directory'">
                            <q-icon
                                class="color-gray font--big"
                                name="folder_open">
                            </q-icon>
                            <div class="m-l-half d-block">
                                <router-link
                                    :to="{ name: 'DirectoriesViewDir', params: { id: scope.row.object.id } }"
                                    class="color-gray">
                                    {{scope.row.object.name}}
                                </router-link>
                            </div>
                        </div>

                        <div class="flex" v-else>
                            <q-icon
                                class="color-gray font--big"
                                name="check_circle_outline">
                            </q-icon>
                            <div class="m-l-half d-block">
                                <router-link
                                    :to="{ name: 'CreateAuditView', query: { checklist: scope.row.object.id } }"
                                    class="color-gray">
                                    {{scope.row.object.name}}
                                </router-link>
                            </div>
                        </div>
                    </template>
                </el-table-column>

                <el-table-column
                    :label="$t('DESCRIPTION')"
                    show-overflow-tooltip>
                    <template slot-scope="scope">
                        {{scope.row.object.description}}
                    </template>
                </el-table-column>

                <!-- Actions -->
                <el-table-column
                    label=""
                    width="60"
                    align="right">
                    <template slot-scope="scope">
                        <el-dropdown trigger="click">
                            <span class="el-dropdown-link">
                                <q-icon name="more_horiz" size="2rem"></q-icon>
                            </span>
                            <el-dropdown-menu slot="dropdown">
                                <el-dropdown-item>
                                    <a href="#" @click.prevent="onEdit(scope.row)">{{$t('EDIT')}}</a>
                                </el-dropdown-item>
                                <el-dropdown-item>
                                    <a href="#" @click.prevent="onDetails(scope.row)">{{$t('DETAILS')}}</a>
                                </el-dropdown-item>
                                <el-dropdown-item divided>
                                    <a href="#" @click.prevent="onCopy(scope.row)">{{$t('COPY')}}</a>
                                </el-dropdown-item>
                                <el-dropdown-item>
                                    <a href="#" @click.prevent="onMove(scope.row)">{{$t('MOVE')}}</a>
                                </el-dropdown-item>
                                <el-dropdown-item>
                                    <a href="#" @click.prevent="onArchive(scope.row)">{{$t('TO_ARCHIVE')}}</a>
                                </el-dropdown-item>
                                <el-dropdown-item divided>
                                    <a href="#" @click.prevent="onDelete(scope.row)">{{$t('DELETE')}}</a>
                                </el-dropdown-item>
                            </el-dropdown-menu>
                        </el-dropdown>
                    </template>
                </el-table-column>
            </el-table>
        </div>

        <!-- ############# Mobile ############# -->
        <div v-if="!isDeviceGreaterSM">
            <el-table ref="TableDirectories" class="w-100"
                v-loading="isLoading"
                @selection-change="handleSelectionChange"
                :data="data"
                :empty-text="$t('NO_DATA_AVAILABLE')">

                <el-table-column
                    type="selection"
                    width="30">
                </el-table-column>

                <el-table-column
                    :label="$t('NAME')"
                    :label-class-name="'font-brand'">
                    <template slot-scope="scope">
                        <q-item class="p-0">
                            <!-- Directory -->
                            <q-item-side v-if="scope.row.objectType === 'directory'">
                                <router-link
                                    :to="{ name: 'DirectoriesViewDir', params: { id: scope.row.object.id } }"
                                    class="color-gray">
                                    <q-icon
                                        class="color-gray font--big"
                                        name="folder_open">
                                    </q-icon>
                                </router-link>
                            </q-item-side>
                            <!-- Checklist -->
                            <q-item-side v-if="scope.row.objectType === 'checklist'">
                                <router-link
                                    :to="{ name: 'CreateAuditView', query: { checklist: scope.row.object.id } }"
                                    class="color-gray">
                                    <q-icon
                                        class="color-gray font--big"
                                        name="check_circle_outline">
                                    </q-icon>
                                </router-link>
                            </q-item-side>

                            <q-item-main>
                                <!-- Directory -->
                                <router-link v-if="scope.row.objectType === 'directory'"
                                    :to="{ name: 'DirectoriesViewDir', params: { id: scope.row.object.id } }"
                                    class="color-gray">
                                    <q-item-tile label>{{scope.row.object.name}}</q-item-tile>
                                    <q-item-tile sublabel class="color-gray fron--small">
                                        {{scope.row.object.name}}
                                    </q-item-tile>
                                </router-link>
                                <!-- Checklist -->
                                <router-link v-if="scope.row.objectType === 'checklist'"
                                    :to="{ name: 'CreateAuditView', query: { checklist: scope.row.object.id } }"
                                    class="color-gray">
                                    <q-item-tile label>{{scope.row.object.name}}</q-item-tile>
                                    <q-item-tile sublabel class="color-gray fron--small">
                                        {{scope.row.object.name}}
                                    </q-item-tile>
                                </router-link>
                            </q-item-main>

                            <q-item-side right color="green">
                                <el-dropdown trigger="click">
                                    <span class="el-dropdown-link">
                                        <q-icon name="more_horiz" size="2rem"></q-icon>
                                    </span>
                                    <el-dropdown-menu slot="dropdown">
                                        <el-dropdown-item>
                                            <a href="#" @click.prevent="onEdit(scope.row)">{{$t('EDIT')}}</a>
                                        </el-dropdown-item>
                                        <el-dropdown-item>
                                            <a href="#" @click.prevent="onDetails(scope.row)">{{$t('DETAILS')}}</a>
                                        </el-dropdown-item>
                                        <el-dropdown-item divided>
                                            <a href="#" @click.prevent="onCopy(scope.row)">{{$t('COPY')}}</a>
                                        </el-dropdown-item>
                                        <el-dropdown-item>
                                            <a href="#" @click.prevent="onMove(scope.row)">{{$t('MOVE')}}</a>
                                        </el-dropdown-item>
                                        <el-dropdown-item>
                                            <a href="#" @click.prevent="onArchive(scope.row)">{{$t('TO_ARCHIVE')}}</a>
                                        </el-dropdown-item>
                                        <el-dropdown-item divided>
                                            <a href="#" @click.prevent="onDelete(scope.row)">{{$t('DELETE')}}</a>
                                        </el-dropdown-item>
                                    </el-dropdown-menu>
                                </el-dropdown>
                            </q-item-side>
                        </q-item>
                    </template>
                </el-table-column>
            </el-table>
        </div>

    </div>
</template>

<script>
import commonMixin from '@/shared/mixins/common';

export default {
	name: 'TableDirectories',

	mixins: [commonMixin],

	props: {
		data: {
			type: Array,
			required: true
		},

		isLoading: {
			type: Boolean,
			required: false,
			default: false
		}
	},

	computed: {
		company() {
			return this.$store.state.user.company;
		},

		isDeviceGreaterSM() {
			return this.$store.getters.IS_DEVICE_GREATER_SM;
		}
	},

	data() {
		return {
			multipleSelection: []
		};
	},

	mounted() {
		this.init();
	},

	methods: {
		handleSelectionChange(val) {
			this.multipleSelection = val;
			this.$emit('change-selection', this.multipleSelection);
		},

		init() {
			this.registerEvents();
		},

		doDelete(row) {
			if (row.objectType === 'directory') {
				this.$store.dispatch('directories/DELETE_DIRECTORY', row.object).then(() => {
					this.$q.notify({
						message: this.$t('DELETE_SUCCESS'),
						type: 'positive'
					});
					this.$store.commit('directories/RESET_DIRECTORY_ENTRIES');
					this.$emit('refresh');
				});
			}
			if (row.objectType === 'checklist') {
				this.$store.dispatch('checklists/DELETE_CHECKLIST', row.object).then(() => {
					this.$q.notify({
						message: this.$t('DELETE_SUCCESS'),
						type: 'positive'
					});
					this.$store.commit('directories/RESET_DIRECTORY_ENTRIES');
					this.$emit('refresh');
				});
			}
		},

		onArchive(row) {
			const DATA = {
				id: row.id,
				company: this.company.id
			};

			this.$store
				.dispatch('directories/ARCHIVE_DIRECTORY', DATA)
				.then(response => {
					if (response.status === 204) {
						this.$q.notify({
							message: this.$t('ARCHIVE_SUCCESS'),
							type: 'positive'
						});
						this.$emit('refresh');
					} else {
						this.handleErrors(response);
					}
				})
				.catch(err => {
					this.handleErrors(err);
				});
		},

		onClickItem(row) {
			console.log('onClickItem', row);
		},

		onCopy(row) {
			console.log('onCopy', row);

			const DATA = {
				id: row.object.id,
				objectType: row.objectType,
				targetId: row.parentId
			};

			this.$store.dispatch('directories/COPY_DIRECTORY', DATA).then(response => {
				this.$q.notify({
					message: this.$t('COPY_SUCCESS'),
					type: 'positive'
				});
				this.$store.commit('directories/RESET_DIRECTORY_ENTRIES');
				this.$emit('refresh');
			});
		},

		onDelete(row) {
			console.log('onDelete', row);
			let message = this.$t('WOULD_YOU_LIKE_TO_DELETE_DIRECTORY', row.name);
			if (row.objectType === 'checklist') {
				message = this.$t('WOULD_YOU_LIKE_TO_DELETE_CHECKLIST', row.name);
			}

			if (this.isDeviceGreaterSM) {
				this.$confirm(message, {
					type: 'warning',
					confirmButtonText: this.$t('OK'),
					cancelButtonText: this.$t('CANCEL')
				})
					.then(() => {
						this.doDelete(row);
					})
					.catch(() => {
						console.log('not deleted');
					});
			} else {
				this.$q
					.dialog({
						title: this.$t('CONFIRM'),
						message: message,
						ok: this.$t('OK'),
						cancel: this.$t('CANCEL')
					})
					.then(() => {
						this.doDelete(row);
					})
					.catch(() => {
						console.log('not deleted');
					});
			}
		},

		onDetails(row) {
			console.log('onDetails', row.id, row);
			const dialogTitle =
				row.objectType === 'directory' ? this.$t('DIRECTORY_DETAILS') : this.$t('CHECKLIST_DETAILS');

			if (this.isDeviceGreaterSM) {
				this.$store.commit('OPEN_DIALOG', {
					title: dialogTitle + ' ' + row.object.name,
					loadComponent: 'Tab/TabDirectoryDetails',
					width: '70%',
					data: row
				});
			} else {
				this.$store.commit('OPEN_MODAL', {
					title: dialogTitle,
					loadComponent: 'Tab/TabDirectoryDetails',
					maximized: true,
					data: row
				});
			}
		},

		onEdit(row) {
			this.$router.push({ name: 'ChecklistView', params: { id: row.object.id }});
		},

		onMove(row) {
			console.log('onMove', row.id);
			let title = this.$t('MOVE');
			let loadComponent = 'Form/FormMoveDirectories';

			if (this.isDeviceGreaterSM) {
				this.$store.commit('OPEN_DIALOG', {
					title: title,
					loadComponent: loadComponent,
					width: '50%',
					refreshAfterClose: true,
					data: [row]
				});
			} else {
				this.$store.commit('OPEN_MODAL', {
					title: title,
					loadComponent: loadComponent,
					maximized: true,
					refreshAfterClose: true,
					data: [row]
				});
			}
		},

		registerEvents() {
			this.$eventbus.$on('dialog:closed', () => {
				this.$emit('refresh');
			});

			this.$eventbus.$on('modal:closed', () => {
				this.$emit('refresh');
			});
		},

		unregisterEvents() {
			this.$eventbus.$off('dialog:closed');
			this.$eventbus.$off('modal:closed');
		}
	},

	destroyed() {
		this.unregisterEvents();
	}
};
</script>

<style lang="scss">
</style>
