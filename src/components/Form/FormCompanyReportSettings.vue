<template>
    <el-form class="company-report-settings">

        <!-- Header -->
        <el-row>
            <el-col>
                <h4 class="headline p-b-small b-b">{{$t('HEADER')}}</h4>
            </el-col>
        </el-row>

        <el-row :gutter="20" class="m-t-1">
            <el-col :xs="24" :md="6">
                <q-field class="m-b-1">
                    <q-select
                        v-model="form.logoPosition"
                        :options="alignmentOptions"
                        :stack-label="$t('ALIGNMENT_LOGO')"
                    />
                </q-field>
            </el-col>
        </el-row>

        <!-- Footer -->
        <el-row class="m-t-3">
            <el-col>
                <h4 class="headline p-b-small b-b">{{$t('FOOTER')}}</h4>
            </el-col>
        </el-row>

        <el-row :gutter="20" class="m-t-1">
            <el-col :xs="24" :md="8" class="m-b-1--sm">
                <q-field class="m-b-1 q-field--overflow">
                    <q-checkbox v-model="form.showCompanyName" :label="$t('SHOW_COMPANY_NAME')" :true-value="1" :false-value="0" />
                </q-field>
                <q-field class="m-b-1 q-field--overflow">
                    <q-checkbox v-model="form.showCompanyAddress" :label="$t('SHOW_ADDRESS')" :true-value="1" :false-value="0" />
                </q-field>
                <q-field class="q-field--overflow">
                    <q-checkbox v-model="form.showUsername" :label="$t('SHOW_NAME')" :true-value="1" :false-value="0" />
                </q-field>
            </el-col>

            <el-col :xs="24" :md="8">
                <q-field class="m-b-1 q-field--overflow">
                    <q-checkbox v-model="form.showPageNumbers" :label="$t('SHOW_PAGENUMBER')" :true-value="1" :false-value="0" />
                </q-field>
                <q-field class="m-b-1 q-field--overflow">
                    <q-checkbox v-model="form.showExportDate" :label="$t('SHOW_EXPORTDATE')" :true-value="1" :false-value="0" />
                </q-field>
                <q-field class="q-field--overflow">
                    <q-checkbox v-model="form.showVersion" :label="$t('SHOW_VERSION')" :true-value="1" :false-value="0" />
                </q-field>
            </el-col>
        </el-row>

        <el-row class="m-t-2">
            <el-col :xs="24" :md="8">
                <q-field>
                    <q-input
                        v-model.trim="form.text"
                        :float-label="$t('FREE_TEXT')"
                        type="textarea"
                        clearable>
                    </q-input>
                </q-field>
            </el-col>
        </el-row>


        <el-row class="m-t-1">
            <el-col :span="24" class="text-right">
                <q-btn
                    :label="$t('SAVE')"
                    color="primary"
                    class="w-100--sm"
                    no-ripple
                    v-loading="loading"
                    @click="doSubmit">
                </q-btn>
            </el-col>
        </el-row>
    </el-form>
</template>

<script>
import commonMixins from '@/shared/mixins/common';
import { ReportSettings } from '@/shared/classes/Company';

export default {
	name: 'FormCompanyReportSettings',

	mixins: [commonMixins],

	props: {
		company: {
			type: Object,
			required: true
		},

		preferences: {
			type: Object,
			required: true
		}
	},

	data() {
		return {
			alignmentOptions: [
				{
					label: this.$t('LEFT'),
					value: 'left'
				},
				{
					label: this.$t('RIGHT'),
					value: 'right'
				},
				{
					label: this.$t('CENTER'),
					value: 'center'
				}
			],
			form: {
				logoPosition: '',
				showCompanyName: false,
				showCompanyAddress: false,
				showUsername: false,
				showPageNumbers: false,
				showExportDate: false,
				showVersion: false,
				text: ''
			},
			loading: false
		};
	},

	mounted() {
		this.init();
	},

	methods: {
		doSubmit() {
			this.loading = true;

			const DATA = new ReportSettings(this.form);
			DATA.companyId = this.company.id;

			this.$store
				.dispatch('companies/UPDATE_COMPANY_REPORT_PREFERENCES', DATA)
				.then(response => {
					this.loading = false;

					if (response.status === 204) {
						this.$q.notify({
							message: this.$t('SAVE_SUCCESS'),
							type: 'positive'
						});
					} else {
						this.handleErrors(response);
					}
				})
				.catch(err => {
					this.loading = false;
				});
		},

		init() {
			this.form = _.cloneDeep(this.preferences);
		}
	},

	watch: {
		preferences(newValue, oldValue) {
			this.init();
		}
	}
};
</script>

<style lang="scss">
</style>
