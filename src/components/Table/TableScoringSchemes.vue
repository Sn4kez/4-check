<!--
@component:         TableScoringSchemes
@environment:       Hyprid
@description:       This component handle the visibility for mobile and desktop devices
                    as well as the data storage for child components.
                    On desktop we show a normal table while on mobile a list is rendered.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-09-06
@modified:          2018-10-15
-->
<template>
    <div>
        <!-- ############# Desktop ############# -->
        <div class="w-100" v-if="isDeviceGreaterSM">
            <el-table
                ref="TableScoringSchemes"
                class="w-100"
                :data="data"
                :empty-text="$t('NO_DATA_AVAILABLE')"
                :default-sort="{prop: 'name', order: 'ascending'}">

                <el-table-column
                    property="name"
                    :label="$t('NAME')"
                    show-overflow-tooltip
                    sortable>
                    <template slot-scope="scope">
                        {{scope.row.name}}
                    </template>
                </el-table-column>

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
            <el-table
                ref="TableScoringSchemes"
                class="w-100"
                :data="data">

                <el-table-column
                    :label="$t('RATING_SYSTEMS')"
                    :label-class-name="'font-brand'">
                    <template slot-scope="scope">
                        <q-item class="p-0">

                            <q-item-main>
                                <q-item-tile label>{{scope.row.name}}</q-item-tile>
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
export default {
	name: 'TableScoringSchemes',

	props: {
		data: {
			type: Array,
			required: true
		},

		isLoading: {
			type: Boolean,
			required: false,
			default: false
		}
	},

	computed: {
		isDeviceGreaterSM() {
			return this.$store.getters.IS_DEVICE_GREATER_SM;
		}
	},

	data() {
		return {
			multipleSelection: []
		};
	},

	methods: {
		doDelete(scheme) {
			this.$store.dispatch('scoringschemes/DELETE_SCORING_SCHEME', scheme).then(() => {
				this.$q.notify({
					message: this.$t('DELETE_SUCCESS'),
					type: 'positive'
				});

				this.$emit('refresh');
			});
		},

		onDelete(row) {
			console.log('onDelete', row);

			if (this.isDeviceGreaterSM) {
				this.$confirm(this.$t('WOULD_YOU_LIKE_TO_DELETE_SCORING_SCHEME', row.name), {
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
						message: this.$t('WOULD_YOU_LIKE_TO_DELETE_SCORING_SCHEME', row.name),
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
			console.log('onEdit scoring scheme', row.id);

			if (this.isDeviceGreaterSM) {
				this.$store.commit('OPEN_DIALOG', {
					title: this.$t('EDIT_SCORING_SCHEME'),
					loadComponent: 'Form/FormEditScoringScheme',
					width: '50%',
					data: row
				});
			} else {
				this.$store.commit('OPEN_MODAL', {
					title: this.$t('EDIT_SCORING_SCHEME'),
					loadComponent: 'Form/FormEditScoringScheme',
					maximized: true,
					data: row
				});
			}
		}
	}
};
</script>

<style lang="scss">
</style>
