<template>
    <div>
        <div class="company-subscription">
            <div class="company-subscription__row">
                <span>{{ $t('YOUR_CURRENT_SUBSCRIPTION') }}:</span> <br>
                <span class="font--regular-plus m-t-half d-inline-block"> {{companySubscription.package}} </span>
            </div>

            <div class="company-subscription__row">
                <span>{{ $t('DATE_OF_EXPIRY') }}</span>: <br>
                <span class="font--regular-plus m-t-half d-inline-block"> {{$d(new Date(companySubscription.end), 'long')}} </span>
            </div>
        </div>

        <div class="m-t-2">
            <el-button type="text" @click="onClickBtnReset">{{$t('RESET_DATA')}}</el-button>
            <br>
            <el-button type="text" @click="onClickBtnDeleteAccount">{{$t('DELETE_ACCOUNT')}}</el-button>
        </div>
    </div>

</template>

<script>
import companyMixins from '@/shared/mixins/companies';

export default {
	name: 'CompanyCurrentSubscription',

	mixins: [companyMixins],

	props: {
		data: {
			type: Object,
			required: false
		}
	},

	computed: {
		company() {
			return this.$store.state.user.company;
		},

		companySubscription() {
			return this.$store.state.companies.subscription;
		},

		isDeviceGreaterSM() {
			return this.$store.getters.IS_DEVICE_GREATER_SM;
		}
	},

	mounted() {
		this.init();
	},

	methods: {
		doHardReset() {
			this.$store
				.dispatch('companies/RESET_COMPANY', { id: this.company.id })
				.then(response => {
					if (response.status === 204) {
						this.$q.notify({
							message: this.$t('RESET_SUCCESS'),
							type: 'positive'
						});
					}
				})
				.catch(err => {});
		},

		init() {
			this.requestCompanySubscription(this.company.id);
		},

		onClickBtnDeleteAccount() {
			if (this.isDeviceGreaterSM) {
				this.$alert(this.$t('DELETE_ACCOUNT_MESSAGE'), this.$t('DELETE_ACCOUNT'), {
					confirmButtonText: this.$t('OK')
				});
			} else {
				this.$q.dialog({
					title: this.$t('DELETE_ACCOUNT'),
					message: this.$t('DELETE_ACCOUNT_MESSAGE'),
					ok: this.$t('OK')
				});
			}
		},

		onClickBtnReset() {
			if (this.isDeviceGreaterSM) {
				this.$confirm(this.$t('WOULD_YOU_LIKE_TO_RESET_YOUR_ACCOUNT'), this.$t('RESET_DATA'), {
					confirmButtonText: this.$t('OK'),
					cancelButtonText: this.$t('CANCEL'),
					type: 'warning',
					dangerouslyUseHTMLString: true
				})
					.then(() => {
						this.$confirm(this.$t('WOULD_YOU_REALLY_LIKE_TO_RESET_YOUR_ACCOUNT'), this.$t('RESET_DATA'), {
							confirmButtonText: this.$t('OK'),
							cancelButtonText: this.$t('CANCEL'),
							type: 'error',
							dangerouslyUseHTMLString: true
						})
							.then(() => {
								this.doHardReset();
							})
							.catch(() => {});
					})
					.catch(() => {});
			} else {
				this.$q
					.dialog({
						title: this.$t('RESET_DATA'),
						message: this.$t('WOULD_YOU_LIKE_TO_RESET_YOUR_ACCOUNT'),
						ok: this.$t('OK'),
						cancel: this.$t('CANCEL')
					})
					.then(() => {
						this.$q
							.dialog({
								title: this.$t('RESET_DATA'),
								message: this.$t('WOULD_YOU_REALLY_LIKE_TO_RESET_YOUR_ACCOUNT'),
								ok: this.$t('OK'),
								cancel: this.$t('CANCEL')
							})
							.then(() => {
								this.doHardReset();
							})
							.catch(() => {});
					})
					.catch(() => {});
			}
		}
	}
};
</script>

<style lang="scss">
.company-subscription {
	@media screen and (min-width: $screen-md) {
		display: flex;
		flex-wrap: wrap;
		justify-content: space-between;
	}

	@media screen and (min-width: $screen-lg) {
		justify-content: flex-start;
	}

	&__row {
		&:not(:first-child) {
			margin-top: 1rem;
		}

		@media screen and (min-width: $screen-md) {
			&:not(:first-child) {
				margin-top: 0;
			}
		}

		@media screen and (min-width: $screen-lg) {
			&:not(:first-child) {
				margin-left: 5rem;
			}
		}
	}
}
</style>
