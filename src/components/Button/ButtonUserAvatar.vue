<template>
    <el-dropdown trigger="click">
        <el-button
            type="text"
            round
            class="color-dark">
            {{this.user.data.firstName}} {{this.user.data.lastName}}<i class="el-icon-caret-bottom"></i>
        </el-button>

        <el-dropdown-menu slot="dropdown">
            <el-dropdown-item>
                <router-link :to="{path: '/settings/user/profile'}">{{ $t('MY_PROFILE') }}</router-link>
            </el-dropdown-item>
            <el-dropdown-item divided>
                <router-link :to="{path: '/logout'}">{{ $t('LOGOUT') }}</router-link>
            </el-dropdown-item>
        </el-dropdown-menu>
    </el-dropdown>
</template>

<script>
export default {
	name: 'ButtonUserAvatar',

	computed: {
		user() {
			return this.$store.state.user;
		}
	},

	mounted() {
		this.init();
	},

	methods: {
		init() {},

		onClick() {
			console.log('onClick button');
		}
	}
};
</script>

<style scoped>
</style>
