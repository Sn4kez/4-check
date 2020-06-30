<!--
@component:         TableLocationsChildren
@description:
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-09-03
@modified:          2018-09-03
-->
<template>
    <div class="el-table__row--root">
        <TableLocationsChildrenRow
            v-for="child in children" :key="child.id"
            :child="child">
        </TableLocationsChildrenRow>
    </div>
</template>

<script>
import TableLocationsChildrenRow from '@/components/Table/TableLocationsChildrenRow';

export default {
	name: 'TableLocationsChildren',

	components: {
		TableLocationsChildrenRow
	},

	props: {
		children: {
			type: Array,
			required: true
		}
	}
};
</script>
