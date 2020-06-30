<template>

    <el-dropdown trigger="click">
        <q-btn
            :color="type"
            :label="$t('CREATE')"
            class="w-100--sm"
            no-ripple />

        <el-dropdown-menu slot="dropdown">
            <el-dropdown-item v-for="item in items" :key="item.id">
                <a @click.prevent="onClick(item)" href="#" class="d-block">
                    <q-icon :name="item.icon" class="m-r-half"></q-icon> {{item.name}}
                </a>
            </el-dropdown-item>
        </el-dropdown-menu>
    </el-dropdown>

</template>

<script>
import locationsMixin from '@/shared/mixins/locations';

export default {
	name: 'ButtonCreateLocation',

	mixins: [locationsMixin],

	props: {
		type: {
			type: String,
			required: false,
			default: 'primary'
		}
	},

	data() {
		return {
			items: [
				{
					id: 1,
					name: this.$t('CREATE_LOCATION'),
					icon: 'place',
					handler: this.createLocation
				},
				{
					id: 2,
					name: this.$t('CREATE_LOCATION_TYPE'),
					icon: 'format_list_bulleted',
					handler: this.promptLocationType
				}
			]
		};
	},

	mounted() {
		this.init();
	},

	methods: {
		createLocation() {
			if (this.isDeviceGreaterSM) {
				this.$store.commit('OPEN_DIALOG', {
					title: this.$t('CREATE_LOCATION'),
					loadComponent: 'Form/FormEditLocation',
					width: '50%'
				});
			} else {
				this.$store.commit('OPEN_MODAL', {
					title: this.$t('CREATE_LOCATION'),
					loadComponent: 'Form/FormEditLocation',
					maximized: true
				});
			}
		},

		init() {
			console.log('ButtonCreateLocation mounted!');
		},

		onClick(item) {
			item.handler();

			console.log('onClick button', item);
		}
	}
};
</script>
