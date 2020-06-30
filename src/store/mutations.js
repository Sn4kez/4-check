import Vue from 'vue';
import * as Browser from './../services/browser';

export default {
	ASIDE_TOGGLE: function(state, payload) {
		state.aside.isCollapse = !state.aside.isCollapse;
	},

	CLOSE_DIALOG: function(state, payload) {
		state.dialog.visible = false;
		state.dialog.loading = false;
		state.dialog.loadComponent = false;
		state.dialog.content = '';
		state.dialog.data = {};

		const body = document.querySelector('body');
		body.classList.remove('el-dialog--open');
		body.classList.remove('q-maximized-modal');

		// Emit event if dialog has been closed to refresh data for example
		if (state.dialog.refreshAfterClose) {
			Vue.prototype.$eventbus.$emit('dialog:closed');
			state.dialog.refreshAfterClose = false;
		}
	},

	CLOSE_MODAL: function(state, payload) {
		state.modal.visible = false;
		state.modal.loading = false;
		state.modal.loadComponent = false;
		state.modal.content = '';

		const body = document.querySelector('body');
		body.classList.remove('q-maximized-modal');

		// Emit event if modal has been closed to refresh data for example
		if (state.modal.refreshAfterClose) {
			Vue.prototype.$eventbus.$emit('modal:closed');
			state.modal.refreshAfterClose = false;
		}
	},

	OPEN_DIALOG: function(state, payload) {
		let fullscreen = payload.fullscreen || false;
		let dialogWidth = payload.width || '70%';

		if (Browser.isDeviceXS() || Browser.isDeviceSM()) {
			dialogWidth = '100%';
			fullscreen = true;
		}

		state.dialog.content = payload.content || '';
		state.dialog.data = payload.data || {};
		state.dialog.fullscreen = fullscreen;
		state.dialog.loadComponent = payload.loadComponent || false;
		state.dialog.loading = payload.loading || false;
		state.dialog.textCenter = payload.textCenter || false;
		state.dialog.title = payload.title || '';
		state.dialog.visible = payload.visible || true;
		state.dialog.width = dialogWidth;
		state.dialog.refreshAfterClose = payload.refreshAfterClose || false;

		const body = document.querySelector('body');
		body.classList.add('el-dialog--open');
	},

	OPEN_MODAL: function(state, payload) {
		state.modal.content = payload.content || '';
		state.modal.data = payload.data || {};
		state.modal.loadComponent = payload.loadComponent || false;
		state.modal.loading = payload.loading || false;
		state.modal.textCenter = payload.textCenter || false;
		state.modal.title = payload.title || '';
		state.modal.visible = payload.visible || true;
		state.modal.maximized = payload.maximized;
		state.modal.minimized = payload.minimized;
		state.modal.position = payload.position;
		state.modal.refreshAfterClose = payload.refreshAfterClose || false;
	},

	REMOVE_USER_PASSWORD: function(state, payload) {
		delete state.user.password;
	},

	SET_APP_LANGUAGE: function(state, payload) {
		// i18n.locale = payload.language;
		state.app.language = payload.language;
		Vue.prototype.$localStorage.set('languageCode', payload.language);
	},

	SET_APP_LOADING: function(state, payload) {
		state.app.loading = payload.loading;
	},

	SET_APP_READY: function(state, payload) {
		state.app.ready = payload.ready;
	},

	SET_BUTTON_FAB_CREATE_NEW_VISIBILITY: function(state, payload) {
		state.showButtonFabCreateNew = payload.visible;
	},

	SET_COMPANY_SUBSCRIPTION: function(state, payload) {
		state.user.company.subscription = payload;
	},

	SET_CUSTOM_DESIGN: function(state, payload) {
		state.app.isCustomDesign = payload.isCustomDesign;
		Vue.prototype.$localStorage.set('isCustomDesign', payload.isCustomDesign);
	},

	SET_DEVICE_PROPERTIES: function(state, payload) {
		state.device.isGreaterSM = Browser.isDeviceGreaterSM();
		state.device.isGreaterMD = Browser.isDeviceGreaterMD();
	},

	SET_NETWORK: function(state, payload) {
		state.network.state = payload.state;
	},

	SET_TOPBAR: function(state, payload) {
		state.topbar.icon = payload.icon;
		state.topbar.viewName = payload.name;
		state.topbar.link = payload.link;
		state.topbar.showBackButton = payload.showBackButton;
	},

	SET_TOPBAR_VIEW_NAME: function(state, payload) {
		state.topbar.viewName = payload.viewName;
	},

	SET_USER: function(state, payload) {
		state.user.data = payload;
	},

	SET_USER_PHONES: function(state, payload) {
		state.user.phones = payload;
	},

	SET_USER_COMPANY: function(state, payload) {
		state.user.company = payload;
	},

	SET_USER_PASSWORD: function(state, payload) {
		state.user.data.password = payload.password;
	},

    SET_USER_LOCALE: function(state, payload) {
        state.user.data.locale = payload.locale;
    },

	SET_WINDOW_SIZE: function(state, payload) {
		state.window.size = payload;
	},

	TOGGLE_DRAWER_RIGHT: function(state, payload) {
		state.app.drawerOpenRight = !state.app.drawerOpenRight;
	}
};
