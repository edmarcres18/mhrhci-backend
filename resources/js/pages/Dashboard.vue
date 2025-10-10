<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import StatCard from '@/components/dashboard/StatCard.vue';
import OverviewChart from '@/components/dashboard/OverviewChart.vue';
import RecentSales from '@/components/dashboard/RecentSales.vue';
import LatestBackup from '@/components/dashboard/LatestBackup.vue';
import { Activity, FileText, Package, RefreshCw, Users } from 'lucide-vue-next';
import { useDashboard } from '@/composables/useDashboard';
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

const {
    stats,
    overview,
    recentActivity,
    latestBackup,
    isLoadingStats,
    isLoadingOverview,
    isLoadingActivity,
    isLoadingBackup,
    refresh,
} = useDashboard({ autoRefresh: true });

const page = usePage();
const user = computed(() => page.props.auth?.user);
const isSystemAdmin = computed(() => user.value?.role === 'system_admin');
const isStaff = computed(() => user.value?.role === 'staff');

const isRefreshing = computed(() => 
    isLoadingStats.value || isLoadingOverview.value || isLoadingActivity.value
);

// Filter overview data for staff users to only show blogs and products
const filteredOverview = computed(() => {
    if (!isStaff.value || !overview.value) return overview.value;
    
    return {
        ...overview.value,
        datasets: overview.value.datasets.filter(
            dataset => dataset.name === 'Blogs' || dataset.name === 'Products'
        )
    };
});

const handleRefresh = async () => {
    await refresh();
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-4 p-4 md:gap-8 md:p-6">
            <!-- Page Header -->
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <h2 class="text-2xl font-bold tracking-tight sm:text-3xl">Dashboard</h2>
                    <p class="text-sm text-muted-foreground">
                        Real-time overview of your system metrics
                    </p>
                </div>
                <div class="flex items-center gap-2 sm:self-auto">
                    <Button 
                        variant="outline" 
                        size="sm"
                        @click="handleRefresh"
                        :disabled="isRefreshing"
                        class="w-full sm:w-auto"
                    >
                        <RefreshCw 
                            class="mr-2 size-4" 
                            :class="{ 'animate-spin': isRefreshing }"
                        />
                        Refresh
                    </Button>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <StatCard
                    title="Total Users"
                    :value="stats?.users.total ?? 0"
                    :change="`${stats?.users.percentage_change ?? 0}% from last month`"
                    :trend="stats?.users.trend"
                    :loading="isLoadingStats"
                    :icon="Users"
                />
                <StatCard
                    title="Blogs"
                    :value="stats?.blogs.total ?? 0"
                    :change="`${stats?.blogs.percentage_change ?? 0}% from last month`"
                    :trend="stats?.blogs.trend"
                    :loading="isLoadingStats"
                    :icon="FileText"
                />
                <StatCard
                    title="Products"
                    :value="stats?.products.total ?? 0"
                    :change="`${stats?.products.percentage_change ?? 0}% from last month`"
                    :trend="stats?.products.trend"
                    :loading="isLoadingStats"
                    :icon="Package"
                />
                <StatCard
                    title="Active Now"
                    :value="stats?.activity.total ?? 0"
                    :change="`${stats?.activity.change ?? 0} since last hour`"
                    :trend="(stats?.activity.change ?? 0) >= 0 ? 'up' : 'down'"
                    :loading="isLoadingStats"
                    :icon="Activity"
                />
            </div>

            <!-- Main Content Grid -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-7">
                <!-- Overview Chart -->
                <Card :class="isSystemAdmin ? 'col-span-full lg:col-span-4' : 'col-span-full lg:col-span-5'">
                    <CardHeader>
                        <CardTitle>Overview</CardTitle>
                        <CardDescription>
                            {{ isStaff ? 'Monthly trends for blogs and products' : 'Monthly trends for users, blogs, and products' }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="pl-0 sm:pl-2">
                        <div class="h-[240px] sm:h-[300px] md:h-[350px]">
                            <OverviewChart 
                                :data="filteredOverview" 
                                :loading="isLoadingOverview"
                            />
                        </div>
                    </CardContent>
                </Card>

                <!-- Recent Activity -->
                <Card :class="isSystemAdmin ? 'col-span-full lg:col-span-3' : 'col-span-full lg:col-span-2'">
                    <CardHeader>
                        <CardTitle>Recent Activity</CardTitle>
                        <CardDescription>
                            Latest additions to your system
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <RecentSales 
                            :data="recentActivity" 
                            :loading="isLoadingActivity"
                        />
                    </CardContent>
                </Card>
            </div>

            <!-- Latest Backup (System Admin Only) -->
            <div v-if="isSystemAdmin" class="grid gap-4">
                <Card>
                    <CardHeader>
                        <CardTitle>Latest Database Backup</CardTitle>
                        <CardDescription>
                            Most recent database backup information
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <LatestBackup 
                            :data="latestBackup" 
                            :loading="isLoadingBackup"
                        />
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
