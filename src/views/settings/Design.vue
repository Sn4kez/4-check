<!--
@component:         DesignView
@environment:       Hyprid
@description:       This component handle the visibility for mobile and desktop devices
                    as well as the data storage for child components.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-07-15
@modified:          2018-10-14
-->
<template>
    <div class="view__general-settings main-container" v-loading="loading">

        <!-- ++++++++++++++ DESKTOP ++++++++++++++ -->
        <div class="m-t-1" v-if="isDeviceGreaterSM">
            <!-- General settings -->
            <el-row :gutter="20">
                <el-col :xs="24">
                    <h3 class="headline">{{$t('GENERAL')}}</h3>
                </el-col>
            </el-row>
            <el-row :gutter="20">
                <el-col :xs="24" :md="24">
                    <el-card>
                        <CompanyGeneralDesign :company="company"></CompanyGeneralDesign>
                    </el-card>
                </el-col>
            </el-row>

            <!-- Web and mobile App -->
            <el-row :gutter="20" class="m-t-4">
                <el-col :xs="24">
                    <h3 class="headline">{{$t('WEB_AND_MOBILE_APP')}}</h3>
                </el-col>
            </el-row>
            <el-row :gutter="20">
                <el-col :xs="24" :md="24">
                    <el-card>
                        <FormCompanyDesignSettings :company="company" :preferences="designPreferences"></FormCompanyDesignSettings>
                    </el-card>
                </el-col>
            </el-row>

            <!-- Report -->
            <el-row :gutter="20" class="m-t-4">
                <el-col :xs="24">
                    <h3 class="headline">{{$t('REPORT')}}</h3>
                </el-col>
            </el-row>
            <el-row :gutter="20">
                <el-col :xs="24" :md="24">
                    <el-card>
                        <p class="m-b-2">{{$t('CONFIGURE_REPORT_EXPORT_DESIGN')}}</p>
                        <FormCompanyReportSettings :company="company" :preferences="reportPreferences"></FormCompanyReportSettings>
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
import axios from 'axios';
import CompanyGeneralDesign from '@/components/CompanyGeneralDesign';
import FormCompanyDesignSettings from '@/components/Form/FormCompanyDesignSettings';
import FormCompanyReportSettings from '@/components/Form/FormCompanyReportSettings';
import ListNavItems from '@/components/List/ListNavItems';

export default {
	name: 'DesignView',

	components: {
		CompanyGeneralDesign,
		FormCompanyDesignSettings,
		FormCompanyReportSettings,
		ListNavItems
	},

	computed: {
		company() {
			return this.$store.state.user.company;
		},

		isDeviceGreaterSM() {
			return this.$store.getters.IS_DEVICE_GREATER_SM;
		}
	},

	data() {
		return {
			designPreferences: {},
			loading: false,
			navItems: [
				{
					name: this.$t('GENERAL'),
					role: 'admin',
					component: {
						name: 'CompanyGeneralDesign',
						props: [
							{
								name: 'company',
								data: {}
							}
						]
					}
				},
				{
					name: this.$t('WEB_AND_MOBILE_APP'),
					role: 'admin',
					component: {
						name: 'Form/FormCompanyDesignSettings',
						props: [
							{
								name: 'company',
								data: {}
							},
							{
								name: 'preferences',
								data: {}
							}
						]
					}
				},
				{
					name: this.$t('REPORT'),
					role: 'admin',
					component: {
						name: 'Form/FormCompanyReportSettings',
						props: [
							{
								name: 'company',
								data: {}
							},
							{
								name: 'preferences',
								data: {}
							}
						]
					}
				}
			],
			reportPreferences: {}
		};
	},

	mounted() {
		this.init();
	},

	methods: {
		/**
		 * In many cases data necessary for ListNavItems components has not been requested yet.
		 * In this case we assign missing data to the props after there have been requested.
		 */
		handleNavItems() {
			this.navItems[0].component.props[0].data = this.company;
			this.navItems[1].component.props[0].data = this.company;
			this.navItems[2].component.props[0].data = this.company;
		},

		init() {
			this.handleNavItems();
			this.requestInitalData();

			console.log('DesignView mounted');
		},

		/**
		 * Concurrent request to get data for child components
		 */
		requestInitalData() {
			this.loading = true;

			const REQUEST = [this.requestDesignPreferences(), this.requestReportPreferences()];

			axios
				.all(REQUEST)
				.then(
					axios.spread((...results) => {
						this.designPreferences = results[0].data.data;
						this.navItems[1].component.props[1].data = this.designPreferences;

						this.reportPreferences = results[1].data.data;
						this.navItems[2].component.props[1].data = this.reportPreferences;

						this.loading = false;
					})
				)
				.catch(err => {
					this.loading = false;
					console.log(err);
				});
		},

		/**
		 * Design preferences
		 */
		requestDesignPreferences() {
			return this.$store
				.dispatch('companies/GET_COMPANY_DESIGN_PREFERENCES', { id: this.company.id })
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		/**
		 * Report preferences
		 */
		requestReportPreferences() {
			return this.$store
				.dispatch('companies/GET_COMPANY_REPORT_PREFERENCES', { id: this.company.id })
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		}
	}
};
</script>

<style lang="scss">
</style>
