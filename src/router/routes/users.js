import i18n from '@/i18n/index';
import AppLayoutComponent from '@/layouts/AppLayout';

const UserManagementView = () => import('@/views/users/UserManagement');
const PermissionView = () => import('@/views/users/Permission');


export default {
	path: '/users',
	component: AppLayoutComponent,
	children: [
		{
			path: '',
			redirect: 'management'
		},
		{
			path: 'management',
			name: 'UserManagementView',
			component: UserManagementView,
			meta: {
				requiresAuth: true,
				role: 'admin',
				topbar: {
					name: 'USER_MANAGEMENT',
					showBackButton: true,
					icon: 'supervised_user_circle'
				}
			}
		},
		{
			path: 'permissions',
			name: 'PermissionView',
			component: PermissionView,
			meta: {
				requiresAuth: true,
				role: 'admin',
				topbar: {
					name: 'PERMISSIONS',
					showBackButton: true,
					icon: 'pan_tool'
				}
			}
		}
	]
};
