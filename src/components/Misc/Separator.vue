<template>
    <div class="separator" :class="{'separator--lines': showLine}">
        <div class="separator__line" v-if="showLine"></div>
        <span class="separator__text" v-if="text">{{ text }}</span>
        <div class="separator__line" v-if="showLine"></div>
    </div>
</template>

<script>
export default {
	name: 'Separator',

	props: {
		showLine: {
			type: Boolean,
			required: false,
			default: false
		},

		text: {
			type: String,
			required: false
		},

		theme: {
			type: String,
			required: false,
			default: 'light'
		}
	}
};
</script>

<style lang="scss">
.separator {
	&--lines {
		display: flex;
		justify-content: space-between;
		align-items: center;
	}

	&__line {
		height: 1px;
		background-color: $c-medium-gray;
		width: 100%;
	}

	&__text {
		font-size: 0.8rem;
		padding: 0 1rem;
		white-space: nowrap;
	}
}
</style>
