<!--
@component:         TableNotifications
@environment:       Hyprid
@description:       This component can be used as a table as well as a list dependant on the property 'variant'.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-08-18
@modified:          2018-10-06
-->
<template>

    <div>
        <!-- ############# Desktop/Table ############# -->
        <div class="w-100" :class="{'hidden': variant !== 'table'}">
            <el-table
                :data="data"
                class="w-100"
                ref="TableNotifications"
                :empty-text="$t('NO_DATA_AVAILABLE')">

                <el-table-column
                    prop="title"
                    :label="$t('NAME')">
                </el-table-column>

                <el-table-column
                    prop="createdAt"
                    :label="$t('CREATED_AT')">
                    <template slot-scope="scope">
                        {{ $d(new Date(scope.row.createdAt), 'long') }}
                    </template>
                </el-table-column>

                <el-table-column
                    prop="sender_id"
                    :label="$t('CREATOR')">
                    <template slot-scope="scope">
                        <!-- <span v-if="scope.row.assignee && !isLoading">
                            {{getUserById(scope.row.assignee).lastName}}, {{getUserById(scope.row.assignee).firstName}}
                        </span> -->
                    </template>
                </el-table-column>
            </el-table>
        </div>

        <!-- ############# Mobile/List ############# -->
        <div class="d-block" :class="{'hidden': variant === 'table'}">
            <el-table
                :data="data"
                class="w-100"
                ref="TableNotifications"
                :show-header="false"
                :empty-text="$t('NO_DATA_AVAILABLE')">

                <el-table-column
                    label=""
                    :label-class-name="'font-brand'"
                    :class-name="'no-padding el-table-cell--no-padding'">
                    <template slot-scope="scope">
                        <q-item link :to="scope.row.link">

                            <q-item-main>
                                <q-item-tile sublabel class="color-gray font--small">{{scope.row.title}}</q-item-tile>
                                <q-item-tile label>{{scope.row.message}}</q-item-tile>
                                <q-item-tile sublabel class="color-gray font--smaller">
                                    {{ $d(new Date(scope.row.createdAt), 'long') }}
                                </q-item-tile>
                            </q-item-main>

                        </q-item>
                    </template>
                </el-table-column>
            </el-table>
        </div>

    </div>
</template>

<script>
export default {
	name: 'TableNotifications',

	props: {
		data: {
			type: Array,
			required: true
		},

		variant: {
			type: String,
			required: false,
			default: 'table'
		}
	},

	data() {
		return {};
	},

	methods: {}
};
</script>

<style lang="scss">
</style>
