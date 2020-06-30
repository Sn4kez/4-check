<!--
@component:         DashboardFilter
@environment:       Hyprid
@description:       This component handle the filter for dashboard data.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-10-06
@modified:          2018-10-06
-->
<template>
    <el-date-picker
        v-model="dateRange"
        type="daterange"
        :range-separator="$t('TO')"
        :start-placeholder="$t('START_DATE')"
        :end-placeholder="$t('END_DATE')"
        :default-time="['00:00:00', '23:59:59']"
        format="dd.MM.yyyy"
        :picker-options="datePickerOptions"
        @change="onChangeDateRange">
    </el-date-picker>
</template>

<script>
import { date } from 'quasar';

export default {
	name: 'DashboardFilter',

	data() {
		return {
			datePickerOptions: {
				firstDayOfWeek: 1,
				shortcuts: [
					{
						text: this.$t('NEXT_WEEK'),
						onClick(picker) {
							const end = new Date();
							const start = new Date();
							end.setTime(end.getTime() + 3600 * 1000 * 24 * 7);
							picker.$emit('pick', [start, end]);
						}
					},
					{
						text: this.$t('NEXT_MONTH'),
						onClick(picker) {
							const end = new Date();
							const start = new Date();
							end.setTime(end.getTime() + 3600 * 1000 * 24 * 30);
							picker.$emit('pick', [start, end]);
						}
					},
					{
						text: this.$t('NEXT_3_MONTH'),
						onClick(picker) {
							const end = new Date();
							const start = new Date();
							end.setTime(end.getTime() + 3600 * 1000 * 24 * 90);
							picker.$emit('pick', [start, end]);
						}
					}
				]
			},
			dateRange: []
		};
	},

	mounted() {
		this.init();
	},

	methods: {
		getTimeStamp(currentDate) {
			return date.formatDate(currentDate, 'x');
		},

		init() {
			this.setInitalDate();

			console.log('DashboardFilter mounted', this.$q.platform.is.desktop);
		},

		onChangeDateRange(value) {
			console.log('onChangeDateRange', value);

			this.$store.commit('dashboard/SET_LAST_AUDIT_FILTER', {
				start: this.getTimeStamp(value[0]),
				end: this.getTimeStamp(value[1])
			});

			this.$emit('change', value);
		},

		setInitalDate() {
			const endDate = new Date();
			let startDate = new Date();
			startDate = date.subtractFromDate(startDate, { days: 30 });

			this.dateRange.push(startDate);
			this.dateRange.push(endDate);
		}
	}
};
</script>

<style lang="scss">
.el-picker-panel__shortcut {
	line-height: 1.2rem;
	margin-top: 0.8rem;

	&:first-child {
		margin-top: 0;
	}
}
</style>
