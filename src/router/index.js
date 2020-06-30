import Vue from 'vue';
import Router from 'vue-router';
import routes from './routes';
import store from './../store/index';

Vue.use(Router);

const router = new Router({
	routes,

	linkActiveClass: 'is-active',

	linkExactActiveClass: 'is-active--exact'
});

function isRoleSufficent(item) {
	if (store.state.user.data.role === item.role) {
		return true;
	}

	if (store.state.user.data.role === 'admin' && item.role === 'user') {
		return true;
	}

	if (store.state.user.data.role === 'superadmin') {
		return true;
	}

	return false;
}

/**
 * Process for each route to check for authentification and redirection
 * @param  {Object} to
 * @param  {Object} from
 * @param  {Function} next
 * @return {Function}
 */
router.beforeEach((to, from, next) => {
	console.log('to from', to, from, to.meta.requiresAuth, store.state.user.data.role);

	// Close modal/dialog on page change
	store.commit('CLOSE_DIALOG');
	store.commit('CLOSE_MODAL');
		
	const tt = Vue.localStorage.get('access_token');

	if(tt != undefined){
		// means token in localstorage, make a copy in sessionstorage
		Vue.prototype.$session.set('access_token', tt);
	}


	// Redirect user to login page if not authenticated
	if (to.meta.requiresAuth) {
		const token = Vue.prototype.$session.get('access_token');
		
		if (!token || !token.length) {
			next({ path: '/login' });
		}

		// ToDo: Improve! Currently we need to wait until user data has been loaded.
		// After that we can compare user role and minimum navigation role
		if (store.state.user.data.role) {
			if (!isRoleSufficent(to.meta)) {
				console.log(isRoleSufficent(to.meta), '-----------', to.meta);
				next({ path: '/dashboard' });
			}
		}
	}

	next();
});

/**
 * Process after each route
 * @param  {Object} to
 * @param  {Object} from
 * @return {void}
 */
router.afterEach((to, from) => {
	// Change topbar view name
	if (to.meta.topbar) {
		store.commit('SET_TOPBAR', to.meta.topbar);
	}

	// Show /Hide FAB to create new items
	store.commit('SET_BUTTON_FAB_CREATE_NEW_VISIBILITY', { visible: to.meta.showButtonFabCreateNew });
});

export default router;
