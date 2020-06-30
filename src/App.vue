<!--
@component:         App
@description:       Description
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-05-22
@modified:          2018-10-12
-->
<template>
  <div id="app" v-loading="loading">

    <router-view />

    <!-- Shared components -->
    <!-- ElementUI Dialog -->
    <el-dialog
        :visible.sync="dialog.visible"
        :fullscreen="dialog.fullscreen"
        :title="dialog.title"
        :width="dialog.width"
        @open="onDialogOpen"
        @close="onDialogClose">

        <!-- Dynamically load component -->
        <component
            :is="dialogComponentLoader"
            v-if="dialog.loadComponent"
            :data="dialog.data"
            @cancel="onDialogClose">
        </component>

        <span
            v-if="dialog.content"
            v-html="dialog.content"
            v-loading="dialog.loading"
            :class="{'text-center d-block': dialog.textCenter}">
        </span>
        <span v-else v-loading="dialog.loading"></span>
    </el-dialog>

    <!-- Quasar Modal -->
    <q-modal
        v-model="modal.visible"
        :position="modal.position"
        :maximized="modal.maximized"
        :minimized="modal.minimized"
        @show="onModalShow"
        @hide="onModalHide">

        <q-modal-layout>
            <q-toolbar slot="header" inverted class="text-black">
                <q-btn
                    round
                    flat
                    @click="onModalHide"
                    icon="close">
                </q-btn>

                <q-toolbar-title>{{modal.title}}</q-toolbar-title>
            </q-toolbar>

            <div class="layout-padding p-t-2 p-r-1 p-b-1 p-l-1">

                <!-- Dynamically load component -->
                <component
                    :is="modalComponentLoader"
                    :data="modal.data"
                    v-if="modal.loadComponent"
                    @cancel="onModalHide">
                </component>

            </div>
        </q-modal-layout>
    </q-modal>

    <!-- Window resize observable -->
    <q-window-resize-observable @resize="onResize" />

    

  </div>
</template>

<script>
import { Plugins, StatusBarStyle } from '@capacitor/core';
const { Network, SplashScreen, StatusBar } = Plugins;

export default {
	name: 'App',

	computed: {
		dialog() {
			return this.$store.state.dialog;
		},

		dialogComponentLoader() {
			if (!this.dialog.loadComponent) {
				return null;
			} else {
				return () => import(`@/components/${this.dialog.loadComponent}`);
			}
		},

		loading() {
			return this.$store.state.app.loading;
		},

		modal() {
			return this.$store.state.modal;
		},

		modalComponentLoader() {
			if (!this.modal.loadComponent) {
				return null;
			} else {
				return () => import(`@/components/${this.modal.loadComponent}`);
			}
		}
	},

	updated(){
		// check if localstorage has value or not, if not then run tour
		var ii = localStorage.getItem('tour');
		// also check if on Dashboard page

		var ww = window.innerWidth;
		if(ii == undefined){
			// if(document.querySelector('.app-topbar--center') != undefined){
			var url = window.location.href;
			if(url.indexOf('dashboard') != -1){
				var lastlogin = localStorage.getItem('lastlogin');
				// now check if web or mobile
				if(ww > 990){
					console.log('this is runnign: ', lastlogin)
					if(lastlogin == undefined || lastlogin == null || lastlogin == "" || lastlogin == "null"){
						// this.$tours['myTour'].start();
					}else{
						// this.$tours['myTour'].stop();
					}
				}else{
					if(lastlogin == undefined || lastlogin == null || lastlogin == "" || lastlogin == "null"){
						// this.$tours['mobileTour'].start();
					}else{
						// this.$tours['mobileTour'].stop();
					}
				}

			}
			// }else{
			// 	// start mobile tour. also check for some condition for Dashboard page :TODO
			// }
		}
	},

	data() {
		return {
			dialogComponent: null,
			myOptions: {
				useKeyboardNavigation: false,
				labels: {
					buttonSkip: 'Beenden',
					buttonPrevious: 'Vorheriger Schritt',
					buttonNext: 'Nächster Schritt',
					buttonStop: 'Beenden'
				}
			}
		};
	},

	mounted() {
		this.init();
	},

	methods: {
		tourStopCallback(){
			localStorage.setItem('tour', 1);
		},

		init() {
			this.registerEvents();
			const state = navigator.onLine ? 'online' : 'offline';
			this.$store.commit('SET_NETWORK', { state: state });

			// Set authorization header
			this.$http.defaults.headers.common['Authorization'] = 'Bearer ' + this.$session.get('access_token');

			if (this.$q.platform.is.cordova) {
				SplashScreen.hide();
				StatusBar.show();
				StatusBar.setStyle({ style: StatusBarStyle.Light });
				StatusBar.setBackgroundColor({ style: StatusBarStyle.Dark });
			}
			console.log('App mounted!', navigator.onLine);

		},

		onDialogClose() {
			this.$store.commit('CLOSE_DIALOG');
		},

		onDialogOpen() {},

		onModalHide() {
			this.$store.commit('CLOSE_MODAL');
		},

		onModalShow() {},

		onResize(size) {
			this.$store.commit('SET_WINDOW_SIZE', size);
			this.$store.commit('SET_DEVICE_PROPERTIES');
		},

		onNetworkChange(event) {
			console.log('onNetworkChange', event.type);
			this.$store.commit('SET_NETWORK', { state: event.type });
		},

		registerEvents() {
			window.addEventListener(
				'online',
				event => {
					console.log('online', event);
					this.onNetworkChange(event);
				},
				true
			);

			window.addEventListener(
				'offline',
				event => {
					console.log('offline', event);
					this.onNetworkChange(event);
				},
				true
			);
		},

		unregisterEvents() {
			window.removeEventListener('online', this.onNetworkChange);
			window.removeEventListener('offline', this.onNetworkChange);
		}
	},

	destroyed() {
		this.unregisterEvents();
	}
};
</script>

<style lang="scss">
@import '@/assets/scss/theme-4-check.scss';
@import '@/assets/scss/styles.scss';
</style>
