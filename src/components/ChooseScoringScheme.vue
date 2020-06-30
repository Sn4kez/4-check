<template>
    <div>
        <el-radio-group v-model="selectedItem" @change="onChange" :disabled="disabled">
            <el-radio-button v-for="item in items" :key="item.id" :label="item.name"></el-radio-button>
        </el-radio-group>
    </div>
</template>

<script>
export default {
	name: 'ChooseScoringScheme',

	props: {
		activeItem: {
			type: String,
			required: false,
			default: null
		},

		disabled: {
			type: Boolean,
			required: false,
			default: false
		},

		items: {
			type: Array,
			required: true
		}
	},

	data() {
		return {
			selectedItem: ''
		};
	},

	mounted() {
		this.init();
	},

	methods: {
		init() {
			console.log('ChooseScoringScheme', this.activeItem);
			if (this.activeItem) {
				this.selectedItem = this.activeItem;
			}
		},

		onChange(value) {
			const item = _.filter(this.items, item => {
				return item.name === value;
			});

			this.$emit('change', item[0]);
			console.log('onChange', value, this.selectedItem, item);
		}
	},

	watch: {
		activeItem(newValue, oldValue) {
			this.init();
		}
	}
};
</script>

<style lang="scss">
</style>
