<!--
@component:         RatingSystemsView
@environment:       Hyprid
@description:       This component handle the visibility for mobile and desktop devices
                    as well as the data storage for child components.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-08-13
@modified:          2018-09-06
-->
<template>
    <div class="view__rating-systems main-container" v-loading="loading">
        <q-pull-to-refresh
            :handler="refreshPage"
            :pull-message="$t('PULL_DOWN_TO_REFRESH')"
            :release-message="$t('RELEASE_TO_REFRESH')"
            :refresh-message="$t('REFRESHING')"
            :class="{'o-visible--y': !isDeviceGreaterSM}">

            <div class="m-b-5--sm">
                <el-row type="flex" justify="space-around" class="m-b-1 m-t-1--sm" style="align-items: center;">
                    <el-col :span="24">
                        <h3 class="headline m-t-0 m-b-0 m-l-half--sm m-r-half--sm">{{ $t('OVERVIEW') }}</h3>
                    </el-col>

                    <el-col :span="24" >
                        <div class="text-right m-l-half--sm m-r-half--sm">
                            <!-- DESKTOP -->
                            <div v-if="isDeviceGreaterSM">
                                <ButtonCreateScoringScheme></ButtonCreateScoringScheme>
                            </div>
                        </div>
                    </el-col>
                </el-row>

                <el-row>
                    <el-col :xs="24">
                        <el-card class="el-card--no-padding">

                            <!-- Scoring Schemes Table -->
                            <TableScoringSchemes
                                :data="scoringschemes"
                                @refresh="requestScoringSchemes">
                            </TableScoringSchemes>

                        </el-card>

                    </el-col>
                </el-row>
            </div>

        </q-pull-to-refresh>

        <ButtonFabCreateScoringScheme v-if="!isDeviceGreaterSM"></ButtonFabCreateScoringScheme>
    </div>
</template>

<script>
import ButtonCreateScoringScheme from '@/components/Button/ButtonCreateScoringScheme';
import ButtonFabCreateScoringScheme from '@/components/Button/ButtonFabCreateScoringScheme';
import TableScoringSchemes from '@/components/Table/TableScoringSchemes';

export default {
	name: 'RatingSystemsView',

	components: {
		ButtonCreateScoringScheme,
		ButtonFabCreateScoringScheme,
		TableScoringSchemes
	},

	computed: {
		company() {
			return this.$store.state.user.company;
		},

		isDeviceGreaterSM() {
			return this.$store.getters.IS_DEVICE_GREATER_SM;
		},

		scoringschemes() {
			return this.$store.state.companies.scoringschemes;
		},

		user() {
			return this.$store.state.user.data;
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
		init() {
			this.requestScoringSchemes();
			console.log('RatingSystemsView mounted');
		},

		refreshPage(done) {
			this.requestScoringSchemes()
				.then(() => {
					done();
				})
				.catch(() => {
					done();
				});
		},

		requestScoringSchemes() {
			this.loading = true;
			return this.$store
				.dispatch('companies/GET_COMPANY_SCORING_SCHEMES', { id: this.company.id })
				.then(response => {
					this.loading = false;
					console.log('requestScoringSchemes', response);
				})
				.catch(err => {
					this.loading = false;
				});
		}
	}
};
</script>

<style lang="scss">
</style>
