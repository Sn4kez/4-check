<!--
@component:         TableLatestAudits
@environment:       Hyprid
@description:       This component handle the visibility for mobile and desktop devices
                    as well as the data storage for child components.
                    On desktop we show a normal table while on mobile a list is rendered.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-10-05
@modified:          2018-10-15
-->
<template>
    <div>
        <!-- Desktop -->
        <div v-if="isDeviceGreaterSM">
            <el-table
                class="w-100"
                ref="TableLatestAudits"
                v-loading="isLoading"
                :data="data"
                :empty-text="$t('NO_DATA_AVAILABLE')">

                <el-table-column
                    property="checklistName"
                    :label="$t('LATEST_AUDITS')"
                    show-overflow-tooltip>
                    <template slot-scope="scope">
                        <router-link :to="'/audit/edit/' + scope.row.id">
                            {{scope.row.checklistName}}
                        </router-link>
                    </template>
                </el-table-column>

                <el-table-column
                    :label="$t('DIRECTORY')"
                    show-overflow-tooltip>
                    <template slot-scope="scope">
                        <ul class="list list--horizontal">
                            <li v-for="(item, index) in scope.row.checklistPath" :key="index">
                                <span v-if="scope.row.checklistPath.length">{{item}}</span>
                                <span v-if="index + 1 < scope.row.checklistPath.length" class="m-l-half">/</span>
                            </li>
                            <li v-if="!scope.row.checklistPath.length">Home</li>
                        </ul>
                    </template>
                </el-table-column>

                <el-table-column
                    property="state"
                    :label="$t('STATE')"
                    align="right">
                    <template slot-scope="scope">
                        <el-tag
                            v-if="getAuditStateById(scope.row.state).name === 'draft'">
                            {{ $t(getAuditStateById(scope.row.state).name.toUpperCase()) }}
                        </el-tag>

                        <el-tag type="success"
                            v-if="getAuditStateById(scope.row.state).name === 'approved'">
                            {{ $t(getAuditStateById(scope.row.state).name.toUpperCase()) }}
                        </el-tag>

                        <el-tag type="warning"
                            v-if="getAuditStateById(scope.row.state).name === 'sync needed'">
                            {{ $t(getAuditStateById(scope.row.state).name.toUpperCase()) }}
                        </el-tag>

                        <el-tag type="info"
                            v-if="getAuditStateById(scope.row.state).name === 'finished'">
                            {{ $t(getAuditStateById(scope.row.state).name.toUpperCase()) }}
                        </el-tag>

                        <el-tag type="danger"
                            v-if="getAuditStateById(scope.row.state).name === 'awaiting approval'">
                            {{ $t(getAuditStateById(scope.row.state).name.toUpperCase()) }}
                        </el-tag>
                    </template>
                </el-table-column>
            </el-table>
        </div>

        <!-- Mobile -->
        <div v-if="!isDeviceGreaterSM">
            <el-table
                class="w-100"
                ref="TableLatestAudits"
                v-loading="isLoading"
                :data="data"
                :empty-text="$t('NO_DATA_AVAILABLE')"
                :cell-class-name="'el-table-cell--no-padding'">

                <el-table-column
                    :label="$t('LATEST_AUDITS')"
                    :label-class-name="'font-brand'">
                    <template slot-scope="scope">
                        <q-item>
                            <q-item-main>
                                <q-item-tile label>
                                    <router-link :to="'/audit/edit/' + scope.row.id">
                                        {{scope.row.checklistName}}
                                    </router-link>
                                </q-item-tile>
                                <q-item-tile
                                    sublabel
                                    class="color-gray fron--small">
                                    Home
                                </q-item-tile>
                            </q-item-main>

                            <q-item-side right color="green">
                                <el-tag
                                    v-if="getAuditStateById(scope.row.state).name === 'draft'">
                                    {{ $t(getAuditStateById(scope.row.state).name.toUpperCase()) }}
                                </el-tag>

                                <el-tag type="success"
                                    v-if="getAuditStateById(scope.row.state).name === 'approved'">
                                    {{ $t(getAuditStateById(scope.row.state).name.toUpperCase()) }}
                                </el-tag>

                                <el-tag type="warning"
                                    v-if="getAuditStateById(scope.row.state).name === 'sync needed'">
                                    {{ $t(getAuditStateById(scope.row.state).name.toUpperCase()) }}
                                </el-tag>

                                <el-tag type="info"
                                    v-if="getAuditStateById(scope.row.state).name === 'finished'">
                                    {{ $t(getAuditStateById(scope.row.state).name.toUpperCase()) }}
                                </el-tag>

                                <el-tag type="danger"
                                    v-if="getAuditStateById(scope.row.state).name === 'awaiting approval'">
                                    {{ $t(getAuditStateById(scope.row.state).name.toUpperCase()) }}
                                </el-tag>
                            </q-item-side>
                        </q-item>
                    </template>
                </el-table-column>
            </el-table>
        </div>
    </div>
</template>

<script>
import auditsMixins from '@/shared/mixins/audits';

export default {
	name: 'TableLatestAudits',

	mixins: [auditsMixins],

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
		init() {
			console.log('TableLatestAudits mounted', this.data);
		}
	}
};
</script>

