<template>
    <el-dropdown trigger="click" @visible-change="handleVisibleChange" @command="handleCommand">
        <q-btn
            round
            :no-ripple="type === 'primary'"
            :flat="type === 'flat'"
            :color="type === 'primary' ? 'primary' : ''"
            :class="{'is-closed': !isOpen}">
            <q-icon name="close" ></q-icon>
        </q-btn>

        <el-dropdown-menu slot="dropdown">
            <el-dropdown-item v-for="item in items" :key="item.id"
                :command="item">
                <q-icon :name="item.icon" class="m-r-half"></q-icon> {{item.name}}
            </el-dropdown-item>
        </el-dropdown-menu>
    </el-dropdown>
</template>

<script>
export default {
	name: 'ButtonCreate',

	props: {
		loadComponent: {
			type: String,
			required: true
		},

		type: {
			type: String,
			required: false,
			default: 'primary'
		}
	},

	computed: {
		company() {
			return this.$store.state.user.company;
		}
	},

	data() {
		return {
			isOpen: false,
			items: [
				{
					id: 1,
					name: this.$t('CHECKLIST'),
					icon: 'check_circle_outline',
					loadComponent: 'Form/FormEditChecklist'
				},
				{
					id: 2,
					name: this.$t('TASK'),
					icon: 'format_list_bulleted',
					loadComponent: 'Form/FormEditTask'
				},
				{
					id: 3,
					name: this.$t('LOCATION'),
					icon: 'place',
					loadComponent: 'Form/FormEditLocation'
				}
			]
		};
	},

	methods: {
		handleCommand(command) {
			let data = {};
			if (command.id === 1) {
				data = {
					parentId: this.company.directoryId
				};
			}

			this.$store.commit('OPEN_DIALOG', {
				title: command.name,
				loadComponent: command.loadComponent,
				width: '70%',
				data: data
			});
		},

		handleVisibleChange(isOpen) {
			this.isOpen = isOpen;
		}
	}
};
</script>

<style lang="scss">
.el-dropdown {
	.q-btn {
		transition: transform 0.3s ease-in-out;

		&.is-closed {
			transform: rotate(-45deg);
		}
	}
}
</style>
