import i18n from '@/i18n/index';
import AppLayoutComponent from '@/layouts/AppLayout';

const HelpIndexView = () => import('@/views/help/index');

export default {
	path: '/help',
	component: AppLayoutComponent,
	children: [
		{
			path: '',
			name: 'HelpIndexView',
			component: HelpIndexView,
			meta: {
				requiresAuth: true,
				role: 'user',
				showButtonFabCreateNew: false,
				topbar: {
					name: 'HELP',
					showBackButton: true,
					icon: 'help_outline'
				}
			}
		}
	]
};
