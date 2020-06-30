<!--
@component:         FormUserNotifications
@environment:       Hyprid
@description:       This component handle the preferences for email and push notifications.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-08-18
@modified:          2018-10-13

@todo:              Push notifications is not implemented.
-->
<template>
    <div>
        <q-list link no-border>
            <!-- Tasks -->
            <q-list-header class="p-l-half p-r-half">{{ $t('TASKS') }}</q-list-header>
            <q-item tag="label" v-for="item in tasks" :key="item.id" class="p-l-half p-r-half">
                <q-item-main>
                    <q-item-tile label>{{ item.desc }}</q-item-tile>
                </q-item-main>
                <q-item-side right>
                    <q-toggle v-model="item.checked" :true-value="1" :false-value="0" @input="onChangeToggle(item)" />
                </q-item-side>
            </q-item>

             <q-item-separator />

            <!-- Checklists -->
            <q-list-header class="p-l-half p-r-half">{{ $t('CHECKLISTS') }}</q-list-header>
            <q-item tag="label" v-for="item in checklists" :key="item.id" class="p-l-half p-r-half">
                <q-item-main>
                    <q-item-tile label>{{ item.desc }}</q-item-tile>
                </q-item-main>
                <q-item-side right>
                    <q-toggle v-model="item.checked" :true-value="1" :false-value="0" @input="onChangeToggle(item)" />
                </q-item-side>
            </q-item>
        </q-list>
    </div>
</template>

<script>
import { QToggle } from 'quasar';
import commonMixins from '@/shared/mixins/common';

export default {
	name: 'FormUserNotifications',

	mixins: [commonMixins],

	props: {
		data: {
			type: Object,
			required: true
		},

		type: {
			type: String,
			required: false,
			default: 'email'
		}
	},

	components: {
		QToggle
	},

	computed: {
		user() {
			return this.$store.state.user.data;
		}
	},

	data() {
		return {
			checklists: [
				{
					id: 10,
					desc: this.$t('NOTIFICATION_CHECKLIST_DUE_ASSIGNED_TO_ME'),
					checked: false,
					property: 'checklistDueMail'
				}/*,
				{
					id: 11,
					desc: this.$t('NOTIFICATION_CHECKLIST_ESKALATION'),
					checked: false,
					property: 'checklistNeedsActivityMail'
				},
				{
					id: 12,
					desc: this.$t('NOTIFICATION_CHECKLIST_CUSTOM_RATING'),
					checked: false,
					property: 'checklistCriticalRatingMail'
				}*/
			],
			flags: {},
			loading: false,
			tasks: [
				{
					id: 1,
					desc: this.$t('NOTIFICATION_TASK_ASSIGNED_TO_ME'),
					checked: false,
					property: 'taskAssignedMail'
				},
				{
					id: 2,
					desc: this.$t('NOTIFICATION_TASK_MY_COMPLETED'),
					checked: false,
					property: 'taskCompletedMail'
				},
				{
					id: 3,
					desc: this.$t('NOTIFICATION_TASK_DUE'),
					checked: false,
					property: 'taskOverdueMail'
				},
				{
					id: 4,
					desc: this.$t('NOTIFICATION_TASK_MY_DELETED'),
					checked: false,
					property: 'taskDeletedMail'
				},
				{
					id: 5,
					desc: this.$t('NOTIFICATION_TASK_MY_CHANGED'),
					checked: false,
					property: 'taskUpdatedMail'
				}
			]
		};
	},

	mounted() {
		this.init();
	},

	methods: {
		doSubmit() {
			this.loading = true;

			this.$store
				.dispatch('users/UPDATE_USER_NOTIFICATION_PREFERENCES', this.flags)
				.then(response => {
					this.loading = false;

					if (response.status === 204) {
						this.$q.notify({
							message: this.$t('SAVE_SUCCESS'),
							type: 'positive'
						});
					} else {
						this.handleErrors(response);
					}
				})
				.catch(err => {
					this.loading = false;
				});
		},

		init() {
			this.flags = this.data;
			this.flags.checklistCriticalRatingMail = this.flags.checklistCriticalRatingMail || 0;

			if (this.type === 'email') {
				this.checklists[0].checked = this.data.checklistDueMail;
				// this.checklists[1].checked = this.data.checklistNeedsActivityMail;
				// this.checklists[2].checked = this.data.checklistCriticalRatingMail || 0;
				this.tasks[0].checked = this.data.taskAssignedMail;
				this.tasks[1].checked = this.data.taskCompletedMail;
				this.tasks[2].checked = this.data.taskOverdueMail;
				this.tasks[3].checked = this.data.taskDeletedMail;
				this.tasks[4].checked = this.data.taskUpdatedMail;
			}

			if (this.type === 'push') {
				this.checklists[0].checked = this.data.checklistDueNotification;
				this.checklists[0].property = 'checklistDueNotification';

				// this.checklists[1].checked = this.data.checklistNeedsActivityNotification;
				// this.checklists[1].property = 'checklistNeedsActivityNotification';

				// this.checklists[2].checked = this.data.checklistCriticalRatingNotification || 0;
				// this.checklists[2].property = 'checklistCriticalRatingNotification';

				this.tasks[0].checked = this.data.taskAssignedNotification;
				this.tasks[0].property = 'taskAssignedNotification';

				this.tasks[1].checked = this.data.taskCompletedNotification;
				this.tasks[1].property = 'taskCompletedNotification';

				this.tasks[2].checked = this.data.taskOverdueNotification;
				this.tasks[2].property = 'taskOverdueNotification';

				this.tasks[3].checked = this.data.taskDeletedNotification;
				this.tasks[3].property = 'taskDeletedNotification';

				this.tasks[4].checked = this.data.taskUpdatedNotification;
				this.tasks[4].property = 'taskUpdatedNotification';
			}

		},

		onChangeToggle(newValue) {
			this.flags[newValue.property] = newValue.checked;
			this.doSubmit();
		}
	},

	watch: {
		data: function(newValue, oldValue) {
			this.init();
		}
	}
};
</script>

<style lang="scss">
</style>
