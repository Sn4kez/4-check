<template>
    <div class="permission-view">

        <el-row :gutter="20">
            <el-col>

                <el-card>
                    <div class="p-half--sm">
                        <el-row :gutter="20">
                            <el-col :xs="24" :md="6">
                                <q-select
                                    v-model.trim="selectedItem"
                                    :options="userOptions"
                                    :placeholder="$t('SELECT_USER')"
                                    />
                            </el-col>

                            <el-col :xs="24" :md="3">
                                <p class="text-center m-t-2--sm">{{ $t('OR') }}</p>
                            </el-col>

                            <el-col :xs="24" :md="6">
                                <q-select
                                    v-model="selectedItem"
                                    :options="groupOptions"
                                    :placeholder="$t('SELECT_GROUP')"
                                    />
                            </el-col>
                        </el-row>

                        <el-row class="m-t-2">
                            <el-col :xs="24">
                                <q-list link>
                                    <q-list-header>{{ $t('GENERAL') }}</q-list-header>
                                    <q-item tag="label" v-for="item in permissions" :key="item.id">
                                        <q-item-side>
                                            <q-checkbox v-model="item.checked"></q-checkbox>
                                        </q-item-side>
                                        <q-item-main>
                                            <q-item-tile label>{{ item.name }}</q-item-tile>
                                            <q-item-tile sublabel>{{ item.desc }}</q-item-tile>
                                        </q-item-main>
                                    </q-item>
                                </q-list>
                            </el-col>
                        </el-row>

                    </div>
                </el-card>

            </el-col>
        </el-row>

        <el-row class="m-t-4">
            <el-col :xs="24">
                <h5 class="headline">{{ $t('ACCESS_PERMISSIONS') }}</h5>
            </el-col>
        </el-row>

        <el-row>
            <el-col :xs="24">

                <el-card class="p-half--sm">
                    <el-row>
                        <el-col>
                            <p class="color-gray">Berechtigungen werden immer auf untergeordnete Objekte vererbt.</p>
                        </el-col>
                    </el-row>

                    <el-row :gutter="60">
                        <el-col :xs="24" :md="12" class="b-r--md">
                            <p>Leserechte für folgende Dateien und Ordner</p>
                            <ListPermission></ListPermission>
                            <q-btn
                                color="primary"
                                :label="$t('EDIT_READ_PERMISSIONS')"
                                @click="onBtnClickPermission"
                                class="m-t-2"
                                no-ripple />
                        </el-col>
                        <el-col :xs="24" :md="12" class="m-t-3--sm">
                            <p>Schreib- und Leserechte für folgende Dateien und Ordner</p>
                            <ListPermission></ListPermission>
                            <q-btn
                                color="primary"
                                :label="$t('EDIT_WRITE_PERMISSIONS')"
                                @click="onBtnClickPermission"
                                class="m-t-2"
                                no-ripple />
                        </el-col>
                    </el-row>

                </el-card>

            </el-col>
        </el-row>


    </div>
</template>

<script>
import ListPermission from '@/components/List/ListPermission';

export default {
	name: 'PermissionView',

	components: {
		ListPermission
	},

	computed: {
		company() {
			return this.$store.state.user.company;
		},

		groups() {
			return this.$store.state.companies.groups;
		},

		isDeviceGreaterSM() {
			return this.$store.getters.IS_DEVICE_GREATER_SM;
		},

		users() {
			return this.$store.state.users.users;
		}
	},

	data() {
		return {
			groupOptions: [],
			loading: false,
			permissions: [
				{
					id: 1,
					name: this.$t('ADMINISTRATION'),
					desc: 'Uneingeschränkte Kontrolle über alle Funktionen, Inhalte und Benutzer.',
					checked: false
				},
				{
					id: 2,
					name: this.$t('GROUP_MANAGEMENT'),
					desc: 'Uneingeschränkte Kontrolle über alle Funktionen, Inhalte und Benutzer.',
					checked: false
				},
				{
					id: 3,
					name: this.$t('USER_MANAGEMENT'),
					desc: 'Uneingeschränkte Kontrolle über alle Funktionen, Inhalte und Benutzer.',
					checked: false
				},
				{
					id: 4,
					name: this.$t('ANALYSES'),
					desc: 'Uneingeschränkte Kontrolle über alle Funktionen, Inhalte und Benutzer.',
					checked: false
				}
			],
			selectedItem: [],
			userOptions: []
		};
	},

	mounted() {
		this.init();
	},

	methods: {
		init() {
			this.requestUsers();

			this.requestGroups(this.company.id);
		},

		onBtnClickPermission() {},

		requestGroups(companyId) {
			this.loading = true;
			this.$store.dispatch('companies/GET_GROUPS', { id: companyId }).then(response => {
				console.log('request groups', response);
				this.loading = false;

				this.groupOptions = this.transformGroups(response.data.data);
			});
		},

		requestUsers() {
			this.loading = true;
			this.$store.dispatch('users/GET_USERS').then(response => {
				console.log('request users3', response);
				this.loading = false;

				this.userOptions = this.transformUsers(response.data.data);
			});
		},

		transformGroups(groups) {
			groups.forEach(group => {
				group.label = `${group.name}`;
				group.value = group.id;
			});

			return groups;
		},

		transformUsers(users) {
			users.forEach(user => {
				user.label = `${user.lastName}, ${user.firstName}`;
				user.value = user.id;
			});

			return users;
		}
	}
};
</script>

<style lang="scss">
</style>
