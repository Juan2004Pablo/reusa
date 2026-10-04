import { Link, usePage } from '@inertiajs/react';
import { Package, PlusCircle, Search, ShieldCheck } from 'lucide-react';
import AppLogo from '@/components/app-logo';
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
import { home } from '@/routes';
import { dashboard as adminDashboard } from '@/routes/admin';
import { index as myPublications } from '@/routes/my-publications';
import { create, index as catalog } from '@/routes/publications';
import type { NavItem } from '@/types';

const mySpaceItems: NavItem[] = [
    { title: 'Mis publicaciones', href: myPublications(), icon: Package },
    { title: 'Publicar objeto', href: create(), icon: PlusCircle },
];

const exploreItems: NavItem[] = [
    { title: 'Catálogo', href: catalog(), icon: Search },
];

const adminItems: NavItem[] = [
    { title: 'Administración', href: adminDashboard(), icon: ShieldCheck },
];

export function AppSidebar() {
    const { auth } = usePage().props;

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={home()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mySpaceItems} label="Mi espacio" />
                <NavMain items={exploreItems} label="Explorar" />
                {auth.user?.is_admin && (
                    <NavMain items={adminItems} label="Moderación" />
                )}
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
