<!--
@component:         OffCanvas
@environment:       Web
@description:       Offcanvas component
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-08-17
@modified:          2018-10-06
-->
<template>
    <div class="app-offcanvas" :class="[{'is-open': open}, 'app-offcanvas--' + position]">
        <header class="app-offcanvas__header">
            <h3 class="app-offcanvas__headline">{{ title }}</h3>

            <!-- Button -->
            <el-button
                plain
                circle
                icon="el-icon-close"
                class="app-offcanvas__btn-close"
                @click="onBtnClick" />
        </header>

        <div class="app-offcanvas__inner p-t-1">
            <!-- Main content goes here -->
            <slot></slot>
        </div>
    </div>
</template>

<script>
export default {
	name: 'OffCanvas',

	props: {
		position: {
			type: String,
			required: false,
			default: 'right'
		},

		open: {
			type: Boolean,
			required: false,
			default: false
		},

		title: {
			type: String,
			required: false,
			default: ''
		}
	},

	data() {
		return {};
	},

	mounted() {},

	methods: {
		onBtnClick() {
			this.$emit('close');
		}
	}
};
</script>

<style lang="scss">
.app-offcanvas {
	background-color: $offcanvas-bg;
	height: 100vh;
	width: 40rem;
	max-width: 70vw;

	position: fixed;
	top: 0;
	z-index: 10000;
	transition: transform 0.2s ease-in-out;

	&.is-open {
		transform: translate3d(0%, 0, 0);
	}

	&--right {
		right: 0;
		left: auto;
		transform: translate3d(101%, 0, 0);

		box-shadow: 0 0 15px -7px rgba(0, 0, 0, 0.1), -6px 0px 9px 0px rgba(0, 0, 0, 0.1);
	}

	&--left {
		left: 0;
		right: auto;
		transform: translate3d(-101%, 0, 0);
		box-shadow: 7px 0 6px 0px rgba(0, 0, 0, 0.1), 0px 0px 0px 0px rgba(0, 0, 0, 0.1);
	}

	&__header {
		background-color: transparent;
		border-bottom: 1px solid lighten($c-light-gray, 3%);
		display: flex;
		justify-content: space-between;
		align-items: center;
		padding: 0.8rem 1.2rem;
	}

	&__headline {
		color: $c-black;
		font-size: 1.2rem;
		margin: 0;
	}

	&__inner {
		max-height: calc(100vh - 4.2rem);
		overflow-y: auto;
	}
}
</style>
