<template>
    <div class="app-layout--default">

        <!-- NETWORK NOTIFICATION -->
        <transition name="slide">
            <div
                v-show="network.state === 'offline'"
                class="notification__container">
                <p class="notification m-b-0"
                    style="background-color: rgba(128,128,128,.8)">
                    {{ $t('NO_NETWORK_CONNECTION') }}
                </p>
            </div>
        </transition>

        <div class="app-layout-inner">
            <!-- Main components are rendered here -->
            <router-view></router-view>
        </div>

    </div>
</template>

<script>
export default {
	name: 'DefaultLayout',

	computed: {
		isDeviceGreaterSM() {
			return this.$store.getters.IS_DEVICE_GREATER_SM;
		},

		network() {
			return this.$store.state.network;
		}
	}
};
</script>

<style lang="scss">
.app-layout--default {
	.notification__container {
		position: fixed;
		top: 0;
		left: 0;
		width: 100%;
		z-index: 10;
	}

	.slide-enter-active,
	.slide-leave-active {
		transition: all 0.3s ease-in-out;
	}

	.slide-enter,
	.slide-leave-to {
		opacity: 0;
		transform: translateY(-100%);
	}
}
</style>

