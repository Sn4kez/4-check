<!--
@component:         ExecutedAudits
@environment:       Hyprid
@description:       This component handle the visibility for mobile and desktop devices
                    as well as the data storage for child components.
                    On desktop we show a normal table while on mobile a list is rendered.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-09-27
@modified:          2018-10-13
-->
<template>
    <div>
        <!-- Filter -->
        <div class="text-right">
            <q-btn v-if="showFilter"
                flat
                :label="$t('HIDE_FILTER')"
                class="m-r-1"
                no-ripple
                @click="showFilter = !showFilter"
                icon="filter_list" />
            <q-btn v-else
                flat
                :label="$t('SHOW_FILTER')"
                class="m-r-1"
                no-ripple
                @click="showFilter = !showFilter"
                icon="filter_list" />
        </div>
        <transition name="slide">
            <FormFilterBarAudits v-if="type === 'checklist'"
                :data="{checklist: data}"
                @refresh="requestAudits"
                class="m-t-1"
                v-show="showFilter">
            </FormFilterBarAudits>
            <FormFilterBarAudits v-if="type === 'directory'"
                :data="{directory: data}"
                @refresh="requestAudits"
                class="m-t-1"
                v-show="showFilter">
            </FormFilterBarAudits>
        </transition>
        <!-- Table -->
        <TableChecklistAudits :data="audits" :is-loading="loading" @loading="loading = $event"/>
        <!-- Go to statistic -->
        <div class="a-right m-t-2" v-if="isDeviceGreaterSM">
            <q-btn
                color="primary"
                no-ripple
                :label="$t('SHOW_STATISTIC')"
                tag="a"
                @click="getPdfFile()"
            >
            </q-btn>
        </div>
    </div>
</template>

<script>
import FormFilterBarAudits from '@/components/Form/FormFilterBarAudits';
import TableChecklistAudits from '@/components/Table/TableChecklistAudits';
import auditMixins from '@/shared/mixins/audits';
import API from '@/config/api';

export default {
	name: 'ExecutedAudits',

	mixins: [auditMixins],

	components: {
		FormFilterBarAudits,
		TableChecklistAudits
	},

	props: {
		data: {
			type: String,
			required: true
		},

		type: {
			type: String,
			required: true,
			default: 'checklist'
		}
	},

	computed: {
		company() {
			return this.$store.state.user.company;
		},

		isDeviceGreaterSM() {
			return this.$store.getters.IS_DEVICE_GREATER_SM;
		},

		user() {
			return this.$store.state.user.data;
		},

		users() {
			return this.$store.state.users.users;
		}
	},

	data() {
		return {
			audits: [],
			loading: false,
			showFilter: false,
		};
	},

	mounted() {
		this.init();
	},

	methods: {
		init() {
			if (this.type === 'checklist') {
				// Clear filter
				this.$store.commit('audits/SET_FILTER', {
					checklist: null //this.data
				});
			}

			if (this.isDeviceGreaterSM) {
				this.showFilter = true;
			}

			this.requestAudits();
		},

		async getPdfFile() {
            let filters = this.$store.state.audits.filter;
            filters = Object.assign(filters, {checklist: this.data});
			let response = await API.get(`analytics/export`, {
				params: filters,
            });
			let url = response.data.response;

			const link = document.createElement('a');
			link.download = 'audit.pdf';
			link.target = '_blank';
			link.href = url;
			document.body.appendChild(link);
			link.click();
			link.remove();
		},

		requestAudits() {
			console.log('request audit...', this.data);
			this.loading = true;

			if (this.type === 'checklist') {
				return this.$store
					.dispatch('audits/GET_AUDITS', { id: this.company.id })
					.then(response => {
						this.audits = this.getAuditsByChecklistId(this.data);
						this.loading = false;
						return response;
					})
					.catch(err => {
						console.log(err);
						this.audits = this.getAuditsByChecklistId(this.data);
						this.loading = false;
						return err;
					});
			}

			if (this.type === 'directory') {
				return this.$store
					.dispatch('audits/GET_DIRECTORY_AUDITS', { id: this.data })
					.then(response => {
						this.audits = response.data.data;
                        this.loading = false;
						return response;
					})
					.catch(err => {
						console.log(err);
						this.audits = [];
                        this.loading = false;
						return err;
					});
			}
		}
	}
};
</script>

<style lang="scss">
.slide-enter-active,
.slide-leave-active {
	transition: all 0.3s ease-in-out;
}

.slide-enter,
.slide-leave-to {
	opacity: 0;
}
</style>
