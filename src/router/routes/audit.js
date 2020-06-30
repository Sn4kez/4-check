import i18n from '@/i18n/index';
import AppLayoutComponent from '@/layouts/AppLayout';

const AuditView = () => import('@/views/audit/index');
const CreateAuditView = () => import('@/views/audit/CreateAudit');

export default {
	path: '/audit',
	component: AppLayoutComponent,
	children: [
		{
			path: '',
			name: 'AuditView',
			component: AuditView,
			meta: {
				requiresAuth: true,
				role: 'user',
				topbar: {
					name: 'AUDIT',
					showBackButton: true,
					icon: 'assignment_turned_in'
				}
			}
		},
		{
			path: 'create',
			name: 'CreateAuditView',
			component: CreateAuditView,
			meta: {
				requiresAuth: true,
				role: 'user',
				topbar: {
					name: 'AUDIT',
					showBackButton: true,
					icon: 'assignment_turned_in'
				}
			}
		},
		{
			path: 'edit/:id',
			name: 'EditAuditView',
			component: CreateAuditView,
			meta: {
				requiresAuth: true,
				role: 'user',
				topbar: {
					name: 'AUDIT',
					showBackButton: true,
					icon: 'assignment_turned_in',
					link: { path: '/checklists/directories' }
				}
			}
		}
	]
};
