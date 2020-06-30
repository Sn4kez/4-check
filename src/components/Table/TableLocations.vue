<!--
@component:         TableLocations
@environment:       Hyprid
@description:       This component handle the visibility for mobile and desktop devices
                    as well as the data storage for child components.
                    On desktop we show a normal table while on mobile a list is rendered.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-08-02
@modified:          2018-09-03
-->
<template>
    <div>
        <!-- ############# Desktop ############# -->
        <div class="w-100" v-if="isDeviceGreaterSM">
            <el-table ref="TableLocations" class="w-100 table-locations"
                v-loading="isLoading"
                :data="data"
                @selection-change="handleSelectionChange"
                @expand-change="onExpandChange"
                :empty-text="$t('NO_DATA_AVAILABLE')">

                <el-table-column
                    type="selection"
                    width="45">
                </el-table-column>

                <el-table-column
                    type="expand">
                    <template slot-scope="scope" class="el-table__row">

                        <TableLocationsChildren
                            v-if="scope.row.children"
                            :children="scope.row.children">
                        </TableLocationsChildren>

                    </template>
                </el-table-column>

                <el-table-column
                    property="name"
                    :label="$t('NAME')"
                    show-overflow-tooltip
                    sortable>
                    <template slot-scope="scope">
                        <a href="#" @click.prevent="onCollapse(scope.row)">
                            {{scope.row.name}}
                        </a>
                    </template>
                </el-table-column>

                <el-table-column
                    :label="$t('TYPE')"
                    sortable
                    show-overflow-tooltip
                    property="type">
                    <template slot-scope="scope">
                        {{getLocationTypeById(scope.row.type).name}}
                    </template>
                </el-table-column>

                <el-table-column
                    property="description"
                    :label="$t('DESCRIPTION')"
                    show-overflow-tooltip>
                </el-table-column>

                <el-table-column
                    property="street"
                    :label="$t('STREET')"
                    show-overflow-tooltip
                    sortable>
                </el-table-column>

                <el-table-column
                    property="streetNumber"
                    :label="$t('NUMBER')"
                    sortable>
                </el-table-column>

                <el-table-column
                    property="postalCode"
                    :label="$t('POSTAL_CODE')"
                    sortable>
                </el-table-column>

                <el-table-column
                    property="city"
                    :label="$t('CITY')"
                    show-overflow-tooltip
                    sortable>
                </el-table-column>

                <el-table-column
                    property="country"
                    :label="$t('COUNTRY')"
                    sortable>
                    <template slot-scope="scope">
                        {{scope.row.country}}
                        <!-- {{getCountryByValue(scope.row.country).label}} -->
                    </template>
                </el-table-column>

                <!-- <el-table-column
                    :label="$t('STATE')"
                    sortable
                    property="state">
                    <template slot-scope="scope">
                        <q-icon v-if="getLocationStateById(scope.row.state).name === 'active'"
                            class="color-brand"
                            name="radio_button_checked">
                        </q-icon>
                        <q-icon v-else
                            name="radio_button_unchecked">
                        </q-icon>
                    </template>
                </el-table-column> -->

                <!-- Actions -->
                <el-table-column
                    label=""
                    width="60"
                    align="right">
                    <template slot-scope="scope">
                        <el-dropdown trigger="click">
                            <span class="el-dropdown-link">
                                <q-icon name="more_horiz" size="2rem"></q-icon>
                            </span>
                            <el-dropdown-menu slot="dropdown">
                                <el-dropdown-item>
                                    <a href="#" @click.prevent="onEdit(scope.row)">{{$t('EDIT')}}</a>
                                </el-dropdown-item>
                                <el-dropdown-item>
                                    <a href="#" @click.prevent="onDelete(scope.row)">{{$t('DELETE')}}</a>
                                </el-dropdown-item>
                            </el-dropdown-menu>
                        </el-dropdown>
                    </template>
                </el-table-column>
            </el-table>
        </div>

        <!-- ############# Mobile ############# -->
        <div v-if="!isDeviceGreaterSM">
            <el-table ref="TableLocations" class="w-100"
                v-loading="isLoading"
                @selection-change="handleSelectionChange"
                :data="data"
                :empty-text="$t('NO_DATA_AVAILABLE')">

                <el-table-column
                    type="selection"
                    width="30">
                </el-table-column>

                <el-table-column
                    :label="$t('LOCATIONS')"
                    :label-class-name="'font-brand'">
                    <template slot-scope="scope">
                        <q-item class="p-0">
                            <!-- <q-item-side icon="place" /> -->

                            <q-item-main>
                                <q-item-tile label>{{scope.row.name}}</q-item-tile>
                                <q-item-tile sublabel class="color-gray fron--small">
                                    {{scope.row.street}} {{scope.row.streetNumber}}, {{scope.row.postalCode}} {{scope.row.city}}
                                </q-item-tile>
                            </q-item-main>


                            <q-item-side right color="green">
                                <el-dropdown trigger="click">
                                    <span class="el-dropdown-link">
                                        <q-icon name="more_horiz" size="2rem"></q-icon>
                                    </span>
                                    <el-dropdown-menu slot="dropdown">
                                        <el-dropdown-item>
                                            <a href="#" @click.prevent="onEdit(scope.row)">{{$t('EDIT')}}</a>
                                        </el-dropdown-item>
                                        <el-dropdown-item>
                                            <a href="#" @click.prevent="onDelete(scope.row)">{{$t('DELETE')}}</a>
                                        </el-dropdown-item>
                                    </el-dropdown-menu>
                                </el-dropdown>
                            </q-item-side>
                        </q-item>
                    </template>
                </el-table-column>
            </el-table>
        </div>

    </div>
