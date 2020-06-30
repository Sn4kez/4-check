<!--
@component:         FormCheckpointNotification
@description:       Form to create checkpoint notifications.

                    Watcher for selectedUsers is used instead of an event because we need to get the old value to remove/add
                    a user.
@authors:           Yannick Herzog, info@hit-services.net
@created:           2018-10-20
@modified:          2018-10-25
-->
<template>
    <el-form @submit.native.prevent v-loading="loading" class="form__checkpoint-notification">
        <ul class="list">
            <li v-for="score in scores" :key="score.id" class="flex m-b-half" style="justify-content:space-around;">
                <div class="w-20 a-center valign-center">
                    <q-checkbox v-model="selectedScores" :val="score.id" />
                </div>
                <q-input v-model="score.name" readonly class="w-50"></q-input>
                <div class="w-30 text-center">
                    <span
                        class="form__checkpoint-notification__score-color"
                        :style="'background-color:' + score.color">
                    </span>
                </div>
            </li>
        </ul>

        <div class="m-t-3">
            <p>{{$t('FOLLOWING_USER_WILL_BE_NOTIFIYED')}}:</p>
            <q-field>
                <q-select
                    :placeholder="$t('SELECT_USER')"
                    multiple
                    checkbox
                    chips
                    v-model="selectedUsers"
                    :options="usersOptions" />
            </q-field>
        </div>

        <!-- Save/Cancel -->
        <div class="text-right m-t-2">
            <q-btn
                :label="$t('CANCEL')"
                v-if="isDeviceGreaterSM"
                @click="onCancel"
                flat
                no-ripple
                class="m-r-1">
            </q-btn>
            <q-btn
                :label="$t('SAVE')"
                class="w-100--sm m-t-1--sm"
                @click="onSubmit"
                color="primary"
                no-ripple>
            </q-btn>
        </div>
    </el-form>
</template>

<script>
import axios from 'axios';
import scoringSchemeMixin from '@/shared/mixins/scoringschemes.js';
import scoresMixin from '@/shared/mixins/scores.js';
import commonMixins from '@/shared/mixins/common';

