export default {
	methods: {
		getCountryByValue(value) {
			return this.$store.getters['countries/getCountryByValue'](value);
		}
	}
};
