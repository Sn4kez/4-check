<!--
@component:         FormMoveDirectories
@environment:       Hyprid
@description:       This component copy directory or checklist.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-09-24
@modified:          2018-09-25
-->
<template>
    <el-form ref="form__move-directory">

        <TreeDirectories @select-node="onSelectNode" :show-checklist="false"></TreeDirectories>

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

export default {
	name: 'FormMoveDirectories',

	mixins: [commonMixins],

	components: {
		TreeDirectories
	},

	props: {
		data: {
			type: Array,
			required: true
		}
	},

	data() {
		return {
			loading: false,
			multiple: false,
			selectedNode: null
		};
	},

	mounted() {
		this.init();
	},

	methods: {
		doSubmit() {
			// Single movement
			let dispatcherName = 'directories/MOVE_DIRECTORY';
			let data = {
				id: this.data[0].object.id,
				objectType: this.data[0].objectType,
				targetId: this.selectedNode.id
			};

			// Multiple movement
			if (this.multiple) {
				data = {
					entries: []
				};

				this.data.forEach(item => {
					const obj = {
						objectId: item.object.id,
						objectType: item.objectType,
						targetId: this.selectedNode.id
					};
					data.entries.push(obj);
				});

				dispatcherName = 'directories/MOVE_DIRECTORIES';
			}

			this.loading = true;
			this.$store
				.dispatch(dispatcherName, data)
				.then(response => {
					this.loading = false;

					if (response.status === 201 || response.status === 204) {
						this.$q.notify({
							message: this.$t('SAVE_SUCCESS'),
							type: 'positive'
						});

						this.$store.commit('directories/RESET_DIRECTORY_ENTRIES');
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

		init() {
			if (this.data.length > 1) {
				this.multiple = true;
			}
			console.log('FormMoveDirectory mounted', this.data);
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
