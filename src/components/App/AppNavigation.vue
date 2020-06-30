<!--
@component:         AppNavigation
@environment:       Hyprid
@description:       This component is used for the main navigation on desktop and provide data for underlaying components
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-06-29
@modified:          2018-10-14
-->
<template>
    <div class="app-navigation">
        <el-menu class="el-menu__main"
            :collapse="isCollapse"
            :router="true"
            :class="['el-menu--' + theme]">

            <div v-for="item in items" :key="item.id" v-if="isRoleSufficent(item)" :id="'sidebar_'+item.name">
                <!-- If item has children -->
                <el-submenu v-if="item.children.length"
                    :index="item.id.toString()"
                    :route="item.route"
                    :class="[{'is-active': isRouteActive(item.route)}, {'is-trail': isRouteTrail(item.route)}]">
                    <template slot="title">

                        <q-icon :name="item.icon"></q-icon>
                        <span>{{$t(item.name)}}</span>
                    </template>

                    <el-menu-item v-for="child in item.children" :key="child.id.toString()"
                        :index="child.id.toString()"
                        :route="child.route"
                        :class="{'is-active123': isRouteActive(child.route)}">

                            <q-icon :name="child.icon"></q-icon>
                            <span slot="title">
                                {{$t(child.name)}}
                            </span>
                    </el-menu-item>
                </el-submenu>

                <!-- If item has no children -->
                <el-menu-item v-if="!item.children.length"
                    :index="item.id.toString()"
                    :route="item.route"
                    :class="{'is-active': isRouteActive(item.route)}">

                    <q-icon :name="item.icon"></q-icon>
                    <span slot="title">
                        {{$t(item.name)}}
                    </span>
                </el-menu-item>
            </div>

        </el-menu>

    </div>
</template>

<script>
import navigationMixin from '@/shared/mixins/navigation';

export default {
	name: 'AppNavigation',

	mixins: [navigationMixin],

	props: {
		items: {
			type: Array,
			required: true
		},

		isCollapse: {
			type: Boolean,
			default: false,
			required: true
		},

		theme: {
			type: String,
			default: 'dark',
			required: false
		}
	},

	computed: {
		getActiveItem() {
			let activeItem = null;
			this.items.forEach(item => {
				if (this.isRouteActive(item.route)) {
					activeItem = item;
				}
			});

			return activeItem;
		}
	},

	data() {
		return {};
	},

	methods: {
		isRouteActive(route) {
			return route.path === this.$route.path;
		},

		isRouteTrail(route) {
			return route.path === this.$route.matched[0].path;
			// return this.$route.path.includes(route.path);
		}
	}
};
</script>

<style lang="scss">
.app-navigation {
	overflow-x: hidden;
	overflow-y: auto;
	width: 100%;

	.el-menu {
		border-right: none !important;
		transition: all 0.3s ease-in-out;
		overflow: hidden;
		width: 100%;

		&--collapse {
			width: $sidebar-with-collapse !important;

			i {
				font-size: 1.5rem;
			}

			.el-menu-item {
				text-align: center;
			}

			.el-submenu {
				text-align: center;

				&__title {
					span,
					.el-submenu__icon-arrow {
						display: none;
					}
				}
			}
		}

		&--dark {
			background-color: $c-sidebar-bg !important;

			.el-menu-item {
				border-left: 5px solid transparent;
				color: $c-light-gray !important;

				&:hover:not(.is-active),
				&:focus:not(.is-active) {
					background-color: lighten($c-sidebar-bg, 13%) !important;

					* {
						color: $c-light-gray !important;
					}
				}

				* {
					color: $c-light-gray !important;
				}

				&.is-active {
					background-color: $c-app-bg;
					border-left-color: $c-brand;
					color: $c-gray !important;

					* {
						color: $c-gray !important;
					}
				}
			}

			.el-submenu {
				&__title {
					background-color: transparent;
					border-left: 5px solid transparent;
					color: $c-light-gray !important;

					> i {
						color: $c-light-gray !important;
					}
				}

				&.is-trail {
					> .el-submenu__title {
						border-left-color: $c-brand;
					}

					.el-menu-item {
						border-left-color: $c-brand;
					}
				}

				&.is-opened {
					> .el-submenu__title {
						background-color: lighten($c-sidebar-bg, 5%) !important;
					}
				}

				.el-menu {
					background-color: lighten($c-sidebar-bg, 8%) !important;
				}

				.el-menu-item {
					border-left: 5px solid transparent;
					font-size: 0.8rem;
				}
			}
		}
	}

	// For not collapsed menu
	.el-menu:not(.el-menu--collapse) {
		.el-menu-item {
			display: flex;
			align-items: center;
			white-space: initial;
			height: auto;

			.q-icon {
				font-size: 1.3rem;
				margin-right: 0.7rem;
				vertical-align: middle;
				width: 24px;
			}
		}

		.el-submenu {
			.el-menu-item {
				line-height: 1.2rem;
				min-height: 54px;
				padding-right: 0.8rem;
				padding-left: 30px !important;
				// hyphens: auto;
				word-break: break-word;
			}

			.q-icon {
				font-size: 1.3rem;
				margin-right: 0.5rem;
				vertical-align: middle;
				width: 24px;
			}
		}
	}
}

// Submenu in collapsed mode
.el-menu--vertical {
	.el-menu-item {
		.q-icon {
			font-size: 1.3rem;
			margin-right: 0.7rem;
			vertical-align: middle;
			width: 24px;
		}
	}
}
</style>
