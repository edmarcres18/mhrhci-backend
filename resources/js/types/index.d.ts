import { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon;
    isActive?: boolean;
}

export interface NavGroup {
    label?: string;
    items: NavItem[];
}

export type AppPageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    sidebarOpen: boolean;
};

export interface User {
    id: number;
    name: string;
    email: string;
    role?: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export type BreadcrumbItemType = BreadcrumbItem;

// Dashboard Types
export interface DashboardStats {
    users: EntityStats;
    blogs: EntityStats;
    products: EntityStats;
    activity: ActivityStats;
}

export interface EntityStats {
    total: number;
    last_month: number;
    last_week: number;
    percentage_change: number;
    trend: 'up' | 'down';
}

export interface ActivityStats {
    total: number;
    users: number;
    blogs: number;
    products: number;
    previous_hour: number;
    change: number;
}

export interface DashboardOverview {
    labels: string[];
    datasets: DatasetItem[];
}

export interface DatasetItem {
    name: string;
    data: number[];
}

export interface RecentActivity {
    users: RecentUser[];
    blogs: RecentBlog[];
    products: RecentProduct[];
}

export interface RecentUser {
    id: number;
    name: string;
    email: string;
    avatar: string;
    created_at: string;
    type: 'user';
}

export interface RecentBlog {
    id: number;
    title: string;
    created_at: string;
    type: 'blog';
}

export interface RecentProduct {
    id: number;
    name: string;
    created_at: string;
    type: 'product';
}

export type RecentItem = RecentUser | RecentBlog | RecentProduct;

export interface LatestBackup {
    filename: string;
    size: string;
    size_bytes: number;
    created_at: string;
    created_at_human: string;
    timestamp: number;
}

export interface DashboardApiResponse<T> {
    success: boolean;
    data: T;
    cached_at?: string;
    message?: string;
    error?: string;
}
