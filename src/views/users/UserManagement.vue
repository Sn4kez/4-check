<!--
@component:         UserManagement
@environment:       Hyprid
@description:       This component handle the visibility for mobile and desktop devices
                    as well as the data storage for child components.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-07-15
@modified:          2018-10-13
-->
<template>
    <div class="user-management-view main-container">
        <!-- User -->
        <el-row type="flex" justify="space-around" class="m-t-2--sm m-b-1" style="align-items: center;">
            <el-col :span="24">
                <h3 class="headline m-t-0 m-b-0 m-l-half--sm m-r-half--sm">{{ $t('USER') }}</h3>
            </el-col>
        </el-row>

        <el-row>
            <el-col :xs="24">
                <el-card>

                    <!-- Desktop -->
                    <div v-if="isDeviceGreaterSM">
                        <el-tabs v-model="selectedTab" :stretch="false">
                            <el-tab-pane :label="$t('ACTIVE_USERS')" :name="$t('ACTIVE_USERS')" class="">
                                <TableUsers :data="users" :is-loading="loadingUsers" @refresh="requestUsers"></TableUsers>
                            </el-tab-pane>
                            <el-tab-pane :label="$t('INVITATIONS')" :name="$t('INVITATIONS')" class="">
                                <TableInvitations :data="invitations" :is-loading="false" @refresh="requestInvitations"></TableInvitations>
                            </el-tab-pane>
                        </el-tabs>
                    </div>

                    <!-- Mobile -->
                    <div v-if="!isDeviceGreaterSM">
                        <q-tabs v-model="selectedTab" align="left" inverted no-pane-border class="q-tabs--brand-ios">
                            <!-- Tabs - notice slot="title" -->
                            <q-tab slot="title" :name="$t('ACTIVE_USERS')">{{ $t('ACTIVE_USERS') }}</q-tab>
                            <q-tab slot="title" :name="$t('INVITATIONS')">{{ $t('INVITATIONS') }}</q-tab>

                            <!-- Targets -->
                            <q-tab-pane :name="$t('ACTIVE_USERS')" class="p-t-1">
                                <TableUsers :data="users" :is-loading="loadingUsers" @refresh="requestUsers"></TableUsers>
                            </q-tab-pane>

                            <q-tab-pane :name="$t('INVITATIONS')" class="p-t-1">
                                <TableInvitations :data="invitations" :is-loading="false" @refresh="requestInvitations"></TableInvitations>
                            </q-tab-pane>
                        </q-tabs>
                    </div>


                </el-card>
            </el-col>
        </el-row>

        <el-row type="flex" justify="space-around" class="m-t-1 m-t-2--sm">
            <el-col :span="24">
                <div class="text-right m-l-half--sm m-r-half--sm">
                    <q-btn
                        color="primary"
                        :label="$t('INVITE_USER')"
                        @click="onInviteUser"
                        class="w-100--sm"
                        no-ripple />
                </div>
            </el-col>
        </el-row>

        <!-- Groups -->
        <el-row type="flex" justify="space-around" class="m-t-4 m-b-1" style="align-items: center;">
            <el-col :span="24">
                <h3 class="headline m-t-0 m-b-0 m-l-half--sm m-r-half--sm">{{ $t('GROUPS') }}</h3>
            </el-col>
        </el-row>

        <el-row>
            <el-col :xs="24">
                <el-card>

                    <TableGroups :data="groups" :is-loading="loadingGroups" @refresh="requestGroups"></TableGroups>

                </el-card>
            </el-col>
        </el-row>

        <el-row type="flex" justify="space-around" class="m-t-1 m-t-2--sm m-b-1">
            <el-col :xs="24">
                <div class="text-right m-l-half--sm m-r-half--sm">
                    <q-btn
                        color="primary"
                        :label="$t('CREATE_GROUP')"
                        @click="onCreateGroup"
                        class="w-100--sm"
                        no-ripple />
                </div>
            </el-col>
        </el-row>
    </div>
</template>

<script>
import TableGroups from '@/components/Table/TableGroups';
import TableInvitations from '@/components/Table/TableInvitations';
import TableUsers from '@/components/Table/TableUsers';

export default {
	name: 'UserManagementView',

	components: {
		TableGroups,
		TableInvitations,
		TableUsers
	},

	computed: {
		company() {
			return this.$store.state.user.company;
		},

		groups() {
			return this.$store.state.companies.groups;
		},

		invitations() {
			return this.$store.state.companies.invitations;
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
			loadingGroups: false,
			loadingUsers: false,
			selectedTab: this.$t('ACTIVE_USERS')
		};
	},

	created() {
		console.log('UserManagementView created');
	},

	mounted() {
		this.init();
	},

	methods: {
		init() {
			console.log('UserManagementView mounted');

			this.requestUsers();
			if (this.company) {
				this.requestGroups();
				this.requestInvitations();
			}
		},

		onCreateGroup() {
			this.$router.push({ path: '/company/group/create' });
		},

		onInviteUser() {
			if (this.isDeviceGreaterSM) {
				this.$store.commit('OPEN_DIALOG', {
					title: this.$t('INVITE_USER'),
					loadComponent: 'Form/FormUserInvite',
					width: '50%',
					refreshAfterClose: true
				});
			} else {
				this.$store.commit('OPEN_MODAL', {
					title: this.$t('INVITE_USER'),
					loadComponent: 'Form/FormUserInvite',
					maximized: true,
					refreshAfterClose: true
				});
			}
		},

		requestGroups() {
			this.loadingGroups = true;
			this.$store.dispatch('companies/GET_GROUPS', { id: this.company.id }).then(response => {
				console.log('request groups', response);
				this.loadingGroups = false;
			});
		},

		requestInvitations() {
			this.$store.dispatch('companies/GET_INVITATIONS', { id: this.company.id }).then(response => {
				console.log('request invitations', response);
			});
		},

		requestUsers() {
			this.loadingUsers = true;
			this.$store.dispatch('users/GET_USERS').then(response => {
				console.log('request users2', response);
				this.loadingUsers = false;
			});
		}
	}
};
</script>

<style lang="scss">
</style>
