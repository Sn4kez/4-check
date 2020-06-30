// importScripts("/precache-manifest.d94cfd71f38ee6e17f0061a5f7a060e6.js", "https://storage.googleapis.com/workbox-cdn/releases/3.6.3/workbox-sw.js", "idb-promised.js");

importScripts("idb-promised.js")

console.log('-------live---------service worker.js');

const dbPromise = createIndexedDB();
const CACHE_NAME ='4-check-api';

function createIndexedDB(){
	// on create, create all needed object stores
	return idb.open('4check', 1, function(upgradeDb){
		if (!upgradeDb.objectStoreNames.contains('loctypes')) {
	    	upgradeDb.createObjectStore('loctypes', {keyPath: 'id'});
	    }

	    if (!upgradeDb.objectStoreNames.contains('location')) {
	    	upgradeDb.createObjectStore('location', {keyPath: 'id'});
	    }

	    if(!upgradeDb.objectStoreNames.contains('deldata')){
	    	upgradeDb.createObjectStore('deldata', {keyPath: 'id'});
	    }

	    if(!upgradeDb.objectStoreNames.contains('tasks')){
	    	upgradeDb.createObjectStore('tasks', {keyPath: 'id'});
	    }

	    /*if(!upgradeDb.objectStoreNames.contains('taskstypes')){
	    	upgradeDb.createObjectStore('taskstypes', {keyPath: 'id'});
	    }*/

	})
}

function saveDataLocally(name, data){
	console.log('Saving Data 2....');

	dbPromise.then(db => {
		// console.log('Found db: ', db)
		const tx = db.transaction(name, 'readwrite');
		const store = tx.objectStore(name);
		return Promise.all(data.map(event => store.put(event)))
		.catch(() => {
			tx.abort();
			throw Error('Events were not added to the store: ', name);
		});
	})
}

function getLocalData(name){
	console.log('GET LOCAL DATA: ', name);
	return dbPromise.then(db => {
		const tx = db.transaction(name, 'readonly');
		const store = tx.objectStore(name)
		return store.getAll();
	})
}

