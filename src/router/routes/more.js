import i18n from '@/i18n/index';
import AppLayoutComponent from '@/layouts/AppLayout';

const MoreLinksView = () => import('@/views/more/MoreLinks');

export default {
	path: '/more',
	component: AppLayoutComponent,
	children: [
		{
			path: '',
			name: 'MoreLinksView',
			component: MoreLinksView,
			meta: {
				requiresAuth: true,
				role: 'user',
				topbar: {
					name: 'MORE',
					showBackButton: false,
					icon: 'more_vert'
				}
			}
		}
	]
};
