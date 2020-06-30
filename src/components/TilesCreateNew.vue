<template>
<ul class="tile__items">
    <li v-for="item in items" :key="item.id"
        class="tile__item">
        <router-link :to="item.route.path" class="tile__item-link">
            <span class="tile__item-icon">
                <i :class="item.icon"></i>
            </span>
            <span class="tile__item-name">
                {{item.name}}
            </span>
        </router-link>
    </li>
</ul>
</template>

<script>
export default {
	name: 'TilesCreateNew',

	data() {
		return {
			items: [
				{
					id: 1,
					name: this.$t('CHECKLIST'),
					icon: 'el-icon-circle-check-outline',
					route: {
						path: '/checklist/new'
					}
				},
				{
					id: 2,
					name: this.$t('TASK'),
					icon: 'el-icon-phone',
					route: {
						path: '/task/new'
					}
				},
				{
					id: 3,
					name: this.$t('LOCATION'),
					icon: 'el-icon-location',
					route: {
						path: '/location/new'
					}
				}
			]
		};
	}
};
</script>

<style scoped lang="scss">
.tile {
	&__items {
		margin: 0;
		padding-left: 0;

		display: flex;
		justify-content: space-between;
		flex-wrap: wrap;
		align-items: center;
	}

	&__item {
		list-style: none;
		width: calc(100% / 3);
	}

	&__item-link {
		display: flex;
		flex-direction: column;
		padding: 1rem 1.5rem;
		align-items: center;

		&:hover,
		&:focus {
			background-color: lighten($c-light-gray, 10%);
		}
	}

	&__item-icon {
		display: inline-block;
		font-size: 1.8rem;
		margin-bottom: 0.6rem;
	}

	&__item-name {
		text-transform: uppercase;
	}
}
</style>
