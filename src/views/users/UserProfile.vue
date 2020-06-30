<!--
@component:         UserProfile
@environment:       Hyprid
@description:       This component handle the visibility for mobile and desktop devices
                    as well as the data storage for child components.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-07-15
@modified:          2018-10-18
-->
<template>
    <div class="view__user-profile main-container">

        <!-- ++++++++++++++ DESKTOP ++++++++++++++ -->
        <div class="m-t-1" v-if="isDeviceGreaterSM">
            <!-- Personal settings -->
            <el-row :gutter="20">
                <el-col :xs="24">
                    <h3 class="headline">{{$t('PERSONAL_SETTINGS')}}</h3>
                </el-col>
            </el-row>

            <el-card>
                <el-row :gutter="20">
                    <el-col :xs="24" :md="24">
                        <FormUserProfile :data="user" :phones="userPhones" @refresh="requestUserPhones(user.id)"></FormUserProfile>
                    </el-col>
                </el-row>

                <!-- Account settings -->
                <el-row :gutter="20" class="m-t-2 m-b-1" v-if="isOwnProfile">
                    <el-col :xs="24" :md="18" :offset="6">
                        <h3 class="headline">{{$t('ACCOUNT_SETTINGS')}}</h3>
                    </el-col>
                </el-row>
                <el-row :gutter="20" v-if="isOwnProfile">
                    <el-col :xs="24" :md="18" :offset="6">
                        <FormUserAccount :data="user"></FormUserAccount>
                    </el-col>
                </el-row>
            </el-card>

            <!-- Notifications -->
            <el-row :gutter="20" class="m-t-4">
                <el-col :xs="24">
                    <h3 class="headline">{{$t('NOTIFICATIONS')}}</h3>
                </el-col>
            </el-row>
            <el-row :gutter="20">
                <el-col :xs="24" :md="24">
                    <el-card>
                        <UserNotificationSettings :user="user"></UserNotificationSettings>
                    </el-card>
                </el-col>
            </el-row>

            <!-- Permission -->
            <el-row :gutter="20" class="m-t-4"
                v-if="this.appUser.data.role !== 'user' && this.currentUser.id !== this.appUser.data.id">
                <el-col :xs="24">
                    <h3 class="headline">{{$t('PERMISSIONS')}}</h3>
                </el-col>
            </el-row>
            <el-row :gutter="20"
                v-if="this.appUser.data.role !== 'user' && this.currentUser.id !== this.appUser.data.id">
                <el-col :xs="24" :md="24">
                    <el-card>
                        <UserPermissions :grants="userAccessGrants" :user="currentUser" @refresh="requestUserAccessGrants(user.id)"></UserPermissions>
                    </el-card>
                </el-col>
            </el-row>
        </div>

        <!-- ++++++++++++++ MOBILE ++++++++++++++ -->
        <el-card v-if="!isDeviceGreaterSM"
            v-loading="loading"
            class="el-card--no-padding-sm"
            style="min-height: 4rem;">

            <ListNavItems :items="navItems" v-if="initListNavItems"></ListNavItems>

        </el-card>

    </div>
</template>

<script>
import FormUserAccount from '@/components/Form/FormUserAccount';
import FormUserProfile from '@/components/Form/FormUserProfile';
import ListNavItems from '@/components/List/ListNavItems';
import UserNotificationSettings from '@/components/UserNotificationSettings';
import UserPermissions from '@/components/UserPermissions';
import { User } from '@/shared/classes/User';
import userMixins from '@/shared/mixins/users';

