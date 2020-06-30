import QS from 'qs';
import API from '@/config/api';

export default {
	namespaced: true,

	state: {
		breadcrumbs: {},
		directories: [],
		entries: [],
		rootDirectory: {}
	},

	getters: {
		getEntriesByDirectoryId: state => id => {
			return state.entries.filter(entry => entry.parentId === id);
		}
	},

	mutations: {
		ADD_BREADCRUMB: function(state, payload) {
			state.breadcrumbs = payload;
		},

		RESET_BREADCRUMBS: function(state, payload) {
			state.breadcrumbs = {};
		},

		RESET_DIRECTORY_ENTRIES: function(state, payload) {
			state.entries = [];
		},

		SET_DIRECTORY: function(state, payload) {
			let found = false;
			state.directories.forEach(directory => {
				if (directory.id === payload.id) {
					directory = Object.assign({}, payload);
					found = true;
				}
			});

			if (!found) {
				state.directories.push(payload);
			}
		},

		/**
		 * Entries are stored in one array.
		 * We use getters to filter directory entries by there parent directory.
		 * Entries are sorted by type (checklist/folder) and name.
		 * @param {Object} state
		 * @param {Array} payload
		 */
		SET_DIRECTORY_ENTRIES: function(state, payload) {
			state.entries = _.unionBy(payload, state.entries, 'id');

			state.entries = _.sortBy(state.entries, entry => {
				return entry.object.name;
			});
			state.entries.reverse();
			state.entries = _.sortBy(state.entries, entry => {
				return entry.objectType;
			});
			state.entries.reverse();
		},

		SET_ROOT_DIRECTORY: function(state, payload) {
			state.rootDirectory = payload;
		}
	},

	actions: {
		ARCHIVE_DIRECTORIES: function({ commit, state }, payload) {
			const data = QS.stringify(payload);

			return API.patch('/directories/archive', data, {
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				}
			})
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		ARCHIVE_DIRECTORY: function({ commit, state }, payload) {
			const data = QS.stringify(payload);

			return API.patch('/directories/' + payload.id + '/archive', data, {
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				}
			})
				.then(response => {
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		COPY_DIRECTORY: function({ commit, state }, payload) {
			const data = QS.stringify(payload);

			return API.patch('/directories/' + payload.id + '/copy', data, {
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				}
			})
				.then(response => {
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		COPY_DIRECTORIES: function({ commit, state }, payload) {
			const data = QS.stringify(payload);

			return API.patch('/directories/copy', data, {
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				}
			})
				.then(response => {
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		CREATE_DIRECTORY: function({ commit, state }, payload) {
			const data = QS.stringify(payload);
			console.log('CREATE_DIRECTORY', data, payload);

			return API.post('/directories', data, {
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				}
			})
				.then(response => {
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		CREATE_DIRECTORY_GRANT: function({ commit, state }, payload) {
			const directoryId = payload.directoryId;
			const data = QS.stringify(payload.data);
			console.log('CREATE_DIRECTORY', data, payload);

			return API.post('/directories/' + directoryId + '/grants', data, {
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				}
			})
				.then(response => {
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		DELETE_DIRECTORY: function({ commit, state }, payload) {
			return API.delete('/directories/' + payload.id)
				.then(response => {
					// Remove group from groups
					// _.filter(state.DIRECTORYs, group => {
					// 	return payload.id !== group.id;
					// });

					return response;
				})
				.catch(err => {
					return err;
				});
		},

		DELETE_DIRECTORIES: function({ commit, state }, payload) {
			return API.delete('/directories', {
				params: {
					directories: payload
				}
			})
				.then(response => {
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		GET_DIRECTORY: function({ commit, state }, payload) {
			return API.get('/directories/' + payload.id)
				.then(response => {
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		GET_DIRECTORY_ENTRIES: function({ commit, state }, payload) {
			return API.get('/directories/' + payload.id + '/entries')
				.then(response => {
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		GET_DIRECTORY_GRANTS: function({ commit, state }, payload) {
			return API.get('/directories/' + payload.id + '/grants')
				.then(response => {
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		MOVE_DIRECTORY: function({ commit, state }, payload) {
			const data = QS.stringify(payload);

			return API.patch('/directories/' + payload.id + '/move', data, {
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				}
			})
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		MOVE_DIRECTORIES: function({ commit, state }, payload) {
			const data = QS.stringify(payload);

			return API.patch('/directories/move', data, {
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				}
			})
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		RESTORE_DIRECTORY: function({ commit, state }, payload) {
			const data = QS.stringify(payload);

			return API.patch('/directories/' + payload.id + '/restore', data, {
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				}
			})
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		RESTORE_DIRECTORIES: function({ commit, state }, payload) {
			const data = QS.stringify(payload);

			return API.patch('/directories/restore', data, {
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				}
			})
				.then(response => {
					return response;
				})
				.catch(err => {
					return err;
				});
		},

		UPDATE_DIRECTORY: function({ commit, state }, payload) {
			const data = QS.stringify(payload);

			return API.patch('/directories/' + payload.id, data, {
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				}
			})
				.then(response => {
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		}
	}
};
