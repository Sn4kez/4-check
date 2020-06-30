<template>
    <div class="group-permissions">
        <div v-if="!groupId">
            <el-alert
                :title="$t('HINT')"
                description="Bitte Speichern Sie zuerst eine Gruppe, bevor Sie Gruppenmitglieder hinzufügen."
                type="warning"
                show-icon
                :closable="false">
            </el-alert>
        </div>

        <el-row :gutter="60" v-if="groupId">
            <el-col :xs="24" :md="12" class="b-r--md">
                <p>{{$t('READ_PERMISSIONS_FOR_THE_FOLLOWING_CHECKLISTS_AND_FOLDERS')}}:</p>

                <ListPermission :grants="readPermissions" @refresh="onRefresh"></ListPermission>

                <q-btn
                    color="primary"
                    :label="$t('EDIT_READ_PERMISSIONS')"
                    @click="onBtnClickPermission('read')"
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
	name: 'CompanyGroupPermissions',

	components: {
		ListPermission
	},

	props: {
		grants: {
			type: Array,
			required: true
		},

		groupId: {
			type: String,
			required: false
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

	methods: {
		onBtnClickPermission(accessType) {
			if (this.isDeviceGreaterSM) {
				this.$store.commit('OPEN_DIALOG', {
					title: this.$t('CHOSE_DIRECTORY_OR_CHECKLIST'),
					loadComponent: 'Form/FormCreateAccessGrant',
					width: '50%',
					refreshAfterClose: true,
					data: { subjectId: this.groupId, type: accessType }
				});
			} else {
				this.$store.commit('OPEN_MODAL', {
					title: this.$t('CHOSE_DIRECTORY_OR_CHECKLIST'),
					loadComponent: 'Form/FormCreateAccessGrant',
					maximized: true,
					refreshAfterClose: true,
					data: { subjectId: this.groupId, type: accessType }
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
.group-permissions {
	.list__item {
		display: flex;
		justify-content: space-between;
		align-items: center;
	}
}
</style>
