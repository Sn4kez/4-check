<!--
@component:         AppLayout
@environment:       Hyprid
@description:       Description
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-06-21
@modified:          2018-10-18
-->
<template>
    <div class="app-layout__outer">
        <!-- NETWORK NOTIFICATION -->
        <transition name="slide">
            <div
                v-show="network.state === 'offline'"
                class="notification__container"
                :style="{'top':headerHeight + 'px'}">
                <p class="notification m-b-0"
                    style="background-color: rgba(128,128,128,.8)">
                    {{ $t('NO_NETWORK_CONNECTION') }}
                </p>
            </div>
        </transition>

        <!-- ++++++++++++++++++++++++++ DESKTOP ++++++++++++++++++++++++++ -->
        <div class="app-layout" v-if="isDeviceGreaterSM">

            <el-container class="pos-r el-container--main">
                <!-- Aside -->
                <el-aside :width="aside.width" :class="{'is-collapse': asideIsCollapse}">
                    <AppAside :is-collapse="asideIsCollapse" />
                </el-aside>

                <el-container class="el-container__inner--right pos-r"
                    :class="{'is-wide': asideIsCollapse}">
                    <el-header :height="header.height">
                        <AppTopBar />
                    </el-header>
                    <el-main :style="{'margin-top': header.height}">

                        <!-- Main content goes here -->
                        <router-view v-if="appIsReady"></router-view>

                    </el-main>
                </el-container>

            </el-container>

            <!-- OffCanvas -->
            <OffCanvas
                :open="drawerOpenRight"
                :title="$t('NOTIFICATIONS')"
                @close="toggleDrawerRight">

                <template>
                    <!-- In further iteration we could think of implementing dynamic components here -->
                    <TabNotifications :reload="drawerOpenRight"></TabNotifications>
                </template>

            </OffCanvas>
        </div>

        <!-- ++++++++++++++++++++++++++ MOBILE ++++++++++++++++++++++++++ -->
        <q-layout view="hHr LpR lFf" v-if="!isDeviceGreaterSM">
            <!-- Header -->
            <q-layout-header>
                <q-toolbar class="text-black" inverted>
                    <q-btn v-if="topbar.showBackButton"
                        icon="arrow_back"
                        flat round dense
                        @click="onClickBtnBack" />
                    <q-icon v-else-if="topbar.icon"
                        :name="topbar.icon" />

                    <q-toolbar-title>
                        {{$t(topbar.viewName)}}
                    </q-toolbar-title>

                    <ButtonCreate :loadComponent="'neussel'" type="flat" />

                    <!-- Notification -->
                    <q-btn
                        @click="drawerOpenRight = !drawerOpenRight"
                        flat
                        round
                        icon="notifications_none" />
                </q-toolbar>
            </q-layout-header>

            <!-- Drawer left -->
            <!-- <q-layout-drawer
                content-class="flex"
                side="left"
                v-model="drawerOpen">

                <AppNavigation :items="mainNavItems" :is-collapse="false" theme="light" class="m-t-2" />

                <div class="flex flex-column w-100 o-hidden" style="justify-content: flex-end;">
                    <Separator :show-line="true" />
                    <AppNavigation :items="subNavItems" :is-collapse="false" theme="light" />
                </div>
            </q-layout-drawer> -->

            <!-- Drawer right -->
            <q-layout-drawer
                side="right"
                v-model="drawerOpenRight">
                <h3 class="headline m-t-1 font--regular-plus p-l-1">{{$t('NOTIFICATIONS')}}</h3>
                <TabNotifications :reload="drawerOpenRight" class="m-t-1 d-block w-100"></TabNotifications>
            </q-layout-drawer>

            <!-- Content -->
            <q-page-container>
                <q-page>
                    <!-- ToDo: Figure out why the animation do not work on mobile devices -->
                    <!-- <transition appear
                        enter-active-class="animated slideInRight"
                        leave-active-class="animated slideOutLeft"> -->

                        <router-view v-if="appIsReady"></router-view>

                    <!-- </transition> -->
                </q-page>
            </q-page-container>

            <ButtonFabCreateNew v-if="showButtonFabCreateNew" class="hide-md" />

            <q-layout-footer>
                <!-- Mobile Tabbar -->
                <AppTabBar :items="tabBarNavItems" />
            </q-layout-footer>
        </q-layout>

    </div>
</template>

<script>
import axios from 'axios';
import AppAside from '@/components/App/AppAside';
import AppNavigation from '@/components/App/AppNavigation';
import AppTopBar from '@/components/App/AppTopBar';
import AppTabBar from '@/components/App/AppTabBar';
import OffCanvas from '@/components/App/OffCanvas';
import ButtonFabCreateNew from '@/components/Button/ButtonFabCreateNew';
import Separator from '@/components/Misc/Separator';
import TabNotifications from '@/components/Tab/TabNotifications';
import locationMixins from '@/shared/mixins/locations';
import tasksMixin from '@/shared/mixins/tasks';
import ButtonCreate from '@/components/Button/ButtonCreate';

