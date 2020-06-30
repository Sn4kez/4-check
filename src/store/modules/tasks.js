import QS from 'qs';
import API from '@/config/api';
import { filter, forEach } from 'lodash';
import { transformForSelect, transformUsersForSelect } from '@/shared/transformers';

function guid() {
  return s4() + s4() + '-' + s4() + '-' + s4() + '-' +
    s4() + '-' + s4() + s4() + s4();
}

function s4() {
  return Math.floor((1 + Math.random()) * 0x10000)
    .toString(16)
    .substring(1);
}

function formatDate(date) {
    var d = new Date(date),
        month = '' + (d.getMonth() + 1),
        day = '' + d.getDate(),
        year = d.getFullYear(),
        hour = d.getHours(),
        min = d.getMinutes(),
        sec = d.getSeconds();

    if (month.length < 2) month = '0' + month;
    if (day.length < 2) day = '0' + day;

    var dd = [year, month, day].join('-');
    var tt = [hour, min, sec].join(':');

    return dd + " " + tt;
}

export default {
	namespaced: true,

	state: {
		taskPriorities: [],
		taskStates: [],
		taskTypes: [],
		tasks: [],
		filter: {}
	},

	getters: {
		getTaskPriorityById: state => id => {
			return state.taskPriorities.find(priority => priority.id === id);
		},

		getTaskPriorityByName: state => name => {
			return state.taskPriorities.find(priority => priority.name === name);
		},

		getTaskStateById: state => id => {
			return state.taskStates.find(state => state.id === id);
		},

		getTaskStateByName: state => name => {
			return state.taskStates.find(state => state.name === name);
		},

		getTaskTypeById: state => id => {
			return state.taskTypes.find(type => type.id === id);
		},

		taskPriorities: function(state, getters) {
			return transformForSelect(state.taskPriorities);
		},

		taskStates: function(state, getters) {
			return transformForSelect(state.taskStates);
		},

		taskTypes: function(state, getters) {
			return transformForSelect(state.taskTypes);
		}
	},

	mutations: {
		SET_FILTER: function(state, payload) {
			state.filter.assignee = payload.assignee || null;
			state.filter.issuer = payload.issuer || null;
			state.filter.name = payload.name || null;
			state.filter.priority = payload.priority || null;
			state.filter.userId = payload.userId || null;
			state.filter.state = payload.state || null;
			state.filter.type = payload.type || null;
		},

		SET_TASKS: function(state, payload) {
			state.tasks = payload;
		}
	},

	actions: {
		CREATE_TASK: function({ commit, state }, payload) {
			const data = QS.stringify(payload);

			return API.post('/tasks', data, {
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				}
			})
				.then(response => {
					if (response.status === 201) {
						state.tasks.push(response.data.data);
					}

					return response;
				})
				.catch(err => {
					// console.log({ err });
					// return err;

					console.log('Error in create tasks: ', err)
					console.log(payload)
					if(window.indexedDB != undefined){
						console.log('Yay! IndexedDB found');
						return new Promise(resolve => {
							var dbPromise = window.indexedDB.open('4check', 1);
							dbPromise.onsuccess = function(event){
					            var db = dbPromise.result;
					            var tx = db.transaction('tasks', 'readwrite');
					            var store = tx.objectStore('tasks');
					            let dd = payload
					            dd['id'] = guid()
					            dd['new'] = 1
					            dd['doneAt'] = formatDate(new Date(payload['doneAt']))
					            dd['createdAt'] = formatDate(new Date())
					            state.tasks.push(dd);

					            console.log(dd)
					            store.put(dd);
					            var toSend = {}
					            toSend['data'] = {}
					            toSend['data']['data'] = dd
					            toSend['status'] = 201
					            toSend['statusText'] = 'Created'
					            return resolve(toSend)
					        }
						})
					}else{
						return err;
					}
				});
		},

		DELETE_TASK: function({ commit, state }, payload) {
			return API.delete('/tasks/' + payload.id)
				.then(response => {
					// Remove group from groups
					// _.filter(state.groups, group => {
					// 	return payload.id !== group.id;
					// });

					return response;
				})
				.catch(err => {
					// return err;

					// put to deldata objectstore, also add field of deleted
					// delete from tasks objectstore

					if(window.indexedDB != undefined){
						console.log('Yay! IndexedDB found');
						return new Promise(resolve => {
							var dbPromise = window.indexedDB.open('4check', 1);
							dbPromise.onsuccess = function(event){
					            var db = dbPromise.result;
					            var tx = db.transaction('deldata', 'readwrite');
					            var store = tx.objectStore('deldata');
					            let dd = payload
					            
					            dd['deleted'] = 1
					            dd['doc_type'] = 'tasks'
					            
					            store.put(dd);
					            var toSend = {}
					            toSend['status'] = 200
					            toSend['statusText'] = 'Deleted'


					            var tx1 = db.transaction('tasks', 'readwrite');
					            var store1 = tx1.objectStore('tasks');
					            var osRequest = store1.delete(payload.id);

					            osRequest.onsuccess = function(e){
						            return resolve(toSend)
					            }

					        }
						})
					}else{
						return err;
					}
				});
		},

		DELETE_TASKS: function({ commit, state }, payload) {
			const itemIds = [];
			payload.forEach(item => {
				console.log(_.isObject(item));

				if (_.isObject(item)) {
					itemIds.push(item.id);
				} else {
					itemIds.push(item);
				}
			});

			return API.patch('/tasks/delete', { items: itemIds })
				.then(response => {
					return response;
				})
				.catch(err => {
					// console.log({ err });
					// return err;

					if(window.indexedDB != undefined){
						console.log('Yay! IndexedDB found');
						return new Promise(resolve => {
							var dbPromise = window.indexedDB.open('4check', 1);
							dbPromise.onsuccess = function(event){
					            var db = dbPromise.result;
					            var tx = db.transaction('deldata', 'readwrite');
					            var store = tx.objectStore('deldata');
					            
					            Promise.all(payload.map(event => {
					            	event['deleted'] = 1;
					            	event['doc_type'] = 'tasks'
					            	store.put(event);
					            })).catch((err) => {
					            	tx.abort();
					            	console.log('Error in promise all: ', err);
					            })

					            var toSend = {}
					            toSend['status'] = 200
					            toSend['statusText'] = 'Deleted'


					            var tx1 = db.transaction('tasks', 'readwrite');
					            var store1 = tx1.objectStore('tasks');
					            var osRequest = store1.clear();

					            osRequest.onsuccess = function(e){
						            return resolve(toSend)
					            }

					        }
						})
					}else{
						return err;
					}

				});
		},

		FINISH_TASKS: function({ commit, state }, payload) {
			const itemIds = [];
			payload.forEach(item => {
				console.log(_.isObject(item));

				if (_.isObject(item)) {
					itemIds.push(item.id);
				} else {
					itemIds.push(item);
				}
			});

			return API.patch('/tasks/finish', { items: itemIds })
				.then(response => {
					return response;
				})
				.catch(err => {
					// console.log({ err });
					// return err;

					// update status of this task or these tasks in indexeddb
					if(window.indexedDB != undefined){
						console.log('Yay! IndexedDB found');
						return new Promise(resolve => {
							var dbPromise = window.indexedDB.open('4check', 1);
							dbPromise.onsuccess = function(event){
					            var db = dbPromise.result;
					            var tx = db.transaction('tasks', 'readwrite');
					            var store = tx.objectStore('tasks');
					            
					            return Promise.all(payload.map(event => {
					            	store.put(event);
					            })).catch((err) => {
					            	tx.abort();
					            	console.log('Error in promise all: ', err);
					            })

					            /*var toSend = {}
					            toSend['status'] = 200
					            toSend['statusText'] = 'Deleted'


					            var tx1 = db.transaction('tasks', 'readwrite');
					            var store1 = tx1.objectStore('tasks');
					            var osRequest = store1.clear();

					            osRequest.onsuccess = function(e){
						            return resolve(toSend)
					            }*/

					        }
						})
					}else{
						return err;
					}

				});
		},

		GET_TASK: function({ commit, state }, payload) {
			return API.get('/tasks/' + payload.id)
				.then(response => {
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		GET_TASK_PRIORITIES: function({ commit, state }, payload) {
			return API.get('/tasks/priorities/company/' + payload.id)
				.then(response => {
					state.taskPriorities = response.data.data;
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		GET_TASK_STATES: function({ commit, state }, payload) {
			return API.get('/tasks/states/company/' + payload.id)
				.then(response => {
					state.taskStates = response.data.data;
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		GET_TASK_TYPES: function({ commit, state }, payload) {
			return API.get('/tasks/types/company/' + payload.id)
				.then(response => {
					state.taskTypes = response.data.data;
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		GET_TASKS: function({ commit, state }, payload) {
			return API.get('/tasks/company/' + payload.id, {
				params: state.filter
			})
				.then(response => {
					state.tasks = response.data.data;
					return response;
				})
				.catch(err => {
					// console.log({ err });
					// return err;

					if(window.indexedDB != undefined){
						console.log('Yay! IndexedDb found');

						return new Promise(resolve => {
							var dbPromise = window.indexedDB.open('4check', 1);
							dbPromise.onsuccess = function(event){
					            var db = dbPromise.result;
					            var tx = db.transaction('tasks', 'readonly');
					            var store = tx.objectStore('tasks');
					            var records = store.getAll()
					            
					            store.getAll().onsuccess = function() {
					            	// set the following variable, that is needed
					                state.tasks = records.result

					                var taskdata = {}
					                taskdata['data'] = {}
					                taskdata['data']['data'] = records.result
					                
					                return resolve(taskdata)
					            }
					        }
							
						})

					}else{
						return err;
					}

				});
		},

		UPDATE_TASK: function({ commit, state }, payload) {
			const data = QS.stringify(payload);

			return API.patch('/tasks/' + payload.id, data, {
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				}
			})
				.then(response => {
					return response;
				})
				.catch(err => {
					// console.log({ err });
					// return err;

					/* Over here update in indexedDb, 
						return data - "", status - 204 & statusText - No Content
					*/
					if(window.indexedDB != undefined){
						console.log('Yay! IndexedDB found');
						return new Promise(resolve => {
							var dbPromise = window.indexedDB.open('4check', 1);
							dbPromise.onsuccess = function(event){
					            var db = dbPromise.result;
					            var tx = db.transaction('tasks', 'readwrite');
					            var store = tx.objectStore('tasks');
					            let dd = payload
					            dd['update'] = 1
					            console.log(dd)
					            store.put(dd);
					            var toSend = {}
					            toSend['data'] = ""
					            toSend['status'] = 204
					            toSend['statusText'] = 'No Content'
					            return resolve(toSend)
					        }
						})
					}else{
						return err
					}
				});
		}
	}
};
