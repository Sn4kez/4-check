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
export default {
	name: 'ButtonFabCreateScoringScheme',

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
		onClickBtn() {
			console.log('onClickBtn CREATE_SCORING_SCHEME new');

			this.$store.commit('OPEN_MODAL', {
				title: this.$t('CREATE_SCORING_SCHEME'),
				loadComponent: 'Form/FormEditScoringScheme',
				maximized: true
			});
		}
	}
};
</script>

<style scoped>
</style>
