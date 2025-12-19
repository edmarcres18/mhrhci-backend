<script setup lang="ts">
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
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
import { type NavGroup } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { LayoutGrid, Package, FileText, Users, Info, Database, Settings, ClipboardList, Image, Building2 } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from './AppLogo.vue';

const page = usePage();
const canAccessUsers = computed(() => (page.props.auth as any)?.canAccessUsers ?? false);
const isSystemAdmin = computed(() => (page.props.auth as any)?.isSystemAdmin ?? false);

const mainNavGroups = computed<NavGroup[]>(() => {
    const groups: NavGroup[] = [
        {
            items: [
                {
                    title: 'Dashboard',
                    href: dashboard(),
                    icon: LayoutGrid,
                },
            ],
        },
        {
            label: 'Management',
            items: [
                {
                    title: 'Products',
                    href: '/products',
                    icon: Package,
                },
                {
                    title: 'Principals',
                    href: '/principals',
                    icon: Building2,
                },
                {
                    title: 'Blogs',
                    href: '/blogs',
                    icon: FileText,
                },
                {
                    title: 'Customer Registrations',
                    href: '/customer-registrations',
                    icon: ClipboardList,
                },
            ],
        },
    ];

    // Only show Administrative section if user can access users OR is system admin
    if (canAccessUsers.value || isSystemAdmin.value) {
        // Settings section (visible when user can access users)
        if (canAccessUsers.value) {
            groups.push({
                label: 'Settings',
                items: [
                    {
                        title: 'Contact Information',
                        href: '/site-information',
                        icon: Info,
                    },
                    {
                        title: 'Hero Backgrounds',
                        href: '/hero-backgrounds',
                        icon: Image,
                    },
                ],
            });
        }

        // Administrative section
        const adminItems = [] as { title: string; href: string; icon: any }[];
        // Users: accessible by Admin and System Admin
        if (canAccessUsers.value) {
            adminItems.push({
                title: 'Users',
                href: '/users',
                icon: Users,
            });
        }
        // Database Backup: accessible only by System Admin
        if (isSystemAdmin.value) {
            adminItems.push({
                title: 'Database Backup',
                href: '/database-backup',
                icon: Database,
            });
        }
        if (adminItems.length > 0) {
            groups.push({
                label: 'Administrative',
                items: adminItems,
            });
        }
    }

    return groups;
});

const footerNavGroups = computed<NavGroup[]>(() => {
    const groups: NavGroup[] = [];

    // Only show System section if user is system admin
    if (isSystemAdmin.value) {
        groups.push({
            items: [
                {
                    title: 'Site Settings',
                    href: '/site-settings',
                    icon: Settings,
                },
            ],
        });
    }

    return groups;
});
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :groups="mainNavGroups" />
        </SidebarContent>

        <SidebarFooter>
            <NavMain v-if="footerNavGroups.length > 0" :groups="footerNavGroups" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
