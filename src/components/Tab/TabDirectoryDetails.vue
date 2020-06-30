<!--
@component:         TabDirectoryDetails
@description:
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-08-23
@modified:          2018-10-11
-->
<template>
    <div v-loading="loading">
        <h3 class="headline" v-if="!isDeviceGreaterSM">{{data.object.name}}</h3>

        <el-tabs v-model="activeName" @tab-click="handleClick">
            <el-tab-pane :label="$t('DETAILS')" name="1">
                <TableDirectoryDetails :data="data" class="m-t-1"></TableDirectoryDetails>
            </el-tab-pane>
            <!-- <el-tab-pane :label="$t('PERMISSIONS')" name="2">
                <DirectoryPermissions :data="data" class="m-t-1"></DirectoryPermissions>
            </el-tab-pane> -->
            <el-tab-pane :label="$t('EXECUTED_CHECKS')" name="3">
                <ExecutedAudits v-if="data.objectType === 'checklist'"
                    :data="data.object.id"
                    :type="data.objectType">
                </ExecutedAudits>
                <ExecutedAudits v-if="data.objectType === 'directory'"
                    :data="data.object.id"
                    :type="data.objectType">
                </ExecutedAudits>
            </el-tab-pane>
        </el-tabs>
    </div>
</template>

<script>
import DirectoryPermissions from '@/components/DirectoryPermissions';
import ExecutedAudits from '@/components/ExecutedAudits';
import TableDirectoryDetails from '@/components/Table/TableDirectoryDetails';

export default {
	name: 'TabDirectoryDetails',

	components: {
		DirectoryPermissions,
		ExecutedAudits,
		TableDirectoryDetails
	},

	props: {
		data: {
			type: Object,
			required: true
		}
	},

	computed: {
		company() {
			return this.$store.state.user.company;
		},

		isDeviceGreaterSM() {
			return this.$store.getters.IS_DEVICE_GREATER_SM;
		},

		user() {
			return this.$store.state.user.data;
		},

		users() {
			return this.$store.state.users.users;
		}
	},

	data() {
		return {
			activeName: '1',
			loading: false
		};
	},

	mounted() {
		this.init();
	},

	methods: {
		handleClick(tab, event) {
			console.log(tab, event, this.activeName);
		},

		init() {
			if (!this.users.length) {
				this.loading = true;
				this.$store
					.dispatch('users/GET_USERS')
					.then(response => {
						this.loading = false;
					})
					.catch(err => {
						this.loading = false;
					});
			}

			console.log('data', this.data);
		}
	}
};
</script>
