<template>
    <div>
        <q-btn
            color="primary"
            :label="$t('CREATE_SCORING_SCHEME')"
            @click.prevent="onClickCreate"
            class="w-100--sm m-l-1"
            no-ripple
            type="a" />
    </div>
</template>

<script>
export default {
	name: 'ButtonCreateScoringScheme',

	props: {
		parentId: {
			type: String,
			required: false,
			default: ''
		},

		type: {
			type: String,
			required: false,
			default: 'primary'
		}
	},

	computed: {
		isDeviceGreaterSM() {
			return this.$store.getters.IS_DEVICE_GREATER_SM;
		}
	},

	data() {
		return {};
	},

	mounted() {
		this.init();
	},

	methods: {
		onClickCreate() {
			if (this.isDeviceGreaterSM) {
				this.$store.commit('OPEN_DIALOG', {
					title: this.$t('CREATE_SCORING_SCHEME'),
					loadComponent: 'Form/FormEditScoringScheme',
					width: '50%',
					data: {}
				});
			} else {
				this.$store.commit('OPEN_MODAL', {
					title: this.$t('CREATE_SCORING_SCHEME'),
					loadComponent: 'Form/FormEditScoringScheme',
					maximized: true,
					data: {}
				});
			}
		},

		init() {
			console.log('ButtonCreateScoringScheme mounted!');
		}
	}
};
</script>
