<!--
@component:         TableTasks
@environment:       Hyprid
@description:       This component handle the visibility for mobile and desktop devices
                    as well as the data storage for child components.
                    On desktop we show a normal table while on mobile a list is rendered.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-07-09
@modified:          2018-09-06
-->
<template>
    <div>
        <!-- ############# Desktop ############# -->
        <div class="w-100" v-if="isDeviceGreaterSM">
            <el-table ref="TableTasks" class="w-100"
                @selection-change="handleSelectionChange"
                :data="data"
                :empty-text="$t('NO_DATA_AVAILABLE')">

                <el-table-column
                    type="selection"
                    width="55">
                </el-table-column>

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
                        {{scope.row.name}}
                    </template>
                </el-table-column>

                <el-table-column
                    property="description"
                    :label="$t('DESCRIPTION')"
                    show-overflow-tooltip>
                </el-table-column>

                <el-table-column
                    :label="$t('PRIORITY')"
                    sortable
                    property="priority">
                    <template slot-scope="scope">
                        <span v-if="scope.row.priority && !isLoading">
                            {{getTaskPriorityById(scope.row.priority).name}}
                        </span>
                    </template>
                </el-table-column>

                <el-table-column
                    :label="$t('STATE')"
                    sortable
                    property="state">
                    <template slot-scope="scope">
                        <q-btn v-if="getState(scope.row).name === 'todo'"
                            flat
                            icon="radio_button_unchecked"
                            :label="$t('OPEN')"
                            @click="onChangeState(scope.row)">
                        </q-btn>
                        <q-btn v-else
                            flat
                            icon="check_circle_outline"
                            :label="$t('DONE')"
                            @click="onChangeState(scope.row)">
                        </q-btn>
                    </template>
                </el-table-column>

                <el-table-column
                    :label="$t('TYPE')"
                    :sortable="true"
                    :sort-method="(a, b) => sortingMethod(a, b)"
                    property="type">
                    <template slot-scope="scope">
                        <span v-if="scope.row.type && !isLoading">{{getTaskTypeById(scope.row.type).name}}</span>
                    </template>
                </el-table-column>

                <el-table-column
                    :label="$t('TO_BE_DONE_BY')"
                    sortable
                    property="type">
                    <template slot-scope="scope">
                        <span v-if="scope.row.assignee && !isLoading">
                            {{getUserById(scope.row.assignee).lastName}}, {{getUserById(scope.row.assignee).firstName}}
                        </span>
                    </template>
                </el-table-column>

                <el-table-column
                    property="doneAt"
                    sortable
                    :label="$t('DUE_DATE')">
                    <template slot-scope="scope">
                        {{ $d(new Date(scope.row.doneAt), 'long') }}
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
            <el-table ref="TableTasks" class="w-100"
                @selection-change="handleSelectionChange"
                :data="data"
                :empty-text="$t('NO_DATA_AVAILABLE')">

                <el-table-column
                    type="selection"
                    width="30">
                </el-table-column>

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
                                <el-dropdown trigger="click">
                                    <span class="el-dropdown-link">
                                        <q-icon name="more_horiz" size="2rem"></q-icon>
                                    </span>
                                    <el-dropdown-menu slot="dropdown">
                                        <el-dropdown-item>
                                            <a v-if="getState(scope.row).name === 'todo'"
                                                href="#" @click.prevent="onChangeState(scope.row)">
                                                <q-icon name="radio_button_unchecked"></q-icon>
                                                {{$t('OPEN')}}
                                            </a>
                                            <a v-else
                                                href="#" @click.prevent="onChangeState(scope.row)">
                                                <q-icon name="check_circle_outline"></q-icon>
                                                {{$t('DONE')}}
                                            </a>
                                        </el-dropdown-item>
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
import tasksMixin from '@/shared/mixins/tasks';
import usersMixin from '@/shared/mixins/users';


export default {
	name: 'TableTasks',

	mixins: [tasksMixin, usersMixin],

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
		getState(row) {
			let state = '';
			if (row.state) {
				state = this.getTaskStateById(row.state);
			}

			return state;
		},

		handleSelectionChange(val) {
			this.multipleSelection = val;
			console.log('handleselcet', this.multipleSelection);
			this.$emit('change-selection', this.multipleSelection);
		},

		onChangeState(row) {
			const state = this.getTaskStateById(row.state);
			let newState = this.getTaskStateByName('done');

			if (state.label === 'done') {
				newState = this.getTaskStateByName('todo');
			}
			row.state = newState.id;
			delete row.image;

			this.doUpdateTask(row).then(response => {
				if (response.status === 201 || response.status === 204) {
					this.$q.notify({
						message: this.$t('SAVE_SUCCESS'),
						type: 'positive'
					});

					this.$emit('refresh');
				}
			});
		},

		doDelete(task) {
			this.$store.dispatch('tasks/DELETE_TASK', task).then(() => {
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
				this.$confirm(this.$t('WOULD_YOU_LIKE_TO_DELETE_TASK', row.name), {
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
						message: this.$t('WOULD_YOU_LIKE_TO_DELETE_TASK', row.name),
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
			console.log('onEdit tasks', row.id);

			if (this.isDeviceGreaterSM) {
				this.$store.commit('OPEN_DIALOG', {
					title: this.$t('EDIT_TASK'),
					loadComponent: 'Form/FormEditTask',
					width: '50%',
					data: row
				});
			} else {
				this.$store.commit('OPEN_MODAL', {
					title: this.$t('EDIT_TASK'),
					loadComponent: 'Form/FormEditTask',
					maximized: true,
					data: row
				});
			}
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

        sortingMethod(a, b) {

            let atype = this.getTaskTypeById(a.type);
            let btype = this.getTaskTypeById(b.type);

            if(atype == undefined || btype == undefined){
                return -1;
            }

            atype = atype.name;
            btype = btype.name;

            if(atype < btype){
                return -1;
            }

            if(atype > btype){
                return 1;
            }

            return 0;
        }
	}
};
</script>

<style lang="scss">
</style>
