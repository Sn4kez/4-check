<template>
    <el-card class="el-card--no-padding-sm" style="min-height: 4rem;">
        <ListNavItems :items="items" :sparse="true"></ListNavItems>
    </el-card>
</template>

<script>
import navigation from '@/shared/navigation';
import ListNavItems from '@/components/List/ListNavItems';

export default {
	name: 'MoreLinksView',

	components: {
		ListNavItems
	},

	data() {
		return {
			items: navigation.tabBarNavMoreItems
		};
	}
};
</script>

<style scoped>
</style>
