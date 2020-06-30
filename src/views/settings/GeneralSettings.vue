<!--
@component:         GeneralSettingsView
@environment:       Hyprid
@description:       This component handle the visibility for mobile and desktop devices
                    as well as the data storage for child components.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-07-15
@modified:          2018-09-03
-->
<template>
    <div class="view__general-settings main-container" v-loading="loading">
        <!-- ++++++++++++++ DESKTOP ++++++++++++++ -->
        <div class="m-t-1" v-if="isDeviceGreaterSM">

            <!-- Company address -->
            <el-row :gutter="20">
                <el-col :xs="24">
                    <h3 class="headline">{{$t('COMPANY_ADDRESS')}}</h3>
                </el-col>
            </el-row>
            <el-row :gutter="20">
                <el-col :xs="24" :md="24">
                    <el-card>
                        <CompanyAddresses :addresses="companyAddresses"></CompanyAddresses>
                    </el-card>
                </el-col>
            </el-row>

            <!-- Subscription -->
            <el-row :gutter="20" class="m-t-4">
                <el-col :xs="24">
                    <h3 class="headline">{{$t('ACCOUNT_MANAGEMENT')}}</h3>
                </el-col>
            </el-row>
            <el-row :gutter="20">
                <el-col :xs="24" :md="24">
                    <el-card>
                        <CompanyCurrentSubscription></CompanyCurrentSubscription>
                    </el-card>
                </el-col>
            </el-row>
        </div>

        <!-- ++++++++++++++ MOBILE ++++++++++++++ -->
        <el-card v-if="!isDeviceGreaterSM"
            class="el-card--no-padding-sm"
            style="min-height: 4rem;">

            <ListNavItems :items="navItems"></ListNavItems>

        </el-card>

    </div>
</template>

<script>
import CompanyAddresses from '@/components/CompanyAddresses';
import CompanyCurrentSubscription from '@/components/CompanyCurrentSubscription';
import ListNavItems from '@/components/List/ListNavItems';

export default {
	name: 'GeneralSettingsView',

	components: {
		CompanyAddresses,
		CompanyCurrentSubscription,
		ListNavItems
	},

	computed: {
		company() {
			return this.$store.state.user.company;
		},

		companyAddresses() {
			return this.$store.state.companies.addresses;
		},

		isDeviceGreaterSM() {
			return this.$store.getters.IS_DEVICE_GREATER_SM;
		},

		user() {
			return this.$store.state.user;
		}
	},

	data() {
		return {
			loading: false,
			navItems: [
				{
					name: this.$t('SYSTEM'),
					role: 'admin',
					component: {
						name: 'Form/FormSystemSettings',
						props: []
					}
				},
				{
					name: this.$t('COMPANY_ADDRESS'),
					role: 'admin',
					component: {
						name: 'CompanyAddresses',
						props: [
							{
								name: 'addresses',
								data: {}
							}
						]
					}
				},
				{
					name: this.$t('ACCOUNT_MANAGEMENT'),
					role: 'admin',
					component: {
						name: 'CompanyCurrentSubscription',
						props: []
					}
				}
			]
		};
	},

	mounted() {
		this.init();
	},

	methods: {
		/**
		 * In many cases data necessary for ListNavItems components has not been requested yet.
		 * In this case we assign missing data to the props after there have been requested
		 */
		handleNavItems() {
			this.navItems[1].component.props[0].data = this.companyAddresses;
		},

		init() {
			if (this.company) {
				this.requestCompanyAddresses(this.company.id);
			}

			console.log('GeneralSettingsView mounted');
		},

		requestCompanyAddresses(id) {
			this.loading = true;
			this.$store
				.dispatch('companies/GET_COMPANY_ADDRESSES', { id: id })
				.then(response => {
					this.loading = false;
					this.handleNavItems();
				})
				.catch(err => {
					this.loading = false;
				});
		}
	},

	watch: {
		company(newValue, oldValue) {
			this.requestCompanyAddresses(newValue.id);
		}
	}
};
</script>

<style lang="scss">
</style>
