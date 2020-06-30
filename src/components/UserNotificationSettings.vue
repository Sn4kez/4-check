<!--
@component:         UserNotificationSettings
@environment:       Hyprid
@description:       This component handle the visibility for mobile and desktop devices
                    as well as the data storage for child components.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-07-15
@modified:          2018-09-02
-->
<template>
    <div v-loading="loading">
        <p class="color-gray">{{$t('NOTIFICATION_SETTINGS')}}</p>

        <!-- ############ Desktop ############ -->
        <div class="hide-sm d-block--md">
            <el-tabs v-model="selectedTab" :stretch="false">
                <el-tab-pane :label="$t('EMAIL')" :name="$t('EMAIL')">
                    <FormUserNotifications :data="mailNotifications" v-if="!loading"></FormUserNotifications>
                </el-tab-pane>
                <!-- <el-tab-pane :label="$t('PUSH_NOTIFICATION')" :name="$t('PUSH_NOTIFICATION')">
                    <FormUserNotifications type="push" :data="pushNotifications"></FormUserNotifications>
                </el-tab-pane> -->
            </el-tabs>
        </div>

        <!-- ############ Mobile ############ -->
        <div class="hide-md d-block--sm">
            <q-tabs v-model="selectedTab" align="left" inverted no-pane-border class="q-tabs--brand-ios">
                <!-- Tabs - notice slot="title" -->
                <q-tab slot="title" :name="$t('EMAIL')">{{ $t('EMAIL') }}</q-tab>
                <!-- <q-tab slot="title" :name="$t('PUSH_NOTIFICATION')">{{ $t('PUSH') }}</q-tab> -->

                <!-- Targets -->
                <q-tab-pane :name="$t('EMAIL')" class="p-t-0">
                    <FormUserNotifications :data="mailNotifications" v-if="!loading"></FormUserNotifications>
                </q-tab-pane>
                <!-- <q-tab-pane :name="$t('PUSH_NOTIFICATION')" class="p-t-0"> -->
                    <!-- <FormUserNotifications type="push" :data="pushNotifications"></FormUserNotifications> -->
                <!-- </q-tab-pane> -->
            </q-tabs>
        </div>

    </div>
</template>

<script>
import FormUserNotifications from '@/components/Form/FormUserNotifications';

export default {
	name: 'UserNotificationSettings',

	components: {
		FormUserNotifications
	},

	props: {
		user: {
			type: Object,
			required: true
		}
	},

	computed: {},

	data() {
		return {
			loading: false,
			notificationPreferences: [],
			pushNotifications: {},
			mailNotifications: {},
			selectedTab: this.$t('EMAIL'),
			searchStrings: ['Mail', 'Notification']
		};
	},

	mounted() {
		this.init();
	},

	methods: {
		/**
		 * Separate notification preferences into individual arrays for each tabs
		 *
		 * @returns {void}
		 */
		generateNotificationPreferencesArrays() {
			_.forEach(this.notificationPreferences, (value, key) => {
				if (key.includes('Mail')) {
					this.mailNotifications[key] = value;
					this.mailNotifications.id = this.notificationPreferences.id;
					this.mailNotifications.userId = this.notificationPreferences.userId;
				}
				if (key.includes('Notification')) {
					this.pushNotifications[key] = value;
					this.pushNotifications.id = this.notificationPreferences.id;
					this.pushNotifications.userId = this.notificationPreferences.userId;
				}
			});
		},

		init() {
			console.log('UserNotificationSettings', this.user);
		},

		requestUserNotificationPreferences() {
			this.loading = true;
			this.$store
				.dispatch('users/GET_USER_NOTIFICATION_PREFERENCES', this.user)
				.then(response => {
					this.loading = false;
					this.notificationPreferences = response.data.data;
					this.generateNotificationPreferencesArrays();
				})
				.catch(err => {
					this.loading = false;
				});
		}
	},

	watch: {
		user(newValue, oldValue) {
			this.requestUserNotificationPreferences();
		}
	}
};
</script>

<style lang="scss">
.el-tabs__header {
	margin-bottom: 0;
}
</style>
