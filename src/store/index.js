import Vue from 'vue';
import Vuex from 'vuex';
import actions from './actions';
import getters from './getters';
import mutations from './mutations';
import state from './state';

// Modules
import addresses from '@/store/modules/addresses';
import analytics from '@/store/modules/analytics';
import archives from '@/store/modules/archives';
import audits from '@/store/modules/audits';
import checklists from '@/store/modules/checklists';
import checkpoints from '@/store/modules/checkpoints';
import companies from '@/store/modules/companies';
import conditions from '@/store/modules/conditions';
import countries from '@/store/modules/countries';
import dashboard from '@/store/modules/dashboard';
import directories from '@/store/modules/directories';
import extensions from '@/store/modules/extensions';
import grants from '@/store/modules/grants';
import groups from '@/store/modules/groups';
import invitations from '@/store/modules/invitations';
import locations from '@/store/modules/locations';
import notifications from '@/store/modules/notifications';
import phones from '@/store/modules/phones';
import scores from '@/store/modules/scores';
import scoringschemes from '@/store/modules/scoringschemes';
import sections from '@/store/modules/sections';
import subscriptions from '@/store/modules/subscriptions';
import tasks from '@/store/modules/tasks';
import users from '@/store/modules/users';
import valueschemes from '@/store/modules/valueschemes';

Vue.use(Vuex);

/* eslint-disable no-new */
export default new Vuex.Store({
	actions,
	getters,
	mutations,
	state,

	modules: {
		addresses,
		analytics,
		archives,
		audits,
		checklists,
		checkpoints,
		companies,
		conditions,
		countries,
		dashboard,
		directories,
		extensions,
		grants,
		groups,
		invitations,
		locations,
		notifications,
		phones,
		scores,
		scoringschemes,
		sections,
		subscriptions,
		tasks,
		users,
		valueschemes
	}
});
