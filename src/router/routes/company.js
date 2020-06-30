import i18n from '@/i18n/index';
import AppLayoutComponent from '@/layouts/AppLayout';

const GroupView = () => import('@/views/company/Group');
const EditGroupView = () => import('@/views/company/EditGroup');

export default {
	path: '/company',
	component: AppLayoutComponent,
	children: [
		{
			path: '',
			redirect: 'group'
		},
		{
			path: 'group',
			component: GroupView,
			children: [
				{
					path: '',
					redirect: 'create'
				},
				{
					path: 'create',
					name: 'CreateGroupView',
					component: EditGroupView,
					props: { create: true },
					meta: {
						requiresAuth: true,
						role: 'admin',
						topbar: {
							name: 'CREATE_GROUP',
							showBackButton: true
						}
					}
				},
				{
					path: 'edit/:id',
					name: 'EditGroupView',
					component: EditGroupView,
					props: { create: false },
					meta: {
						requiresAuth: true,
						role: 'admin',
						topbar: {
							name: 'EDIT_GROUP',
							showBackButton: true
						}
					}
				}
			]
		}
	]
};