import navigation from '@/shared/navigation';

export default {
	name: 'AppLayout',

	mixins: [locationMixins, tasksMixin],

	components: {
		AppAside,
		AppNavigation,
		AppTabBar,
		AppTopBar,
		ButtonFabCreateNew,
		OffCanvas,
		Separator,
		TabNotifications,
        ButtonCreate,
	},

	computed: {
		appIsReady() {
			return this.$store.state.app.ready;
		},

		asideIsCollapse() {
			return this.$store.state.aside.isCollapse;
		},

		company() {
			return this.$store.state.user.company;
		},

		drawerRightOpen() {
			return this.$store.state.app.drawerOpenRight;
		},

		headerHeight() {
			let height = 54;
			if (this.isDeviceGreaterSM) {
				height = 58;
				if (document.querySelector('.el-header')) {
					height = document.querySelector('.el-header').offsetHeight;
				}
			} else {
				height = 70;
			}
			return height;
		},

		isDeviceGreaterSM() {
			return this.$store.getters.IS_DEVICE_GREATER_SM;
		},

		network() {
			return this.$store.state.network;
		},

		showButtonFabCreateNew() {
			return this.$store.state.showButtonFabCreateNew;
		},

		topbar() {
			return this.$store.state.topbar;
		}
	},

	data() {
		return {
			drawerOpen: false,
			drawerOpenRight: false,
			aside: {
				width: '13rem'
			},
			header: {
				height: '3rem'
			},
			mainNavItems: navigation.mainNavItems,
			subNavItems: navigation.subNavItems,
			tabBarNavItems: navigation.tabBarNavItems
		};
	},

	mounted() {
		this.init();
	},

	methods: {
		init() {
			this.$http.defaults.headers.common['Authorization'] = 'Bearer ' + localStorage.getItem('access_token');

			this.requestCurrentUser();
			this.$store.dispatch('users/GET_USERS');
		},

		onClickBtnBack() {
			console.log('onClickBtnBack', this.topbar);
			if (this.topbar.link) {
				this.$router.push(this.topbar.link);
			} else {
				this.$router.go(-1);
			}
		},

		requestAppData(companyId) {
			this.$store.commit('SET_APP_LOADING', { loading: true });
			const REQUEST = [];

			REQUEST.push(this.requestAuditStates(companyId));
			REQUEST.push(this.requestLocationTypes());
			REQUEST.push(this.requestLocations());
			REQUEST.push(this.requestTasksTypes(companyId));
			REQUEST.push(this.requestTasksStates(companyId));
			REQUEST.push(this.requestTasksPriorities(companyId));

			axios
				.all(REQUEST)
				.then(
					axios.spread((...results) => {
						console.log('app data successfully loaded.', results);
						this.$store.commit('SET_APP_LOADING', { loading: false });
					})
				)
				.catch(err => {
					console.log('error during app data load.', { err });
					this.$store.commit('SET_APP_LOADING', { loading: false });
				});
		},

		requestAuditStates(companyId) {
			return this.$store
				.dispatch('audits/GET_AUDIT_STATES', { id: companyId })
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		requestCurrentUser() {
			this.$store.commit('SET_APP_LOADING', { loading: true });
			const userDataRequests = [];

			this.$store
				.dispatch('GET_CURRENT_USER')
				.then(response => { console.log('res', response);
					this.$store.commit('SET_APP_LOADING', { loading: false });
					localStorage.setItem('lastlogin', response.data.data.lastLogin);
					userDataRequests.push(this.requestUserCompany(response.data.data.companyId));
					userDataRequests.push(this.requestUserPhones(response.data.data.id));

					this.requestUserDataAll(userDataRequests);
				})
				.catch(err => {
					this.$store.commit('SET_APP_LOADING', { loading: false });
				});
		},

		requestUserCompany(companyId) {
			return this.$store.dispatch('companies/GET_COMPANY', { id: companyId }).then(response => {
				this.$store.commit('SET_USER_COMPANY', response.data.data);

				// Request additional data like states and types for locations and tasks
				this.requestAppData(response.data.data.id);

				return response;
			});
		},

		requestUserDataAll(userDataRequests) {
			axios.all(userDataRequests).then(
				axios.spread((...results) => {
					this.$store.commit('SET_APP_READY', { ready: true });
				})
			);
		},

		requestUserPhones(userId) {
			return this.$store.dispatch('users/GET_PHONES', { id: userId }).then(response => {
				this.$store.commit('SET_USER_PHONES', response.data.data);
				return response;
			});
		},

		requestSubscription(id) {
			return this.$store.dispatch('subscriptions/GET_SUBSCRIPTION', { id: id }).then(response => {
				this.$store.commit('SET_USER_SUBSCRIPTION', response.data.data);
				return response;
			});
		},

		toggleDrawerRight() {
			this.$store.commit('TOGGLE_DRAWER_RIGHT');
		}
	},

	watch: {
		drawerRightOpen(newValue) {
			this.drawerOpenRight = newValue;
		}
	}
};
</script>

<style lang="scss">
.notification__container {
	position: fixed;
	top: 52px;
	left: 0;
	width: 100%;
	z-index: 1;
}

.app-layout__outer {
	.slide-enter-active,
	.slide-leave-active {
		transition: all 0.3s ease-in-out;
	}

	.slide-enter,
	.slide-leave-to {
		opacity: 0;
		transform: translateY(-100%);
	}
}

.app-layout {
	.el-aside {
		display: none;
		transition: all 0.3s ease-in-out;

		@media screen and (min-width: $screen-md) {
			display: inherit;
			background-color: $c-sidebar-bg;
			color: #fff;
			flex-direction: column;
			height: 100vh;
			position: fixed;
			z-index: 1;

			width: 100%;
			max-width: 15rem !important;
		}

		&.is-collapse {
			width: $sidebar-with-collapse !important;
		}
	}

	.el-container {
		&--main {
			margin-bottom: $app-tabbar-height-xs;
			min-height: 100vh;

			@media screen and (max-width: $screen-md) {
				margin-bottom: $app-tabbar-height-sm;
			}

			@media screen and (min-width: $screen-md) {
				margin-bottom: 0;
			}
		}

		&__inner {
			&--right {
				margin-left: 0;

				@media screen and (min-width: $screen-md) {
					margin-left: $sidebar-width;
					transition: all 0.3s ease-in-out;
					width: calc(100vw - #{$sidebar-width});

					&.is-wide {
						margin-left: $sidebar-with-collapse;
						width: calc(100vw - (#{$sidebar-with-collapse} + 15px));

						.el-header {
							width: calc(100% - #{$sidebar-with-collapse});
						}
					}
				}
			}
		}
	}

	.el-header {
		background-color: $c-header-bg;
		border-bottom: 1px solid $c-light-gray;
		position: fixed;
		height: $header-height-sm;
		width: 100%;
		z-index: 10;
		transition: all 0.3s ease-in-out;

		@media screen and (min-width: $screen-md) {
			height: $header-height-md !important;
			width: calc(100% - #{$sidebar-width});
		}
	}

	.el-main {
		background-color: $c-app-bg;
		padding: 28px;

		@media screen and (min-width: $screen-md) {
			margin-top: $header-height-md !important;
		}
	}
}

.q-layout {
	.v-leave-to {
		animation-duration: 0.2s;
		height: 0 !important;
	}

	.v-enter-to {
		animation-duration: 0.4s;
	}

	.q-layout-page-container {
		background-color: $c-app-bg--mobile;
		height: 100%;

		> * {
			height: 100%;
		}

		//padding-bottom: 0 !important; // Quick fix
	}
}

/**
 * BACKGROUND
 */
.bg {
	&--app {
		background-color: $c-app-bg;
	}

	&--light {
		background-color: #ffffff;
	}
}

.q-layout-header {
	box-shadow: 0 2px 4px -1px rgba(0, 0, 0, 0.2), 0 1px 5px rgba(0, 0, 0, 0.01) !important;
}

.q-layout-footer {
	box-shadow: 0 -2px 4px -1px rgba(0, 0, 0, 0.1), 0 -4px 5px rgba(0, 0, 0, 0.07), 0 -1px 10px rgba(0, 0, 0, 0.06) !important;
}

@media only screen
    and (min-device-width : 414px)
    and (max-device-width : 736px)
    and (device-width : 414px)
    and (device-height : 736px)
    and (orientation : portrait)
    and (-webkit-min-device-pixel-ratio : 3)
    and (-webkit-device-pixel-ratio : 3){

	.m-t-1{
		margin-top:2rem!important;
	}

}

@media only screen
    and (min-device-width : 375px) // or 213.4375em
    and (max-device-width : 667px) // or 41.6875em
    and (width : 375px) // or 23.4375em
    and (height : 559px) // or 34.9375em
    and (orientation : portrait)
    and (color : 8)
    and (device-aspect-ratio : 375/667)
    and (aspect-ratio : 375/559)
    and (device-pixel-ratio : 2)
    and (-webkit-min-device-pixel-ratio : 2){

 	 .m-t-1{
		margin-top:2rem!important;
	}
}

</style>
