<!--
@component:         LocationsView
@environment:       Hyprid
@description:       This component handle the visibility for mobile and desktop devices
                    as well as the data storage for child components.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-08-13
@modified:          2018-09-05
-->
<template>
    <div class="locations-view main-container" v-loading="loading">
        <q-pull-to-refresh
            :handler="refreshPage"
            :pull-message="$t('PULL_DOWN_TO_REFRESH')"
            :release-message="$t('RELEASE_TO_REFRESH')"
            :refresh-message="$t('REFRESHING')"
            :class="{'o-visible--y': !isDeviceGreaterSM}">

            <div class="locations__inner m-b-5--sm">
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

                                <ButtonCreateLocation></ButtonCreateLocation>
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
                                <FormFilterBarLocations
                                    v-show="showFilter"
                                    class="bg-color-light-gray p-1 p-t-0 p-b-2"
                                    @refresh="requestLocations">
                                </FormFilterBarLocations>
                            </transition>

                            <!-- Locations Table -->
                            <TableLocations
                                :data="locations"
                                :is-loading="loadingLocations"
                                @refresh="requestLocations"
                                @change-selection="onChangeSelection">
                            </TableLocations>

                            <!-- <TreeLocations
                                class="m-t-2"
                                @refresh="requestLocations">
                            </TreeLocations> -->

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

        <ButtonFabCreateLocation v-if="!isDeviceGreaterSM"></ButtonFabCreateLocation>
    </div>
</template>

<script>
import axios from 'axios';
import ButtonCreateLocation from '@/components/Button/ButtonCreateLocation';
import ButtonFabCreateLocation from '@/components/Button/ButtonFabCreateLocation';
import FormFilterBarLocations from '@/components/Form/FormFilterBarLocations';
import TabActionBar from '@/components/Tab/TabActionBar';
import TableLocations from '@/components/Table/TableLocations';
import TreeLocations from '@/components/Tree/TreeLocations';
import locationsMixin from '@/shared/mixins/locations';

export default {
	name: 'LocationsView',

	mixins: [locationsMixin],

	components: {
		ButtonCreateLocation,
		ButtonFabCreateLocation,
		FormFilterBarLocations,
		TabActionBar,
		TableLocations,
		TreeLocations
	},

	computed: {
		company() {
			return this.$store.state.user.company;
		},

		isDeviceGreaterSM() {
			return this.$store.getters.IS_DEVICE_GREATER_SM;
		},

		locations() {
			return this.$store.getters['locations/locationOptions'];
		},

		locationTypes() {
			return this.$store.getters['locations/locationTypes'];
		},

		locationStates() {
			return this.$store.getters['locations/locationStates'];
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
			loadingLocations: false,
			selectedItems: [],
			showFilter: false,
			tabBarActions: [
				// {
				// 	name: 'copy',
				// 	label: this.$t('COPY'),
				// 	icon: 'file_copy',
				// 	handler: this.handleDeleteItems
				// },
				// {
				// 	name: 'move',
				// 	label: this.$t('MOVE'),
				// 	icon: 'open_with',
				// 	handler: this.handleDeleteItems
				// },
				// {
				// 	name: 'archive',
				// 	label: this.$t('ARCHIVE'),
				// 	icon: 'archive',
				// 	handler: this.handleDeleteItems
				// },
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
		handleDeleteItems() {
			if (this.isDeviceGreaterSM) {
				this.$confirm(this.$t('WOULD_YOU_LIKE_TO_DELETE_LOCATIONS'), this.$t('CONFIRM_DELETE'), {
					confirmButtonText: this.$t('OK'),
					cancelButtonText: this.$t('CANCEL'),
					type: 'warning'
				})
					.then(() => {
						// this.loadingLocations = true;
						this.doDeleteLocations(this.selectedItems)
							.then(response => {
								// this.loadingLocations = false;
								this.requestLocations();
							})
							.catch(err => {
								// this.loadingLocations = false;
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
					.then(() => {
						this.doDeleteLocations(this.selectedItems)
							.then(response => {
								this.requestLocations();
							})
							.catch(err => {});
					})
					.catch(() => {});
			}
		},

		init() {
			console.log('LocationsView mounted');

			this.requestLocations();
			this.requestLocationsMeta();
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
					console.log('delete items', this.selectedItems);
					this.handleDeleteItems();
					break;
				default:
					console.log('nichts trifft zu');
					break;
			}
		},

		refreshPage(done) {
			this.requestLocations()
				.then(() => {
					done();
				})
				.catch(() => {
					done();
				});
		},

		requestLocations() {
			this.loading = true;
			return this.$store
				.dispatch('locations/GET_LOCATIONS', { id: this.company.id })
				.then(response => {
					this.loading = false;
					return response;
				})
				.catch(err => {
					this.loading = false;
					return err;
				});
		},

		requestLocationsMeta() {
			this.loading = true;

			const REQUEST = [];

			if (!this.locationStates.length) {
				REQUEST.push(this.requestLocationStates());
			}

			if (!this.locationTypes.length) {
				REQUEST.push(this.requestLocationTypes());
			}

			if (!this.users.length) {
				REQUEST.push(this.requestUsers());
			}

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

		requestLocationStates() {
			return this.$store.dispatch('locations/GET_LOCATION_STATES', { id: this.company.id }).then(response => {
				return response;
			});
		},

		requestLocationTypes() {
			return this.$store.dispatch('locations/GET_LOCATION_TYPES', { id: this.company.id }).then(response => {
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
