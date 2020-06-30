0000<!--
@component:         DirectoriesView
@environment:       Hyprid
@description:       This component handle the visibility for mobile and desktop devices
                    as well as the data storage for child components.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-08-21
@modified:          2018-10-16
-->
<template>
    <div class="view__checklists main-container">
        <q-pull-to-refresh
            :handler="refreshPage"
            :pull-message="$t('PULL_DOWN_TO_REFRESH')"
            :release-message="$t('RELEASE_TO_REFRESH')"
            :refresh-message="$t('REFRESHING')"
            :class="{'o-visible--y': !isDeviceGreaterSM}">

            <div class="m-b-5--sm">
                <el-row class="m-b-2 m-t-1--sm p-l-half--sm">
                    <el-col :xs="24">
                        <!-- Breadcrumbs -->
                        <BreadcrumbDirectories
                            :breadcrumbs="breadcrumbs"
                            :directories="entries"
                            :root-directory="rootDirectory">
                        </BreadcrumbDirectories>
                    </el-col>
                </el-row>

                <el-row type="flex" justify="space-around" class="m-b-1 m-t-1--sm" style="align-items: center;">
                    <el-col :span="24">
                        <!-- Headline -->
                        <h3 class="headline m-t-0 m-b-0 m-l-half--sm m-r-half--sm">{{ $t('OVERVIEW') }}</h3>
                    </el-col>

                    <el-col :span="24">
                        <div class="text-right m-l-half--sm m-r-half--sm">
                            <!-- DESKTOP -->
                            <div v-if="isDeviceGreaterSM" class="a-right">
                                <q-btn v-if="showFilter"
                                    flat
                                    :label="$t('HIDE_FILTER')"
                                    class="m-r-1 hidden"
                                    no-ripple
                                    @click="toggleFilter"
                                    icon="filter_list" />
                                <q-btn v-else
                                    flat
                                    :label="$t('SHOW_FILTER')"
                                    class="m-r-1 hidden"
                                    no-ripple
                                    @click="toggleFilter"
                                    icon="filter_list" />

                                <ButtonGroupCreateChecklist
                                    :parent-id="directoryId">
                                </ButtonGroupCreateChecklist>
                            </div>
                            <!-- MOBILE -->
                            <div v-if="!isDeviceGreaterSM">
                                <q-btn
                                    flat
                                    round
                                    no-ripple
                                    @click="toggleFilter"
                                    class="hidden"
                                    icon="filter_list" />
                                <q-btn v-if="selectedItems.length"
                                    flat
                                    round
                                    no-ripple
                                    icon="more_vert"
                                    @click="onClickBtnEdit" />
                            </div>
                        </div>
                    </el-col>
                </el-row>

                <el-row>
                    <el-col :xs="24">
                        <el-card class="el-card--no-padding">
                            <!-- Tasks Directories -->
                            <TableDirectories
                                :data="entries"
                                :isLoading="loading"
                                @refresh="init"
                                @change-selection="onChangeSelection">
                            </TableDirectories>
                        </el-card>

                        <TabActionBar v-if="selectedItems.length"
                            :actions="tabBarActions"
                            :data="selectedItems"
                            @item-click="onTabActionBarItemClick"
                            class="m-t-1--md hide-sm">
                        </TabActionBar>
                    </el-col>
                </el-row>
            </div>
        </q-pull-to-refresh>

        <ButtonFabCreateChecklist v-if="!isDeviceGreaterSM"></ButtonFabCreateChecklist>
    </div>
</template>

<script>
import axios from 'axios';
import BreadcrumbDirectories from '@/components/Breadcrumb/BreadcrumbDirectories';
import ButtonFabCreateChecklist from '@/components/Button/ButtonFabCreateChecklist';
import ButtonGroupCreateChecklist from '@/components/Button/ButtonGroupCreateChecklist';
import directoriesMixin from '@/shared/mixins/directories';
import { Directory } from '@/shared/classes/Directory';
import TabActionBar from '@/components/Tab/TabActionBar';
import TableDirectories from '@/components/Table/TableDirectories';
import commonMixins from '@/shared/mixins/common';

