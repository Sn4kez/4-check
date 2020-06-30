<!--
@component:         AccountManagementView
@environment:       Hyprid
@description:       This component handle the visibility for mobile and desktop devices
                    as well as the data storage for child components.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-09-15
@modified:          2018-09-18
-->
<template>
    <div class="view__accountmanagement main-container">
        <el-row justify="space-around" class="m-t-2--sm m-b-1 flex--md" style="align-items: center;">
            <el-col :xs="24" :sm="16">
                <h3 class="headline m-t-0 m-b-0 m-l-half--sm m-r-half--sm">{{ $t('ACCOUNT_MANAGEMENT') }}</h3>
            </el-col>

            <el-col :xs="24" :sm="8" class="m-t-2--sm p-l-half--sm p-r-half--sm">
                <el-input
                    placeholder="Suche nach Vorname, Nachname, E-Mail. Min. 2 Ziffern."
                    prefix-icon="el-icon-search"
                    clearable
                    v-model="searchString">
                </el-input>
            </el-col>
        </el-row>

        <el-row>
            <el-col :xs="24">
                <el-card class="el-card--no-padding" v-loading="loading" style="min-height: 6rem;">
                    <TableUserAdministration v-if="!loading"
                        :data="filteredUsers"
                        :is-loading="false">
                    </TableUserAdministration>
                </el-card>
            </el-col>
        </el-row>

    </div>
</template>

<script>
import axios from 'axios';
import TableUserAdministration from '@/components/Table/TableUserAdministration';
import { searchStringInArray } from '@/services/utils';

export default {
	name: 'AccountManagementView',

	components: {
		TableUserAdministration
	},

	computed: {
		companies() {
			return this.$store.state.companies.companies;
		},
		users() {
			return this.$store.state.users.users;
		}
	},

	data() {
		return {
			filteredUsers: [],
			loading: false,
			searchString: '',
			searchInColumns: ['firstName', 'lastName', 'email']
		};
	},

	mounted() {
		this.init();
	},

	methods: {
		init() {
			console.log('Account administration mounted');
			this.requestInitalData();
			this.registerEvents();
		},

		onSearchInput() {
			const string = this.searchString.toString();
			const searchString = string.toLowerCase();

			this.filteredUsers = searchStringInArray(searchString, this.users, this.searchInColumns);
		},

		requestInitalData() {
			const REQUEST = [];

			REQUEST.push(this.requestCompanies());
			REQUEST.push(this.requestUsers());

			this.loading = true;
			axios
				.all(REQUEST)
				.then(
					axios.spread((...results) => {
						this.loading = false;
						this.filteredUsers = _.cloneDeep(this.users);
					})
				)
				.catch(err => {
					this.loading = false;
				});
		},

		requestCompanies() {
			return this.$store
				.dispatch('companies/GET_COMPANIES')
				.then(response => {
					console.log('ok companies');
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		requestUsers() {
			return this.$store
				.dispatch('users/GET_USERS')
				.then(response => {
					console.log('ok users');
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		registerEvents() {
			this.$eventbus.$on('dialog:closed', () => {
				this.requestUsers();
			});

			this.$eventbus.$on('modal:closed', () => {
				this.requestUsers();
			});
		}
	},

	watch: {
		searchString(newValue, oldValue) {
			if (newValue === oldValue) {
				return;
			}

			if (newValue.length && newValue.length < 2) {
				return;
			}

			this.onSearchInput();
		}
	}
};
</script>

<style lang="scss">
</style>
