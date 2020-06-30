import i18n from '@/i18n/index';
import AppLayoutComponent from '@/layouts/AppLayout';

const AnalysisView = () => import('@/views/analysis/index');

export default {
	path: '/analysis',
	component: AppLayoutComponent,
	children: [
		{
			path: '',
			name: 'AnalysisView',
			component: AnalysisView,
			meta: {
				requiresAuth: true,
				role: 'user',
				topbar: {
					name: 'ANALYSIS',
					showBackButton: true,
					icon: 'show_chart'
				}
			}
		}
	]
};
