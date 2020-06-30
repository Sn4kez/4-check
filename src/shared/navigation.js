import i18n from '@/i18n/index';

/**
 * MAIN NAVIGATION
 */
const mainNavItems = [
	{
		id: 1,
		name: 'DASHBOARD',
		route: {
			path: '/dashboard'
		},
		icon: 'dashboard',
		role: 'user',
		children: []
	},
	{
		id: 2,
		name: 'CHECKLISTS',
		route: {
			path: '/checklists'
		},
		icon: 'check_circle_outline',
		role: 'user',
		children: [
			{
				id: 21,
				name: 'CHECKLISTS',
				route: {
					path: '/checklists/directories'
				},
				icon: 'check_box',
				role: 'user'
			},
			{
				id: 22,
				name: 'RATING_SYSTEMS',
				route: {
					path: '/checklists/rating-systems'
				},
				icon: 'grade',
				role: 'user'
			},
			// {
			// 	id: 23,
			// 	name: 'TEMPLATE_LIBRARY',
			// 	route: {
			// 		path: '/checklists/template-library'
			// 	},
			// 	icon: 'ballot'
			// },
			{
				id: 24,
				name: 'ARCHIVE',
				route: {
					path: '/checklists/archive'
				},
				icon: 'archive',
				role: 'user'
			}
		]
	},
	{
		id: 3,
		name: 'TASKS',
		route: {
			path: '/tasks'
		},
		icon: 'format_list_bulleted',
		role: 'user',
		children: []
	},
	// {
	// 	id: 4,
	// 	name: 'SCHEDULE',
	// 	route: {
	// 		path: '/schedule'
	// 	},
	// 	icon: 'today',
	// 	children: []
	// },
	// {
	// 	id: 5,
	// 	name: 'ANALYSIS',
	// 	route: {
	// 		path: '/analysis'
	// 	},
	// 	icon: 'show_chart',
	// 	children: []
	// },
	{
		id: 6,
		name: 'LOCATIONS',
		route: {
			path: '/locations'
		},
		icon: 'place',
		role: 'user',
		children: []
	},
	// {
	// 	id: 7,
	// 	name: 'DOCUMENTS',
	// 	route: {
	// 		path: '/documents'
	// 	},
	// 	icon: 'description',
	// 	children: []
	// },
	{
		id: 8,
		name: 'USER',
		route: {
			path: '/users'
		},
		icon: 'account_circle',
		role: 'admin',
		children: [
			{
				id: 81,
				name: 'USER_MANAGEMENT',
				route: {
					path: '/users/management'
				},
				icon: 'supervised_user_circle',
				role: 'admin'
			}
			// {
			// 	id: 82,
			// 	name: 'PERMISSIONS',
			// 	route: {
			// 		path: '/users/permissions'
			// 	},
			// 	icon: 'pan_tool',
			// 	role: 'admin'
			// }
		]
	},
	{
		id: 9,
		name: 'SETTINGS',
		route: {
			path: '/settings'
		},
		icon: 'settings',
		role: 'admin',
		children: [
			// {
			// 	id: 91,
			// 	name: 'PERSONAL_PROFILE',
			// 	route: {
			// 		path: '/settings/user/profile'
			// 	},
			// 	icon: 'person'
			// },
			{
				id: 92,
				name: 'GENERAL_SETTINGS',
				route: {
					path: '/settings/general'
				},
				icon: 'settings_input_component',
				role: 'admin'
			},
			{
				id: 93,
				name: 'DESIGN',
				route: {
					path: '/settings/design'
				},
				icon: 'color_lens',
				role: 'admin'
			}
			// {
			// 	id: 94,
			// 	name: 'INTEGRATION',
			// 	route: {
			// 		path: '/settings/integration'
			// 	},
			// 	icon: 'share'
			// },
			// {
			// 	id: 95,
			// 	name: 'SUBSCRIPTION',
			// 	route: {
			// 		path: '/settings/subscription'
			// 	},
			// 	icon: 'payment'
			// }
		]
	}
];

/**
 * SUBNAV ITEMS
 */
const subNavItems = [
	{
		id: 1,
		name: 'ACCOUNT_MANAGEMENT',
		route: {
			path: '/admin/account-management'
		},
		icon: 'supervised_user_circle',
		role: 'superadmin',
		children: []
	},
	{
		id: 2,
		name: 'HELP',
		route: {
			path: '/help'
		},
		icon: 'help_outline',
		role: 'user',
		children: []
	}
];

/**
 * TABBAR NAV ITEMS
 */
const tabBarNavItems = [
	{
		id: 1,
		name: 'DASHBOARD',
		route: {
			path: '/dashboard'
		},
		icon: 'dashboard',
		role: 'user'
	},
	{
		id: 2,
		name: 'CHECKLISTS',
		route: {
			path: '/checklists/index'
		},
		icon: 'check_circle_outline',
		role: 'user'
	},
	{
		id: 3,
		name: 'LOCATIONS',
		route: {
			path: '/locations'
		},
		icon: 'place',
		role: 'user'
	},
	{
		id: 4,
		name: 'TASKS',
		route: {
			path: '/tasks'
		},
		icon: 'format_list_bulleted',
		role: 'user'
	},
	{
		id: 5,
		name: 'MORE',
		route: {
			path: '/more'
		},
		icon: 'more_horiz',
		role: 'user'
	}
];

/**
 * TABBAR MORE NAV ITEMS
 */
const tabBarNavMoreItems = [
	// {
	// 	name: 'SCHEDULE',
	// 	route: {
	// 		path: '/schedule'
	// 	},
	// 	icon: 'today'
	// },
	// {
	// 	name: 'ANALYSIS',
	// 	route: {
	// 		path: '/analysis'
	// 	},
	// 	icon: 'show_chart'
	// },
	{
		name: 'MY_PROFILE',
		route: {
			path: '/settings/user/profile'
		},
		icon: 'person',
		role: 'user'
	},
	{
		name: 'USERS',
		route: {
			path: '/users'
		},
		icon: 'supervisor_account',
		role: 'admin'
	},
	{
		name: 'SETTINGS',
		route: {
			path: '/settings'
		},
		icon: 'settings',
		role: 'admin'
	},
	{
		name: 'ACCOUNT_MANAGEMENT',
		route: {
			path: '/admin/account-management'
		},
		icon: 'supervised_user_circle',
		role: 'superadmin'
	},
	{
		name: 'HELP',
		route: {
			path: '/help'
		},
		icon: 'help_outline',
		role: 'user'
	},
	{
		name: 'LOGOUT',
		route: {
			path: '/logout'
		},
		icon: 'exit_to_app',
		role: 'user'
	}
];

export default {
	mainNavItems,
	subNavItems,
	tabBarNavItems,
	tabBarNavMoreItems
};
