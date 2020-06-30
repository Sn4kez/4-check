<template>
    <div class="el-table__row-children">
        <ul class="el-table__row">
            <li class="flex a-center">
                <div class="m-r-half">
                    <q-icon name="place" size="1.4rem" class="color-gray"></q-icon>
                </div>
                <div class="cell">
                    <span class="font--regular">{{child.name}}<span v-if="child.description">, {{child.description}}</span></span> <br>
                    <span class="font--small">
                        <span v-if="child.street || child.streetNumber">{{child.street}} {{child.streetNumber}}, </span><span v-if="child.postalCode || child.city">{{child.postalCode}} {{child.city}}, </span><span v-if="child.country">{{child.country}}</span>
                    </span>
                </div>
            </li>

            <li>
                <div class="cell">
                    <el-dropdown trigger="click">
                        <span class="el-dropdown-link">
                            <q-icon name="more_horiz" size="2rem"></q-icon>
                        </span>
                        <el-dropdown-menu slot="dropdown">
                            <el-dropdown-item>
                                <a href="#" @click.prevent="onEdit(child)">{{$t('EDIT')}}</a>
                            </el-dropdown-item>
                            <el-dropdown-item>
                                <a href="#" @click.prevent="onDelete(child)">{{$t('DELETE')}}</a>
                            </el-dropdown-item>
                        </el-dropdown-menu>
                    </el-dropdown>
                </div>
            </li>
        </ul>

        <!-- Generate further children recursive -->
        <TableLocationsChildrenRow
            v-for="(items, index) in child.children" :key="index"
            :child="items">
        </TableLocationsChildrenRow>

    </div>
</template>

<script>
import locationsMixin from '@/shared/mixins/locations';

export default {
	name: 'TableLocationsChildrenRow',

	mixins: [locationsMixin],

	props: {
		child: {
			type: Object,
			required: true
		}
	},

	methods: {
		onDelete(row) {
			console.log('onDelete', row);

			if (this.isDeviceGreaterSM) {
				this.$confirm(this.$t('WOULD_YOU_LIKE_TO_DELETE_LOCATION', row.name), {
					type: 'warning',
					confirmButtonText: this.$t('OK'),
					cancelButtonText: this.$t('CANCEL')
				})
					.then(() => {
						this.doDelete(row);
					})
					.catch(() => {
						console.log('not deleted');
					});
			} else {
				this.$q
					.dialog({
						title: this.$t('CONFIRM'),
						message: this.$t('WOULD_YOU_LIKE_TO_DELETE_LOCATION', row.name),
						ok: this.$t('OK'),
						cancel: this.$t('CANCEL')
					})
					.then(() => {
						this.doDelete(row);
					})
					.catch(() => {
						console.log('not deleted');
					});
			}
		},

		onEdit(row) {
			console.log('onEdit locations', row.id);

			if (this.isDeviceGreaterSM) {
				this.$store.commit('OPEN_DIALOG', {
					title: this.$t('EDIT_LOCATION'),
					loadComponent: 'Form/FormEditLocation',
					width: '50%',
					data: row
				});
			} else {
				this.$store.commit('OPEN_MODAL', {
					title: this.$t('EDIT_LOCATION'),
					loadComponent: 'Form/FormEditLocation',
					maximized: true,
					data: row
				});
			}
		}
	}
};
</script>

<style lang="scss">
.el-table__expanded-cell {
	background-color: $c-lighter-gray;

	&:hover {
		background-color: $c-lighter-gray !important;
	}
}

.el-table__row--root {
	margin-left: 6.5rem;

	> .el-table__row-children {
		padding-left: 1rem;
	}
}

.el-table__row-children {
	padding-left: 1rem;

	&:not(:last-child) {
		border-bottom: 1px solid lighten($c-light-gray, 6%);
	}

	.el-table__row {
		display: flex;
		justify-content: space-between;
		align-items: center;
		width: 100%;
		margin: 0;
		padding-left: 0;
		list-style: none;

		&:not(:last-child) {
			border-bottom: 1px solid lighten($c-light-gray, 6%);
		}

		li {
			padding: 12px 0;
		}
	}
}
</style>
