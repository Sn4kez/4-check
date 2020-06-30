<template>
    <div class="group-view">
        <router-view></router-view>
    </div>
</template>

<script>
export default {
	name: 'GroupView',

	components: {},

	computed: {
		companies() {
			return this.$store.state.companies.companies;
		},

		groups() {
			return this.$store.state.companies.groups;
		},

		isDeviceGreaterSM() {
			return this.$store.getters.IS_DEVICE_GREATER_SM;
		},

		users() {
			return this.$store.state.users.users;
		}
	},

	data() {
		return {
			loading: false
		};
	},

	mounted() {
		this.init();
	},

	methods: {
		init() {
			console.log('GroupView mounted');

			// this.requestUsers();
			// this.requestCompanies();
		},

		onCreateGroup() {},

		requestCompanies() {
			this.$store.dispatch('companies/GET_COMPANIES').then(response => {
				console.log('request companies', response);
				this.loading = false;
				this.requestGroups(response.data.data[0].id); // ToDo: Get all groups for current user/company
			});
		},

		requestGroups(companyId) {
			this.loading = true;
			this.$store.dispatch('companies/GET_COMPANY_GROUPS', { id: companyId }).then(response => {
				console.log('request groups', response);
				this.loading = false;
			});
		},

		requestUsers() {
			this.loading = true;
			this.$store.dispatch('users/GET_USERS').then(response => {
				console.log('request users1', response);
				this.loading = false;
			});
		}
	}
};
</script>

<style lang="scss">
</style>
