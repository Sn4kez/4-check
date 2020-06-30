<template>
    <div class="app-aside__inner">
        <div class="el-aside__logo">
            <router-link :to="{path: '/'}">
                <SVGLogoSymbol />
            </router-link>
        </div>

        <!-- Main navigation -->
        <AppNavigation :items="mainNavItems" :is-collapse="isCollapse" />

        <div class="el-aside__inner--bottom">
            <div class="el-aside__separator"></div>
            <!-- Sub navigation -->
            <AppNavigation :items="subNavItems" :is-collapse="isCollapse" />
        </div>

        <div class="el-aside__separator"></div>

        <div class="text-center">
            <el-button type="text" @click="onClickToggleAside" class="w-100">
                <i v-if="isCollapse" class="el-icon-d-arrow-right"></i>
                <i v-else class="el-icon-d-arrow-left"></i>
            </el-button>
        </div>
    </div>
</template>

<script>
import AppNavigation from '@/components/App/AppNavigation';
import SVGLogoSymbol from '@/assets/img/4-check-symbol.svg';
import navigation from '@/shared/navigation';

export default {
	name: 'AppAside',

	components: {
		AppNavigation,
		SVGLogoSymbol
	},

	props: {
		isCollapse: {
			type: Boolean,
			required: true,
			default: false
		}
	},

	data() {
		return {
			mainNavItems: navigation.mainNavItems,
			subNavItems: navigation.subNavItems
		};
	},

	methods: {
		onClickToggleAside() {
			this.$store.commit('ASIDE_TOGGLE');
		}
	}
};
</script>

<style lang="scss">
.app-aside__inner {
	display: flex;
	flex-direction: column;
	height: 100%;
	overflow: hidden;
}

.el-aside__logo {
	text-align: center;
	margin-top: 1.2rem;
	margin-bottom: 1.2rem;
}

.el-aside__inner--bottom {
	display: flex;
	flex-direction: column;
	flex: 1 0 auto;
	justify-content: flex-end;
	width: 100%;
}

.el-aside__separator {
	background-color: $c-medium-gray;
	height: 1px;
	width: 100%;
}
</style>