if (workbox) {
	console.log(`Yay! Workbox is loaded too 🎉`);

	if("indexedDB" in self){
		console.log('Yay! Found indexed DB')
	}

	// For locale files
	workbox.precaching.precacheAndRoute(self.__precacheManifest);

	/*const bgSyncPlugin = new workbox.backgroundSync.Plugin('audit', {
		maxRetentionTime: 24 * 60 // Retry for max of 24 Hours
	});*/

	const bgSyncPlugin = new workbox.backgroundSync.Plugin('4check-queue', {
		maxRetentionTime: 24 * 60 // Retry for max of 24 Hours
	});

	const networkWithBackgroundSync = new workbox.strategies.NetworkOnly({
		plugins: [bgSyncPlugin],
	});



	const handleGetLocations = ({url, event, params}) => {
		console.log('handleGetLocations: ', url.href);

		fetch(url.href, {
			method: 'GET',
			headers: event.request.headers
		}).then(response => {
			console.log("LOOK OVER HERE")
			return response.json()
		}).then(data => {
			// console.log(data);
			if(url.href.indexOf('types') == -1){
				// console.log('ACTUAL LOCATIONS DATA: ', data.data)
				saveDataLocally('location', data.data);
			}else{
				// console.log('TYPES DATA: ', data.data);
				saveDataLocally('loctypes', data.data);
			}
		}).catch(err => {
			console.log("ERROR OVER HERE");
		})
	};

	const handleGetTasks = ({url, event, params}) => {
		console.log('handleGetTasks: ', url.href);

		fetch(url.href, {
			method: 'GET',
			headers: event.request.headers
		}).then(response => {
			console.log("LOOK OVER HERE")
			return response.json()
		}).then(data => {
			// console.log(data);
			saveDataLocally('tasks', data.data);
			/*if(url.href.indexOf('types') == -1){
				saveDataLocally('tasks', data.data);
			}else{
				saveDataLocally('taskstypes', data.data);
			}*/
		}).catch(err => {
			console.log("ERROR OVER HERE");
		})
	}

	// only for location states
	workbox.routing.registerRoute(
		/(?:http:\/\/.*|https:\/\/.*)(.locations\/states.)(\w{1,})/g,
		workbox.strategies.staleWhileRevalidate(),
		'GET'
	)


	workbox.routing.registerRoute(
		/(?:http:\/\/.*|https:\/\/.*)(.locations.)(\w{1,})/g,
		// workbox.strategies.staleWhileRevalidate(),
		handleGetLocations,
		'GET'
	)

	// delete a location
	workbox.routing.registerRoute(
		/(?:http:\/\/.*|https:\/\/.*)(.locations.)([\w-]{1,})/g,
		networkWithBackgroundSync,
		'DELETE'
	)

	// create new location
	workbox.routing.registerRoute(
		/(?:http:\/\/.*|https:\/\/.*)(locations)/g,
		networkWithBackgroundSync,
		'POST'
	)

	// update a location
	workbox.routing.registerRoute(
		/(?:http:\/\/.*|https:\/\/.*)(.locations.)([\w-]{1,})/g,
		networkWithBackgroundSync,
		'PATCH'
	)

	// store tasks
	workbox.routing.registerRoute(
		/(?:http:\/\/.*|https:\/\/.*)(.tasks\/company\/.)([\w-]{1,})/g,
		handleGetTasks,
		'GET'
	)

	// store tasks types
	workbox.routing.registerRoute(
		/(?:http:\/\/.*|https:\/\/.*)(.tasks\/types\/company\/.)([\w-]{1,})/g,
		workbox.strategies.staleWhileRevalidate(),
		'GET'
	)

	// tasks priorities
	workbox.routing.registerRoute(
		/(?:http:\/\/.*|https:\/\/.*)(.tasks\/priorities\/company\/.)([\w-]{1,})/g,
		workbox.strategies.staleWhileRevalidate(),
		'GET'
	)

	// tasks states
	workbox.routing.registerRoute(
		/(?:http:\/\/.*|https:\/\/.*)(.tasks\/states\/company\/.)([\w-]{1,})/g,
		workbox.strategies.staleWhileRevalidate(),
		'GET'
	)

	// create new task
	workbox.routing.registerRoute(
		/(?:http:\/\/.*|https:\/\/.*)(tasks)/g,
		networkWithBackgroundSync,
		'POST'
	)

	// finish a task
	workbox.routing.registerRoute(
		/(?:http:\/\/.*|https:\/\/.*)(.tasks.)(\w{1,})/g,
		networkWithBackgroundSync,
		'PATCH'
	)


	// audit patch
	workbox.routing.registerRoute(
		/(?:http:\/\/.*|https:\/\/.*)(.audits.)([\w-\/]+)/g,
		networkWithBackgroundSync,
		'PATCH'
	)

	/*// when add location
	workbox.routing.registerRoute(
		/(?:http:\/\/.*|https:\/\/.*)(locations)/g,
		customHandler,
		'POST'
	)*/

	// Phones
	workbox.routing.registerRoute(
		/(?:http:\/\/.*|https:\/\/.*)(.users.)(.*)(\/phones)/g,
		workbox.strategies.staleWhileRevalidate({
			cacheName: CACHE_NAME
		})
	);

	/* workbox.routing.registerRoute(
		new RegExp('/\/api\/v2\/users/'),
		workbox.strategies.staleWhileRevalidate(),
		'GET'
	) */

	workbox.routing.registerRoute(
		/(?:http:\/\/.*|https:\/\/.*)(users)(\/me)?/ig,
		workbox.strategies.networkFirst({
			cacheName: CACHE_NAME
		}),
		'GET'
	)

	// Audit
	/*workbox.routing.registerRoute(
		/(?:http:\/\/.*|https:\/\/.*)(.audits\/checks\/.)(\w{1,})/g,
		workbox.strategies.networkOnly({
			cacheName: 'audit',
			plugins: [bgSyncPlugin]
		}),
		'PATCH'
	);*/

	// Current company
	workbox.routing.registerRoute(
		/(?:http:\/\/.*|https:\/\/.*)(.companies.)([\w0-9-]{1,})/g,
		workbox.strategies.staleWhileRevalidate()
	);

	// Tasks
	workbox.routing.registerRoute(
		/(?:http:\/\/.*|https:\/\/.*)(.tasks.)(\w{1,})/g,
		workbox.strategies.staleWhileRevalidate()
	);

	workbox.routing.registerRoute(
		/(?:http:\/\/.*|https:\/\/.*)(.audits\/states\/company\/.)(\w{1,})/g,
		workbox.strategies.staleWhileRevalidate()
	);

	// All
	workbox.routing.registerRoute(
		/(?:http:\/\/.*|https:\/\/.*)(?:api\/v2\/|v2\/)/g,
		workbox.strategies.networkFirst({
			cacheName: CACHE_NAME
		})
	);
} else {
	console.log(`Boo! Workbox didn't load 😬`);
}



