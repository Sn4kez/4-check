<template>
    <q-btn
        :flat="flat"
        :outline="outline"
        color="primary"
        icon="add_circle_outline"
        :label="$t('ADD_ELEMENT')"
        @click="onClickAddElement">
    </q-btn>
</template>

<script>
import { getElements } from '@/shared/checklistElements';

export default {
	name: 'ButtonAddChecklistElement',

	props: {
		context: {
			type: Object,
			required: true
		},

		flat: {
			type: Boolean,
			required: false,
			default: false
		},

		outline: {
			type: Boolean,
			required: false,
			default: true
		}
	},

	computed: {
		isDeviceGreaterSM() {
			return this.$store.getters.IS_DEVICE_GREATER_SM;
		}
	},

	data() {
		return {
			elements: getElements()
		};
	},

	mounted() {
		this.init();
	},

	methods: {
		init() {
			if (this.context.type === 'sections') {
				this.elements = {
					basic: this.elements.basic.slice(1),
					extensions: this.elements.extensions
				};
			}

			if (this.context.type === 'checkpoints') {
				this.elements = {
					basic: [],
					extensions: this.elements.extensions
				};
			}
		},

		onClickAddElement() {
			console.log('onClickAddElement');

			if (this.isDeviceGreaterSM) {
				this.$store.commit('OPEN_DIALOG', {
					title: this.$t('ADD_ELEMENT'),
					loadComponent: 'List/ListChecklistElements',
					width: '50%',
					data: {
						context: this.context,
						elements: this.elements
					}
				});
			} else {
				this.$store.commit('OPEN_MODAL', {
					title: this.$t('ADD_ELEMENT'),
					loadComponent: 'List/ListChecklistElements',
					maximized: false,
					position: 'bottom',
					data: {
						context: this.context,
						elements: this.elements
					}
				});
			}
		}
	}
};
</script>
