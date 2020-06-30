import QS from 'qs';
import API from '@/config/api';
import { transformForSelect, transformForTree } from '@/shared/transformers';

function guid() {
  return s4() + s4() + '-' + s4() + '-' + s4() + '-' +
    s4() + '-' + s4() + s4() + s4();
}

function s4() {
  return Math.floor((1 + Math.random()) * 0x10000)
    .toString(16)
    .substring(1);
}

export default {
	namespaced: true,

	state: {
		locationStates: [],
		locationTypes: [],
		locations: [],
		filter: {
			name: '',
			state: [],
			type: []
		}
	},

	getters: {
		getLocationById: state => id => {
			return state.locations.find(state => state.id === id);
		},

		getLocationByName: state => name => {
			return state.locations.find(state => state.name === name);
		},

		getLocationStateById: state => id => {
			return state.locationStates.find(state => state.id === id);
		},

		getLocationStateByName: state => name => {
			return state.locationStates.find(state => state.name === name);
		},

		getLocationTypeById: state => id => {
			return state.locationTypes.find(type => type.id === id);
		},

		locationOptions: function(state, getters) {
			return transformForSelect(state.locations);
		},

		locationTree: function(state, getters) {
			return transformForTree(state.locations);
		},

		locationStates: function(state, getters) {
			return transformForSelect(state.locationStates);
		},

		locationTypes: function(state, getters) {
			return transformForSelect(state.locationTypes);
		}
	},

	mutations: {
		SET_FILTER: function(state, payload) {
			state.filter.name = payload.name;
			state.filter.state = payload.state;
			state.filter.type = payload.type;
			state.filter.selected = payload.selected;
		},

		SET_LOCATIONS: function(state, payload) {
			state.tasks = payload;
		}
	},

	actions: {
		CREATE_LOCATION: function({ commit, state }, payload) {
			const data = QS.stringify(payload);

			return API.post('/locations', data, {
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				}
			})
				.then(response => {
					if (response.status === 201) {
						state.locations.push(response.data.data);
					}

					return response;
				})
				.catch(err => {
					console.log('Error in create location: ', err)
					console.log(payload)
					if(window.indexedDB != undefined){
						console.log('Yay! IndexedDB found');
						return new Promise(resolve => {
							var dbPromise = window.indexedDB.open('4check', 1);
							dbPromise.onsuccess = function(event){
					            var db = dbPromise.result;
					            var tx = db.transaction('location', 'readwrite');
					            var store = tx.objectStore('location');
					            let dd = payload
					            dd['id'] = guid()
					            dd['new'] = 1
					            state.locations.push(dd);

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

		CREATE_LOCATION_TYPE: function({ commit, state }, payload) {
			const data = QS.stringify(payload);

			return API.post('/locations/types', data, {
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				}
			})
				.then(response => {
					if (response.status === 201) {
						state.locationTypes.push(response.data.data);
					}

					return response;
				})
				.catch(err => {
					/*console.log({ err });
					return err;*/
					console.log('Error in create locationtype: ', err)
					console.log(payload)
					if(window.indexedDB != undefined){
						console.log('Yay! IndexedDB found');
						return new Promise(resolve => {
							var dbPromise = window.indexedDB.open('4check', 1);
							dbPromise.onsuccess = function(event){
					            var db = dbPromise.result;
					            var tx = db.transaction('loctypes', 'readwrite');
					            var store = tx.objectStore('loctypes');
					            let dd = payload
					            dd['id'] = guid()
					            dd['new'] = 1
					            state.locationTypes.push(dd);

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

		DELETE_LOCATION: function({ commit, state }, payload) {
			return API.delete('/locations/' + payload.id)
				.then(response => {
					// Remove group from groups
					// _.filter(state.groups, group => {
					// 	return payload.id !== group.id;
					// });

					return response;
				})
				.catch(err => {
					// console.log('Err delete location: ', payload);
					// return err;

					// put to deldata objectstore, also add field of deleted
					// delete from location objectstore

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
					            dd['doc_type'] = 'location'
					            
					            store.put(dd);
					            var toSend = {}
					            toSend['status'] = 200
					            toSend['statusText'] = 'Deleted'


					            var tx1 = db.transaction('location', 'readwrite');
					            var store1 = tx1.objectStore('location');
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

		DELETE_LOCATIONS: function({ commit, state }, payload) {
			const itemIds = [];
			payload.forEach(item => {
				if (_.isObject(item)) {
					itemIds.push(item.id);
				} else {
					itemIds.push(item);
				}
			});

			return API.patch('/locations/delete', { items: itemIds })
				.then(response => {
					return response;
				})
				.catch(err => {
					// console.log({ err });
					// return err;

					// put to deldata objectstore, also add field of deleted
					// delete all from locations

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
					            	event['doc_type'] = 'location'
					            	store.put(event);
					            })).catch((err) => {
					            	tx.abort();
					            	console.log('Error in promise all: ', err);
					            })

					            var toSend = {}
					            toSend['status'] = 200
					            toSend['statusText'] = 'Deleted'


					            var tx1 = db.transaction('location', 'readwrite');
					            var store1 = tx1.objectStore('location');
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

		DELETE_LOCATION_TYPE: function({ commit, state }, payload) {
			return API.delete('/locations/types/' + payload.id)
				.then(response => {
					// Remove type from groups
					// _.filter(state.locationTypes, type => {
					// 	return payload.id !== type.id;
					// });

					return response;
				})
				.catch(err => {
					// return err;

					// put to deldata objectstore, also add field of deleted
					// delete from loctypes objectstore

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
					            dd['doc_type'] = 'loctypes'
					            
					            store.put(dd);
					            var toSend = {}
					            toSend['status'] = 200
					            toSend['statusText'] = 'Deleted'


					            var tx1 = db.transaction('loctypes', 'readwrite');
					            var store1 = tx1.objectStore('loctypes');
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

		GET_LOCATION: function({ commit, state }, payload) {
			console.log('get locations', payload);
			return API.get('/locations/' + payload.id)
				.then(response => {
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		GET_LOCATION_STATES: function({ commit, state }, payload) {
			return API.get('/locations/states/company/' + payload.id)
				.then(response => {
					state.locationStates = response.data.data;
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		GET_LOCATION_TYPE: function({ commit, state }, payload) {
			return API.get('/locations/types/' + payload.id)
				.then(response => {
					return response;
				})
				.catch(err => {
					console.log({ err });
					return err;
				});
		},

		GET_LOCATION_TYPES: function({ commit, state }, payload) {
			return API.get('/locations/types/company/' + payload.id)
				.then(response => {
					state.locationTypes = response.data.data;
					return response;
				})
				.catch(err => {
					if(window.indexedDB != undefined){
						console.log('Yay! IndexedDb found');

						return new Promise(resolve => {
							var dbPromise = window.indexedDB.open('4check', 1);
							dbPromise.onsuccess = function(event){
					            var db = dbPromise.result;
					            var tx = db.transaction('loctypes', 'readonly');
					            var store = tx.objectStore('loctypes');
					            var records = store.getAll()
					            
					            store.getAll().onsuccess = function() {
					            	// set the following variable, that is needed
					                state.locationTypes = records.result

					                var locdata = {}
					                locdata['data'] = {}
					                locdata['data']['data'] = records.result
					                
					                return resolve(locdata)
					            }
					        }
						})

					}else{
						return err;
					}
				});
		},

		GET_LOCATIONS: function({ commit, state }, payload) {
			if (!payload || !payload.id) {
				return false;
			}

			let params = Object.assign({}, state.filter);
			if (payload.filter) {
				params = Object.assign({}, state.filter, payload.filter);
			}

			return API.get('/locations/company/' + payload.id, {
				params: params
			})
				.then(response => {
					state.locations = response.data.data;
					return response;
				})
				.catch(err => {
					if(window.indexedDB != undefined){
						console.log('Yay! IndexedDb found');

						return new Promise(resolve => {
							var dbPromise = window.indexedDB.open('4check', 1);
							dbPromise.onsuccess = function(event){
					            var db = dbPromise.result;
					            var tx = db.transaction('location', 'readonly');
					            var store = tx.objectStore('location');
					            var records = store.getAll()
					            
					            store.getAll().onsuccess = function() {
					            	// set the following variable, that is needed
					                state.locations = records.result

					                var locdata = {}
					                locdata['data'] = {}
					                locdata['data']['data'] = records.result
					                
					                return resolve(locdata)
					            }
					        }
							
						})

					}else{
						return err;
					}

				});
		},

		UPDATE_LOCATION: function({ commit, state }, payload) {
			const data = QS.stringify(payload);

			return API.patch('/locations/' + payload.id, data, {
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				}
			})
				.then(response => {
					return response;
				})
				.catch(err => {
					/*console.log({ err });
					return err;*/

					/* Over here update in indexedDb, 
						return data - "", status - 204 & statusText - No Content
					*/
					if(window.indexedDB != undefined){
						console.log('Yay! IndexedDB found');
						return new Promise(resolve => {
							var dbPromise = window.indexedDB.open('4check', 1);
							dbPromise.onsuccess = function(event){
					            var db = dbPromise.result;
					            var tx = db.transaction('location', 'readwrite');
					            var store = tx.objectStore('location');
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
		},

		UPDATE_LOCATION_TYPE: function({ commit, state }, payload) {
			const data = QS.stringify(payload);

			return API.patch('/locations/types/' + payload.id, data, {
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				}
			})
				.then(response => {
					return response;
				})
				.catch(err => {
					/*console.log({ err });
					return err;*/

					/* Over here update in indexedDb, 
						return data - "", status - 204 & statusText - No Content
					*/
					if(window.indexedDB != undefined){
						console.log('Yay! IndexedDB found');
						return new Promise(resolve => {
							var dbPromise = window.indexedDB.open('4check', 1);
							dbPromise.onsuccess = function(event){
					            var db = dbPromise.result;
					            var tx = db.transaction('loctypes', 'readwrite');
					            var store = tx.objectStore('loctypes');
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
					}
				});
		}
	}
};
