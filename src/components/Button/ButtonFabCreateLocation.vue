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
import locationsMixin from '@/shared/mixins/locations';

export default {
	name: 'ButtonFabCreateLocation',

	mixins: [locationsMixin],

	props: {
		offset: {
			type: Array,
			required: false,
			default() {
				return [70, 70];
			}
		}
	},

	methods: {
		createLocation() {
			this.$store.commit('OPEN_MODAL', {
				title: this.$t('CREATE_LOCATION'),
				loadComponent: 'Form/FormEditLocation',
				maximized: true
			});
		},

		onClickBtn() {
			console.log('onClickBtn location new');

			const actionSheet = this.$q
				.actionSheet({
					title: this.$t('NEW'),
					dismissLabel: this.$t('CANCEL'),
					grid: false,
					actions: [
						{
							label: this.$t('CREATE_LOCATION'),
							handler: this.createLocation,
							icon: 'place'
						},
						{
							label: this.$t('CREATE_LOCATION_TYPE'),
							handler: this.promptLocationType
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
