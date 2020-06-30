<!--
@component:         ElementExtension
@environment:       Hyprid
@description:       -
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-09-12
@modified:          2018-10-28
-->
<template>
    <div class="checklist__element-extension">
        <component
            v-if="data.type"
            :is="getComponentName(data.type)"
            :data="data"
            :open="open">
        </component>
    </div>
</template>

<script>
import checklistMixins from '@/shared/mixins/checklists';
import ElementLocation from '@/components/Element/ElementLocation';
import ElementParticipant from '@/components/Element/ElementParticipant';
import ElementPicture from '@/components/Element/ElementPicture';
import ElementTextfield from '@/components/Element/ElementTextfield';

export default {
	name: 'ElementExtension',

	mixins: [checklistMixins],

	components: {
		ElementLocation,
		ElementParticipant,
		ElementPicture,
		ElementTextfield
	},

	props: {
		data: {
			type: Object,
			required: true
		},

		open: {
			type: Boolean,
			required: false,
			default: false
		}
	},

	data() {
		return {
			loading: false
		};
	},

	mounted() {
		this.init();
	},

	methods: {
		init() {},

		getComponentName(type) {
			let result = '';

			switch (type) {
				case 'textfield':
					result = 'ElementTextfield';
					break;
				case 'picture':
					result = 'ElementPicture';
					break;
				case 'participant':
					result = 'ElementParticipant';
					break;
				case 'location':
					result = 'ElementLocation';
					break;
			}

			return result;
		}
	}
};
</script>

<style lang="scss">
.checklist__element-extension {
	padding: 0.8rem;
}
</style>

