<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, onMounted, reactive, ref } from 'vue';

interface SubscriptionItem {
    id: number;
    first_name: string;
    last_name: string;
    email: string;
    created_at?: string | null;
}

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Newsletter Subscriptions', href: '/newsletter-subscriptions' },
];

const items = ref<SubscriptionItem[]>([]);
const loading = ref(false);
const search = ref('');
const lastUpdated = ref<string | null>(null);
const state = reactive({
    message: '',
    messageType: '' as 'success' | 'error' | '',
});

const filteredItems = computed(() => {
    const q = search.value.trim().toLowerCase();
    if (!q) return items.value;
    return items.value.filter((i) =>
        [i.first_name, i.last_name, i.email]
            .filter(Boolean)
            .some((v) => String(v).toLowerCase().includes(q)),
    );
});

function formatDate(iso?: string | null) {
    if (!iso) return '';
    const d = new Date(iso);
    return d.toLocaleString();
}

async function fetchItems() {
    loading.value = true;
    state.message = '';
    state.messageType = '';
    try {
        const { data } = await axios.get('/api/v1/newsletter');
        if (data?.success) {
            items.value = data.data as SubscriptionItem[];
            lastUpdated.value = new Date().toISOString();
        } else {
            state.message = data?.message || 'Failed to load subscriptions';
            state.messageType = 'error';
        }
    } catch (error: any) {
        state.message = error?.response?.data?.message || 'Failed to load subscriptions';
        state.messageType = 'error';
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    fetchItems();
});
</script>

<template>
    <Head title="Newsletter Subscriptions" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full max-w-7xl px-4 py-4 sm:py-6 space-y-5">
            <section class="rounded-2xl border border-neutral-200/80 bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-950 sm:p-6">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div class="space-y-2">
                        <div class="inline-flex items-center gap-2 rounded-full bg-neutral-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-neutral-600 dark:bg-neutral-900 dark:text-neutral-300">
                            Newsletter
                            <span class="h-1 w-1 rounded-full bg-emerald-500"></span>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">Subscriptions</h1>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400">Monitor who’s opted in. Search, refresh, and view recent signups.</p>
                        </div>
                    </div>
                    <div class="flex flex-col gap-2 sm:items-end">
                        <button
                            type="button"
                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-neutral-900 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-neutral-800 disabled:opacity-60 dark:bg-white dark:text-neutral-900 dark:hover:bg-neutral-200"
                            :disabled="loading"
                            @click="fetchItems"
                            aria-label="Refresh subscriptions"
                        >
                            <svg
                                class="h-4 w-4"
                                :class="{ 'animate-spin': loading }"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m0 0A7.5 7.5 0 0119 9M5 9h4m11 11v-5h-.581m0 0a7.5 7.5 0 01-13.418 0M19 15h-4" />
                            </svg>
                            {{ loading ? 'Refreshing...' : 'Refresh' }}
                        </button>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400">
                            Last updated: {{ lastUpdated ? formatDate(lastUpdated) : '—' }}
                        </div>
                    </div>
                </div>

                <div class="mt-4 grid gap-3 sm:grid-cols-[1fr_auto] sm:items-center">
                    <div class="relative">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search by name or email"
                            class="w-full rounded-lg border border-neutral-200 bg-neutral-50 px-3 py-2 pl-9 text-sm text-neutral-800 outline-none ring-2 ring-transparent transition focus:border-neutral-400 focus:ring-neutral-200 dark:border-neutral-800 dark:bg-neutral-900 dark:text-white dark:focus:border-neutral-600"
                        />
                        <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400">⌕</span>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 text-sm text-neutral-600 dark:text-neutral-300">
                        <span class="rounded-full bg-neutral-100 px-3 py-1 text-xs font-semibold text-neutral-700 dark:bg-neutral-800 dark:text-neutral-200">
                            Total: {{ items.length }}
                        </span>
                        <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200">
                            Showing: {{ filteredItems.length }}
                        </span>
                    </div>
                </div>

                <div class="mt-4 space-y-3">
                    <!-- Mobile cards -->
                    <div class="space-y-3 sm:hidden">
                        <div v-if="loading" class="rounded-xl border border-neutral-200 bg-neutral-50 p-4 dark:border-neutral-800 dark:bg-neutral-900">
                            <div class="animate-pulse space-y-3">
                                <div class="h-4 w-2/3 rounded bg-neutral-200 dark:bg-neutral-700"></div>
                                <div class="h-3 w-1/2 rounded bg-neutral-200 dark:bg-neutral-700"></div>
                                <div class="h-3 w-1/3 rounded bg-neutral-200 dark:bg-neutral-700"></div>
                            </div>
                        </div>
                        <div v-else-if="filteredItems.length === 0" class="rounded-xl border border-dashed border-neutral-200 bg-neutral-50 p-4 text-sm text-neutral-500 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-300">
                            No subscriptions found.
                        </div>
                        <div
                            v-else
                            v-for="item in filteredItems"
                            :key="item.id"
                            class="rounded-xl border border-neutral-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-neutral-800 dark:bg-neutral-950"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <div class="text-base font-semibold text-neutral-900 dark:text-white">
                                        {{ item.first_name }} {{ item.last_name }}
                                    </div>
                                    <div class="text-sm text-neutral-500 dark:text-neutral-400">{{ item.email }}</div>
                                </div>
                                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200">
                                    Subscribed
                                </span>
                            </div>
                            <div class="mt-2 text-xs text-neutral-500 dark:text-neutral-400">
                                Joined {{ formatDate(item.created_at) }}
                            </div>
                        </div>
                    </div>

                    <!-- Desktop table -->
                    <div class="hidden overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-800 dark:bg-neutral-950 sm:block">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-800">
                                <thead class="bg-neutral-50 text-neutral-600 dark:bg-neutral-900 dark:text-neutral-300">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">Name</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">Email</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">Subscribed</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">
                                    <tr v-if="loading">
                                        <td colspan="3" class="px-4 py-4 text-center text-sm text-neutral-600 dark:text-neutral-300">Loading...</td>
                                    </tr>
                                    <tr v-else-if="filteredItems.length === 0">
                                        <td colspan="3" class="px-4 py-4 text-center text-sm text-neutral-500 dark:text-neutral-300">No subscriptions found.</td>
                                    </tr>
                                    <tr v-else v-for="item in filteredItems" :key="item.id" class="hover:bg-neutral-50 dark:hover:bg-neutral-900/60">
                                        <td class="px-4 py-3 text-sm font-medium text-neutral-900 dark:text-white">
                                            {{ item.first_name }} {{ item.last_name }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-neutral-700 dark:text-neutral-300">{{ item.email }}</td>
                                        <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-300">
                                            {{ formatDate(item.created_at) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>

            <div v-if="state.message" :class="[
                'rounded-lg border px-4 py-3 text-sm',
                state.messageType === 'error'
                    ? 'border-red-300 bg-red-50 text-red-700 dark:border-red-800 dark:bg-red-950/50 dark:text-red-200'
                    : 'border-emerald-300 bg-emerald-50 text-emerald-700 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200',
            ]">
                {{ state.message }}
            </div>
        </div>
    </AppLayout>
</template>
