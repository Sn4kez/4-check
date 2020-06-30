import i18n from '@/i18n/index';
import AppLayoutComponent from '@/layouts/AppLayout';

const LocationsView = () => import('@/views/locations/index');

export default {
	path: '/locations',
	component: AppLayoutComponent,
	children: [
		{
			path: '',
			name: 'LocationsView',
			component: LocationsView,
			meta: {
				requiresAuth: true,
				role: 'user',
				topbar: {
					name: 'LOCATIONS',
					showBackButton: false,
					icon: 'place'
				}
			}
		}
	]
};
