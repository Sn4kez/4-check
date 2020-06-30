<template>
    <div>
        <!-- Desktop -->
        <div class="hide-sm d-block--md">
            <el-tabs v-model="selectedTab" :stretch="false">
                <el-tab-pane :label="$t('POSTAL_ADDRESS')" :name="$t('POSTAL_ADDRESS')" class="p-t-2">
                    <FormCompanyProfile :data="company" :addresses="postalAddress"></FormCompanyProfile>
                </el-tab-pane>
                <el-tab-pane :label="$t('BILLING_ADDRESS')" :name="$t('BILLING_ADDRESS')" class="p-t-2">
                    <FormCompanyProfile :data="company" :addresses="billingAddress" address-type="billing"></FormCompanyProfile>
                </el-tab-pane>
            </el-tabs>
        </div>

        <!-- Mobile -->
        <div class="hide-md d-block--sm">
            <q-tabs v-model="selectedTab" align="left" inverted no-pane-border class="q-tabs--brand-ios">
                <!-- Tabs - notice slot="title" -->
                <q-tab slot="title" :name="$t('POSTAL_ADDRESS')">{{ $t('POSTAL_ADDRESS') }}</q-tab>
                <q-tab slot="title" :name="$t('BILLING_ADDRESS')">{{ $t('BILLING_ADDRESS') }}</q-tab>

                <!-- Targets -->
                <q-tab-pane :name="$t('POSTAL_ADDRESS')" class="p-t-1">
                    <FormCompanyProfile :data="company" :addresses="postalAddress"></FormCompanyProfile>
                </q-tab-pane>

                <q-tab-pane :name="$t('BILLING_ADDRESS')" class="p-t-1">
                    <FormCompanyProfile :data="company" :addresses="billingAddress" address-type="billing"></FormCompanyProfile>
                </q-tab-pane>
            </q-tabs>
        </div>
    </div>
</template>

<script>
import FormCompanyProfile from '@/components/Form/FormCompanyProfile';

export default {
	name: 'CompanyAddresses',

	components: {
		FormCompanyProfile
	},

	props: {
		addresses: {
			type: Array,
			required: true
		}
	},

	computed: {
		billingAddress() {
			return _.filter(this.addresses, { type: 'billing' });
		},

		company() {
			return this.$store.state.user.company;
		},

		postalAddress() {
			return _.filter(this.addresses, { type: 'postal' });
		}
	},

	data() {
		return {
			selectedTab: this.$t('POSTAL_ADDRESS')
		};
	}
};
</script>

<style lang="scss">
</style>
