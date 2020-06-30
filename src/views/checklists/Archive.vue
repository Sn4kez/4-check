<!--
@component:         ChecklistsArchiveView
@environment:       Hyprid
@description:       This component handle the visibility for mobile and desktop devices
                    as well as the data storage for child components.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-08-21
@modified:          2018-09-27
-->
<template>
    <div class="view__checklists-archive main-container">
         <div class="m-b-5--sm">
            <el-row class="m-b-2 m-t-1--sm p-l-half--sm">
                <el-col :xs="24">
                    <!-- Breadcrumbs -->
                    <BreadcrumbDirectories
                        :is-archive="true"
                        :breadcrumbs="breadcrumbs"
                        :directories="directories"
                        :root-directory="rootDirectory">
                    </BreadcrumbDirectories>
                </el-col>
            </el-row>

            <el-row type="flex" justify="space-around" class="m-b-1 m-t-1--sm" style="align-items: center;">
                <el-col :span="24">
                    <!-- Headline -->
                    <h3 class="headline m-t-0 m-b-0 m-l-half--sm m-r-half--sm">{{ $t('OVERVIEW') }}</h3>
                </el-col>
            </el-row>

            <el-row>
                <el-col :xs="24">
                    <el-card class="el-card--no-padding">
                        <!-- Directories -->
                        <TableDirectoriesArchives
                            :data="directories"
                            :isLoading="loading"
                            @refresh="init"
                            @change-selection="onChangeSelection">
                        </TableDirectoriesArchives>
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
    </div>
</template>

<script>
import axios from 'axios';
import commonMixins from '@/shared/mixins/common';
import directoriesMixin from '@/shared/mixins/directories';
import BreadcrumbDirectories from '@/components/Breadcrumb/BreadcrumbDirectories';
import TabActionBar from '@/components/Tab/TabActionBar';
import TableDirectoriesArchives from '@/components/Table/TableDirectoriesArchives';

export default {
	name: 'ChecklistsArchiveView',

	mixins: [commonMixins, directoriesMixin],

	components: {
		BreadcrumbDirectories,
		TabActionBar,
		TableDirectoriesArchives
	},

	computed: {
		breadcrumbs() {
			return this.$store.state.archives.breadcrumbs;
		},

		company() {
			return this.$store.state.user.company;
		},

		directories() {
			return this.$store.state.archives.directories;
		},

		isDeviceGreaterSM() {
			return this.$store.getters.IS_DEVICE_GREATER_SM;
		},

		rootDirectory() {
			return this.$store.state.archives.rootDirectory;
		}
	},

	data() {
		return {
			archiveId: '',
			loading: false,
			selectedItems: [],
			tabBarActions: [
				{
					name: 'restore',
					label: this.$t('RESTORE'),
					icon: 'unarchive',
					handler: this.handleRestoreItems
				}
			]
		};
	},

	mounted() {
		this.init();
	},

	methods: {
		handleBreadcrumb() {
			this.archiveId = this.getDirectoryIdFromRoute(this.$route);

			if (this.archiveId === 'archive') {
				this.$store.commit('archives/RESET_BREADCRUMBS');
			} else {
				this.requestArchiveDirectory(this.archiveId).then(response => {
					this.$store.commit('archives/ADD_BREADCRUMB', response.data.data);
				});
			}
		},

		handleDirectoryChange() {
			this.archiveId = this.getDirectoryIdFromRoute(this.$route);
			this.archiveId = this.archiveId === 'archive' ? this.company.archiveId : this.archiveId;

			this.loading = true;
			this.requestArchiveDirectoryEntries(this.archiveId).then(response => {
				this.$store.commit('archives/SET_DIRECTORIES', response.data.data);
				this.loading = false;
			});

			this.handleBreadcrumb();

			console.log('handleDirectoryChange', this.archiveId);
		},

		handleRestoreItems() {
			const items = [];
			this.selectedItems.forEach(item => {
				items.push(item.id);
			});

			const DATA = {
				company: this.company.id,
				items: items
			};

			console.log('handleRestoreItems', items, DATA);

			this.$store
				.dispatch('directories/RESTORE_DIRECTORIES', DATA)
				.then(response => {
					this.handleDirectoryChange();
				})
				.catch(err => {
					this.handleErrors(err);
				});
		},

		init() {
			this.archiveId = this.getDirectoryIdFromRoute(this.$route);
			this.archiveId = this.archiveId === 'archive' ? this.company.archiveId : this.archiveId;

			console.log('ChecklistsArchiveView mounted', this.$route, this.archiveId);

			this.loading = true;
			const REQUESTS = [
				this.requestArchiveDirectory(this.company.archiveId),
				this.requestArchiveDirectoryEntries(this.archiveId)
			];
			// We need the directory for the current folder in breadcrumb
			if (this.archiveId !== this.company.archiveId) {
				REQUESTS.push(this.requestArchiveDirectory(this.archiveId));
			}

			axios
				.all(REQUESTS)
				.then(
					axios.spread((...results) => {
						this.loading = false;
						this.$store.commit('archives/SET_ROOT_DIRECTORY', results[0].data.data);
						this.$store.commit('archives/SET_DIRECTORIES', results[1].data.data);

						if (this.archiveId !== this.company.archiveId) {
							this.$store.commit('archives/ADD_BREADCRUMB', results[2].data.data);
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

		onTabActionBarItemClick(data) {
			console.log('onTabActionBarItemClick', data);
			switch (data.item) {
				case 'restore':
					this.handleRestoreItems();
					break;
				default:
					console.log('nichts trifft zu');
					break;
			}
		},

		requestArchiveDirectory(id) {
			return this.$store
				.dispatch('archives/GET_ARCHIVE', { id: id })
				.then(response => {
					return response;
				})
				.catch(err => {
					this.loading = false;
					return err;
				});
		},

		requestArchiveDirectoryEntries(id) {
			return this.$store
				.dispatch('archives/GET_ARCHIVE_ENTRIES', { id: id })
				.then(response => {
					return response;
				})
				.catch(err => {
					this.loading = false;
					return err;
				});
		}
	},

	destroyed() {
		this.$store.commit('archives/RESET_BREADCRUMBS');
	},

	watch: {
		$route(to, from) {
			console.log('Route changed', to, from);
			this.handleDirectoryChange();
		}
	}
};
</script>
