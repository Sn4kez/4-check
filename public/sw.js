importScripts('https://storage.googleapis.com/workbox-cdn/releases/3.6.1/workbox-sw.js');

console.log('----------------service worker.js');

if (workbox) {
	console.log(`Yay! Workbox is loaded 2 🎉`, workbox);

	/*const bgSyncPlugin = new workbox.backgroundSync.Plugin('audit', {
		maxRetentionTime: 24 * 60 // Retry for max of 24 Hours
	});*/

	/*const bgSyncPlugin = new workbox.backgroundSync.Plugin(
		'4-check-queue'
	)*/

	const handlerCb = ({url, event, params}) => {
		console.log('Handler CB called: ', url);
		console.log(event);
		console.log(params);
		/*return fetch(event.request)
		.then((response) => {
			console.log(response);
			return response.text();
		})
		.then((responseBody) => {
			console.log(responseBody);
			return new Response(`${responseBody} <!-- Look Ma. Added Content. -->`);
		});*/
	};

	workbox.routing.registerRoute(
		/\/api\/v2\/locations/,
		handlerCb,
		'GET'
	)

	workbox.routing.registerRoute(
		/\/api\/v2\/locations/,
		handlerCb,
		'PATCH'
	)

	workbox.routing.registerRoute(
		/\/api\/v2\/locations/,
		handlerCb,
		'OPTIONS'
	)

	workbox.routing.registerRoute(
		/\/api\/v2\/locations/,
		handlerCb,
		'POST'
	)

	// caching static assets
	/*workbox.router.get(/\.(?:js|css)$/, workbox.cacheFirst, {
		cache: {
			name: 'pages'
		}
	})*/

	// All
	/*workbox.routing.registerRoute(
		/(?:http:\/\/.*|https:\/\/.*)(?:api\/v2\/|v2\/)/g,
		workbox.strategies.networkFirst({
			cacheName: '4-check-api'
		})
	);*/

	// Audit
	/*workbox.routing.registerRoute(
		/(?:http:\/\/.*|https:\/\/.*)(.audits\/checks\/.)(\w{1,})/g,
		workbox.strategies.networkOnly({
			cacheName: 'audit',
			plugins: [bgSyncPlugin]
		}),
		'PATCH'
	);*/

	// Audits for company
	// workbox.routing.registerRoute(
	// 	/(?:http:\/\/.*|https:\/\/.*)(.audits\/company\/.)(\w{1,})/g,
	// 	workbox.strategies.networkFirst({
	// 		cacheName: 'audits'
	// 	})
	// );

	// Audit checks
	// workbox.routing.registerRoute(
	// 	/(?:http:\/\/.*|https:\/\/.*)(.audits\/checks\/.)(\w{1,})/g,
	// 	workbox.strategies.networkFirst({
	// 		cacheName: 'audit'
	// 	})
	// );

	// workbox.routing.registerRoute(
	// 	/(?:http:\/\/.*|https:\/\/.*)(.audits\/checks\/.)(\w{1,})/g,
	// 	workbox.strategies.networkOnly({
	// 		cacheName: 'audit',
	// 		plugins: [bgSyncPlugin]
	// 	}),
	// 	'PATCH'
	// );

	// Audit entries
	// workbox.routing.registerRoute(
	// 	/(?:http:\/\/.*|https:\/\/.*)(.audits\/entries\/.)(\w{1,})/g,
	// 	workbox.strategies.networkFirst({
	// 		cacheName: 'audit'
	// 	})
	// );

	// Value schemes
	// workbox.routing.registerRoute(
	// 	/(?:http:\/\/.*|https:\/\/.*)(.companies.)(.*)(\/valueschemes)/g,
	// 	workbox.strategies.networkFirst({
	// 		cacheName: 'valueSchemes'
	// 	})
	// );

	// Conditions
	// workbox.routing.registerRoute(
	// 	/(?:http:\/\/.*|https:\/\/.*)(.valueschemes.)(.*)(\/conditions)/g,
	// 	workbox.strategies.networkFirst({
	// 		cacheName: 'conditions'
	// 	})
	// );

	// Scoring Schemes
	// workbox.routing.registerRoute(
	// 	/(?:http:\/\/.*|https:\/\/.*)(.companies.)(.*)(\/scoringschemes)/g,
	// 	workbox.strategies.networkFirst({
	// 		cacheName: 'scoringSchemes'
	// 	})
	// );

	// Scores
	// workbox.routing.registerRoute(
	// 	/(?:http:\/\/.*|https:\/\/.*)(.scoringschemes.)(.*)(\/scores)/g,
	// 	workbox.strategies.networkFirst({
	// 		cacheName: 'scores'
	// 	})
	// );

	// Current user
	// workbox.routing.registerRoute(
	// 	/(?:http:\/\/.*|https:\/\/.*)(.users.)(\w{1,})/g,
	// 	workbox.strategies.networkFirst({
	// 		cacheName: 'users'
	// 	})
	// );

	// Company user
	// workbox.routing.registerRoute(
	// 	/(?:http:\/\/.*|https:\/\/.*)(.companies.)(.*)(\/users)/g,
	// 	workbox.strategies.networkFirst({
	// 		cacheName: 'users'
	// 	})
	// );

	// Phones
	// workbox.routing.registerRoute(
	// 	/(?:http:\/\/.*|https:\/\/.*)(.users.)(.*)(\/phones)/g,
	// 	workbox.strategies.networkFirst({
	// 		cacheName: 'users'
	// 	})
	// );

	// Current company
	// workbox.routing.registerRoute(
	// 	/(?:http:\/\/.*|https:\/\/.*)(.companies.)(\w{1,})/g,
	// 	workbox.strategies.networkFirst({
	// 		cacheName: 'currentCompany'
	// 	})
	// );

	// Preferences
	// workbox.routing.registerRoute(
	// 	/(?:http:\/\/.*|https:\/\/.*)(.companies\/preferences\/.)(\w{1,})/g,
	// 	workbox.strategies.networkFirst({
	// 		cacheName: 'company'
	// 	})
	// );

	// Addresses
	// workbox.routing.registerRoute(
	// 	/(?:http:\/\/.*|https:\/\/.*)(.companies.)(.*)(\/addresses)/g,
	// 	workbox.strategies.networkFirst({
	// 		cacheName: 'company'
	// 	})
	// );

	// Locations
	// workbox.routing.registerRoute(
	// 	/(?:http:\/\/.*|https:\/\/.*)(.locations.)(\w{1,})/g,
	// 	workbox.strategies.networkFirst({
	// 		cacheName: 'locations'
	// 	})
	// );

	/*workbox.routing.registerRoute(
		/(?:http:\/\/.*|https:\/\/.*)(.locations.)(\w{1,})/g,
		handlerCb,
		'GET'
	)

	workbox.routing.registerRoute(
		/(?:http:\/\/.*|https:\/\/.*)(.locations\/company\/.)(\w{1,})/g,
		handlerCb,
		'GET'
		// workbox.strategies.networkFirst({
		// 	cacheName: 'locations'
		// })
	);*/

	// Tasks
	// workbox.routing.registerRoute(
	// 	/(?:http:\/\/.*|https:\/\/.*)(.tasks.)(\w{1,})/g,
	// 	workbox.strategies.networkFirst({
	// 		cacheName: 'tasks'
	// 	})
	// );

	// workbox.routing.registerRoute(
	// 	/(?:http:\/\/.*|https:\/\/.*)(.tasks\/company\/.)(\w{1,})/g,
	// 	workbox.strategies.networkFirst({
	// 		cacheName: 'tasks'
	// 	})
	// );

	// Checklists
	// workbox.routing.registerRoute(
	// 	/(?:http:\/\/.*|https:\/\/.*)(.checklists)(\w{1,})/g,
	// 	workbox.strategies.networkFirst({
	// 		cacheName: 'checklists'
	// 	})
	// );

	// Checklist entries
	// workbox.routing.registerRoute(
	// 	/(?:http:\/\/.*|https:\/\/.*)(.checklists.)(.*)(\/entries)/g,
	// 	workbox.strategies.networkFirst({
	// 		cacheName: 'checklists'
	// 	})
	// );

	// Checklist grants
	// workbox.routing.registerRoute(
	// 	/(?:http:\/\/.*|https:\/\/.*)(.checklists.)(.*)(\/grants)/g,
	// 	workbox.strategies.networkFirst({
	// 		cacheName: 'checklists'
	// 	})
	// );

	// Checklist checkpoints
	// workbox.routing.registerRoute(
	// 	/(?:http:\/\/.*|https:\/\/.*)(.checklists.)(.*)(\/checkpoints)/g,
	// 	workbox.strategies.networkFirst({
	// 		cacheName: 'checklists'
	// 	})
	// );

	// Checklist sections
	// workbox.routing.registerRoute(
	// 	/(?:http:\/\/.*|https:\/\/.*)(.checklists.)(.*)(\/sections)/g,
	// 	workbox.strategies.networkFirst({
	// 		cacheName: 'checklists'
	// 	})
	// );

	// Checklist extensions
	// workbox.routing.registerRoute(
	// 	/(?:http:\/\/.*|https:\/\/.*)(.checklists.)(.*)(\/extensions)/g,
	// 	workbox.strategies.networkFirst({
	// 		cacheName: 'checklists'
	// 	})
	// );

	// Checklist scoring schemes
	// workbox.routing.registerRoute(
	// 	/(?:http:\/\/.*|https:\/\/.*)(.checklists.)(.*)(\/scoringschemes)/g,
	// 	workbox.strategies.networkFirst({
	// 		cacheName: 'checklists'
	// 	})
	// );

	// Checklist approvers
	// workbox.routing.registerRoute(
	// 	/(?:http:\/\/.*|https:\/\/.*)(.checklists.)(.*)(\/approvers)/g,
	// 	workbox.strategies.networkFirst({
	// 		cacheName: 'checklists'
	// 	})
	// );

	// Section
	// workbox.routing.registerRoute(
	// 	/(?:http:\/\/.*|https:\/\/.*)(.sections)(\w{1,})/g,
	// 	workbox.strategies.networkFirst({
	// 		cacheName: 'sections'
	// 	})
	// );

	// Section checkpoints
	// workbox.routing.registerRoute(
	// 	/(?:http:\/\/.*|https:\/\/.*)(.sections.)(.*)(\/checkpoints)/g,
	// 	workbox.strategies.networkFirst({
	// 		cacheName: 'sections'
	// 	})
	// );

	// Section extensions
	// workbox.routing.registerRoute(
	// 	/(?:http:\/\/.*|https:\/\/.*)(.sections.)(.*)(\/extensions)/g,
	// 	workbox.strategies.networkFirst({
	// 		cacheName: 'sections'
	// 	})
	// );

	// Section entries
	// workbox.routing.registerRoute(
	// 	/(?:http:\/\/.*|https:\/\/.*)(.sections.)(.*)(\/entries)/g,
	// 	workbox.strategies.networkFirst({
	// 		cacheName: 'sections'
	// 	})
	// );

	// Directories
	// workbox.routing.registerRoute(
	// 	/(?:http:\/\/.*|https:\/\/.*)(.directories)(\w{1,})/g,
	// 	workbox.strategies.networkFirst({
	// 		cacheName: 'directories'
	// 	})
	// );

	// Directory entries
	// workbox.routing.registerRoute(
	// 	/(?:http:\/\/.*|https:\/\/.*)(.directories.)(.*)(\/entries)/g,
	// 	workbox.strategies.networkFirst({
	// 		cacheName: 'directories'
	// 	})
	// );

	// Directory grants
	// workbox.routing.registerRoute(
	// 	/(?:http:\/\/.*|https:\/\/.*)(.directories.)(.*)(\/grants)/g,
	// 	workbox.strategies.networkFirst({
	// 		cacheName: 'directories'
	// 	})
	// );

	// Notifications
	// workbox.routing.registerRoute(
	// 	/(?:http:\/\/.*|https:\/\/.*)(.notifications)/g,
	// 	workbox.strategies.networkFirst({
	// 		cacheName: 'notifications'
	// 	})
	// );

	// Media
	// workbox.routing.registerRoute(
	// 	/(?:http:\/\/.*|https:\/\/.*)(.media)/g,
	// 	workbox.strategies.networkFirst({
	// 		cacheName: 'media'
	// 	})
	// );

	// Dashboard
	// workbox.routing.registerRoute(
	// 	/(?:http:\/\/.*|https:\/\/.*)(.dashboard)/g,
	// 	workbox.strategies.networkFirst({
	// 		cacheName: 'dashboard'
	// 	})
	// );

	// workbox.routing.registerRoute(
	// 	/(?:http:\/\/.*|https:\/\/.*)(.dashboard.)(.*)/g,
	// 	workbox.strategies.networkFirst({
	// 		cacheName: 'dashboard'
	// 	})
	// );
} else {
	console.log(`Boo! Workbox didn't load 😬`);
}
