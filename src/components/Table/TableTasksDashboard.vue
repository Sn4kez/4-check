<!--
@component:         TableTasksDashboard
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
        <!-- ############# Desktop ############# -->
        <div class="w-100" v-if="isDeviceGreaterSM">
            <el-table
                class="w-100"
                ref="TableTasksDashboard"
                :data="data"
                :empty-text="$t('NO_DATA_AVAILABLE')">

                <el-table-column
                    width="45"
                    :render-header="renderAttachmentsHeader">
                    <template slot-scope="scope">
                        <el-tooltip
                            class="item"
                            effect="dark"
                            :content="$t('ATTACHMENT_AVAILABLE')"
                            placement="top">
                            <q-icon v-if="scope.row.image" name="attach_file" class="m-r-half"></q-icon>
                        </el-tooltip>
                    </template>
                </el-table-column>

                <el-table-column
                    property="name"
                    :label="$t('NAME')"
                    show-overflow-tooltip
                    sortable>
                    <template slot-scope="scope">
                        <span class="d-block">{{scope.row.name}}</span>
                        <span class="d-block color-gray font--small">{{scope.row.description}}</span>
                    </template>
                </el-table-column>

                <el-table-column
                    :label="$t('STATE')"
                    property="state">
                    <template slot-scope="scope">
                        <span v-if="scope.row.state && !loading" class="font--bold">
                            <el-tag type="warning" v-if="getTaskStateById(scope.row.state).name === 'todo'">
                                {{ $t(getTaskStateById(scope.row.state).name.toUpperCase()) }}
                            </el-tag>
                            <el-tag type="success" v-else>
                                {{ $t(getTaskStateById(scope.row.state).name.toUpperCase()) }}
                            </el-tag>
                        </span>
                    </template>
                </el-table-column>

                <el-table-column
                    :label="$t('TYPE')"
                    property="type">
                    <template slot-scope="scope">
                        <span v-if="scope.row.type && !loading">
                            {{ $t(getTaskTypeById(scope.row.type).name.toUpperCase()) }}
                        </span>
                    </template>
                </el-table-column>

                <el-table-column
                    :label="$t('PRIORITY')"
                    property="priority">
                    <template slot-scope="scope">
                        <span v-if="scope.row.priority && !loading"
                            :class="{'color-danger font--bold': getTaskPriorityById(scope.row.priority).name === 'high'}">
                            {{ $t(getTaskPriorityById(scope.row.priority).name.toUpperCase()) }}
                        </span>
                    </template>
                </el-table-column>

                <el-table-column
                    :label="$t('TO_BE_DONE_BY')"
                    property="type">
                    <template slot-scope="scope">
                        <span v-if="scope.row.assignee && !loading">
                            {{ getUserById(scope.row.assignee).lastName}}, {{getUserById(scope.row.assignee).firstName }}
                        </span>
                    </template>
                </el-table-column>

                <el-table-column
                    property="doneAt"
                    :label="$t('DUE_DATE')"
                    sortable>
                    <template slot-scope="scope">
                        {{ $d(new Date(scope.row.doneAt), 'short') }}
                    </template>
                </el-table-column>

            </el-table>
        </div>

        <!-- ############# Mobile ############# -->
        <div v-if="!isDeviceGreaterSM">
            <el-table
                class="w-100"
                ref="TableTasksDashboard"
                :data="data"
                :empty-text="$t('NO_DATA_AVAILABLE')">

                <el-table-column
                    :label="$t('TASKS')"
                    :label-class-name="'font-brand'">
                    <template slot-scope="scope">
                        <q-item class="p-0">

                            <q-item-main>
                                <q-item-tile label>{{scope.row.name}}</q-item-tile>
                                <q-item-tile v-if="scope.row.description"
                                    sublabel
                                    class="color-gray fron--small">
                                    {{scope.row.description}}
                                </q-item-tile>
                            </q-item-main>

                            <q-item-side right color="green">
                                <span v-if="scope.row.state && !loading" class="font--bold">
                                    <el-tag type="warning" v-if="getTaskStateById(scope.row.state).name === 'todo'">
                                        {{ $t(getTaskStateById(scope.row.state).name.toUpperCase()) }}
                                    </el-tag>
                                    <el-tag type="success" v-else>
                                        {{ $t(getTaskStateById(scope.row.state).name.toUpperCase()) }}
                                    </el-tag>
                                </span>
                            </q-item-side>
                        </q-item>
                    </template>
                </el-table-column>
            </el-table>
        </div>

    </div>
</template>

<script>
import axios from 'axios';
import tasksMixin from '@/shared/mixins/tasks';
import usersMixin from '@/shared/mixins/users';

export default {
	name: 'TableTasksDashboard',

	mixins: [tasksMixin, usersMixin],

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
		company() {
			return this.$store.state.user.company;
		},

		isDeviceGreaterSM() {
			return this.$store.getters.IS_DEVICE_GREATER_SM;
		},

		taskPriorities() {
			return this.$store.getters['tasks/taskPriorities'];
		},

		taskStates() {
			return this.$store.getters['tasks/taskStates'];
		},

		taskTypes() {
			return this.$store.getters['tasks/taskTypes'];
		}
	},

	data() {
		return {
			loading: false
		};
	},

	mounted() {
		this.init();
	},

	methods: {
		getState(row) {
			let state = '';
			if (row.state) {
				state = this.getTaskStateById(row.state);
			}

			return state;
		},

		init() {
			this.requestInitalData();
		},

		renderAttachmentsHeader(h, { column, $index }) {
			return h('span', [
				column.label,
				h('q-icon', {
					props: {
						name: 'attach_file'
					}
				})
			]);
		},

		requestInitalData() {
			const REQUEST = [];
			this.loading = true;

			if (!this.taskPriorities.length) {
				REQUEST.push(this.requestTasksPriorities(this.company.id));
			}

			if (!this.taskStates.length) {
				REQUEST.push(this.requestTasksStates(this.company.id));
			}

			if (!this.taskTypes.length) {
				REQUEST.push(this.requestTasksTypes(this.company.id));
			}

			axios
				.all(REQUEST)
				.then(
					axios.spread((...results) => {
						this.loading = false;
					})
				)
				.catch(err => {
					//
				});
		}
	}
};
</script>

<style lang="scss">
</style>
