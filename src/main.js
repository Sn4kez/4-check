// Main
import Vue from 'vue';

// STORE
import store from '@/store/index';

// AXIOS
import API from '@/config/api';
import VueAxios from 'vue-axios';

// i18n
import i18n from '@/i18n/index';

// ELEMENT UI
import ElementUI from 'element-ui';
import enLang from 'element-ui/lib/locale/lang/en';
import deLang from 'element-ui/lib/locale/lang/de';

// QUASAR FRAMEWORK
import './assets/stylus/quasar.styl';
import 'quasar-framework/dist/quasar.ie.polyfills';
import 'quasar-extras/animate';
import 'quasar-extras/roboto-font';
import 'quasar-extras/material-icons';
import 'quasar-extras/fontawesome';
import Quasar, {
	QAutocomplete,
	QBtn,
	QBtnDropdown,
	QCheckbox,
	QColor,
	QDatetime,
	QDatetimePicker,
	QField,
	QIcon,
	QInput,
	QItem,
	QItemMain,
	QItemSeparator,
	QItemSide,
	QItemTile,
	QLayout,
	QPageContainer,
	QPage,
	QLayoutHeader,
	QLayoutFooter,
	QLayoutDrawer,
	QPageSticky,
	QPullToRefresh,
	QList,
	QListHeader,
	QModal,
	QModalLayout,
	QRadio,
	QSearch,
	QSelect,
	QSlider,
	QTable,
	QTab,
	QTabs,
	QTabPane,
	QToggle,
	QToolbar,
	QToolbarTitle,
	QUploader,
	QWindowResizeObservable,
	CloseOverlay,
	ActionSheet,
	Dialog,
	Notify,
	TouchPan
} from 'quasar';

// VALIDATION
import Vuelidate from 'vuelidate';

// STORAGE
import VueLocalStorage from 'vue-localstorage';
import VueSessionStorage from 'vue-sessionstorage';

// PROGRESSBAR
import VueProgressBar from 'vue-progressbar';
const progressbarOptions = {
	color: '#3498db',
	failedColor: '#D80F16',
	thickness: '5px',
	transition: {
		speed: '0.2s',
		opacity: '0.6s',
		termination: 300
	}
};

// APP
import App from '@/App.vue';

// GLOBAL COMPONENTS
import ElementContainer from '@/components/Element/ElementContainer';
import ElementAuditContainer from '@/components/Element/ElementAuditContainer';

// ROUTER
import router from '@/router/index';

// SERVICE WORKER
import '@/registerServiceWorker';

// vue tour
import VueTour from 'vue-tour';

// EventBus
import EventBus from '@/eventbus/index';
Vue.prototype.$eventbus = EventBus;

// CONFIG
Vue.config.productionTip = false;

// REGISTER GLOBAL COMPONENTS
Vue.component('ElementContainer', ElementContainer);
Vue.component('ElementAuditContainer', ElementAuditContainer);

/**
 * USE Block
 *
 * For Quasar it is important to not register all available components, directives, plugins and animations.
 * Instead register only the stuff we use in our app to keep it simple and lightweight.
 */
Vue.use(ElementUI, { locale: enLang });
Vue.use(VueAxios, API);
Vue.use(VueProgressBar, progressbarOptions);
Vue.use(VueLocalStorage);
Vue.use(VueSessionStorage);
Vue.use(Vuelidate);
Vue.use(Quasar, {
	components: [
		QAutocomplete,
		QBtn,
		QBtnDropdown,
		QCheckbox,
		QColor,
		QDatetime,
		QDatetimePicker,
		QField,
		QIcon,
		QInput,
		QItem,
		QItemMain,
		QItemSeparator,
		QItemSide,
		QItemTile,
		QLayout,
		QPageContainer,
		QPage,
		QLayoutHeader,
		QLayoutFooter,
		QLayoutDrawer,
		QPageSticky,
		QPullToRefresh,
		QList,
		QListHeader,
		QModal,
		QModalLayout,
		QRadio,
		QSearch,
		QSelect,
		QSlider,
		QTable,
		QTab,
		QTabs,
		QTabPane,
		QToggle,
		QToolbar,
		QToolbarTitle,
		QUploader,
		QWindowResizeObservable
	],
	directives: [CloseOverlay, TouchPan],
	plugins: [ActionSheet, Dialog, Notify],
	animations: 'slideInRight slideOutLeft',
	supportIE: true
});

require('vue-tour/dist/vue-tour.css')

Vue.use(VueTour)

new Vue({
	i18n,

	router,

	store,

	render: h => h(App)
}).$mount('#app');

// service worker
const shouldSW = 'serviceWorker' in navigator;
if(shouldSW){
	navigator.serviceWorker.register('service-worker.js').then(() => {
		console.log('Service Worker Registered.');
	});


	self.addEventListener('install', () => {
		console.log('Install success');
	});

    self.addEventListener('activate', event => {
        const cacheWhitelist = ['4-check-api'];

        event.waitUntil(
            caches.keys().then(cacheNames => {
                return Promise.all(
                    cacheNames.map(cacheName => {
                        if (cacheWhitelist.indexOf(cacheName) === -1) {
                            return caches.delete(cacheName);
                        }
                    })
                );
            })
        );
    });

}
