<!--
@component:         TableDirectoriesArchives
@environment:       Hyprid
@description:       This component handle the visibility for mobile and desktop devices
                    as well as the data storage for child components.
                    On desktop we show a normal table while on mobile a list is rendered.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-09-26
@modified:          2018-10-14
-->
<template>
    <div>
        <!-- ############# Desktop ############# -->
        <div class="w-100" v-if="isDeviceGreaterSM">
            <el-table ref="TableDirectoriesArchives" class="w-100"
                v-loading="isLoading"
                @selection-change="handleSelectionChange"
                :empty-text="$t('NO_DATA_AVAILABLE')"
                :data="data">

                <el-table-column
                    type="selection"
                    width="55">
                </el-table-column>

                <el-table-column
                    :label="$t('NAME')"
                    show-overflow-tooltip>
                    <template slot-scope="scope">
                        <!-- Directory -->
                        <div class="flex color-gray" v-if="scope.row.objectType === 'archive'">
                            <q-icon
                                class="color-gray font--big"
                                name="folder_open">
                            </q-icon>
                            <div class="m-l-half">
                                <router-link
                                    :to="{path: '/checklists/archive/' + scope.row.object.id}"
                                    class="color-gray">
                                    {{scope.row.object.name}}
                                </router-link>
                            </div>
                        </div>
                        <!-- Checklist -->
                        <div class="flex color-gray" v-else>
                            <q-icon
                                class="color-gray font--big"
                                name="check_circle_outline">
                            </q-icon>
                            <div class="m-l-half">
                                {{scope.row.object.name}}
                            </div>
                        </div>
                    </template>
                </el-table-column>

                <el-table-column
                    :label="$t('DESCRIPTION')"
                    show-overflow-tooltip>
                    <template slot-scope="scope">
                        {{scope.row.object.description}}
                    </template>
                </el-table-column>

                <el-table-column
                    :label="$t('PATH')"
                    show-overflow-tooltip>
                    <template slot-scope="scope">
                        <ul class="list list--horizontal flex--wrap valign-center">
                            <li class="list__item font--small" style="margin-right: 0.2rem;">{{rootDirectory.name}}</li>
                            <li v-if="scope.row.object.path.length"
                                class="list__item font--small" style="margin-right: 0.2rem;"
                                v-for="(path, index) in scope.row.object.path" :key="index">
                                <span>/</span>
                                {{path.name}}
                            </li>
                        </ul>
                    </template>
                </el-table-column>

                <el-table-column
                    :label="$t('LAST_MODIFIED')"
                    show-overflow-tooltip>
                    <template slot-scope="scope">
                        {{ $d(new Date(scope.row.updatedAt), 'short')}}
                    </template>
                </el-table-column>

                <el-table-column
                    :label="$t('LAST_MODIFIED_BY')"
                    show-overflow-tooltip>
                    <template slot-scope="scope">
                        <span v-if="scope.row.object.lastUpdatedBy && !isLoading">
                            {{getUserById(scope.row.object.lastUpdatedBy).lastName}}, {{getUserById(scope.row.object.lastUpdatedBy).firstName}}
                        </span>
                    </template>
                </el-table-column>

                <el-table-column
                    :label="$t('CREATED_BY')"
                    show-overflow-tooltip>
                    <template slot-scope="scope">
                        <span v-if="scope.row.object.createdBy && !isLoading">
                            {{getUserById(scope.row.object.createdBy).lastName}}, {{getUserById(scope.row.object.createdBy).firstName}}
                        </span>
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
                                    <a href="#" @click.prevent="onRestore(scope.row)">{{$t('RESTORE')}}</a>
                                </el-dropdown-item>
                            </el-dropdown-menu>
                        </el-dropdown>
                    </template>
                </el-table-column>
            </el-table>
        </div>

        <!-- ############# Mobile ############# -->
        <div v-if="!isDeviceGreaterSM">
            <el-table ref="TableDirectoriesArchives" class="w-100"
                v-loading="isLoading"
                @selection-change="handleSelectionChange"
                :empty-text="$t('NO_DATA_AVAILABLE')"
                :data="data">

                <el-table-column
                    type="selection"
                    width="55">
                </el-table-column>

                <el-table-column
                    :label="$t('NAME')"
                    :label-class-name="'font-brand'">
                    <template slot-scope="scope">
                        <q-item class="p-0">
                            <q-item-side>
                                <router-link
                                    :to="{path: '/checklists/archive/' + scope.row.object.id}"
                                    class="color-gray">
                                    <q-icon
                                        class="color-gray font--big"
                                        name="archive">
                                    </q-icon>
                                </router-link>
                            </q-item-side>

                            <q-item-main>
                                <router-link
                                    :to="{path: '/checklists/archive/' + scope.row.object.id}"
                                    class="color-gray">
                                    <q-item-tile label>{{scope.row.object.name}}</q-item-tile>
                                    <q-item-tile sublabel class="color-gray fron--small">
                                        {{scope.row.object.description}}
                                    </q-item-tile>
                                </router-link>
                            </q-item-main>

                            <q-item-side right color="green">
                                <el-dropdown trigger="click">
                                    <span class="el-dropdown-link">
                                        <q-icon name="more_horiz" size="2rem"></q-icon>
                                    </span>
                                    <el-dropdown-menu slot="dropdown">
                                        <el-dropdown-item>
                                            <a href="#" @click.prevent="onRestore(scope.row)">{{$t('RESTORE')}}</a>
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
import commonMixin from '@/shared/mixins/common';
import usersMixin from '@/shared/mixins/users';

export default {
	name: 'TableDirectoriesArchives',

	mixins: [commonMixin, usersMixin],

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

		rootDirectory() {
			return this.$store.state.archives.rootDirectory;
		}
	},

	data() {
		return {
			multipleSelection: []
		};
	},

	mounted() {
		this.init();
	},

	methods: {
		handleSelectionChange(val) {
			this.multipleSelection = val;
			this.$emit('change-selection', this.multipleSelection);
		},

		init() {
			console.log('TableDirectoriesArchives', this.data);
		},

		doRestore(row) {
			if (row.objectType === 'directory') {
				this.$store.dispatch('directories/RESTORE_DIRECTORY', row.object).then(() => {
					this.$q.notify({
						message: this.$t('RESTORE_SUCCESS'),
						type: 'positive'
					});
					this.$emit('refresh');
				});
			}
			if (row.objectType === 'checklist') {
				this.$store.dispatch('checklists/RESTORE_DIRECTORY', row.object).then(() => {
					this.$q.notify({
						message: this.$t('RESTORE_SUCCESS'),
						type: 'positive'
					});
					this.$emit('refresh');
				});
			}
		},

		onRestore(row) {
			console.log('onRestore', row.id);
			const DATA = {
				id: row.id,
				company: this.company.id
			};

			this.$store
				.dispatch('directories/RESTORE_DIRECTORY', DATA)
				.then(response => {
					if (response.status === 204) {
						this.$q.notify({
							message: this.$t('RESTORE_SUCCESS'),
							type: 'positive'
						});
						this.$emit('refresh');
					} else {
						this.handleErrors(response);
					}
				})
				.catch(err => {
					this.handleErrors(err);
				});
		}
	}
};
</script>

<style lang="scss">
</style>
