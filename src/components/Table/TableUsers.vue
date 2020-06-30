<template>
    <div>
        <!-- Desktop -->
        <div class="hide-sm w-100">
            <el-table ref="TableUsers" class="w-100"
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
                        <span>{{ scope.row.firstName }}</span>
                    </template>
                </el-table-column>

                <el-table-column
                    :label="$t('LASTNAME')"
                    property="lastName">
                </el-table-column>

                <el-table-column
                    property="role"
                    :label="$t('ROLE')"
                    show-overflow-tooltip>
                </el-table-column>

                <el-table-column
                    property="createdAt"
                    :label="$t('CREATED_AT')">
                    <template slot-scope="scope">
                        {{$d(new Date(scope.row.createdAt), 'long')}}
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

        <!-- Mobile -->
        <div class="hide-md d-block--sm">
            <el-table ref="TableUsers" class="w-100"
                v-loading="isLoading"
                @selection-change="handleSelectionChange"
                :data="data"
                :empty-text="$t('NO_DATA_AVAILABLE')">

                <!-- <el-table-column
                    type="selection"
                    width="30">
                </el-table-column> -->

                <el-table-column
                    :label="$t('USER')"
                    :label-class-name="'font-brand'">
                    <template slot-scope="scope">
                        <q-item>
                            <q-item-side icon="person" />

                            <q-item-main>
                                <q-item-tile label>{{scope.row.firstName}} {{scope.row.lastName}}</q-item-tile>
                                <q-item-tile sublabel class="color-light-gray fron--small">
                                    {{$d(new Date(scope.row.createdAt), 'long')}}
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
export default {
	name: 'TableUsers',

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
			multipleSelection: []
		};
	},

	methods: {
		handleSelectionChange(val) {
			this.multipleSelection = val;
		},

		doDelete(user) {
			this.$store.dispatch('users/DELETE_USER', user).then(() => {
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
				this.$confirm(this.$t('WOULD_YOU_LIKE_TO_DELETE_USER', row.firstName + ' ' + row.lastName), {
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
						message: this.$t('WOULD_YOU_LIKE_TO_DELETE_USER', row.firstName + ' ' + row.lastName),
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
			console.log(row.id);

			this.$router.push({ path: '/settings/user/profile/' + row.id });
		}
	}
};
</script>

<style lang="scss">
</style>
