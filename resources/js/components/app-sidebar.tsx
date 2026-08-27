import { Link, usePage } from '@inertiajs/react';
import {
    BookOpen,
    Briefcase,
    ChartColumn,
    ClipboardList,
    FileText,
    FolderGit2,
    LayoutGrid,
    Users,
} from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import applications from '@/routes/applications';
import jobs from '@/routes/jobs';
import reports from '@/routes/reports';
import users from '@/routes/users';
import type { Auth, NavItem } from '@/types';

/*
| [REUSE] This is the starter kit's own app-sidebar.tsx. Everything below the
|         imports is unchanged EXCEPT mainNavItems, which is now built inside
|         the component so it can react to the user's role.
*/

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/react-starter-kit',
        icon: FolderGit2,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#react',
        icon: BookOpen,
    },
];

export function AppSidebar() {
    /*
      [LEARN] usePage() reads the SHARED props that
              app/Http/Middleware/HandleInertiaRequests::share() sends with every
              response. That is how any component, at any depth, can know who is
              logged in without a single prop being passed down.
    */
    const { auth } = usePage().props as unknown as { auth: Auth };

    const canManage = auth.user.role === 'admin' || auth.user.role === 'editor';
    // [EXAM] PSRS 1.c - only an admin may manage people, so this is a
    //        SEPARATE check from canManage. Mirrors the two route groups.
    const isAdmin = auth.user.role === 'admin';

    /*
      [EXAM] PSRS 1.c - a viewer simply never sees Review or Reports.
      [LEARN] This is CONVENIENCE, NOT SECURITY. Anyone can type the URL. The
              real guard is 'role:admin,editor' in routes/web.php plus
              JobPolicy. Never let the frontend be the only thing saying no.
    */
    const mainNavItems: NavItem[] = [
        { title: 'Dashboard', href: dashboard(), icon: LayoutGrid },
        { title: 'Jobs', href: jobs.index(), icon: Briefcase },
        // No role gate - every signed-in user is a potential applicant.
        { title: 'My Applications', href: applications.mine(), icon: FileText },
        ...(canManage
            ? [
                  {
                      title: 'Review',
                      href: applications.review(),
                      icon: ClipboardList,
                  },
                  { title: 'Reports', href: reports.index(), icon: ChartColumn },
              ]
            : []),
        ...(isAdmin
            ? [{ title: 'Users', href: users.index(), icon: Users }]
            : []),
    ];

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
