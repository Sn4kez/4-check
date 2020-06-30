import i18n from '@/i18n/index';
import AppLayoutComponent from '@/layouts/AppLayout';

const ChecklistsArchiveView = () => import('@/views/checklists/Archive');
const ChecklistView = () => import('@/views/checklists/Checklist');
const DirectoriesView = () => import('@/views/checklists/Directories');
const IndexView = () => import('@/views/checklists/index');
const RatingSystemsView = () => import('@/views/checklists/RatingSystems');
const TemplateLibraryView = () => import('@/views/checklists/TemplateLibrary');

export default {
	path: '/checklists',
	component: AppLayoutComponent,
	children: [
		{
			path: '',
			redirect: 'directories'
		},
		{
			path: 'checklist/:id',
			name: 'ChecklistView',
			component: ChecklistView,
			meta: {
				requiresAuth: true,
				role: 'user',
				topbar: {
					name: 'CHECKLISTS',
					showBackButton: true,
					icon: 'check_box'
				}
			}
		},
		{
			path: 'index',
			name: 'IndexView',
			component: IndexView,
			meta: {
				requiresAuth: true,
				role: 'user',
				topbar: {
					name: 'CHECKLISTS',
					showBackButton: false,
					icon: 'check_circle_outline'
				}
			}
		},
        {
            path: 'directories/:id',
            name: 'DirectoriesViewDir',
            component: DirectoriesView,
            meta: {
                requiresAuth: true,
                role: 'user',
            }
        },
		{
			path: 'directories',
			alias: 'directories/:id',
			name: 'DirectoriesView',
			component: DirectoriesView,
			meta: {
				requiresAuth: true,
				role: 'user',
				topbar: {
					name: 'CHECKLISTS',
					showBackButton: true,
					icon: 'check_box'
				}
			}
		},
		{
			path: 'rating-systems',
			name: 'RatingSystemsView',
			component: RatingSystemsView,
			meta: {
				requiresAuth: true,
				role: 'user',
				topbar: {
					name: 'RATING_SYSTEMS',
					showBackButton: true,
					icon: 'grade'
				}
			}
		},
		{
			path: 'template-library',
			name: 'TemplateLibraryView',
			component: TemplateLibraryView,
			meta: {
				requiresAuth: true,
				role: 'user',
				topbar: {
					name: 'TEMPLATE_LIBRARY',
					showBackButton: true,
					icon: 'ballot'
				}
			}
		},
		{
			path: 'archive',
			alias: 'archive/:id',
			name: 'ChecklistsArchiveView',
			component: ChecklistsArchiveView,
			meta: {
				requiresAuth: true,
				role: 'user',
				topbar: {
					name: 'ARCHIVE',
					showBackButton: true,
					icon: 'archive'
				}
			}
		}
	]
};
