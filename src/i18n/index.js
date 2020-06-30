import Vue from 'vue';
import VueI18n from 'vue-i18n';
import { getLocale } from '@/services/browser';
import messages from './messages';

Vue.use(VueI18n);

const dateTimeFormats = {
	en: {
		short: {
			year: 'numeric',
			month: 'short',
			day: 'numeric'
		},
		long: {
			year: 'numeric',
			month: 'short',
			day: 'numeric',
			weekday: 'short',
			hour: 'numeric',
			minute: 'numeric'
		}
	},
	de: {
		short: {
			year: 'numeric',
			month: 'short',
			day: 'numeric'
		},
		long: {
			year: 'numeric',
			month: 'short',
			day: 'numeric',
			weekday: 'short',
			hour: 'numeric',
			minute: 'numeric'
		}
	},
    fr: {
        short: {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        },
        long: {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            weekday: 'short',
            hour: 'numeric',
            minute: 'numeric'
        }
    }
};

export default new VueI18n({
	dateTimeFormats,

	fallbackLocale: getLocale(),

	locale: localStorage.getItem('locale') || getLocale(),

	messages
});
