<template>
    <div>
        <el-checkbox-group v-model="selectedItem" @change="onChange" :disabled="disabled">
            <el-checkbox-button v-for="item in items" :key="item.id" :label="item.name"></el-checkbox-button>
        </el-checkbox-group>
    </div>
</template>

<script>
    export default {
        name: 'ChooseScore',

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
                selectedItem: [],
            };
        },

        mounted() {
            this.init();
        },

        methods: {
            getScoreByName(name) {
                return _.filter(this.items, item => {
                    return item.name === name;
                });
            },

            getScoreByValue(value) {
                return _.filter(this.items, item => {
                    return item.value === value;
                });
            },

            init() {
                if (this.activeItem) {
                    this.selectedItem = [this.activeItem];
                }
            },

            onChange() {
                // Allow only one item to be selected
                // (last selected item is appended to the array - it occupies the last position)
                if (this.selectedItem.length > 1) {
                    this.selectedItem = [this.selectedItem[this.selectedItem.length - 1]]
                }
                if (this.selectedItem.length > 0) {
                    const score = this.getScoreByName(this.selectedItem[0]);
                    this.$emit('change', score[0]);
                }
                this.$emit('change', {
                    schemeId: this.items[0].schemeId,
                    id: null,
                    value: null,
                });
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
    .el-checkbox-button.is-disabled.is-checked {
        .el-checkbox-button__inner {
            color: #fff;
            background-color: #4DAE4E;
        }
    }
</style>
