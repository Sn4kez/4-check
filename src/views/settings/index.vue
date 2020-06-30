<!--
@component:         SettingsIndexView
@environment:       Hyprid
@description:       This component handle the visibility for mobile and desktop devices
                    as well as the data storage for child components.

                    Currently, at the desktop version it is not possible to hit this route.
                    On mobile we show a tab bar to the user for navigation and
                    need to show the children routes of each entry.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-08-11
@modified:          2018-10-14
-->
<template>
    <el-card class="el-card--no-padding-sm" style="min-height: 4rem;">
        <ListNavItems :items="navItems" :sparse="true"></ListNavItems>
    </el-card>
</template>

<script>
import navigation from '@/shared/navigation';
import ListNavItems from '@/components/List/ListNavItems';

export default {
	name: 'SettingsIndexView',

	components: {
		ListNavItems
	},

	data() {
		return {
			navItems: []
		};
	},

	mounted() {
		this.navItems = _.find(navigation.mainNavItems, { id: 9 }).children;
	}
};
</script>
