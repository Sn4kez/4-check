import DefaultLayoutComponent from '@/layouts/DefaultLayout';

const LogoutView = () => import('@/views/logout/index');

export default {
	path: '/logout',
	component: DefaultLayoutComponent,
	children: [
		{
			path: '',
			name: 'LogoutView',
			component: LogoutView
		}
	]
};
