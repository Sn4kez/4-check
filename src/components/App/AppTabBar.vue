<!--
@component:         AppTabBar
@environment:       Hyprid
@description:       This component is used for tab bar on mobile and provide data for underlaying components
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-08-29
@modified:          2018-10-14
-->
<template>
    <div class="app-tabbar">
        <ul class="tabbar" :class="'tabbar--' + theme">
            <li v-for="item in items" :key="item.id" v-if="isRoleSufficent(item)"
                class="tabbar__item" :id="'appbar_'+item.name">
                <router-link :to="item.route" class="tabbar__item-link">
                    <q-icon :name="item.icon" class="tabbar__item-icon"></q-icon>
                    <span class="tabbar__item-name">{{$t(item.name)}}</span>
                </router-link>
            </li>
        </ul>
    </div>
</template>

<script>
import navigationMixin from '@/shared/mixins/navigation';

export default {
	name: 'AppTabBar',

	mixins: [navigationMixin],

	props: {
		items: {
			type: Array,
			required: true,
			validator: function(values) {
				return values.length < 6;
			}
		},

		theme: {
			type: String,
			default: 'light',
			required: false
		}
	}
};
</script>

<style lang="scss">
.app-tabbar {
	@media screen and (max-width: $screen-md) {
		background-color: $c-app-tabbar;
	}
}

.tabbar {
	display: flex;
	margin: 0;
	padding-left: 0;
	height: 100%;

	&__item {
		list-style: none;
		width: calc(100% / 5);
	}

	&__item-name {
		font-size: 0.7rem;
	}

	&__item-link {
		display: flex;
		border-bottom: 3px solid transparent;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		height: 100%;

		font-size: 0.8rem;
		padding: 0.5rem 0;

		@media screen and (min-width: $screen-xs) {
			font-size: 0.9rem;
			padding: 0.7rem 0.5rem;
		}
	}

	&__item-icon {
		margin-bottom: 3px;
		font-size: 1rem !important;

		@media screen and (min-width: $screen-xs) {
			font-size: 1.5rem;
			margin-bottom: 6px;
		}
	}

	&--light {
		background-color: #ffffff;
		border-top: 1px solid $c-light-gray;

		.tabbar__item-link {
			color: $c-gray;

			&.is-active {
				border-bottom-color: $c-brand;
				color: $c-brand;
			}
		}
	}

	&--dark {
		background-color: $c-sidebar-bg;
		border-top: 1px solid transparent;

		.tabbar__item-link {
			color: $c-light-gray;

			&.is-active {
				border-bottom-color: $c-brand;
				color: $c-brand;
			}
		}
	}
}
</style>
