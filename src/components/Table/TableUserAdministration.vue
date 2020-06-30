<!--
@component:         TableUserAdministration
@environment:       Hyprid
@description:       This component handle the visibility for mobile and desktop devices
                    as well as the data storage for child components.
                    On desktop we show a normal table while on mobile a list is rendered.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-09-15
@modified:          2018-09-15
-->
<template>
    <div>
        <!-- ############# Desktop ############# -->
        <div class="hide-sm w-100">
            <el-table ref="TableUserAdministration" class="w-100"
                v-loading="isLoading"
                :data="data"
                :empty-text="$t('NO_DATA_AVAILABLE')">

                <el-table-column
                    :label="$t('FIRSTNAME')"
                    property="firstName"
                    sortable>
                    <template slot-scope="scope">
                        <span>{{ scope.row.firstName }}</span>
                    </template>
                </el-table-column>

                <el-table-column
                    :label="$t('LASTNAME')"
                    property="lastName"
                    sortable>
                </el-table-column>

                <el-table-column
                    :label="$t('COMPANY')"
                    property="companyId"
                    sortable>
                    <template slot-scope="scope">
                        {{getCompanyById(scope.row.companyId).name}}
                    </template>
                </el-table-column>

                <el-table-column
                    property="createdAt"
                    :label="$t('CREATED_AT')"
                    sortable>
                    <template slot-scope="scope">
                        {{$d(new Date(scope.row.createdAt), 'short')}}
                    </template>
                </el-table-column>

                <el-table-column
                    property="isActive"
                    :label="$t('STATE')"
                    sortable>
                    <template slot-scope="scope">
                        <span v-if="scope.row.isActive">{{ $t('ACTIVE') }}</span>
                        <span v-else>{{ $t('INACTIVE') }}</span>
                    </template>
                </el-table-column>

                <el-table-column
                    property="role"
                    :label="$t('ROLE')"
                    sortable>
                </el-table-column>

                <el-table-column
                    property="updatedAt"
                    :label="$t('LAST_ACTIVE_ON')"
                    sortable>
                    <template slot-scope="scope">
                        {{$d(new Date(scope.row.updatedAt), 'short')}}
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
                            </el-dropdown-menu>
                        </el-dropdown>
                    </template>
                </el-table-column>
            </el-table>
        </div>

        <!-- ############# Mobile ############# -->
        <div class="hide-md d-block--sm">
            <el-table ref="TableUserAdministration" class="w-100"
                v-loading="isLoading"
                :data="data"
                :empty-text="$t('NO_DATA_AVAILABLE')">

                <el-table-column
                    :label="$t('USER')"
                    :label-class-name="'font-brand'">
                    <template slot-scope="scope">
                        <q-item>
                            <q-item-side icon="person" />

                            <q-item-main>
                                <q-item-tile label>{{scope.row.firstName}} {{scope.row.lastName}}</q-item-tile>
                                <q-item-tile sublabel class="color-light-gray font--regular">{{getCompanyById(scope.row.companyId).name}}</q-item-tile>
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
import companiesMixins from '@/shared/mixins/companies';

export default {
	name: 'TableUserAdministration',

	mixins: [companiesMixins],

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
		return {};
	},

	methods: {
		onEdit(row) {
			console.log(row.id);

			if (this.isDeviceGreaterSM) {
				this.$store.commit('OPEN_DIALOG', {
					title: this.$t('COMPANYSETTINGS'),
					loadComponent: 'AdminCompanySettings',
					width: '70%',
					data: row
				});
			} else {
				this.$store.commit('OPEN_MODAL', {
					title: this.$t('COMPANYSETTINGS'),
					loadComponent: 'AdminCompanySettings',
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
