export default {
	computed: {
		user() {
			return this.$store.state.user.data;
		}
	},

	methods: {
		isRoleSufficent(item) {
			if (item.role === 'user') {
				return true;
			}

			if (this.user.role === item.role) {
				return true;
			}

			if (this.user.role === 'admin' && item.role === 'user') {
				return true;
			}

			if (this.user.role === 'admin' && item.role === 'admin') {
				return true;
			}

			if (this.user.role === 'superadmin') {
				return true;
			}

			return false;
		}
	}
};
