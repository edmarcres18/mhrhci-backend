import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import type {
    DashboardStats,
    DashboardOverview,
    RecentActivity,
    DashboardApiResponse,
    LatestBackup,
} from '@/types';

// Shared state for dashboard data
const stats = ref<DashboardStats | null>(null);
const overview = ref<DashboardOverview | null>(null);
const recentActivity = ref<RecentActivity | null>(null);
const latestBackup = ref<LatestBackup | null>(null);

const isLoadingStats = ref(false);
const isLoadingOverview = ref(false);
const isLoadingActivity = ref(false);
const isLoadingBackup = ref(false);

const statsError = ref<string | null>(null);
const overviewError = ref<string | null>(null);
const activityError = ref<string | null>(null);
const backupError = ref<string | null>(null);

const isStatsLoaded = ref(false);
const isOverviewLoaded = ref(false);
const isActivityLoaded = ref(false);
const isBackupLoaded = ref(false);

// Auto-refresh intervals (in milliseconds)
const STATS_REFRESH_INTERVAL = 30000; // 30 seconds
const OVERVIEW_REFRESH_INTERVAL = 60000; // 60 seconds
const ACTIVITY_REFRESH_INTERVAL = 15000; // 15 seconds
const BACKUP_REFRESH_INTERVAL = 60000; // 60 seconds

let statsIntervalId: ReturnType<typeof setInterval> | null = null;
let overviewIntervalId: ReturnType<typeof setInterval> | null = null;
let activityIntervalId: ReturnType<typeof setInterval> | null = null;
let backupIntervalId: ReturnType<typeof setInterval> | null = null;

