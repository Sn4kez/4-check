<template>
    <el-dropdown trigger="click">
        <q-btn icon="more_vert" round flat no-ripple></q-btn>

        <el-dropdown-menu slot="dropdown">
            <el-dropdown-item v-for="item in items" :key="item.id">
                <a @click.prevent="onClick(item)" href="#" class="d-block">
                    <q-icon v-if="item.icon" :name="item.icon" class="m-r-half"></q-icon> {{item.name}}
                </a>
            </el-dropdown-item>
        </el-dropdown-menu>
    </el-dropdown>
</template>

<script>
export default {
	name: 'ButtonChecklistSectionItem',

	props: {
		data: {
			type: Object,
			required: true
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
		return {
			items: [
				{
					id: 1,
					name: this.$t('EDIT'),
					icon: '',
					handler: this.handleEdit
				},
				{
					id: 2,
					name: this.$t('DELETE'),
					icon: '',
					handler: this.onDelete
				}
			]
		};
	},

	mounted() {
		this.init();
	},

	methods: {
		doDeleteCheckpoint(item) {
			this.$store.dispatch('checkpoints/DELETE_CHECKPOINT', item).then(() => {
				this.$q.notify({
					message: this.$t('DELETE_SUCCESS'),
					type: 'positive'
				});

				// Trigger refresh
				this.$eventbus.$emit('checklist:refresh');
				this.$eventbus.$emit('section:' + this.data.parentId + ':refresh');
			});
		},

		doDeleteExtension(item) {
			this.$store.dispatch('extensions/DELETE_EXTENSION', item).then(() => {
				this.$q.notify({
					message: this.$t('DELETE_SUCCESS'),
					type: 'positive'
				});

				// Trigger refresh
				this.$eventbus.$emit('checklist:refresh');
				this.$eventbus.$emit('checkpoint:' + this.data.parentId + ':refresh');
				this.$eventbus.$emit('section:' + this.data.parentId + ':refresh');
			});
		},

		doDeleteSection(item) {
			this.$store.dispatch('sections/DELETE_SECTION', item).then(() => {
				this.$q.notify({
					message: this.$t('DELETE_SUCCESS'),
					type: 'positive'
				});

				this.$eventbus.$emit('checklist:refresh');
			});
		},

		doEditSection(item) {
			this.$store
				.dispatch('sections/UPDATE_SECTION', item)
				.then(response => {
					this.$eventbus.$emit('checklist:refresh');
				})
				.catch(err => {
					console.log('ERR UPDATE SECTION');
				});
		},

		handleDelete() {
			switch (this.data.objectType) {
				case 'section':
					this.doDeleteSection(this.data.object);
					break;
				case 'checkpoint':
					this.doDeleteCheckpoint(this.data.object);
					break;
				case 'extension':
					this.doDeleteExtension(this.data.object);
					break;
			}
		},

		handleEdit() {
			console.log('handleEditItem-------', this.data);

			switch (this.data.objectType) {
				case 'section':
					this.handleEditSection(this.data.object);
					break;
			}
		},

		handleEditSection(item) {
			console.log('1. handleEditSection', item);

			this.$q
				.dialog({
					title: this.$t('EDIT_SECTION'),
					message: this.$t('PLEASE_ENTER_TITLE'),
					ok: this.$t('OK'),
					cancel: this.$t('CANCEL'),
					prompt: {
						model: item.title,
						type: 'text'
					}
				})
				.then(value => {
					// Create new section
					item.title = value;

					this.doEditSection(item);

					console.log('2. handleEditSection', value, item);
				})
				.catch(err => {});
		},

		init() {
			if (this.data.objectType === 'checkpoint' || this.data.objectType === 'extension') {
				this.items = this.items.slice(1);
			}

			console.log('ButtonCreateLocation mounted!');
		},

		onClick(item) {
			item.handler();

			console.log('onClick button', item);
		},

		onDelete() {
			console.log('onDelete-------', this.data);

			if (this.isDeviceGreaterSM) {
				this.$confirm(this.$t('WOULD_YOU_LIKE_TO_DELETE_ENTRY'), {
					type: 'warning',
					confirmButtonText: this.$t('OK'),
					cancelButtonText: this.$t('CANCEL')
				})
					.then(() => {
						this.handleDelete();
					})
					.catch(() => {
						console.log('not deleted');
					});
			} else {
				this.$q
					.dialog({
						title: this.$t('CONFIRM'),
						message: this.$t('WOULD_YOU_LIKE_TO_DELETE_ENTRY'),
						ok: this.$t('OK'),
						cancel: this.$t('CANCEL')
					})
					.then(() => {
						this.handleDelete();
					})
					.catch(() => {
						console.log('not deleted');
					});
			}
		}
	}
};
</script>
