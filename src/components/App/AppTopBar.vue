<!--
@component:         AppTopBar
@environment:       Desktop
@description:       This component handle the visibility for desktop devices
                    as well as the data storage for child components.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-06-01
@modified:          2018-08-20
-->
<template>
    <div class="app-topbar" :class="{'app-topbar--center': !topbar.showBackButton}">
        <!-- Icon -->
        <div class="app-topbar__back m-r-half">
            <q-icon :name="topbar.icon" />
        </div>
        <!-- Title -->
        <div class="app-topbar__name">
            {{$t(topbar.viewName)}}
        </div>
        <!-- Button Bar right -->
        <div class="app-topbar__button-bar">
            <ButtonCreate :loadComponent="'neussel'" class="hide-sm" />

            <ButtonBell class="m-l-1--md" />

            <ButtonUserAvatar class="hide-sm" />
        </div>
    </div>
</template>

<script>
import ButtonBell from '@/components/Button/ButtonBell';
import ButtonCreate from '@/components/Button/ButtonCreate';
import ButtonUserAvatar from '@/components/Button/ButtonUserAvatar';

export default {
	name: 'AppTopBar',

	components: {
		ButtonBell,
		ButtonCreate,
		ButtonUserAvatar
	},

	computed: {
		topbar() {
			return this.$store.state.topbar;
		}
	}
};
</script>

<style lang="scss">
.app-topbar {
	display: flex;
	align-items: center;
	height: 100%;

	&--center {
		justify-content: space-between;
	}

	@media screen and (min-width: $screen-md) {
		justify-content: initial;
	}

	&__name {
		font-size: 1.1rem;
	}

	&__button-bar {
		display: flex;

		@media screen and (min-width: $screen-md) {
			flex: 1 0 auto;
			justify-content: flex-end;
		}
	}

	.el-badge__content.is-dot {
		background-color: $c-red;
		right: 14px !important;
		top: 5px;
		height: 14px;
		width: 14px;
	}

	.el-button {
		&--purple {
			background-color: $c-purple;
			color: #ffffff;
		}
	}
}
</style>
