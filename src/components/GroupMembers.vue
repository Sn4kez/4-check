<template>
    <div>
        <div v-if="!groupId">
            <el-alert
                :title="$t('HINT')"
                description="Bitte Speichern Sie zuerst eine Gruppe, bevor Sie Gruppenmitglieder hinzufügen."
                type="warning"
                show-icon
                :closable="false">
            </el-alert>
        </div>

        <div v-if="groupId">
            <TableGroupMembers
                :data="groupMembers"
                :group="group"
                :is-loading="loading"
                @refresh="requestGroupMembers(groupId)">
            </TableGroupMembers>

            <div class="text-right">
                <q-btn
                    color="primary"
                    :disable="!users.length"
                    :label="$t('ADD_USER')"
                    @click="onBtnClickAddGroupMember"
                    class="m-t-2"
                    no-ripple />
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import TableGroupMembers from '@/components/Table/TableGroupMembers';

export default {
	name: 'GroupMembers',

	components: {
		TableGroupMembers
	},

	props: {
		groupId: {
			type: String,
			required: false,
			default: ''
		}
	},

	computed: {
		isDeviceGreaterSM() {
			return this.$store.getters.IS_DEVICE_GREATER_SM;
		},

		users() {
			return this.$store.state.users.users;
		}
	},

	data() {
		return {
			group: {},
			groupMembers: [],
			loading: false
		};
	},

	mounted() {
		this.init();
	},

	methods: {
		init() {
			console.log('GroupMembers mounted', this.$route, this.groupId);

			const REQUEST = [];

			if (this.groupId) {
				REQUEST.push(this.requestGroup(this.groupId));
				REQUEST.push(this.requestGroupMembers(this.groupId));
			}

			if (!this.users.length) {
				REQUEST.push(this.requestUsers());
			}

			if (REQUEST.length) {
				this.loading = true;

				axios
					.all(REQUEST)
					.then(
						axios.spread((...results) => {
							this.loading = false;
						})
					)
					.catch(err => {
						this.loading = false;
					});
			}

			this.registerEvents();
		},

		onBtnClickAddGroupMember() {
			if (this.isDeviceGreaterSM) {
				this.$store.commit('OPEN_DIALOG', {
					title: this.$t('ADD_GROUP_MEMBER'),
					loadComponent: 'Form/FormAddGroupMember',
					data: {
						group: this.group,
						users: this.users
					}
				});
			} else {
				this.$store.commit('OPEN_MODAL', {
					title: this.$t('ADD_GROUP_MEMBER'),
					loadComponent: 'Form/FormAddGroupMember',
					maximized: true,
					data: {
						group: this.group,
						users: this.users
					}
				});
			}
		},

		registerEvents() {
			if (this.groupId) {
				this.$eventbus.$on('groupmembers:refresh', payload => {
					this.loading = true;
					this.requestGroupMembers(this.groupId)
						.then(response => {
							this.loading = false;
						})
						.catch(err => {
							this.loading = false;
						});
				});
			}
		},

		requestGroup(id) {
			return this.$store
				.dispatch('groups/GET_GROUP', { id: id })
				.then(response => {
					this.group = response.data.data;
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		requestGroupMembers(groupId) {
			return this.$store
				.dispatch('groups/GET_GROUP_MEMBERS', { id: groupId })
				.then(response => {
					this.groupMembers = response.data.data;
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		requestUsers() {
			return this.$store
				.dispatch('users/GET_USERS')
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		}
	}
};
</script>

<style scoped>
</style>
