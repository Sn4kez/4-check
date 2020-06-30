<!--
@component:         TableCompanyUsers
@environment:       Web
@description:       This component handle the visibility for mobile and desktop devices
                    as well as the data storage for child components.
                    On desktop we show a normal table while on mobile a list is rendered.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-09-15
@modified:          2018-10-05
-->
<template>
    <div>
        <!-- ############# Desktop ############# -->
        <div class="hide-sm w-100">
            <el-table ref="TableCompanyUsers" class="w-100"
                v-loading="isLoading"
                :data="data"
                :empty-text="$t('NO_DATA_AVAILABLE')">

                <el-table-column type="expand">
                    <template slot-scope="scope">
                        <el-form ref="FormCompanyUser">
                            <p>
                                <q-toggle
                                    v-model="scope.row.isActive"
                                    @input="doUpdateUser(scope.row)"
                                    :label="$t('ACCOUNT_ACTIVE')"
                                    :true-value="1" :false-value="0"
                                    :disable="scope.row.id === appUser.id" />
                            </p>
                            <p class="m-t-1">
                                Account-Typ:
                                <el-select
                                    v-model="scope.row.role"
                                    :placeholder="$t('ROLE')"
                                    @change="doUpdateUser(scope.row)">
                                    <el-option v-for="role in options.role" :key="role.value"
                                        :label="role.name"
                                        :value="role.value"
                                        :disabled="scope.row.id === appUser.id">
                                    </el-option>
                                </el-select>
                            </p>
                            <p class="m-t-1">
                                <q-btn :disable="scope.row.id === appUser.id"
                                    color="primary"
                                    @click.prevent="doResetPasswort(scope.row.email)"
                                    v-loading="loading"
                                    tag="a">
                                    {{ $t('RESET_PASSWORD') }}
                                </q-btn>
                            </p>
                        </el-form>
                    </template>
                </el-table-column>

                <el-table-column
                    :label="$t('FIRSTNAME')"
                    property="firstName"
                    sortable>
                </el-table-column>

                <el-table-column
                    :label="$t('LASTNAME')"
                    property="lastName"
                    sortable>
                </el-table-column>

                <el-table-column
                    :label="$t('EMAIL')"
                    property="email"
                    sortable>
                </el-table-column>

                <el-table-column
                    property="role"
                    :label="$t('ROLE')"
                    sortable>
                </el-table-column>

                <!-- ToDo: Change model -->
                <el-table-column
                    property="updatedAt"
                    :label="$t('LAST_ACTIVE_ON')"
                    sortable>
                    <template slot-scope="scope">
                        {{$d(new Date(scope.row.updatedAt), 'short')}}
                    </template>
                </el-table-column>
            </el-table>
        </div>
    </div>
</template>

<script>
import commonMixins from '@/shared/mixins/common';
import companiesMixins from '@/shared/mixins/companies';
import { User } from '@/shared/classes/User';

export default {
	name: 'TableCompanyUsers',

	mixins: [commonMixins, companiesMixins],

	props: {
		data: {
			type: Array,
			required: true
		},

		isLoading: {
			type: Boolean,
			required: false,
			default: false
		}
	},

	computed: {
		appUser() {
			return this.$store.state.user.data;
		},

		isDeviceGreaterSM() {
			return this.$store.getters.IS_DEVICE_GREATER_SM;
		}
	},

	data() {
		return {
			loading: false,
			options: {
				role: [
					{
						id: 1,
						name: this.$t('USER'),
						value: 'user'
					},
					{
						id: 2,
						name: this.$t('ADMINISTRATOR'),
						value: 'admin'
					}
				]
			}
		};
	},

	methods: {
		doResetPasswort(email) {
			console.log('doResetPW', email);
			this.loading = true;

			this.$http
				.post('/users/password/token', {
					email: email
				})
				.then(result => {
					this.loading = false;

					if (result.status === 200 || result.status === 204) {
						this.$q.notify({
							message: 'Passwort wurde erfolgreich zurückgesetzt.',
							type: 'positive'
						});
					} else {
						this.handleErrors(result);
					}
				})
				.catch(err => {
					console.log('doResetPasswort err', { err });
					this.loading = false;

					this.handleErrors(err);
				});
		},

		doUpdateUser(user) {
			const USER = new User(user);
			console.log('doUpdateUser', user, USER);

			// ToDO: Superadmin is not allowed to update user
			return this.$store
				.dispatch('users/UPDATE_USER', USER)
				.then(response => {
					this.$q.notify({
						message: this.$t('SAVE_SUCCESS'),
						type: 'positive'
					});

					return response;
				})
				.catch(err => {
					return err;
				});
		}
	}
};
</script>

<style lang="scss">
</style>
