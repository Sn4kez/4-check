<!--
@component:         DashboardView
@environment:       Hyprid
@description:       This component is a wrapper for the dashboard component on web and mobile.
                    Dependent on device size the inner component will be imported dynamically.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-06-22
@modified:          2018-10-12
-->
<template>
    <div class="view__dashboard main-container">

        <component :is="component" :loading="loading" class="dashboard"></component>

    </div>
</template>

<script>
export default {
	name: 'DashboardView',

	computed: {
		company() {
			return this.$store.state.user.company;
		},

		dashboard() {
			return this.$store.state.dashboard.data;
		},

		isDeviceGreaterSM() {
			return this.$store.getters.IS_DEVICE_GREATER_SM;
		},

		last_audits() {
			return this.$store.state.dashboard.data.last_audits;
		},

		next_audits() {
			return this.$store.state.dashboard.data.next_audits;
		}
	},

	data() {
		return {
			component: null,
			componentName: 'index_mobile',
			loading: false
		};
	},

	mounted() {
		this.init();
	},

	methods: {
		handleDeviceProperty() {
			if (this.isDeviceGreaterSM) {
				this.componentName = 'index_web';
			} else {
				this.componentName = 'index_mobile';
			}
		},

		init() {
			console.log('DashboardView mounted');
			this.requestDashboard();

			this.handleDeviceProperty();
			this.loadComponent();
		},

		loadComponent() {
			this.component = () => import(`@/views/dashboard/${this.componentName}`);
		},

		requestDashboard() {
			this.loading = true;

			return this.$store
				.dispatch('dashboard/GET_DATA')
				.then(response => {
					this.loading = false;
					return response;
				})
				.catch(err => {
					this.loading = false;
					return err;
				});
		}
	},

	watch: {
		isDeviceGreaterSM(newValue, oldValue) {
			if (newValue === oldValue) {
				return;
			}

			this.handleDeviceProperty();
			this.loadComponent();
		}
	}
};
</script>

<style lang="scss">
.dashboard {
	&__card-headline {
		color: $c-gray;
		font-size: 1.2rem;
		margin: 0 0 0.625rem;
	}

	.el-row {
		@media screen and (min-width: $screen-md) {
			margin-top: 40px;

			&:first-child {
				margin-top: 0;
			}
		}
	}

	.el-col {
		@media screen and (max-width: $screen-md) {
			margin-top: 40px;

			&:first-child {
				margin-top: 0;
			}
		}
	}

	.el-radio-button {
		flex: 1 0 auto;

		&__inner {
			width: 100%;
		}
	}
}
</style>
