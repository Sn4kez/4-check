<!--
@component:         ListPermission
@environment:       Hyprid
@description:       List access grant for user/group
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-08-18
@modified:          2018-10-11
-->
<template>
    <ul class="list">
        <li v-for="grant in grants" :key="grant.id" class="list__item">
            <div>
                <router-link v-if="grant.objectType === 'directory'"
                    :to="{path: '/checklists/directories/' + grant.objectId}">
                    <q-icon
                        class="color-gray"
                        name="folder_open">
                    </q-icon>
                    {{grant.objectName}}
                </router-link>

                <router-link v-else
                    :to="{path: '/checklists/checklist/' + grant.objectId}" >
                    <q-icon
                        class="color-gray"
                        name="check_circle_outline">
                    </q-icon>
                    {{grant.objectName}}
                </router-link>
            </div>
            <div>
                <q-btn
                    class="color-gray"
                    icon="delete"
                    flat
                    size="0.8rem"
                    @click="onBtnClickRemovePermission(grant)">
                </q-btn>
            </div>
        </li>
    </ul>
</template>

<script>
export default {
	name: 'ListPermission',

	props: {
		grants: {
			type: Array,
			required: true
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
	methods: {
		doDeleteAccessGrant(grant) {
			console.log('doDeleteGrant', grant);

			this.$store.dispatch('grants/DELETE_GRANT', grant).then(response => {
				this.$q.notify({
					message: this.$t('DELETE_SUCCESS'),
					type: 'positive'
				});

				this.$emit('refresh');
			});
		},

		onBtnClickRemovePermission(grant) {
			if (this.isDeviceGreaterSM) {
				this.$confirm(this.$t('WOULD_YOU_LIKE_TO_DELETE_PERMISSIONS'), this.$t('DELETE'), {
					confirmButtonText: this.$t('OK'),
					cancelButtonText: this.$t('CANCEL'),
					type: 'warning'
				})
					.then(() => {
						this.doDeleteAccessGrant(grant);
					})
					.catch(() => {});
			} else {
				this.$q
					.dialog({
						title: this.$t('DELETE'),
						message: this.$t('WOULD_YOU_LIKE_TO_DELETE_PERMISSIONS'),
						ok: this.$t('OK'),
						cancel: this.$t('CANCEL')
					})
					.then(() => {
						this.doDeleteAccessGrant();
					})
					.catch(() => {});
			}
		}
	}
};
</script>
