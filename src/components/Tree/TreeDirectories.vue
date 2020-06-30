<!--
@component:         TreeDirectories
@environment:       Hyprid
@description:       Display a tree structure for directories and checklists
                    and give opportunity to select a checklist or folder for further usage.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-09-20
@modified:          2018-09-25
-->
<template>
    <div class="tree__directories" v-loading="loading">
        <!-- Root -->
        <button
            class="btn color-gray valign-center p-l-half"
            :class="{'is-selected': rootDirectory.id === selectedNode.id}"
            @click.prevent="onSelectRootNode">
            <q-icon name="home" size="1rem" class="m-r-small"></q-icon>
            <span class="font--regular">{{rootDirectory.name}}</span>
        </button>

        <el-tree
            :props="defaultProps"
            :load="loadNodeLazy"
            :empty-text="$t('NO_DATA_AVAILABLE')"
            @node-click="onNodeClick"
            ref="treeDirectories"
            lazy>
            <div slot-scope="{node, data}" class="w-100 font--regular">
                <div class="valign-center">
                    <q-icon v-if="data.objectType === 'directory'"
                        class="color-gray d-inline-block"
                        style="margin-right:0.2rem"
                        name="folder_open">
                    </q-icon>
                    <q-icon v-else-if="showChecklist"
                        class="color-gray d-inline-block"
                        style="margin-right:0.2rem"
                        name="check_circle_outline">
                    </q-icon>

                    {{data.name}}
                </div>
            </div>
        </el-tree>
    </div>
</template>

<script>
import { transformDirectoriesForTree } from '@/shared/transformers';

export default {
	name: 'TreeDirectories',

	props: {
		showChecklist: {
			type: Boolean,
			required: false,
			default: true
		}
	},

	computed: {
		company() {
			return this.$store.state.user.company;
		},

		isDeviceGreaterSM() {
			return this.$store.getters.IS_DEVICE_GREATER_SM;
		},

		rootDirectory() {
			return this.$store.state.directories.rootDirectory;
		},

		user() {
			return this.$store.state.user.data;
		}
	},

	data() {
		return {
			currentDirectoryId: null,
			currentDirectories: [],
			defaultProps: {
				children: 'children',
				label: 'name'
			},
			loading: false,
			selectedNode: {}
		};
	},

	methods: {
		/**
		 * Lazy load tree and leafs.
		 *
		 * The initial request don´t have a data object for the currently clicked node
		 * so we need to use the company root directory id.
		 * For further requests we can use the currently clicked node.
		 *
		 * @param   {Object}    node,
		 * @param   {Function}  resolve
		 */
		loadNodeLazy(node, resolve) {
			if (node.data === undefined) {
				this.loading = true;
				this.currentDirectoryId = this.company.directoryId;
			} else {
				this.currentDirectoryId = node.data.id;

				// Prevent request directory entries if current node is a checklist
				if (node.data.objectType === 'checklist') {
					return resolve([]);
				}
			}

			this.requestDirectoryEntries(this.currentDirectoryId).then(response => {
				this.loading = false;
				return resolve(transformDirectoriesForTree(response.data.data, this.showChecklist));
			});
		},

		onNodeClick(item, node, treeNode) {
			this.selectedNode = item;
			this.$emit('select-node', this.selectedNode);
			this.$refs['treeDirectories'].currentNode.$el.classList.add('is-current');
		},

		onSelectRootNode() {
			this.selectedNode = Object.assign({}, this.rootDirectory);
			this.$emit('select-node', this.rootDirectory);

			const currentNode = document.querySelector('.tree__directories .el-tree-node.is-current');
			if (currentNode) {
				currentNode.classList.remove('is-current');
			}
			this.$refs['treeDirectories'].currentNode = {};
		},

		requestDirectoryEntries(id) {
			return this.$store
				.dispatch('directories/GET_DIRECTORY_ENTRIES', { id: id })
				.then(response => {
					return response;
				})
				.catch(err => {
					this.loading = false;
					return err;
				});
		}
	}
};
</script>

<style lang="scss">
.tree__directories {
	.el-tree-node {
		&.is-current {
			> .el-tree-node__content {
				font-weight: 700;

				.q-icon {
					font-weight: 700;
				}
			}
		}
	}

	.is-selected {
		font-weight: 700;

		.q-icon {
			font-weight: 700;
		}
	}
}
</style>
