<template>
    <el-form ref="FormTaskFilter" class="form-filter-bar">

        <div class="flex wrap">
            <div>
                <q-field class="m-b-1 q-field--bg-white">
                    <q-search
                        v-model="form.name"
                        clearable
                        :debounce="600"
                        :placeholder="$t('TASK')"
                        :float-label="$t('SEARCH_TASKS')"
                        @input="onChangeInput"
                        ref="name" />
                </q-field>
            </div>
            <div>
                <q-field class="m-b-1 q-field--bg-white">
                    <q-select
                        v-model="form.state"
                        :float-label="$t('STATE')"
                        :placeholder="$t('STATE')"
                        :options="taskStates"
                        @input="onChangeInput"
                        clearable
                        ref="state" />
                </q-field>
            </div>
            <div>
                <q-field class="m-b-1 q-field--bg-white">
                    <q-select
                        v-model="form.type"
                        :float-label="$t('TYPE')"
                        :placeholder="$t('TYPE')"
                        :options="taskTypes"
                        @input="onChangeInput"
                        clearable
                        ref="type" />
                </q-field>
            </div>
            <div>
                <q-field class="m-b-1 q-field--bg-white">
                    <q-select
                        v-model="form.priority"
                        :float-label="$t('PRIORITY')"
                        :placeholder="$t('PRIORITY')"
                        :options="taskPriorities"
                        @input="onChangeInput"
                        clearable
                        ref="priority" />
                </q-field>
            </div>
            <div>
                <q-field class="m-b-1 q-field--bg-white">
                    <q-select
                        v-model="form.timerange"
                        :float-label="$t('TIMERANGE')"
                        :placeholder="$t('TIMERANGE')"
                        :options="options.timerange"
                        clearable
                        ref="timerange" />
                </q-field>
            </div>
            <!-- <div>
                <q-field class="m-b-1 q-field--bg-white">
                    <q-select
                        v-model="form.location"
                        :float-label="$t('LOCATIONS')"
                        :placeholder="$t('LOCATIONS')"
                        :options="locations"
                        clearable
                        ref="locations" />
                </q-field>
            </div> -->
            <div v-if="user.role !== 'user'">
                <q-field class="m-b-1 q-field--bg-white">
                    <q-select
                        v-model="form.assignee"
                        :float-label="$t('ASSIGNED_TO')"
                        :placeholder="$t('ASSIGNED_TO')"
                        :options="users"
                        @input="onChangeInput"
                        clearable
                        ref="assignee" />
                </q-field>
            </div>
            <div v-if="user.role !== 'user'">
                <q-field class="m-b-1 q-field--bg-white">
                    <q-select
                        v-model="form.issuer"
                        :float-label="$t('CREATOR')"
                        :placeholder="$t('CREATOR')"
                        :options="users"
                        @input="onChangeInput"
                        clearable
                        ref="issuer" />
                </q-field>
            </div>
            <div>
                <q-btn
                    flat
                    no-ripple
                    :label="$t('RESET_FILTER')"
                    class="color-white"
                    @click="resetForm()">
                </q-btn>
            </div>
        </div>

    </el-form>
</template>

<script>
export default {
	name: 'FormFilterBarTasks',

	props: {},

	computed: {
		isDeviceGreaterSM() {
			return this.$store.getters.IS_DEVICE_GREATER_SM;
		},

		locations() {
			return this.$store.getters['locations/locationOptions'];
		},

		taskPriorities() {
			return this.$store.getters['tasks/taskPriorities'];
		},

		taskStates() {
			return this.$store.getters['tasks/taskStates'];
		},

		taskTypes() {
			return this.$store.getters['tasks/taskTypes'];
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
				name: '',
				timerange: ''
			},
			options: {
				timerange: [
					{
						label: this.$t('LAST_30_DAYS'),
						value: 'last30days'
					},
					{
						label: this.$t('LAST_60_DAYS'),
						value: 'last60days'
					}
				]
			}
		};
	},

	mounted() {
		console.log('FormFilterBarTasks mounted');
	},

	methods: {
		onChangeInput(value) {
			console.log('onChangeInput', value);
			this.$store.commit('tasks/SET_FILTER', this.form);
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
