import DefaultLayoutComponent from '@/layouts/DefaultLayout';

const RegisterView = () => import('@/views/register/index');
const RegisterComplete = () => import('@/views/register/RegisterComplete');

export default {
	path: '/register',
	component: DefaultLayoutComponent,
	children: [
		{
			path: '',
			name: 'RegisterView',
			component: RegisterView
		},
		{
			path: 'complete/:token',
			name: 'RegisterComplete',
			component: RegisterComplete
		}
	]
};
