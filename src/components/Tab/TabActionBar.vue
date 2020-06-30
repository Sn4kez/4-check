<!--
@component:         TabActionBar
@description:       This component show a tab bar with different actions for selected table items.
                    The actions prop define whether or not the item is visible and need look like:
                    { label: '', icon: '', name: '' }
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-08-18
@modified:          2018-08-19
-->
<template>
    <div class="tab-action-bar__container">
        <ul class="tab-action-bar">
            <li class="tab-action-bar__item">
                <span class="tab-action-bar__item-counter">
                    {{data.length}}
                </span>
            </li>

            <!-- Items -->
            <li v-for="(item, index) in actions" :key="index"
                class="tab-action-bar__item">
                <button class="tab-action-bar__item-link" @click="onItemClick(item.name)">
                    <q-icon :name="item.icon" class="tab-action-bar__item-icon"></q-icon>
                    <span class="tab-action-bar__item-name">
                        {{ item.label }}
                    </span>
                </button>
            </li>

        </ul>
    </div>
</template>

<script>
export default {
	name: 'TabActionBar',

	props: {
		actions: {
			type: Array,
			required: true
		},

		data: {
			type: Array,
			required: true
		}
	},

	methods: {
		isVisible(name) {
			let visible = false;

			this.actions.forEach(item => {
				if (item === name) {
					visible = true;
				}
			});

			return visible;
		},

		onItemClick(item) {
			console.log('onItemClick', item);
			this.$emit('item-click', { item: item });
		}
	}
};
</script>

<style lang="scss">
.tab-action-bar {
	display: flex;
	padding-left: 0;
	margin: 0;

	&__item {
		list-style: none;
		position: relative;

		display: flex;
		align-items: center;
		justify-content: center;

		width: calc((100% / 5) - 2px);

		&:not(:last-child):after {
			content: '';
			position: absolute;
			height: 60%;
			width: 1px;
			background-color: $c-light-gray;
			top: 20%;
			right: 0;
		}

		@media screen and (min-width: $screen-sm) {
			width: auto;
		}
	}

	&__item-link {
		color: $c-gray;
		cursor: pointer;
		display: flex;
		flex-direction: column;
		padding: 0.5rem 1rem;
		border: none;
		outline: none;
		background-color: transparent;
		transition: all 0.2s ease-in-out;
		align-items: center;

		&:hover {
			background-color: transparentize($c-light-gray, 0.3);
		}
	}

	&__item-name {
		font-size: 0.8rem;
		margin-top: 2px;
	}

	&__item-icon {
		font-size: 1.3rem;
	}

	&__item-counter {
		border-radius: 50%;
		height: 30px;
		width: 30px;
		background-color: $c-gray;
		color: #ffffff;
		display: flex;
		font-weight: 600;
		justify-content: center;
		align-items: center;
		margin: 0 0.2rem;

		@media screen and (min-width: $screen-sm) {
			margin: 0 1rem;
		}
	}
}
</style>
