<!--
@component:         EditGroupView
@environment:       Hyprid
@description:       This component handle the visibility for mobile and desktop devices
                    as well as the data storage for child components.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-07-15
@modified:          2018-10-14
-->
<template>
    <div class="edit-group-view main-container">

        <!-- ++++++++++++++ DESKTOP ++++++++++++++ -->
        <div v-if="isDeviceGreaterSM">
            <!-- General settings -->
            <el-row :gutter="20">
                <el-col :xs="24">
                    <h3 class="headline">{{$t('GENERAL_SETTINGS')}}</h3>
                </el-col>
            </el-row>
            <el-row :gutter="20">
                <el-col :xs="24" :md="24">
                    <el-card>
                        <FormEditGroup
                            :data="group"
                            :edit="edit"
                            :company="company"
                            @group-created="onCreateGroup">
                        </FormEditGroup>
                    </el-card>
                </el-col>
            </el-row>

            <!-- Members -->
            <el-row :gutter="20" class="m-t-4">
                <el-col :xs="24">
                    <h3 class="headline">{{$t('MEMBERS')}}</h3>
                </el-col>
            </el-row>
            <el-row :gutter="20">
                <el-col :xs="24" :md="24">
                    <el-card>
                        <GroupMembers :group-id="groupId"></GroupMembers>
                    </el-card>
                </el-col>
            </el-row>

            <!-- Permissions -->
            <el-row :gutter="20" class="m-t-4">
                <el-col :xs="24">
                    <h3 class="headline">{{$t('PERMISSIONS')}}</h3>
                </el-col>
            </el-row>
            <el-row :gutter="20">
                <el-col :xs="24" :md="24">
                    <el-card>
                        <CompanyGroupPermissions
                            :grants="groupAccessGrants"
                            :group-id="groupId"
                            @refresh="requestGroupAccessGrants(groupId)">
                        </CompanyGroupPermissions>
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
import { required } from 'vuelidate/lib/validators';
import CompanyGroupPermissions from '@/components/CompanyGroupPermissions';
import FormEditGroup from '@/components/Form/FormEditGroup';
import GroupMembers from '@/components/GroupMembers';
import ListNavItems from '@/components/List/ListNavItems';
import ListPermission from '@/components/List/ListPermission';

export default {
	name: 'EditGroupView',

	components: {
		CompanyGroupPermissions,
		FormEditGroup,
		GroupMembers,
		ListNavItems,
		ListPermission
	},

	computed: {
		company() {
			return this.$store.state.user.company;
		},

		groupId() {
			return this.$route.params.id || null;
		},

		isDeviceGreaterSM() {
			return this.$store.getters.IS_DEVICE_GREATER_SM;
		}
	},

	data() {
		return {
			activeCollapseItems: [],
			edit: false,
			group: {},
			groupAccessGrants: [],
			loading: false,
			navItems: [
				{
					name: this.$t('GENERAL_SETTINGS'),
					role: 'admin',
					component: {
						name: 'Form/FormEditGroup',
						props: [
							{
								name: 'data',
								data: {}
							},
							{
								name: 'company',
								data: {}
							},
							{
								name: 'edit',
								data: {}
							}
						]
					}
				},
				{
					name: this.$t('MEMBERS'),
					role: 'admin',
					component: {
						name: 'GroupMembers',
						props: [
							{
								name: 'groupId',
								data: ''
							}
						]
					}
				},
				{
					name: this.$t('PERMISSIONS'),
					role: 'admin',
					component: {
						name: 'CompanyGroupPermissions',
						props: [
							{
								name: 'groupId',
								data: ''
							},
							{
								name: 'grants',
								data: []
							}
						]
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
			// General settings
			this.navItems[0].component.props[0].data = this.group;
			this.navItems[0].component.props[1].data = this.company;
			this.navItems[0].component.props[2].data = this.edit;
			// Members
			this.navItems[1].component.props[0].data = this.group.id;
			// Permissions
			this.navItems[2].component.props[0].data = this.group.id;
			this.navItems[2].component.props[1].data = this.groupAccessGrants;
		},

		init() {
			if (this.$route.params.id) {
				this.edit = true;
				const groupId = this.$route.params.id;

				this.requestGroup(groupId);
				this.requestGroupAccessGrants(groupId);
				this.registerEvents();
			} else {
				this.handleNavItems();
			}
		},

		onCreateGroup(payload) {
			console.log('Group has been created.... here is parent', payload);

			this.$router.push({ path: '/company/group/edit/' + payload.id });
		},

		registerEvents() {
			this.$eventbus.$on('dialog:closed', () => {
				this.requestGroupAccessGrants(this.groupId);
			});
			this.$eventbus.$on('modal:closed', () => {
				this.requestGroupAccessGrants(this.groupId);
			});
		},

		requestGroup(id) {
			return this.$store
				.dispatch('groups/GET_GROUP', { id: id })
				.then(response => {
					this.group = response.data.data;
					this.handleNavItems();
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		/**
		 * Request group access grants by id
		 *
		 * @param int group id
		 * @returns Promise
		 */
		requestGroupAccessGrants(id) {
			return this.$store
				.dispatch('groups/GET_GROUP_ACCESS_GRANTS', { id: id })
				.then(response => {
					this.groupAccessGrants = response.data.data;
					return response;
				})
				.catch(err => {
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
			if(to.path.indexOf('edit') != -1 && from.path.indexOf('create') != -1){
				location.reload();
			}
		}
	}
};
</script>

<style lang="scss">
</style>
