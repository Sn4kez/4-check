<!--
@component:         TabNotifications
@environment:       Hyprid
@description:       This component handle notifications and display them inside tabs.
                    Until we have more kategories only a simple table is rendered.

@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-07-23
@modified:          2018-10-06
-->
<template>
    <div v-loading="loading">
        <TableNotifications :data="notifications" variant="list"></TableNotifications>
    </div>

    <!-- <el-tabs stretch v-model="activeName" @tab-click="handleTabClick">
        <el-tab-pane label="System" name="1">
            <TableNotifications :data="notifications" variant="list"></TableNotifications>
        </el-tab-pane>
        <el-tab-pane label="Account" name="2">
            <TableNotifications :data="notifications" variant="list"></TableNotifications>
        </el-tab-pane>
    </el-tabs> -->
</template>

<script>
import TableNotifications from '@/components/Table/TableNotifications';

export default {
	name: 'TabNotifications',

	components: {
		TableNotifications
	},

	props: {
		reload: {
			type: Boolean,
			required: false,
			default: false
		}
	},

	computed: {
		notifications() {
			return this.$store.state.notifications.notifications;
		}
	},

	data() {
		return {
			activeName: '1',
			loading: false
		};
	},

	methods: {
		handleTabClick(value) {
			console.log('handleTabClick', value);
		},

		requestNotifications() {
			this.loading = true;

			return this.$store
				.dispatch('notifications/GET_NOTIFICATIONS')
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
		reload(newValue, oldValue) {
			if (newValue) {
				this.requestNotifications();
			}
		}
	}
};
</script>