export default {
	name: 'UserProfile',

	mixins: [userMixins],

	components: {
		FormUserAccount,
		FormUserProfile,
		ListNavItems,
		UserNotificationSettings,
		UserPermissions
	},

	computed: {
		appUser() {
			return this.$store.state.user;
		},

		currentUser() {
			return this.user;
		},

		isDeviceGreaterSM() {
			return this.$store.getters.IS_DEVICE_GREATER_SM;
		},

		isOwnProfile() {
			return this.appUser.data.id === this.user.id;
		},

		userPhones() {
			return this.$store.state.users.phones;
		}
	},

	data() {
		return {
			activeCollapseItems: [],
			initListNavItems: false,
			loading: false,
			navItems: [
				{
					name: this.$t('PERSONAL_SETTINGS'),
					role: 'user',
					component: {
						name: 'Form/FormUserProfile',
						props: [
							{
								name: 'data',
								data: {}
							},
							{
								name: 'phones',
								data: {}
							}
						]
					}
				},
				{
					name: this.$t('ACCOUNT_SETTINGS'),
					role: 'user',
					component: {
						name: 'Form/FormUserAccount',
						props: [
							{
								name: 'data',
								data: {}
							}
						]
					}
				},
				{
					name: this.$t('NOTIFICATIONS'),
					role: 'user',
					component: {
						name: 'UserNotificationSettings',
						props: [
							{
								name: 'user',
								data: {}
							}
						]
					}
				},
				{
					name: this.$t('PERMISSIONS'),
					role: 'admin',
					component: {
						name: 'UserPermissions',
						props: [
							{
								name: 'user',
								data: {}
							},
							{
								name: 'grants',
								data: []
							}
						]
					}
				}
			],
			user: {},
			userId: '',
			userAccessGrants: []
		};
	},

	mounted() {
		this.init();
	},

	methods: {
		init() {
			this.userId = this.getUserId();

			if (this.isDeviceGreaterSM) {
				this.activeCollapseItems = ['1', '2', '3', '4'];
			}

			// If the user call his own profile use user data from store
			// otherwise request user data for given id as parameter in the url
			if (this.userId) {
				this.requestUser(this.userId).then(response => {
					if (this.appUser.data.role === 'admin') {
						this.loading = true;
						this.requestUserAccessGrants(response.data.data.id).then(response => {
							this.loading = false;
							console.log('requestUserAccessGrants', response);
						});
					}

					this.requestUserPhones(response.data.data.id).then(result => {
						this.handleNavItems();
					});
				});
			} else {
				this.user = Object.assign({}, this.appUser.data);
				this.requestUserPhones(this.user.id).then(result => {
					this.handleNavItems();
				});

				this.requestUserAccessGrants(this.user.id).then(response => {
					this.loading = false;
					this.userAccessGrants = response.data.data;
					console.log('requestUserAccessGrants', response);
				});
			}

			this.registerEvents();
		},

		/**
		 * ToDo: Until we do not get the current user at application start,
		 * we need to extract the user id from the path
		 */
		getUserId() {
			const STRING = '/settings/user/profile/';
			const HASH = this.$route.path.split(STRING);

			if (HASH.length === 1) {
				return null;
			}

			return HASH[HASH.length - 1];
		},

		/**
		 * In many cases data necessary for ListNavItems components has not been requested yet.
		 * In this case we assign missing data to the props after there have been requested
		 */
		handleNavItems() {
			this.initListNavItems = true;
			// Personal settings
			this.navItems[0].component.props[0].data = this.currentUser;
			this.navItems[0].component.props[1].data = this.userPhones;
			// Account settings
			this.navItems[1].component.props[0].data = this.currentUser;
			// Notifications
			this.navItems[2].component.props[0].data = this.currentUser;
			// Permissions
			this.navItems[3].component.props[0].data = this.currentUser;
			this.navItems[3].component.props[1].data = this.userAccessGrants;
		},

		registerEvents() {
			this.$eventbus.$on('dialog:closed', () => {
				this.requestUserAccessGrants(this.user.id);
			});
			this.$eventbus.$on('modal:closed', () => {
				this.requestUserAccessGrants(this.user.id);
			});
		},

		/**
		 * Request user by id
		 *
		 * @param int user id
		 * @returns Promise
		 */
		requestUser(id) {
			this.loading = true;
			return this.$store.dispatch('users/GET_USER', { id: id }).then(response => {
				this.user = response.data.data;
				this.loading = false;
				return response;
			});
		},

		/**
		 * Request user access grants by id
		 *
		 * @param int user id
		 * @returns Promise
		 */
		requestUserAccessGrants(id) {
			return this.$store
				.dispatch('users/GET_USER_ACCESS_GRANTS', { id: id })
				.then(response => {
					this.userAccessGrants = response.data.data;
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		/**
		 * Request user phones
		 *
		 * @param int user id
		 * @returns Promise
		 */
		requestUserPhones(userId) {
			this.loading = true;
			return this.$store
				.dispatch('users/GET_PHONES', { id: userId })
				.then(response => {
					this.loading = false;
					return response;
				})
				.catch(err => {
					this.loading = false;
					return err;
				});
		},

		unregisterEvents() {
			this.$eventbus.$off('dialog:closed');
			this.$eventbus.$off('modal:closed');
		}
	},

	destroyed() {
		this.unregisterEvents();
	},

	watch: {
		$route(to, from) {
			this.init();
		}
	}
};
</script>
