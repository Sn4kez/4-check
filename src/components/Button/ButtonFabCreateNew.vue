<template>
    <!-- <q-layout> -->
        <q-page-sticky
            position="bottom-right"
            :offset="offset"
            >
            <q-btn
                round
                color="primary"
                class="fixed"
                icon="add"
                @click="onClickBtn">
            </q-btn>
        </q-page-sticky>
    <!-- </q-layout> -->
</template>

<script>
export default {
	name: 'ButtonFabCreateNew',

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
		onClickBtn() {
			const actionSheet = this.$q
				.actionSheet({
					title: this.$t('NEW'),
					dismissLabel: this.$t('CANCEL'),
					grid: false,
					actions: [
						{
							id: 1,
							label: this.$t('CHECKLIST'),
							icon: 'check_circle_outline',
							loadComponent: 'Form/FormEditChecklist',
							handler() {
								console.log('create checklist');
							}
						},
						{
							id: 2,
							label: this.$t('TASK'),
							icon: 'format_list_bulleted',
							loadComponent: 'Form/FormEditTask',
							handler() {
								console.log('create task');
							}
						},
						{
							id: 3,
							label: this.$t('LOCATION'),
							icon: 'place',
							loadComponent: 'Form/FormEditLocation',
							handler() {
								console.log('create location', this);
							}
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
			let data = {};
			if (item.id === 1) {
				data = {
					parentId: this.company.directoryId
				};
			}

			this.$store.commit('OPEN_MODAL', {
				title: item.label,
				loadComponent: item.loadComponent,
				maximized: true,
				data: data
			});
		}
	}
};
</script>
