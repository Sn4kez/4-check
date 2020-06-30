<!--
@component:         TableGroupMembers
@environment:       Hyprid
@description:       This component handle the visibility for mobile and desktop devices
                    as well as the data storage for child components.
                    On desktop we show a normal table while on mobile a list is rendered.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-08-23
@modified:          2018-10-05
-->
<template>
    <div>
        <!-- Desktop -->
        <el-table ref="TableGroupMembers" class="hide-sm d-block--md w-100"
            v-loading="isLoading"
            @selection-change="handleSelectionChange"
            :data="data"
            :empty-text="$t('NO_DATA_AVAILABLE')">

            <!-- <el-table-column
                type="selection"
                width="55">
            </el-table-column> -->

            <el-table-column
                :label="$t('FIRSTNAME')">
                <template slot-scope="scope">
                    <span >{{ scope.row.firstName }}</span>
                </template>
            </el-table-column>

            <el-table-column
                :label="$t('LASTNAME')"
                property="lastName">
            </el-table-column>

            <el-table-column
                property="createdAt"
                :label="$t('CREATED_AT')">
                <template slot-scope="scope">
                    {{ $d(new Date(scope.row.createdAt), 'long')}}
                </template>
            </el-table-column>

            <el-table-column
                label=""
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

        <!-- Mobile -->
        <el-table ref="TableGroupMembers" class="hide-md w-100"
            v-loading="isLoading"
            @selection-change="handleSelectionChange"
            :data="data"
            :empty-text="$t('NO_DATA_AVAILABLE')">

            <!-- <el-table-column
                type="selection"
                width="30">
            </el-table-column> -->

            <el-table-column
                :label="$t('MEMBERS')"
                :label-class-name="'font-brand'">
                <template slot-scope="scope">
                    <q-item>
                        <q-item-side icon="person" />

                        <q-item-main>
                            <q-item-tile label>{{scope.row.firstName}} {{scope.row.lastName}}</q-item-tile>
                            <q-item-tile sublabel class="color-light-gray fron--small">{{scope.row.createdAt}}</q-item-tile>
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
</template>

<script>
export default {
	name: 'TableGroupMembers',

	props: {
		data: {
			type: Array,
			required: true
		},

		group: {
			type: Object,
			required: false
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
		doDelete(row) {
			this.$store.dispatch('groups/DELETE_GROUP_MEMBER', { id: this.group.id, data: row }).then(response => {
				this.$q.notify({
					message: this.$t('DELETE_SUCCESS'),
					type: 'positive'
				});

				this.$emit('refresh');
			});
		},

		handleSelectionChange(val) {
			this.multipleSelection = val;
		},

		onDelete(row) {
			console.log('onDelete', row);

			if (this.isDeviceGreaterSM) {
				this.$confirm(this.$t('WOULD_YOU_LIKE_TO_DELETE_USER_FROM_GROUP', row.name), {
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
				const confirmObj = {
					title: this.$t('CONFIRM_DELETE'),
					message: this.$t('WOULD_YOU_LIKE_TO_DELETE_USER_FROM_GROUP'),
					ok: this.$t('OK'),
					cancel: this.$t('CANCEL')
				};

				this.$q
					.dialog(confirmObj)
					.then(response => {
						this.doDelete(row);
					})
					.catch(err => {
						console.log('not deleted');
					});
			}
		}
	}
};
</script>

<style lang="scss">
</style>
