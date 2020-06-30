<!--
@component:         FormCreateAccessGrant
@environment:       Hyprid
@description:       This component create access grant for user and group.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-09-20
@modified:          2018-09-21
-->
<template>
    <el-form ref="form__create-access-grant">

        <TreeDirectories @select-node="onSelectNode"></TreeDirectories>

        <el-row class="m-t-1">
            <el-col :span="24" class="text-right">
                <q-btn
                    :label="$t('CHOOSE')"
                    color="primary"
                    class="w-100--sm"
                    no-ripple
                    v-loading="loading"
                    @click="doSubmit"
                    tag="a"
                    :disable="!selectedNode">
                </q-btn>
            </el-col>
        </el-row>

    </el-form>
</template>

<script>
import TreeDirectories from '@/components/Tree/TreeDirectories';
import commonMixins from '@/shared/mixins/common';
import { Grant } from '@/shared/classes/Grant';

export default {
	name: 'FormCreateAccessGrant',

	mixins: [commonMixins],

	components: {
		TreeDirectories
	},

	props: {
		data: {
			type: Object,
			required: true
		}
	},

	data() {
		return {
			loading: false,
			selectedNode: null
		};
	},

	methods: {
		doSubmit() {
			let dispatcherName = 'directories/CREATE_DIRECTORY_GRANT';

			const GRANT = new Grant({
				subjectId: this.data.subjectId,
				index: 1,
				view: 1,
				delete: this.data.type === 'write' ? 1 : 0,
				update: this.data.type === 'write' ? 1 : 0
			});

			const DATA = {
				directoryId: this.selectedNode.id,
				data: GRANT
			};

			if (this.selectedNode.objectType === 'checklist') {
				dispatcherName = 'checklists/CREATE_GRANT';
				DATA.checklistId = this.selectedNode.id;
			}

			console.log('doSubmit', this.selectedNode, DATA);

			this.loading = true;
			this.$store
				.dispatch(dispatcherName, DATA)
				.then(response => {
					this.loading = false;
					if (response.status === 201 || response.status === 204) {
						this.$q.notify({
							message: this.$t('SAVE_SUCCESS'),
							type: 'positive'
						});

						this.onCancel();
					} else {
						this.handleErrors(response);
					}
				})
				.catch(err => {
					this.loading = false;
					this.handleErrors(err);
				});
		},

		onCancel() {
			this.$emit('cancel');
		},

		onSelectNode(node) {
			this.selectedNode = Object.assign({}, node);
		}
	}
};
</script>