export default {
	name: 'DirectoriesView',

	mixins: [commonMixins, directoriesMixin],

	components: {
		BreadcrumbDirectories,
		ButtonFabCreateChecklist,
		ButtonGroupCreateChecklist,
		TabActionBar,
		TableDirectories
	},

	computed: {
		breadcrumbs() {
			return this.$store.state.directories.breadcrumbs;
		},

		company() {
			return this.$store.state.user.company;
		},

		directories() {
			return this.$store.state.directories.directories;
		},

		directoryEntries() {
			return this.$store.state.directories.entries;
		},

		isDeviceGreaterSM() {
			return this.$store.getters.IS_DEVICE_GREATER_SM;
		},

		rootDirectory() {
			return this.$store.state.directories.rootDirectory;
		},

		user() {
			return this.$store.state.user;
		}
	},

	data() {
		return {
			directoryId: '',
			entries: [],
			loading: false,
			selectedItems: [],
			showFilter: false,
			tabBarActions: [
				{
					name: 'copy',
					label: this.$t('COPY'),
					icon: 'file_copy',
					handler: this.handleCopyItems
				},
				{
					name: 'move',
					label: this.$t('MOVE'),
					icon: 'open_with',
					handler: this.handleMoveItems
				},
				{
					name: 'archive',
					label: this.$t('TO_ARCHIVE'),
					icon: 'archive',
					handler: this.handleArchiveItems
				},
				{
					name: 'delete',
					label: this.$t('DELETE'),
					icon: 'delete',
					handler: this.handleDeleteItems
				}
			]
		};
	},

	mounted() {
		this.init();
	},

	methods: {
		doDeleteChecklists(Ids) {
			return this.$store
				.dispatch('checklists/DELETE_CHECKLISTS', Ids)
				.then(response => {
					console.log('doDeleteChecklists', response);
					// this.$store.commit('directories/RESET_DIRECTORY_ENTRIES');
					// this.handleDirectoryChange();

					return response;
				})
				.catch(err => {
					return err;
				});
		},

		doDeleteDirectories(Ids) {
			return this.$store
				.dispatch('directories/DELETE_DIRECTORIES', Ids)
				.then(response => {
					console.log('doDeleteDirectories', response);
					// this.$store.commit('directories/RESET_DIRECTORY_ENTRIES');
					// this.handleDirectoryChange();

					return response;
				})
				.catch(err => {
					return err;
				});
		},

		handleArchiveItems() {
			const items = [];
			this.selectedItems.forEach(item => {
				items.push(item.id);
			});

			const DATA = {
				company: this.company.id,
				items: items
			};

			this.$store
				.dispatch('directories/ARCHIVE_DIRECTORIES', DATA)
				.then(response => {
					if (response.status === 204) {
						this.$q.notify({
							message: this.$t('ARCHIVE_SUCCESS'),
							type: 'positive'
						});
						this.$store.commit('directories/RESET_DIRECTORY_ENTRIES');
						this.handleDirectoryChange();
					} else {
						this.handleErrors(response);
					}
				})
				.catch(err => {
					this.handleErrors(err);
				});
		},

		handleBreadcrumb() {
			this.directoryId = this.getDirectoryIdFromRoute(this.$route);

			if (this.directoryId === 'directories') {
				this.$store.commit('directories/RESET_BREADCRUMBS');
			} else {
				this.requestDirectory(this.directoryId).then(response => {
					this.$store.commit('directories/ADD_BREADCRUMB', response.data.data);
				});
			}
		},

		handleCopyItems() {
			const items = [];
			this.selectedItems.forEach(item => {
				const obj = {
					objectId: item.object.id,
					objectType: item.objectType,
					targetId: item.parentId
				};
				items.push(obj);
			});

			const DATA = {
				entries: items
			};

			console.log('handleCopyItems', items, DATA);

			this.$store
				.dispatch('directories/COPY_DIRECTORIES', DATA)
				.then(response => {
					this.handleDirectoryChange();
				})
				.catch(err => {
					this.handleErrors(err);
				});
		},

		handleDeleteItems() {
			const checklistIds = [];
			const directoryIds = [];

			this.selectedItems.forEach(item => {
				if (item.objectType === 'checklist') {
					checklistIds.push(item.object.id);
				}
				if (item.objectType === 'directory') {
					directoryIds.push(item.object.id);
				}
			});

			if (this.isDeviceGreaterSM) {
				this.$confirm(this.$t('WOULD_YOU_LIKE_TO_DELETE_LOCATIONS'), this.$t('CONFIRM_DELETE'), {
					confirmButtonText: this.$t('OK'),
					cancelButtonText: this.$t('CANCEL'),
					type: 'warning'
				})
					.then(() => {
						const REQUESTS = [];
						if (checklistIds.length) {
							REQUESTS.push(this.doDeleteChecklists(checklistIds));
						}
						if (directoryIds.length) {
							REQUESTS.push(this.doDeleteDirectories(directoryIds));
						}

						axios
							.all(REQUESTS)
							.then(
								axios.spread((...results) => {
									this.$store.commit('directories/RESET_DIRECTORY_ENTRIES');
									this.handleDirectoryChange();
								})
							)
							.catch(err => {
								//
							});
					})
					.catch(() => {});
			} else {
				this.$q
					.dialog({
						title: this.$t('CONFIRM_DELETE'),
						message: this.$t('WOULD_YOU_LIKE_TO_DELETE_LOCATIONS'),
						ok: true,
						cancel: true
					})
					.then(() => {})
					.catch(() => {});
			}
		},

		handleDirectoryChange() {
			this.directoryId = this.getDirectoryIdFromRoute(this.$route);
			if (this.directoryId === 'directories') {
				this.directoryId = this.company.directoryId;
			}

			this.loading = true;
			this.requestDirectoryEntries(this.directoryId).then(response => {
				this.$store.commit('directories/SET_DIRECTORY_ENTRIES', response.data.data);

				if (this.directoryId === 'directories') {
					this.directoryId = this.company.directoryId;
				}
				this.entries = this.getEntriesByDirectoryId(this.directoryId);
				this.loading = false;
			});

			this.handleBreadcrumb();
			console.log('handleDirectoryChange', this.directoryId);
		},

		handleMoveItems() {
			let title = this.$t('MOVE');
			let loadComponent = 'Form/FormMoveDirectories';

			if (this.isDeviceGreaterSM) {
				this.$store.commit('OPEN_DIALOG', {
					title: title,
					loadComponent: loadComponent,
					width: '50%',
					refreshAfterClose: true,
					data: this.selectedItems
				});
			} else {
				this.$store.commit('OPEN_MODAL', {
					title: title,
					loadComponent: loadComponent,
					maximized: true,
					refreshAfterClose: true,
					data: this.selectedItems
				});
			}
		},

		init() {
			this.directoryId = this.getDirectoryIdFromRoute(this.$route);
			if (this.directoryId === 'directories') {
				this.directoryId = this.company.directoryId;
			}

			console.log('DirectoriesView mounted', this.$route, this.directoryId);

			this.loading = true;
			const REQUESTS = [
				this.requestDirectory(this.company.directoryId),
				this.requestDirectoryEntries(this.directoryId)
			];
			// We need the directory for the current folder in breadcrumb
			if (this.directoryId !== this.company.directoryId) {
				REQUESTS.push(this.requestDirectory(this.directoryId));
			}

			axios
				.all(REQUESTS)
				.then(
					axios.spread((...results) => {
						this.loading = false;
						this.$store.commit('directories/SET_ROOT_DIRECTORY', results[0].data.data);

						this.$store.commit('directories/RESET_DIRECTORY_ENTRIES');
						this.$store.commit('directories/SET_DIRECTORY_ENTRIES', results[1].data.data);

						if (this.directoryId !== this.company.directoryId) {
							this.$store.commit('directories/ADD_BREADCRUMB', results[2].data.data);
						}
					})
				)
				.catch(err => {
					this.loading = false;
				});
		},

		onChangeSelection(data) {
			this.selectedItems = data;
			console.log('onChangeSeleciton', data);
		},

		onClickBtnEdit() {
			this.$q
				.actionSheet({
					title: this.$t('ACTION'),

					actions: this.tabBarActions
				})
				.then(action => {
					console.log('action', action);
				})
				.catch(() => {});
		},

		onTabActionBarItemClick(data) {
			console.log('onTabActionBarItemClick', data);
			switch (data.item) {
				case 'delete':
					this.handleDeleteItems();
					break;
				case 'copy':
					this.handleCopyItems();
					break;
				case 'move':
					this.handleMoveItems();
					break;
				case 'archive':
					this.handleArchiveItems();
					break;
				default:
					console.log('nichts trifft zu');
					break;
			}
		},

		refreshPage(done) {
			this.requestDirectoryEntries(this.directoryId)
				.then(() => {
					done();
				})
				.catch(() => {
					done();
				});
		},

		requestDirectory(id) {
			return this.$store
				.dispatch('directories/GET_DIRECTORY', { id: id })
				.then(response => {
					if (response.status !== 200) {
						this.handleErrors(response);
					}
					return response;
				})
				.catch(err => {
					this.loading = false;
					return err;
				});
		},

		requestDirectoryEntries(id) {
			return this.$store
				.dispatch('directories/GET_DIRECTORY_ENTRIES', { id: id })
				.then(response => {
					if (response.status !== 200) {
						this.handleErrors(response);
					}
					return response;
				})
				.catch(err => {
					this.loading = false;
					return err;
				});
		},

		requestUsers() {
			return this.$store.dispatch('users/GET_USERS').then(response => {
				return response;
			});
		},

		toggleFilter() {
			this.showFilter = !this.showFilter;
		}
	},

	destroyed() {
		this.$store.commit('directories/RESET_BREADCRUMBS');
	},

	watch: {
		$route(to, from) {
			console.log('Route changed', to, from);
			this.handleDirectoryChange();
		},

		directoryEntries(newValue, oldValue) {
			this.directoryId = this.getDirectoryIdFromRoute(this.$route);
			if (this.directoryId === 'directories') {
				this.directoryId = this.company.directoryId;
			}

			this.entries = this.getEntriesByDirectoryId(this.directoryId);
		}
	}
};
</script>
