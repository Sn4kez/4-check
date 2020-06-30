import i18n from '@/i18n/index';
import AppLayoutComponent from '@/layouts/AppLayout';

const DesignView = () => import('@/views/settings/Design');
const GeneralSettingsView = () => import('@/views/settings/GeneralSettings');
const IndexView = () => import('@/views/settings/index');
const SubscriptionView = () => import('@/views/settings/Subscription');
const UserProfile = () => import('@/views/users/UserProfile');

export default {
	path: '/settings',
	component: AppLayoutComponent,
	children: [
		{
			path: '',
			name: 'IndexView',
			component: IndexView,
			meta: {
				requiresAuth: true,
				role: 'admin',
				topbar: {
					name: 'SETTINGS',
					showBackButton: true,
					icon: 'settings'
				}
			}
		},
		{
			path: 'general',
			name: 'GeneralSettingsView',
			component: GeneralSettingsView,
			meta: {
				requiresAuth: true,
				role: 'admin',
				topbar: {
					name: 'GENERAL_SETTINGS',
					showBackButton: true,
					icon: 'settings_input_component'
				}
			}
		},
		{
			path: 'design',
			name: 'DesignView',
			component: DesignView,
			meta: {
				requiresAuth: true,
				role: 'admin',
				topbar: {
					name: 'DESIGN_SETTINGS',
					showBackButton: true,
					icon: 'color_lens'
				}
			}
		},
		{
			path: 'user',
			component: UserProfile,
			children: [
				{
					path: '',
					redirect: 'profile'
				},
				{
					path: 'profile',
					alias: 'profile/:id',
					name: 'UserProfile',
					component: UserProfile,
					meta: {
						requiresAuth: true,
						role: 'user',
						topbar: {
							name: 'PERSONAL_PROFILE',
							showBackButton: true,
							icon: 'person'
						}
					}
				}
			]
		},
		{
			path: 'subscription',
			name: 'SubscriptionView',
			component: SubscriptionView,
			meta: {
				requiresAuth: true,
				role: 'admin',
				topbar: {
					name: 'SUBSCRIPTION',
					showBackButton: true,
					icon: 'payment'
				}
			}
		}
	]
};
