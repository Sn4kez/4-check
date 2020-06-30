import i18n from '@/i18n/index';
import AppLayoutComponent from '@/layouts/AppLayout';

const ScheduleView = () => import('@/views/schedule/index');

export default {
	path: '/schedule',
	component: AppLayoutComponent,
	children: [
		{
			path: '',
			name: 'ScheduleView',
			component: ScheduleView,
			meta: {
				requiresAuth: true,
				role: 'user',
				topbar: {
					name: 'SCHEDULE',
					showBackButton: true,
					icon: 'schedule'
				}
			}
		}
	]
};
