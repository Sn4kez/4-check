// Use one of these components to define the overall layout
import AppLayoutComponent from '@/layouts/AppLayout';

// Dynamically load component
const NotFoundView = () => import('@/views/404/index');

// Routes
import AdminRoutes from '@/router/routes/admin';
import AnalysisRoutes from '@/router/routes/analysis';
import AuditRoutes from '@/router/routes/audit';
import ChecklistsRoutes from '@/router/routes/checklists';
import CompanyRoutes from '@/router/routes/company';
import DashboardRoutes from '@/router/routes/dashboard';
import HelpRoutes from '@/router/routes/help';
import LocationsRoutes from '@/router/routes/locations';
import LoginRoutes from '@/router/routes/login';
import LogoutRoutes from '@/router/routes/logout';
import MoreRoutes from '@/router/routes/more';
import RegisterRoutes from '@/router/routes/register';
import ScheduleRoutes from '@/router/routes/schedule';
import SettingsRoutes from '@/router/routes/settings';
import TaskRoutes from '@/router/routes/task';
import UsersRoutes from '@/router/routes/users';

export default [
	AdminRoutes,

	AnalysisRoutes,

	AuditRoutes,

	ChecklistsRoutes,

	CompanyRoutes,

	DashboardRoutes,

	HelpRoutes,

	LocationsRoutes,

	LoginRoutes,

	LogoutRoutes,

	MoreRoutes,

	RegisterRoutes,

	ScheduleRoutes,

	SettingsRoutes,

	TaskRoutes,

	UsersRoutes,

	{
		path: '*',
		component: AppLayoutComponent,
		children: [
			{
				path: '',
				component: NotFoundView,
				meta: {
					requiresAuth: false,
					role: 'user',
					topbar: {
						name: '404',
						shoshowBackButtonwBack: false
					}
				}
			}
		]
	}
];
