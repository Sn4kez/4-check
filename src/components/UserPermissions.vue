<template>
    <div class="user-permissions">
        <el-row :gutter="60">
            <el-col :xs="24" :md="12" class="b-r--md">
                <p>{{$t('READ_PERMISSIONS_FOR_THE_FOLLOWING_CHECKLISTS_AND_FOLDERS')}}:</p>

                <ListPermission :grants="readPermissions" @refresh="onRefresh"></ListPermission>

                <q-btn
                    color="primary"
                    :label="$t('EDIT_READ_PERMISSIONS')"
                    @click="onBtnClickPermission"
                    class="m-t-2" />
            </el-col>

            <el-col :xs="24" :md="12" class="m-t-3--sm">
                <p>{{$t('WRITE_PERMISSIONS_FOR_THE_FOLLOWING_CHECKLISTS_AND_FOLDERS')}}:</p>

                <ListPermission :grants="writePermissions" @refresh="onRefresh"></ListPermission>

                <q-btn
                    color="primary"
                    :label="$t('EDIT_WRITE_PERMISSIONS')"
                    @click="onBtnClickPermission('write')"
                    class="m-t-2" />
            </el-col>
        </el-row>
    </div>
</template>

<script>
import ListPermission from '@/components/List/ListPermission';

export default {
	name: 'UserPermissions',

	components: {
		ListPermission
	},

	props: {
		grants: {
			type: Array,
			required: true
		},

		user: {
			type: Object,
			required: true
		}
	},

	computed: {
		isDeviceGreaterSM() {
			return this.$store.getters.IS_DEVICE_GREATER_SM;
		},

		readPermissions() {
			return this.grants.filter(grant => {
				return !grant.update;
			});
		},

		writePermissions() {
			return this.grants.filter(grant => {
				return grant.update;
			});
		}
	},

	mounted() {
		this.init();
	},

	methods: {
		init() {
			console.log('UserPermissions', this.grants);
		},

		onBtnClickPermission(accessType = 'read') {
			if (this.isDeviceGreaterSM) {
				this.$store.commit('OPEN_DIALOG', {
					title: this.$t('CHOSE_DIRECTORY_OR_CHECKLIST'),
					loadComponent: 'Form/FormCreateAccessGrant',
					width: '50%',
					data: { subjectId: this.user.id, type: accessType },
					refreshAfterClose: true
				});
			} else {
				this.$store.commit('OPEN_MODAL', {
					title: this.$t('CHOSE_DIRECTORY_OR_CHECKLIST'),
					loadComponent: 'Form/FormCreateAccessGrant',
					maximized: true,
					data: { subjectId: this.user.id, type: accessType },
					refreshAfterClose: true
				});
			}
		},

		onRefresh() {
			this.$emit('refresh');
		}
	}
};
</script>

<style lang="scss">
.user-permissions {
	.list__item {
		display: flex;
		justify-content: space-between;
		align-items: center;
	}
}
</style>
