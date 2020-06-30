import { getCountries } from '@/services/country';

export default {
	namespaced: true,

	state: {
		countries: getCountries()
	},

	getters: {
		getCountryByValue: state => value => {
			const code = value.toUpperCase();
			return state.countries.find(state => state.value === code);
		}
	},

	mutations: {},

	actions: {}
};