export function useDashboard(options: { autoRefresh?: boolean } = {}) {
    const { autoRefresh = true } = options;

    /**
     * Fetch dashboard statistics.
     */
    const fetchStats = async (force = false) => {
        if (isStatsLoaded.value && !force) {
            return stats.value;
        }

        isLoadingStats.value = true;
        statsError.value = null;

        try {
            const response = await axios.get<DashboardApiResponse<DashboardStats>>(
                '/api/dashboard/stats'
            );

            if (response.data.success && response.data.data) {
                stats.value = response.data.data;
                isStatsLoaded.value = true;
            } else {
                throw new Error(response.data.message || 'Failed to fetch statistics');
            }
        } catch (error) {
            if (axios.isAxiosError(error)) {
                statsError.value =
                    error.response?.data?.message || 'Failed to fetch dashboard statistics';
            } else {
                statsError.value = 'An unexpected error occurred';
            }
            console.error('Failed to fetch dashboard stats:', error);
        } finally {
            isLoadingStats.value = false;
        }

        return stats.value;
    };

    /**
     * Fetch overview chart data.
     */
    const fetchOverview = async (force = false) => {
        if (isOverviewLoaded.value && !force) {
            return overview.value;
        }

        isLoadingOverview.value = true;
        overviewError.value = null;

        try {
            const response = await axios.get<DashboardApiResponse<DashboardOverview>>(
                '/api/dashboard/overview'
            );

            if (response.data.success && response.data.data) {
                overview.value = response.data.data;
                isOverviewLoaded.value = true;
            } else {
                throw new Error(response.data.message || 'Failed to fetch overview data');
            }
        } catch (error) {
            if (axios.isAxiosError(error)) {
                overviewError.value =
                    error.response?.data?.message || 'Failed to fetch overview data';
            } else {
                overviewError.value = 'An unexpected error occurred';
            }
            console.error('Failed to fetch dashboard overview:', error);
        } finally {
            isLoadingOverview.value = false;
        }

        return overview.value;
    };

    /**
     * Fetch recent activity data.
     */
    const fetchRecentActivity = async (force = false) => {
        if (isActivityLoaded.value && !force) {
            return recentActivity.value;
        }

        isLoadingActivity.value = true;
        activityError.value = null;

        try {
            const response = await axios.get<DashboardApiResponse<RecentActivity>>(
                '/api/dashboard/recent-activity'
            );

            if (response.data.success && response.data.data) {
                recentActivity.value = response.data.data;
                isActivityLoaded.value = true;
            } else {
                throw new Error(response.data.message || 'Failed to fetch recent activity');
            }
        } catch (error) {
            if (axios.isAxiosError(error)) {
                activityError.value =
                    error.response?.data?.message || 'Failed to fetch recent activity';
            } else {
                activityError.value = 'An unexpected error occurred';
            }
            console.error('Failed to fetch recent activity:', error);
        } finally {
            isLoadingActivity.value = false;
        }

        return recentActivity.value;
    };

    /**
     * Fetch latest backup data (System Admin only).
     */
    const fetchLatestBackup = async (force = false) => {
        if (isBackupLoaded.value && !force) {
            return latestBackup.value;
        }

        isLoadingBackup.value = true;
        backupError.value = null;

        try {
            const response = await axios.get<DashboardApiResponse<LatestBackup | null>>(
                '/api/dashboard/latest-backup'
            );

            if (response.data.success) {
                latestBackup.value = response.data.data;
                isBackupLoaded.value = true;
            } else {
                // If unauthorized (403), silently skip - user is not system admin
                if (response.status !== 403) {
                    throw new Error(response.data.message || 'Failed to fetch latest backup');
                }
            }
        } catch (error) {
            if (axios.isAxiosError(error)) {
                // Silently handle 403 - user doesn't have permission
                if (error.response?.status !== 403) {
                    backupError.value =
                        error.response?.data?.message || 'Failed to fetch latest backup';
                    console.error('Failed to fetch latest backup:', error);
                }
            } else {
                backupError.value = 'An unexpected error occurred';
                console.error('Failed to fetch latest backup:', error);
            }
        } finally {
            isLoadingBackup.value = false;
        }

        return latestBackup.value;
    };

    /**
     * Fetch all dashboard data.
     */
    const fetchAll = async (force = false) => {
        await Promise.all([
            fetchStats(force), 
            fetchOverview(force), 
            fetchRecentActivity(force),
            fetchLatestBackup(force)
        ]);
    };

    /**
     * Clear dashboard cache on the server.
     */
    const clearCache = async () => {
        try {
            const response = await axios.post<DashboardApiResponse<null>>(
                '/api/dashboard/clear-cache'
            );

            if (response.data.success) {
                // Refetch all data after clearing cache
                await fetchAll(true);
                return true;
            }
            return false;
        } catch (error) {
            console.error('Failed to clear dashboard cache:', error);
            return false;
        }
    };

    /**
     * Start auto-refresh intervals.
     */
    const startAutoRefresh = () => {
        if (!autoRefresh) return;

        // Clear existing intervals if any
        stopAutoRefresh();

        // Set up new intervals
        statsIntervalId = setInterval(() => {
            fetchStats(true);
        }, STATS_REFRESH_INTERVAL);

        overviewIntervalId = setInterval(() => {
            fetchOverview(true);
        }, OVERVIEW_REFRESH_INTERVAL);

        activityIntervalId = setInterval(() => {
            fetchRecentActivity(true);
        }, ACTIVITY_REFRESH_INTERVAL);

        backupIntervalId = setInterval(() => {
            fetchLatestBackup(true);
        }, BACKUP_REFRESH_INTERVAL);
    };

    /**
     * Stop auto-refresh intervals.
     */
    const stopAutoRefresh = () => {
        if (statsIntervalId) {
            clearInterval(statsIntervalId);
            statsIntervalId = null;
        }
        if (overviewIntervalId) {
            clearInterval(overviewIntervalId);
            overviewIntervalId = null;
        }
        if (activityIntervalId) {
            clearInterval(activityIntervalId);
            activityIntervalId = null;
        }
        if (backupIntervalId) {
            clearInterval(backupIntervalId);
            backupIntervalId = null;
        }
    };

    /**
     * Refresh all data manually.
     */
    const refresh = async () => {
        await fetchAll(true);
    };

    // Auto-fetch on mount
    onMounted(() => {
        fetchAll();
        if (autoRefresh) {
            startAutoRefresh();
        }
    });

    // Clean up intervals on unmount
    onUnmounted(() => {
        stopAutoRefresh();
    });

    return {
        // Data
        stats,
        overview,
        recentActivity,
        latestBackup,

        // Loading states
        isLoadingStats,
        isLoadingOverview,
        isLoadingActivity,
        isLoadingBackup,
        isLoading:
            isLoadingStats.value || isLoadingOverview.value || isLoadingActivity.value || isLoadingBackup.value,

        // Error states
        statsError,
        overviewError,
        activityError,
        backupError,

        // Loaded states
        isStatsLoaded,
        isOverviewLoaded,
        isActivityLoaded,
        isBackupLoaded,

        // Methods
        fetchStats,
        fetchOverview,
        fetchRecentActivity,
        fetchLatestBackup,
        fetchAll,
        clearCache,
        refresh,
        startAutoRefresh,
        stopAutoRefresh,
    };
}
