import i18n from '@/i18n/index';
import AppLayoutComponent from '@/layouts/AppLayout';

const TasksView = () => import('@/views/tasks/index');

export default {
	path: '/tasks',
	component: AppLayoutComponent,
	children: [
		{
			path: '',
			name: 'TasksView',
			component: TasksView,
			meta: {
				requiresAuth: true,
				role: 'user',
				topbar: {
					name: 'TASKS',
					showBackButton: false,
					icon: 'format_list_bulleted'
				}
			}
		}
	]
};
