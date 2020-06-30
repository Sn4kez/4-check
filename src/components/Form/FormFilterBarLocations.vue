<template>
    <el-form ref="FormLocationFilter" class="form-filter-bar">

        <el-row :gutter="20">
            <!-- Search -->
            <el-col :xs="24" :sm="7">
                <q-search
                    v-model="searchString"
                    clearable
                    :debounce="600"
                    :placeholder="$t('LOCATION')"
                    :float-label="$t('SEARCH_LOCATIONS')"
                    @input="onFilterChange"
                    ref="searchString" />
            </el-col>

            <!-- Type -->
            <el-col :xs="24" :sm="9">
                <q-select
                    v-model="selectedType"
                    multiple
                    chips
                    :float-label="$t('TYPE')"
                    :options="locationTypes"
                    @input="onFilterChange"
                    ref="selectedTypes" />
            </el-col>

            <!-- State -->
            <!-- <el-col :xs="24" :sm="3">
                <q-select
                    v-model="selectedState"
                    multiple
                    chips
                    :float-label="$t('STATE')"
                    :options="locationStates"
                    @input="onFilterChange"
                    ref="selectedStates" />
            </el-col> -->

            <el-col :xs="24" :sm="5">
                <q-btn
                    flat
                    color="secondary"
                    no-ripple
                    :label="$t('RESET_FILTER')"
                    class="color-white1 m-t-1"
                    @click="resetForm()">
                </q-btn>
            </el-col>
        </el-row>

    </el-form>
</template>

<script>
export default {
	name: 'FormFilterBarLocations',

	props: {},

	computed: {
		isDeviceGreaterSM() {
			return this.$store.getters.IS_DEVICE_GREATER_SM;
		},

		locations() {
			return this.$store.getters['locations/locationOptions'];
		},

		locationStates() {
			return this.$store.getters['locations/locationStates'];
		},

		locationTypes() {
			return this.$store.getters['locations/locationTypes'];
		},

		user() {
			return this.$store.state.user.data;
		},

		users() {
			return this.$store.getters['users/usersOptions'];
		}
	},

	data() {
		return {
			form: {
				state: '',
				type: '',
				assignee: '',
				issuer: '',
				priority: '',
				location: '',
				timerange: ''
			},
			searchString: '',
			selectedState: [],
			selectedType: []
		};
	},

	mounted() {
		console.log('FormFilterBarLocations mounted');
	},

	methods: {
		onFilterChange(value) {
			console.log('onFilterChange', value);
			this.$store.commit('locations/SET_FILTER', {
				name: this.searchString,
				state: this.selectedState,
				type: this.selectedType
			});
			this.$emit('refresh');
		},

		resetForm() {
			_.forEach(this.$refs, item => {
				// Reset only input fields with clear method
				if (typeof item.clear === 'function') {
					item.clear();
				}
			});
		}
	}
};
</script>

<style lang="scss">
.form-filter-bar {
	.flex {
		> * {
			width: calc((100% / 2) - 6%);
			margin: 0 3%;

			@media screen and (min-width: $screen-sm) {
				width: calc((100% / 4) - 2%);
				margin: 0 1%;
			}

			@media screen and (min-width: $screen-lg) {
				width: calc((100% / 6) - 1%);
				margin: 0 0.5%;
			}
		}
	}
}
</style>
