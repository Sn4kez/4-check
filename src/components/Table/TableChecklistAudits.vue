<!--
@component:         TableChecklistAudits
@environment:       Hyprid
@description:       This component handle the visibility for mobile and desktop devices
                    as well as the data storage for child components.
                    On desktop we show a normal table while on mobile a list is rendered.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-09-27
@modified:          2018-10-11
-->
<template>
    <div>
        <!-- ############# Desktop ############# -->
        <div class="w-100" v-if="isDeviceGreaterSM">
            <el-table ref="TableChecklistAudits" class="w-100"
                      v-loading="isLoading"
                      :empty-text="$t('NO_DATA_AVAILABLE')"
                      :data="data">

                <el-table-column
                    type="index">
                </el-table-column>

                <el-table-column
                    label="">
                    <template slot-scope="scope">
                        <router-link :to="{path: '/audit/edit/' + scope.row.id }" replace>{{ $t('AUDIT_DETAILS') }}
                        </router-link>
                    </template>
                </el-table-column>

                <el-table-column
                    :label="$t('EXECUTION_AT')"
                    prop="updatedAt"
                    sortable
                    show-overflow-tooltip>
                    <template slot-scope="scope">
                        <span v-if="scope.row.updatedAt">{{ $d(new Date(scope.row.updatedAt), 'long')}}</span>
                        <span v-else>&mdash;</span>
                    </template>
                </el-table-column>

                <el-table-column
                    :label="$t('EXECUTION_DUE')"
                    prop="executionDue"
                    show-overflow-tooltip>
                    <template slot-scope="scope">
                        <!-- &mdash; -->
                        {{ $d(new Date(scope.row.executionDue), 'long')}}
                    </template>
                </el-table-column>

                <el-table-column
                    :label="$t('USER')"
                    prop="lastName"
                    show-overflow-tooltip>
                    <template slot-scope="scope">
                        <span v-if="scope.row.user">
                            {{getUserById(scope.row.user).lastName}}, {{getUserById(scope.row.user).firstName}}
                        </span>
                    </template>
                </el-table-column>

                <el-table-column
                    :label="$t('STATE')"
                    show-overflow-tooltip>
                    <template slot-scope="scope">
                        <span v-if="scope.row.state">
                            <el-tag
                                v-if="getAuditStateById(scope.row.state).name === 'draft'">
                                {{ $t(getAuditStateById(scope.row.state).name) }}
                            </el-tag>

                            <el-tag type="success"
                                    v-if="getAuditStateById(scope.row.state).name === 'approved'">
                                {{ $t(getAuditStateById(scope.row.state).name) }}
                            </el-tag>

                            <el-tag type="warning"
                                    v-if="getAuditStateById(scope.row.state).name === 'sync needed'">
                                {{ $t(getAuditStateById(scope.row.state).name) }}
                            </el-tag>

                            <el-tag type="info"
                                    v-if="getAuditStateById(scope.row.state).name === 'finished'">
                                {{ $t(getAuditStateById(scope.row.state).name) }}
                            </el-tag>

                            <el-tag type="danger"
                                    v-if="getAuditStateById(scope.row.state).name === 'awaiting approval'">
                                {{ $t(getAuditStateById(scope.row.state).name) }}
                            </el-tag>
                        </span>
                    </template>
                </el-table-column>

                <!-- Actions -->
                <el-table-column width="39">
                    <template slot-scope="scope">
                        <span @click="getPdfFile(scope.row.id)">
                            <q-icon name="far fa-file-pdf" />
                        </span>
                    </template>
                </el-table-column>
            </el-table>
        </div>

        <!-- ############# Mobile ############# -->
        <div v-if="!isDeviceGreaterSM">
            <el-table ref="TableChecklistAudits" class="w-100"
                      v-loading="isLoading"
                      :data="data"
                      :empty-text="$t('NO_DATA_AVAILABLE')">

                <el-table-column
                    :label="$t('NAME')"
                    :label-class-name="'font-brand'">
                    <template slot-scope="scope">
                        <q-item multiline class="p-0" link :to="{path: '/audit/edit/' + scope.row.id }" replace>
                            <q-item-main>
                                <q-item-tile label lines="1">{{ $t('AUDIT_DETAILS') }}</q-item-tile>
                                <q-item-tile sublabel lines="2">
                                    {{ $d(new Date(scope.row.createdAt), 'long')}} {{$t('BY')}}
                                    {{getUserById(scope.row.user).lastName}}, {{getUserById(scope.row.user).firstName}}
                                </q-item-tile>
                            </q-item-main>

                            <q-item-side right>
                                <span v-if="scope.row.state">
                                    <el-tag
                                        v-if="getAuditStateById(scope.row.state).name === 'draft'">
                                        {{ $t(getAuditStateById(scope.row.state).name) }}
                                    </el-tag>

                                    <el-tag type="success"
                                            v-if="getAuditStateById(scope.row.state).name === 'approved'">
                                        {{ $t(getAuditStateById(scope.row.state).name) }}
                                    </el-tag>

                                    <el-tag type="warning"
                                            v-if="getAuditStateById(scope.row.state).name === 'sync needed'">
                                        {{ $t(getAuditStateById(scope.row.state).name) }}
                                    </el-tag>

                                    <el-tag type="info"
                                            v-if="getAuditStateById(scope.row.state).name === 'finished'">
                                        {{ $t(getAuditStateById(scope.row.state).name) }}
                                    </el-tag>

                                    <el-tag type="danger"
                                            v-if="getAuditStateById(scope.row.state).name === 'awaiting approval'">
                                        {{ $t(getAuditStateById(scope.row.state).name) }}
                                    </el-tag>
                                </span>
                            </q-item-side>
                            <q-item-side right>
                                <span @click="getPdfFile(scope.row.id)">
                                    <q-icon name="far fa-file-pdf" />
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
	import auditMixins from '@/shared/mixins/audits';
	import usersMixin from '@/shared/mixins/users';
    import API from '@/config/api';
	import axios from 'axios';

	export default {
		name: 'TableChecklistAudits',

		mixins: [auditMixins, usersMixin],

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
			doDelete(item) {
				this.$store.dispatch('grants/DELETE_GRANT', item).then(() => {
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
					this.$confirm(this.$t('WOULD_YOU_LIKE_TO_DELETE_PERMISSIONS', row.name), {
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
							message: this.$t('WOULD_YOU_LIKE_TO_DELETE_PERMISSIONS', row.name),
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

			async getPdfFile(id) {
				this.$emit('loading', true);
				let response = await API.get(`/export/audit/pdf/${id}`);
				this.$emit('loading', false);
                let url = response.data.response;

				const link = document.createElement('a');
				link.download = 'audit.pdf';
				link.target = '_blank';
				link.href = url;
				document.body.appendChild(link);
				link.click();
				link.remove();
			}
		}
	};
</script>

<style lang="scss" scoped>
    .q-icon.far.fa-file-pdf {
        cursor: pointer;
        transition: color 0.3s linear;
        color: #979797;
        font-size: 25px;
        &:hover {
            color: #b9b9b9;
        }
    }
</style>
