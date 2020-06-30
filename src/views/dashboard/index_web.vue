<!--
@component:         DashboardViewWeb
@environment:       Web
@description:       Order the cards and informations inside the dashboard.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-07-09
@modified:          2018-10-19
-->
<template>
    <div>
        <el-row :gutter="gutter">
            <el-col :sm="24">
                <h1 class="headline font--bigger">{{ $t('DASHBOARD') }}</h1>
            </el-col>
            <el-col :sm="24" class="flex valign-center">
                <p class="color-gray m-b-0 m-r-1">{{ $t('LAST_ACTIVITIES') }}:</p>

                <DashboardFilter
                    @change="onChangeDateRange">
                </DashboardFilter>
            </el-col>
        </el-row>

        <el-row :gutter="gutter">
            <el-col :sm="12">
                <h3 class="dashboard__card-headline">{{ $t('CHECKLIST') }}</h3>
                <el-card>
                    <TableNextAudits :data="dashboard.next_audits" v-loading="loading"></TableNextAudits>
                    <el-pagination
                        class="m-t-1 text-center"
                        small
                        layout="prev, pager, next"
                        :page-size="5"
                        :total="dashboard.next_audits_total"
                        @current-change="onChangePageNextAudits">
                    </el-pagination>
                </el-card>
            </el-col>

            <el-col :sm="12">
                <h3 class="dashboard__card-headline">&nbsp;</h3>
                <el-card>
                    <TableLatestAudits :data="dashboard.last_audits" v-loading="loading"></TableLatestAudits>
                    <el-pagination
                        class="m-t-1 text-center"
                        small
                        layout="prev, pager, next"
                        :page-size="5"
                        :total="dashboard.last_audits_total"
                        @current-change="onChangePageLastAudits">
                    </el-pagination>
                </el-card>
            </el-col>
        </el-row>

        <el-row>
            <el-col :sm="24">
                <h3 class="dashboard__card-headline">{{ $t('MY_TASKS') }}</h3>
                <el-card>
                    <TableTasksDashboard :data="dashboard.tasks" v-loading="loading"></TableTasksDashboard>

                    <div class="flex space-between">
                        <!-- Show all -->
                        <p class="m-t-1 m-b-0">
                            <router-link
                                :to="'/tasks'"
                                class="font--small el-button--text">
                                {{ $t('VIEW_ALL') }}
                            </router-link>
                        </p>
                        <!-- Pagination -->
                        <el-pagination
                            class="m-t-1 text-center"
                            small
                            layout="prev, pager, next"
                            :page-size="5"
                            :total="dashboard.tasks_total"
                            @current-change="onChangePageTasks">
                        </el-pagination>
                        <!-- Placeholder -->
                        <div>&nbsp;</div>
                    </div>
                </el-card>
            </el-col>
        </el-row>
    </div>
</template>

<script>
import DashboardFilter from '@/components/DashboardFilter';
import TableLatestAudits from '@/components/Table/TableLatestAudits';
import TableNextAudits from '@/components/Table/TableNextAudits';
import TableTasksDashboard from '@/components/Table/TableTasksDashboard';

export default {
	name: 'DashboardViewWeb',

	components: {
		DashboardFilter,
		TableTasksDashboard,
		TableLatestAudits,
		TableNextAudits
	},

	props: {
		loading: {
			type: Boolean,
			required: false,
			default: false
		}
	},

	computed: {
		dashboard() {
			return this.$store.state.dashboard.data;
		},

		lastAuditFilter() {
			return this.$store.state.dashboard.lastAuditFilter;
		}
	},

	data() {
		return {
			gutter: 30
		};
	},

	mounted() {
		this.init();
	},

	methods: {
		init() {
			console.log('DashboardView mounted');
		},

		onChangeDateRange(value) {
			console.log('onChangeDateRange', value);

			this.requestDashboardLastAudits();
		},

		onChangePageLastAudits(page) {
			console.log('onChangePageLastAudits', page);

			this.$store.commit('dashboard/SET_LAST_AUDIT_FILTER', {
				start: this.lastAuditFilter.start,
				end: this.lastAuditFilter.end,
				page: page
			});

			this.requestDashboardLastAudits();
		},

		onChangePageNextAudits(page) {
			console.log('onChangePageNextAudits', page);

			this.$store.commit('dashboard/SET_NEXT_AUDITS_FILTER', {
				page: page
			});

			this.requestDashboardNextAudits();
		},

		onChangePageTasks(page) {
			console.log('onChangePageNextAudits', page);

			this.$store.commit('dashboard/SET_TASKS_FILTER', {
				page: page
			});

			this.requestDashboardTasks();
		},

		requestDashboardLastAudits() {
			return this.$store
				.dispatch('dashboard/GET_LAST_AUDITS')
				.then(response => {
					console.log('requestDashboardLastAudits', response);
				})
				.catch(err => {
					//
				});
		},

		requestDashboardNextAudits() {
			return this.$store
				.dispatch('dashboard/GET_NEXT_AUDITS')
				.then(response => {
					console.log('requestDashboardNextAudits', response);
				})
				.catch(err => {
					//
				});
		},

		requestDashboardTasks() {
			return this.$store
				.dispatch('dashboard/GET_TASKS')
				.then(response => {
					console.log('requestDashboardTasks', response);
				})
				.catch(err => {
					//
				});
		}
	}
};
</script>

<style lang="scss">
.dashboard {
	&__card-headline {
		color: $c-gray;
		font-size: 1.2rem;
		margin: 0 0 0.625rem;
	}

	.el-row {
		@media screen and (min-width: $screen-md) {
			margin-top: 40px;

			&:first-child {
				margin-top: 0;
			}
		}
	}

	.el-col {
		@media screen and (max-width: $screen-md) {
			margin-top: 40px;

			&:first-child {
				margin-top: 0;
			}
		}
	}

	.el-radio-button {
		flex: 1 0 auto;

		&__inner {
			width: 100%;
		}
	}

	// Date picker
	.el-range-separator {
		color: $c-gray;
		width: 2rem;
	}
}
.v-step__header{background-color:#454d5d;border-top-left-radius:3px;border-top-right-radius:3px;margin:-1rem -1rem .5rem;padding:.5rem;}
.v-step{width:400px;z-index:999;filter: none!important;}
.v-step[x-placement="right"]{
	left:275px!important;
}
.v-step button{
	border:1px solid #4fc08d!important;
}
.v-step button:last-child{
	background-color: #4fc08d;
}

@media(min-width:2500px)

{

.v-step p{
font-size:20px !important;
letter-spacing:2px;
font-weight:normal;
}


}



</style>