export default {
	name: 'FormCheckpointNotification',

	mixins: [commonMixins, scoringSchemeMixin, scoresMixin],

	props: {
		data: {
			type: Object,
			required: true
		}
	},

	computed: {
		isDeviceGreaterSM() {
			return this.$store.getters.IS_DEVICE_GREATER_SM;
		},

		usersOptions() {
			return this.$store.getters['users/usersOptions'];
		}
	},

	data() {
		return {
			checklistId: null,
			loading: false,
			scores: [],
			scoreNotices: [],
			selectedScores: [],
			selectedUsers: []
		};
	},

	mounted() {
		this.init();
	},

	methods: {
		doAddUser(items) {
			const DATA = {
				id: this.data.id,
				userId: items[0]
			};

			this.$store.dispatch('checklists/UPDATE_CHECKLIST_APPROVER', DATA).then(response => {
				console.log('selectedUsers', response);
			});
		},

		doCreateScoreNotice(notice) {
			return this.$store
				.dispatch('scores/CREATE_SCORE_NOTICE', notice)
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		doDeleteScoreNotification(id) {
			return this.$store
				.dispatch('scores/DELETE_SCORE_NOTICE', { id: id })
				.then(reponse => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		doDeleteScoreNotifications(scoreNotices) {
			const REQUEST = [];
			scoreNotices.forEach(notice => {
				REQUEST.push(this.doDeleteScoreNotification(notice.id));
			});

			axios
				.all(REQUEST)
				.then(
					axios.spread((...results) => {
						this.$q.notify({
							message: this.$t('DELETE_SUCCESS'),
							type: 'positive'
						});

						this.requestInitData(this.data.checkpoint.scoringSchemeId, false);
					})
				)
				.catch(err => {
					// err
				});
		},

		doRemoveUser(items) {
			const DATA = {
				id: this.data.id,
				userId: items[0]
			};

			this.$store.dispatch('checklists/DELETE_CHECKLIST_APPROVER', DATA).then(response => {
				console.log('selectedUsers', response);
			});
		},

		doSubmit() {
			const REQUEST = [];

			this.selectedScores.forEach(score => {
				this.selectedUsers.forEach(user => {
					const item = {
						id: score,
						checklistId: this.checklistId,
						objectType: 'user',
						objectId: user
					};

					REQUEST.push(this.doCreateScoreNotice(item));
				});
			});

			axios
				.all(REQUEST)
				.then(
					axios.spread((...results) => {
						results.forEach(result => {
							console.log('result', result);
						});

						this.$q.notify({
							message: this.$t('SAVE_SUCCESS'),
							type: 'positive'
						});

						this.onCancel();
					})
				)
				.catch(err => {
					// err
				});

			console.log('doSubmit form');
		},

		handleDeleteScoreNotices(removeScore) {
			const removeScoreNotices = [];

			// Get score notification ids of scores to be deleted
			this.scoreNotices.forEach(notice => {
				if (notice.length) {
					notice.forEach(noticeScore => {
						if (noticeScore.scoreId === removeScore[0]) {
							removeScoreNotices.push(noticeScore);
						}
					});
				}
			});

			if (removeScoreNotices.length) {
				this.doDeleteScoreNotifications(removeScoreNotices);
			}
		},

		handleDeleteUsers(users) {
			const removeScoreNotices = [];

			// Get score notification for current user to be deleted
			this.scores.forEach(score => {
				if (score.noticed.length) {
					score.noticed.forEach(notice => {
						if (notice.objectId === users[0]) {
							removeScoreNotices.push(notice);
						}
					});
				}
			});

			if (removeScoreNotices.length) {
				this.doDeleteScoreNotifications(removeScoreNotices);
			}
		},

		handlePrefillData() {
			this.scores.forEach(score => {
				if (score.noticed.length) {
					this.selectedScores.push(score.id);

					score.noticed.forEach(notice => {
						if (notice.objectType === 'user') {
							this.selectedUsers.push(notice.objectId);
						}
					});
				}
			});

			this.selectedUsers = _.uniq(this.selectedUsers);
		},

		init() {
			if (this.$route.params.id) {
				this.checklistId = this.$route.params.id;
			}

			this.requestInitData(this.data.checkpoint.scoringSchemeId);
		},

		/**
		 * Close the modal/dialog
		 */
		onCancel() {
			this.$emit('cancel');
		},

		requestInitData(id, prefill = true) {
			this.loading = true;

			this.requestScoringSchemeScores(id)
				.then(response => {
					this.scores = response.data.data;
					this.loading = false;

					if (prefill) {
						this.handlePrefillData();
					}
					this.requestScoreNotices();
				})
				.catch(err => {
					this.loading = false;
				});
		},

		requestScoreNotices() {
			const REQUEST = [];

			this.scores.forEach(score => {
				REQUEST.push(this.requestScoreNotice(score.id, this.checklistId));
			});

			axios
				.all(REQUEST)
				.then(
					axios.spread((...results) => {
						results.forEach(result => {
							this.scoreNotices.push(result.data.data);
						});
					})
				)
				.catch(err => {
					// err
				});
		},

		onSubmit() {
			this.doSubmit();
		}
	},

	watch: {
		selectedUsers(newValue, oldValue) {
			if (this.selectedUsers.length > oldValue.length) {
				const addUser = this.selectedUsers.filter(x => !oldValue.includes(x));
				console.log('add', addUser);
			}

			if (this.selectedUsers.length < oldValue.length) {
				const removeUser = oldValue.filter(x => !this.selectedUsers.includes(x));

				this.handleDeleteUsers(removeUser);

				if (!this.selectedUsers.length) {
					this.selectedScores = [];
				}
			}
		},

		selectedScores(newValue, oldValue) {
			if (this.selectedScores.length > oldValue.length) {
				const addScore = this.selectedScores.filter(x => !oldValue.includes(x));
				console.log('add', addScore);
			}

			if (this.selectedScores.length < oldValue.length) {
				const removeScore = oldValue.filter(x => !this.selectedScores.includes(x));

				this.handleDeleteScoreNotices(removeScore);

				if (!this.selectedScores.length) {
					this.selectedUsers = [];
				}
			}
		}
	}
};
</script>

<style lang="scss">
.form__checkpoint-notification__score {
	&-color {
		border-radius: 50%;
		border: 1px solid $c-light-gray;
		display: inline-block;
		height: 40px;
		width: 40px;
		outline: 0;

		&:focus {
			box-shadow: 0 0 5px 0 transparentize($c-navi-blue, 0.1);
		}
	}
}
</style>
