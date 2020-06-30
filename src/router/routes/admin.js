import i18n from '@/i18n/index';
import AppLayoutComponent from '@/layouts/AppLayout';

const AccountManagementView = () => import('@/views/admin/AccountManagement');
const AdminDashboardView = () => import('@/views/admin/index');

export default {
	path: '/admin',
	component: AppLayoutComponent,
	children: [
		{
			path: '',
			name: 'AdminDashboardView',
			component: AdminDashboardView,
			meta: {
				requiresAuth: true,
				role: 'superadmin',
				topbar: {
					name: 'DASHBOARD',
					showBackButton: false
				}
			}
		},
		{
			path: 'account-management',
			name: 'AccountManagementView',
			component: AccountManagementView,
			meta: {
				requiresAuth: true,
				role: 'superadmin',
				topbar: {
					name: 'ACCOUNT_MANAGEMENT',
					showBackButton: false
				}
			}
		}
	]
};
