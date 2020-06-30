<!--
@component:         TasksView
@environment:       Hyprid
@description:       This component handle the visibility for mobile and desktop devices
                    as well as the data storage for child components.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-08-09
@modified:          2018-10-13
-->
<template>
    <div class="tasks-view main-container" v-loading="loading">
        <q-pull-to-refresh
            :handler="refreshPage"
            :pull-message="$t('PULL_DOWN_TO_REFRESH')"
            :release-message="$t('RELEASE_TO_REFRESH')"
            :refresh-message="$t('REFRESHING')"
            :class="{'o-visible--y': !isDeviceGreaterSM}">

            <div class="tasks__inner m-b-5--sm">
                <el-row type="flex" justify="space-around" class="m-b-1 m-t-1--sm" style="align-items: center;">
                    <el-col :span="24">
                        <h3 class="headline m-t-0 m-b-0 m-l-half--sm m-r-half--sm">{{ $t('OVERVIEW') }}</h3>
                    </el-col>

                    <el-col :span="24" >
                        <div class="text-right m-l-half--sm m-r-half--sm">
                            <!-- DESKTOP -->
                            <div v-if="isDeviceGreaterSM">
                                <q-btn v-if="showFilter"
                                    flat
                                    :label="$t('HIDE_FILTER')"
                                    class="m-r-1"
                                    no-ripple
                                    @click="toggleFilter"
                                    icon="filter_list" />
                                <q-btn v-else
                                    flat
                                    :label="$t('SHOW_FILTER')"
                                    class="m-r-1"
                                    no-ripple
                                    @click="toggleFilter"
                                    icon="filter_list" />

                                <q-btn
                                    color="primary"
                                    :label="$t('CREATE_TASK')"
                                    class="w-100--sm"
                                    @click="onClickBtnNewTask"
                                    no-ripple />
                            </div>
                            <!-- MOBILE -->
                            <div v-if="!isDeviceGreaterSM">
                                <q-btn
                                    flat
                                    round
                                    no-ripple
                                    @click="toggleFilter"
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
                            <transition name="slide">
                                <!-- Filters -->
                                <FormFilterBarTasks
                                    v-show="showFilter"
                                    class="bg-color-gray p-1 p-b-0"
                                    @refresh="requestTasks">
                                </FormFilterBarTasks>
                            </transition>

                            <!-- Tasks Table -->
                            <TableTasks
                                :data="tasks"
                                :is-loading="loading"
                                @refresh="requestTasks"
                                @change-selection="onChangeSelection">
                            </TableTasks>
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

        <ButtonFabCreateTask v-if="!isDeviceGreaterSM"></ButtonFabCreateTask>
    </div>
</template>

<script>
import axios from 'axios';
import ButtonFabCreateTask from '@/components/Button/ButtonFabCreateTask';
import FormFilterBarTasks from '@/components/Form/FormFilterBarTasks';
import TabActionBar from '@/components/Tab/TabActionBar';
import TableTasks from '@/components/Table/TableTasks';
import tasksMixin from '@/shared/mixins/tasks';

