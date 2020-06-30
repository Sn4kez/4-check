<!--
@component:         AdminCompanySettings
@environment:       Hyprid
@description:       This component handle the visibility for mobile and desktop devices
                    as well as the data storage for child components.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-09-15
@modified:          2018-09-18
-->
<template>
    <div class="p-1" v-loading="loading">
        <el-row :guter="20">
            <el-col :xs="24" :sm="16">
                <h3 class="headline">{{company.name}}</h3>
                <p v-if="addresses.length">
                    {{addresses[0].line1}} {{addresses[0].line2}} <br>
                    {{addresses[0].postalCode}} {{addresses[0].city}}
                </p>
            </el-col>
            <el-col :xs="24" :sm="8">
                <p>{{ $t('LAST_ACTIVE_ON') }}</p>
                <el-date-picker
                    v-model="subscription.updatedAt"
                    type="date"
                    :placeholder="$t('LAST_ACTIVE_ON')"
                    readonly>
                </el-date-picker>
            </el-col>
        </el-row>

        <el-row :gutter="20" class="m-t-3">
            <el-col :xs="24" :sm="6">
                <span>{{ $t('YOUR_CURRENT_SUBSCRIPTION') }}:</span> <br>
                <span class="font--regular-plus m-t-half d-inline-block"> Premium </span>
            </el-col>
            <el-col :xs="24" :sm="10">
                <span>{{ $t('DATE_OF_EXPIRY') }}</span>: <br>
                <span class="font--regular-plus m-t-half d-inline-block"> 31.12.2018 </span>
            </el-col>
            <el-col :xs="24" :sm="8">
                <q-btn
                    :label="$t('CHANGE_SUBSCRIPTION')"
                    color="primary"
                    :to="{path: '/settings/subscription'}"
                    class="w-100--sm">
                </q-btn>
            </el-col>
        </el-row>

        <!-- Term of contract -->
        <el-row :gutter="20" class="m-t-3">
            <el-col :xs="24">
                <h3 class="headline">{{$t('TERM_OF_CONTRACT')}}</h3>
            </el-col>
        </el-row>

        <el-row :gutter="20" class="">
            <el-col :xs="24" :sm="6">
                <!-- ToDo: Change model -->
                <el-date-picker
                    v-model="subscription.createdAt"
                    type="date"
                    :placeholder="$t('FROM')">
                </el-date-picker>
            </el-col>
            <el-col :xs="24" :sm="6">
                <!-- ToDo: Change model -->
                <el-date-picker
                    v-model="subscription.updatedAt"
                    type="date"
                    :placeholder="$t('TO')">
                </el-date-picker>
            </el-col>
        </el-row>

        <el-row :gutter="20" class="m-t-2">
            <el-col :xs="24">
                <q-toggle v-model="company.isActive" @input="doUpdateCompany(company)" :label="$t('ACCOUNT_ACTIVE')" :true-value="1" :false-value="0" />
            </el-col>
        </el-row>

        <!-- Users -->
        <el-row :gutter="20" class="m-t-4">
            <el-col :xs="24">
                <h3 class="headline">{{$t('USERS')}}</h3>
            </el-col>
        </el-row>

        <el-row>
            <el-col :xs="24">
                <TableCompanyUsers :data="users" :is-loading="false"></TableCompanyUsers>
            </el-col>
        </el-row>
    </div>
</template>

<script>
import axios from 'axios';
import { Company } from '@/shared/classes/Company';
import TableCompanyUsers from '@/components/Table/TableCompanyUsers';

export default {
	name: 'AdminCompanySettings',

	components: {
		TableCompanyUsers
	},

	props: {
		data: {
			type: Object,
			required: true
		}
	},

	computed: {
		users() {
			return this.$store.state.companies.users;
		}
	},

	data() {
		return {
			addresses: [],
			company: {},
			loading: false,
			subscription: {}
		};
	},

	mounted() {
		this.init();
	},

	methods: {
		doUpdateCompany(company) {
			const COMPANY = new Company(company);

			return this.$store
				.dispatch('companies/UPDATE_COMPANY', COMPANY)
				.then(response => {
					this.$q.notify({
						message: this.$t('SAVE_SUCCESS'),
						type: 'positive'
					});

					return response;
				})
				.catch(err => {
					return err;
				});
		},

		init() {
			console.log('AdminCompanySettings mounted', this.data);

			this.requestInitalData();
		},

		requestInitalData() {
			const REQUEST = [];

			REQUEST.push(this.requestCompany(this.data.companyId));
			REQUEST.push(this.requestAddresses(this.data.companyId));
			REQUEST.push(this.requestUsers(this.data.companyId));

			this.loading = true;
			axios
				.all(REQUEST)
				.then(
					axios.spread((...results) => {
						this.loading = false;
					})
				)
				.catch(err => {
					this.loading = false;
					console.log('requestInitalData error', err);
				});
		},

		requestAddresses(companyId) {
			return this.$store
				.dispatch('companies/GET_COMPANY_ADDRESSES', { id: companyId })
				.then(response => {
					console.log('ok addresses');
					this.addresses = response.data.data;
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		requestCompany(id) {
			return this.$store
				.dispatch('companies/GET_COMPANY', { id: id })
				.then(response => {
					console.log('ok company');
					this.company = response.data.data;
					this.requestSubscription(this.company.subscription);

					return response;
				})
				.catch(err => {
					return err;
				});
		},

		requestSubscription(id) {
			this.loading = true;
			return this.$store
				.dispatch('subscriptions/GET_SUBSCRIPTION', { id: id })
				.then(response => {
					this.loading = false;
					console.log('ok subscription');
					this.subscription = response.data.data;

					return response;
				})
				.catch(err => {
					this.loading = false;
					return err;
				});
		},

		requestUsers(companyId) {
			return this.$store
				.dispatch('companies/GET_USERS', { id: companyId })
				.then(response => {
					console.log('ok users');
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
