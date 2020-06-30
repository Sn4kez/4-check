<!--
@component:         TableDirectoryPermissions
@environment:       Hyprid
@description:       This component handle the visibility for mobile and desktop devices
                    as well as the data storage for child components.
                    On desktop we show a normal table while on mobile a list is rendered.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-08-23
@modified:          2018-10-05
-->
<template>
    <div class="directory-permissions__table">
        <!-- ############# Desktop ############# -->
        <div class="w-100" v-if="isDeviceGreaterSM">
            <el-table ref="TableDirectoryPermissions" class="w-100"
                v-loading="isLoading"
                :data="data"
                :empty-text="$t('NO_DATA_AVAILABLE')">

                <el-table-column
                    :label="$t('NAME')"
                    show-overflow-tooltip>
                    <template slot-scope="scope">
                        <div class="flex">
                            <q-icon v-if="scope.row.subjectType === 'group'"
                                class="color-gray font--big"
                                name="group">
                            </q-icon>
                            <q-icon v-else-if="scope.row.subjectType === 'user'"
                                class="color-gray font--big"
                                name="person">
                            </q-icon>
                            <div class="m-l-half d-block">
                                {{scope.row.subjectId}}
                            </div>
                        </div>
                    </template>
                </el-table-column>

                <el-table-column
                    :label="$t('PERMISSIONS')"
                    show-overflow-tooltip>
                    <template slot-scope="scope">
                        <ul class="permissions__list">
                            <li class="permissions__list-item" v-if="scope.row.index">{{ $t('LIST') }}</li>
                            <li class="permissions__list-item" v-if="scope.row.view">{{ $t('READ') }}</li>
                            <li class="permissions__list-item" v-if="scope.row.update">{{ $t('EDIT') }}</li>
                            <li class="permissions__list-item" v-if="scope.row.delete">{{ $t('DELETE') }}</li>
                        </ul>
                    </template>
                </el-table-column>

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
                :data="data"
                :empty-text="$t('NO_DATA_AVAILABLE')">

                <el-table-column
                    :label="$t('NAME')"
                    :label-class-name="'font-brand'">
                    <template slot-scope="scope">
                        <q-item class="p-0">
                            <q-item-side>
                                <q-icon v-if="scope.row.subjectType === 'group'"
                                    class="color-gray font--big"
                                    name="group">
                                </q-icon>
                                <q-icon v-else-if="scope.row.subjectType === 'user'"
                                    class="color-gray font--big"
                                    name="person">
                                </q-icon>
                            </q-item-side>

                            <q-item-main>
                                <q-item-tile label>{{scope.row.subjectId}}</q-item-tile>
                                <q-item-tile sublabel>
                                    <ul class="permissions__list">
                                        <li class="permissions__list-item" v-if="scope.row.index">{{ $t('LIST') }}</li>
                                        <li class="permissions__list-item" v-if="scope.row.view">{{ $t('READ') }}</li>
                                        <li class="permissions__list-item" v-if="scope.row.update">{{ $t('EDIT') }}</li>
                                        <li class="permissions__list-item" v-if="scope.row.delete">{{ $t('DELETE') }}</li>
                                    </ul>
                                </q-item-tile>
                            </q-item-main>

                            <q-item-side right color="green">
                                <el-dropdown trigger="click">
                                    <span class="el-dropdown-link">
                                        <q-icon name="more_horiz" size="2rem"></q-icon>
                                    </span>
                                    <el-dropdown-menu slot="dropdown">
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
	name: 'TableDirectoryPermissions',

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
		return {};
	},

	methods: {
		doDelete(item) {
			this.$store.dispatch('grants/DELETE_GRANT', item).then(() => {
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
				this.$confirm(this.$t('WOULD_YOU_LIKE_TO_DELETE_PERMISSIONS', row.name), {
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
						message: this.$t('WOULD_YOU_LIKE_TO_DELETE_PERMISSIONS', row.name),
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
		}
	}
};
</script>

<style lang="scss">
.directory-permissions__table {
	.permissions__list {
		display: flex;
		flex-wrap: wrap;
		margin: 0;
		padding: 0;
		list-style: none;

		&-item {
			&:not(:last-child) {
				margin-right: 2px;

				&:after {
					content: ',';
				}
			}
		}
	}
}
</style>
