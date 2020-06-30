<template>
    <div>
        <q-btn
            color="primary"
            :label="$t('CREATE_CHECKLIST')"
            @click.prevent="promptNewChecklist"
            class="w-100--sm m-l-1"
            no-ripple
            type="a" />
        <q-btn
            color="primary"
            :label="$t('CREATE_DIRECTORY')"
            @click.prevent="promptNewDirectory"
            class="w-100--sm m-l-1"
            no-ripple
            type="a" />
    </div>
</template>

<script>
import checklistsMixin from '@/shared/mixins/checklists';
import directoriesMixin from '@/shared/mixins/directories';

export default {
	name: 'ButtonGroupCreateChecklist',

	mixins: [checklistsMixin, directoriesMixin],

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
		promptNewChecklist() {
			if (this.isDeviceGreaterSM) {
				this.$store.commit('OPEN_DIALOG', {
					title: this.$t('CREATE_CHECKLIST'),
					loadComponent: 'Form/FormEditChecklist',
					width: '50%',
					data: {
						parentId: this.parentId
					}
				});
			} else {
				this.$store.commit('OPEN_MODAL', {
					title: this.$t('CREATE_CHECKLIST'),
					loadComponent: 'Form/FormEditChecklist',
					maximized: true,
					data: {
						parentId: this.parentId
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

		init() {
			console.log('ButtonCreateLocation mounted!');
		}
	}
};
</script>