export default {
	name: 'TasksView',

	mixins: [tasksMixin],

	components: {
		ButtonFabCreateTask,
		FormFilterBarTasks,
		TabActionBar,
		TableTasks
	},

	computed: {
		company() {
			return this.$store.state.user.company;
		},

		isDeviceGreaterSM() {
			return this.$store.getters.IS_DEVICE_GREATER_SM;
		},

		tasks() {
			return this.$store.state.tasks.tasks;
		},

		locations() {
			return this.$store.getters['locations/locationOptions'];
		},

		taskPriorities() {
			return this.$store.getters['tasks/taskPriorities'];
		},

		taskStates() {
			return this.$store.getters['tasks/taskStates'];
		},

		taskTypes() {
			return this.$store.getters['tasks/taskTypes'];
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
			loading: false,
			loadingTasks: false,
			selectedItems: [],
			showFilter: false,
			tabBarActions: [
				{
					name: 'mark_done',
					label: this.$t('DONE'),
					icon: 'done',
					handler: this.handleChangeStates
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
		handleChangeStates() {
			this.doFinishTasks(this.selectedItems).then(response => {
				if (response.status === 204) {
					this.$q.notify({
						message: this.$t('SAVE_SUCCESS'),
						type: 'positive'
					});
				}
				this.requestTasks();
			});
		},

		handleDeleteItems() {
			if (this.isDeviceGreaterSM) {
				this.$confirm(this.$t('WOULD_YOU_LIKE_TO_DELETE_TASKS'), this.$t('CONFIRM_DELETE'), {
					confirmButtonText: this.$t('OK'),
					cancelButtonText: this.$t('CANCEL'),
					type: 'warning'
				})
					.then(() => {
						this.doDeleteTasks(this.selectedItems)
							.then(response => {
								this.requestTasks();
							})
							.catch(err => {});
					})
					.catch(() => {});
			} else {
				this.$q
					.dialog({
						title: this.$t('CONFIRM_DELETE'),
						message: this.$t('WOULD_YOU_LIKE_TO_DELETE_TASKS'),
						ok: true,
						cancel: true
					})
					.then(() => {
						this.doDeleteTasks(this.selectedItems)
							.then(response => {
								this.requestTasks();
							})
							.catch(err => {});
					})
					.catch(() => {});
			}
		},

		init() {
			console.log('TasksView mounted');

			// this.requestTasks();
			// this.requestTaskMeta();
			this.requestInitalData();
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

		onClickBtnNewTask() {
			console.log('onClickBtnNewTask');

			if (this.isDeviceGreaterSM) {
				this.$store.commit('OPEN_DIALOG', {
					title: this.$t('CREATE_TASK'),
					loadComponent: 'Form/FormEditTask',
					width: '50%',
					refreshAfterClose: true
				});
			} else {
				this.$store.commit('OPEN_MODAL', {
					title: this.$t('CREATE_TASK'),
					loadComponent: 'Form/FormEditTask',
					maximized: true,
					refreshAfterClose: true
				});
			}
		},

		onTabActionBarItemClick(data) {
			console.log('onTabActionBarItemClick', data);
			switch (data.item) {
				case 'delete':
					console.log('delete items', this.selectedItems);
					this.handleDeleteItems();
					break;
				case 'mark_done':
					console.log('done items');
					this.handleChangeStates();
					break;
				default:
					console.log('nichts trifft zu');
					break;
			}
		},

		refreshPage(done) {
			this.requestTasks()
				.then(() => {
					done();
				})
				.catch(() => {
					done();
				});
		},

		requestInitalData() {
			this.loading = true;

			const REQUEST = [this.requestTasks(), this.requestTaskMeta()];

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

		requestLocations() {
			return this.$store
				.dispatch('locations/GET_LOCATIONS', { id: this.company.id })
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		/**
		 * We need all kinds of additional data to feed our child components.
		 */
		requestTaskMeta() {
			const REQUEST = [this.requestLocations()];

			if (!this.taskPriorities.length) {
				REQUEST.push(this.requestTaskPriorites());
			}

			if (!this.taskStates.length) {
				REQUEST.push(this.requestTaskStates());
			}

			if (!this.taskTypes.length) {
				REQUEST.push(this.requestTaskTypes());
			}

			if (!this.users.length) {
				REQUEST.push(this.requestUsers());
			}

			axios
				.all(REQUEST)
				.then((...results) => {})
				.catch(err => {
					console.log('err', err);
				});
		},

		requestTasks() {
			this.loading = true;

			return this.$store
				.dispatch('tasks/GET_TASKS', { id: this.company.id })
				.then(response => {
					this.loading = false;
					return response;
				})
				.catch(err => {
					this.loading = false;
					return err;
				});
		},

		requestTaskPriorites() {
			return this.$store.dispatch('tasks/GET_TASK_PRIORITIES', { id: this.company.id }).then(response => {
				return response;
			});
		},

		requestTaskStates() {
			return this.$store.dispatch('tasks/GET_TASK_STATES', { id: this.company.id }).then(response => {
				return response;
			});
		},

		requestTaskTypes() {
			return this.$store.dispatch('tasks/GET_TASK_TYPES', { id: this.company.id }).then(response => {
				return response;
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
	}
};
</script>

<style lang="scss">
.slide-enter-active,
.slide-leave-active {
	transition: all 0.3s ease-in-out;
}

.slide-enter,
.slide-leave-to {
	opacity: 0;
}
</style>
