<!--
@component:         TableNextAudits
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
                ref="TableNextAudits"
                v-loading="isLoading"
                :data="data"
                :empty-text="$t('NO_DATA_AVAILABLE')">

                <el-table-column
                    property="checklistName"
                    :label="$t('NEXT_AUDITS')">
                    <template slot-scope="scope">
                        <router-link :to="'/checklists/checklist/' + scope.row.checklist">
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
                    label=""
                    align="right">
                    <template slot-scope="scope">
                        <q-btn
                            :to="{path: '/audit/create', query: {'checklist': scope.row.checklist}}"
                            flat
                            color="primary"
                            :label="$t('START_AUDIT')">
                        </q-btn>
                    </template>
                </el-table-column>
            </el-table>
        </div>

        <!-- Mobile -->
        <div v-if="!isDeviceGreaterSM">
            <el-table
                class="w-100"
                ref="TableNextAudits"
                v-loading="isLoading"
                :data="data"
                :empty-text="$t('NO_DATA_AVAILABLE')"
                :cell-class-name="'el-table-cell--no-padding'">

                <el-table-column
                    :label="$t('NEXT_AUDITS')"
                    :label-class-name="'font-brand'">
                    <template slot-scope="scope">
                        <q-item>
                            <q-item-main>
                                <q-item-tile label>
                                    <router-link :to="'/checklists/checklist/' + scope.row.checklist">
                                        {{scope.row.checklistName}}
                                    </router-link>
                                </q-item-tile>
                                <q-item-tile
                                    sublabel
                                    class="color-gray fron--small">
                                    Home
                                </q-item-tile>
                            </q-item-main>

                            <q-item-side right>
                                <q-btn
                                    :to="{path: '/audit/create', query: {'checklist': scope.row.checklist}}"
                                    flat
                                    color="primary"
                                    :label="$t('START_AUDIT')">
                                </q-btn>
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
	name: 'TableNextAudits',

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
			console.log('TableNextAudits mounted', this.data);
		}
	}
};
</script>
