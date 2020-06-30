import i18n from '@/i18n/index';
import AppLayoutComponent from '@/layouts/AppLayout';

const DashboardView = () => import('@/views/dashboard/index');

export default {
	path: '/',
	component: AppLayoutComponent,
	children: [
		{
			path: '',
			redirect: '/dashboard'
		},
		{
			path: 'dashboard',
			name: 'DashboardView',
			component: DashboardView,
			meta: {
				requiresAuth: true,
				role: 'user',
				showButtonFabCreateNew: true,
				topbar: {
					name: 'DASHBOARD',
					showBackButton: false,
					icon: 'dashboard'
				}
			}
		}
	]
};
