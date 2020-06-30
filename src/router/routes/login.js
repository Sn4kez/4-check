import DefaultLayoutComponent from '@/layouts/DefaultLayout';

const LoginView = () => import('@/views/login/index');
const ResetPasswordView = () => import('@/views/login/ResetPassword');
const RenewPasswordView = () => import('@/views/login/RenewPassword');

export default {
	path: '/login',
	component: DefaultLayoutComponent,
	children: [
		{
			path: '',
			name: 'LoginView',
			component: LoginView
		},
		{
			path: 'resetpassword',
			name: 'ResetPasswordView',
			component: ResetPasswordView
		},
		{
			path: 'renewpassword',
			name: 'RenewPasswordView',
			component: RenewPasswordView
		}
	]
};
