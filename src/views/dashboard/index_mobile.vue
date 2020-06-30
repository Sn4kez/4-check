<!--
@component:         DashboardViewMobile
@environment:       Mobile
@description:       Order the cards and informations inside the dashboard.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-07-09
@modified:          2018-10-12
-->
<template>
    <div class="p-b-2">

        <q-pull-to-refresh
            :handler="refreshPage"
            :pull-message="$t('PULL_DOWN_TO_REFRESH')"
            :release-message="$t('RELEASE_TO_REFRESH')"
            :refresh-message="$t('REFRESHING')">

            <div class="m-t-1 p-l-half p-r-half">
                <h3 class="dashboard__card-headline">{{ $t('CHECKLISTS') }}</h3>
                <q-tabs v-model="selectedTab" align="center" inverted no-pane-border class="q-tabs--brand-ios">
                    <q-tab slot="title" :name="$t('NEXT_AUDITS')">{{ $t('NEXT_AUDITS') }}</q-tab>
                    <q-tab slot="title" :name="$t('LATEST_AUDITS')">{{ $t('LATEST_AUDITS') }}</q-tab>

                    <q-tab-pane :name="$t('NEXT_AUDITS')" class="p-1">
                        <el-card>
                            <TableNextAudits :data="dashboard.next_audits" v-loading="loading"></TableNextAudits>
                        </el-card>
                    </q-tab-pane>
                    <q-tab-pane :name="$t('LATEST_AUDITS')">
                        <el-card>
                            <TableLatestAudits :data="dashboard.last_audits" v-loading="loading"></TableLatestAudits>

                        </el-card>
                    </q-tab-pane>
                </q-tabs>
            </div>

            <div class="m-t-1 p-l-half p-r-half">
                <h3 class="dashboard__card-headline">{{ $t('MY_TASKS') }}</h3>
                <el-card>
                    <TableTasksDashboard :data="dashboard.tasks" v-loading="loading"></TableTasksDashboard>
                </el-card>
            </div>

        </q-pull-to-refresh>
    </div>
</template>

<script>
import { QTabs, QTabPane, QTab } from 'quasar';
import TableLatestAudits from '@/components/Table/TableLatestAudits';
import TableNextAudits from '@/components/Table/TableNextAudits';
import TableTasksDashboard from '@/components/Table/TableTasksDashboard';

export default {
	name: 'DashboardViewMobile',

	components: {
		QTabs,
		QTab,
		QTabPane,
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
		}
	},

	data() {
		return {
			selectedTab: this.$t('NEXT_AUDITS')
		};
	},

	mounted() {
		this.init();
	},

	methods: {
		init() {
			console.log('DashboardView mobile mounted');
		},

		onChangeDateRange(value) {
			this.requestDashboardTasks();
		},

		refreshPage(done) {
			this.requestDashboard()
				.then(() => {
					done();
				})
				.catch(() => {
					done();
				});
		},

		requestDashboard() {
			return this.$store
				.dispatch('dashboard/GET_DATA')
				.then(response => {
					console.log('requestDashboard', response);
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
.v-step__header{background-color:#454d5d;border-top-left-radius:3px;border-top-right-radius:3px;margin:-1rem -1rem .5rem;padding:.5rem;}
.v-step{width:400px;z-index:99999;filter: none!important;}
.v-step[x-placement="right"]{
	bottom:350px!important;
}
.v-step[x-placement="left"]{
	bottom:420px!important;
}
.v-step[x-placement="top"]{
	bottom:480px!important;
}
.v-step button{
	border:1px solid #4fc08d!important;
}
.v-step button:last-child{
	background-color: #4fc08d;
}
.v-step__buttons button{
	margin-top:5px!important;
	margin-bottom:5px!important;
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
