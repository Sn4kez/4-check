<!--
@component:         DirectoryPermissions
@description:       Show form to create and update directory/checklist permission for user and group.
                    List all permissions for current directory/checklist
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-08-23
@modified:          2018-08-23
-->
<template>
    <div class="directory-permissions">
        <FormEditDirectoryPermission
            :data="data"
            :grants="grants"
            @refresh="requestDirectoryGrants(data.object.id)">
        </FormEditDirectoryPermission>

        <h3 class="headline m-t-2">{{ $t('OVERVIEW') }}</h3>

        <TableDirectoryPermissions
            :data="grants"
            @refresh="requestDirectoryGrants(data.object.id)"
            class="m-t-1">
        </TableDirectoryPermissions>
    </div>
</template>

<script>
import FormEditDirectoryPermission from '@/components/Form/FormEditDirectoryPermission';
import TableDirectoryPermissions from '@/components/Table/TableDirectoryPermissions';

export default {
	name: 'DirectoryPermissions',

	components: {
		FormEditDirectoryPermission,
		TableDirectoryPermissions
	},

	props: {
		data: {
			type: Object,
			required: false
		}
	},

	computed: {},

	data() {
		return {
			grants: []
		};
	},

	mounted() {
		this.init();
	},

	methods: {
		init() {
			console.log('DirectoryPermissions mounted');

			this.requestDirectoryGrants(this.data.object.id);
		},

		requestDirectoryGrants(id) {
			this.$store.dispatch('directories/GET_DIRECTORY_GRANTS', { id: id }).then(response => {
				this.grants = response.data.data;
			});
		}
	}
};
</script>

<style lang="scss">
</style>
