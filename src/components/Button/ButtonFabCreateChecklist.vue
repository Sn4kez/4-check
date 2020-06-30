<template>
    <q-page-sticky
        position="bottom-right"
        :offset="offset">
        <q-btn
            round
            no-ripple
            color="primary"
            class="fixed"
            icon="add"
            @click="onClickBtn">
        </q-btn>
    </q-page-sticky>
</template>

<script>
import checklistsMixin from '@/shared/mixins/checklists';
import directoriesMixin from '@/shared/mixins/directories';

export default {
	name: 'ButtonFabCreateChecklist',

	mixins: [checklistsMixin, directoriesMixin],

	props: {
		offset: {
			type: Array,
			required: false,
			default() {
				return [70, 70];
			}
		}
	},

	computed: {
		company() {
			return this.$store.state.user.company;
		}
	},

	methods: {
		promptNewChecklist() {
			if (this.isDeviceGreaterSM) {
				this.$store.commit('OPEN_DIALOG', {
					title: this.$t('CREATE_CHECKLIST'),
					loadComponent: 'Form/FormEditChecklist',
					width: '50%',
					data: {
						parentId: this.company.directoryId
					}
				});
			} else {
				this.$store.commit('OPEN_MODAL', {
					title: this.$t('CREATE_CHECKLIST'),
					loadComponent: 'Form/FormEditChecklist',
					maximized: true,
					data: {
						parentId: this.company.directoryId
					}
				});
			}
		},

		promptNewDirectory() {
			if (this.isDeviceGreaterSM) {
				this.$store.commit('OPEN_DIALOG', {
					title: this.$t('CREATE_DIRECTORY'),
					loadComponent: 'Form/FormEditDirectory',
					width: '50%',
					data: {}
				});
			} else {
				this.$store.commit('OPEN_MODAL', {
					title: this.$t('CREATE_DIRECTORY'),
					loadComponent: 'Form/FormEditDirectory',
					maximized: true,
					data: {}
				});
			}
		},

		onClickBtn() {
			console.log('onClickBtn location new');

			const actionSheet = this.$q
				.actionSheet({
					title: this.$t('NEW'),
					dismissLabel: this.$t('CANCEL'),
					grid: true,
					actions: [
						{
							label: this.$t('CREATE_CHECKLIST'),
							handler: this.promptNewChecklist,
							icon: 'check_box',
							color: 'primary'
						},
						{
							label: this.$t('CREATE_DIRECTORY'),
							handler: this.promptNewDirectory,
							icon: 'folder_open',
							color: 'primary'
						}
					]
				})
				.then(result => {
					this.onClickActionSheetItem(result);
				})
				.catch(err => {
					console.log('cancel action sheet');
				});
		},

		onClickActionSheetItem(item) {
			console.log(item);
		}
	}
};
</script>