</template>

<script>
import countriesMixin from '@/shared/mixins/countries';
import locationsMixin from '@/shared/mixins/locations';
import usersMixin from '@/shared/mixins/users';
import TableLocationsChildren from '@/components/Table/TableLocationsChildren';

export default {
	name: 'TableLocations',

	mixins: [countriesMixin, locationsMixin, usersMixin],

	components: {
		TableLocationsChildren
	},

	props: {
		data: {
			type: Array,
			required: true
		},

		isLoading: {
			type: Boolean,
			required: false,
			default: true
		}
	},

	computed: {
		isDeviceGreaterSM() {
			return this.$store.getters.IS_DEVICE_GREATER_SM;
		}
	},

	data() {
		return {
			expandAfterRequest: null,
			expandedRows: [],
			multipleSelection: []
		};
	},

	mounted() {
		console.log('muntedsdf...');
	},

	methods: {
		handleSelectionChange(val) {
			this.multipleSelection = val;
			this.$emit('change-selection', this.multipleSelection);
		},

		doDelete(task) {
			this.$store.dispatch('locations/DELETE_LOCATION', task).then(() => {
				this.$q.notify({
					message: this.$t('DELETE_SUCCESS'),
					type: 'positive'
				});

				this.$emit('refresh');
			});
		},

		isRowOpen(row) {
			let result = false;

			if (this.expandedRows.length) {
				this.expandedRows.forEach(openRow => {
					if (openRow.id === row.id) {
						result = true;
					}
				});
			}

			return result;
		},

		// WIP
		onCollapse(row) {
			if (row.loaded) {
				this.$refs['TableLocations'].toggleRowExpansion(row);
				return;
			}

			if (this.isRowOpen(row)) {
				return;
			}

			this.selectLocation(row);
		},

		onExpandChange(row, expandedRows) {
			this.expandedRows = expandedRows;

			if (row.loaded !== undefined) {
				return;
			}

			this.selectLocation(row);

			console.log('onExpandChange', row.loaded);
		},

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
		},

		selectLocation(row) {
			this.expandAfterRequest = row;

			this.$store.commit('locations/SET_FILTER', {
				selected: row.id
			});

			this.$emit('refresh');
		}
	},

	watch: {
		data(newValue, oldValue) {
			/**
			 * Because the table rerenders every time the data change
			 * we need to save the selected row and compare it with all new rows from data.
			 * After that we can expand the row we have searched for.
			 */
			if (this.expandAfterRequest) {
				newValue.forEach((row, index) => {
					if (row.id === this.expandAfterRequest.id) {
						row.loaded = true;

						setTimeout(() => {
							this.$refs['TableLocations'].toggleRowExpansion(row, true);
						}, 100);
					}
				});
				this.expandAfterRequest = null;
			}
		}
	}
};
</script>

<style lang="scss">
.el-table__expanded-cell {
	padding: 0 !important;

	.el-table__header-wrapper {
		display: none;
	}
}
</style>
