<template>
    <div>
        <!-- Desktop -->
        <el-table
            ref="TableGroups"
            class="hide-sm w-100"
            v-loading="isLoading"
            @selection-change="handleSelectionChange"
            :data="data"
            :empty-text="$t('NO_DATA_AVAILABLE')">

            <!-- <el-table-column
                type="selection"
                width="55">
            </el-table-column> -->

            <el-table-column
                :label="$t('NAME')">
                <template slot-scope="scope">
                    <span>{{ scope.row.name }}</span>
                </template>
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

        <!-- Mobile -->
        <el-table
            ref="TableGroups"
            class="hide-md w-100"
            v-loading="isLoading"
            @selection-change="handleSelectionChange"
            :data="data"
            :empty-text="$t('NO_DATA_AVAILABLE')">

            <!-- <el-table-column
                type="selection"
                width="30">
            </el-table-column> -->

            <el-table-column
                :label="$t('GROUPS')"
                :label-class-name="'font-brand'">
                <template slot-scope="scope">
                    <q-item>
                        <q-item-side icon="group" />

                        <q-item-main>
                            <q-item-tile label>{{scope.row.name}}</q-item-tile>
                            <q-item-tile sublabel class="color-light-gray fron--small">
                                {{ $d(new Date(scope.row.createdAt), 'long')}}
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
</template>

<script>
export default {
	name: 'TableGroups',

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
		doDelete(group) {
			this.$store.dispatch('groups/DELETE_GROUP', group).then(() => {
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
				this.$confirm(this.$t('WOULD_YOU_LIKE_TO_DELETE_GROUP', row.name), {
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
					message: this.$t('WOULD_YOU_LIKE_TO_DELETE_GROUP'),
					ok: this.$t('OK'),
					cancel: this.$t('CANCEL')
				};

				this.$q
					.dialog(confirmObj)
					.then(response => {
						this.doDelete(row);
					})
					.catch(err => {
						console.log('cancel', row);
					});
			}
		},

		onEdit(row) {
			console.log(row);

			this.$router.push({ name: 'EditGroupView', params: { id: row.id } });

			// if (this.isDeviceGreaterSM) {
			// 	this.$store.commit('OPEN_DIALOG', {
			// 		title: this.$t('EDIT_USER'),
			// 		loadComponent: 'form/FormEditUser',
			// 		data: row
			// 	});
			// } else {
			// 	this.$store.commit('OPEN_MODAL', {
			// 		title: this.$t('EDIT_USER'),
			// 		loadComponent: 'form/FormEditUser',
			// 		maximized: true,
			// 		data: row
			// 	});
			// }
		}
	}
};
</script>

<style lang="scss">
</style>
