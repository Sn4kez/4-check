<template>
    <el-badge :is-dot="false">
        <el-button type="default" circle @click="onClick">
            <q-icon name="fa fa-bell"></q-icon>
        </el-button>
    </el-badge>
</template>

<script>
export default {
	name: 'ButtonBell',

	mounted() {
		this.init();
	},

	methods: {
		init() {
			console.log('ButtonBell mounted!');
		},

		onClick() {
			console.log('onClick button');
			this.$store.commit('TOGGLE_DRAWER_RIGHT');
		}
	}
};
</script>

<style scoped lang="scss">
.el-button {
	padding: 9px !important;

	@media screen and (min-width: $screen-md) {
		padding: 12px !important;
	}
}
</style>
