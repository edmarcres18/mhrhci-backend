<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { computed, reactive, ref, watch, onMounted, onBeforeUnmount } from 'vue';
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogFooter,
} from '@/components/ui/dialog';
import Toast from './Toast.vue';

interface Announcement {
  id: number;
  title: string;
  description?: string | null;
  created_at?: string | null;
}

interface Pagination<T> {
  data: T[];
  links: { url: string | null; label: string; active: boolean }[];
}

const page = usePage();
const announcements = computed(() => (page.props.announcements as Pagination<Announcement>) ?? { data: [], links: [] });
const initialFilters = computed(() => (page.props as any)?.filters ?? { search: '', perPage: 10 });
const search = ref<string>(initialFilters.value.search || '');
const perPage = ref<number>(Number(initialFilters.value.perPage) || 10);
let searchTimer: number | undefined;

watch(
  () => search.value,
  (val) => {
    if (searchTimer) window.clearTimeout(searchTimer);
    searchTimer = window.setTimeout(() => {
      router.get('/announcements', { search: val || undefined, perPage: perPage.value }, { preserveState: true, preserveScroll: true, replace: true });
    }, 350);
  }
);

watch(
  () => perPage.value,
  (val) => {
    router.get('/announcements', { search: search.value || undefined, perPage: val }, { preserveState: true, preserveScroll: true, replace: true });
  }
);

const breadcrumbs = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Announcements', href: '/announcements' },
];

// Delete confirmation modal state
const confirmOpen = ref(false);
const deletingId = ref<number | null>(null);
const deletingTitle = ref<string>('');
const isDeleting = ref(false);

function requestDelete(id: number, title: string) {
  deletingId.value = id;
  deletingTitle.value = title;
  confirmOpen.value = true;
}

function cancelDelete() {
  confirmOpen.value = false;
  deletingId.value = null;
  deletingTitle.value = '';
}

// Toast state
type ToastType = 'success' | 'error';
const toast = reactive<{ show: boolean; type: ToastType; message: string }>({
  show: false,
  type: 'success',
  message: '',
});
let toastTimer: number | undefined;

function showToast(type: ToastType, message: string, duration = 3000) {
  toast.show = true;
  toast.type = type;
  toast.message = message;
  if (toastTimer) window.clearTimeout(toastTimer);
  toastTimer = window.setTimeout(() => (toast.show = false), duration);
}

function closeToast() {
  toast.show = false;
  if (toastTimer) window.clearTimeout(toastTimer);
  toastTimer = undefined;
}

onBeforeUnmount(() => {
  if (toastTimer) window.clearTimeout(toastTimer);
});

function confirmDelete() {
  if (!deletingId.value) return;
  isDeleting.value = true;
  router.delete(`/announcements/${deletingId.value}`, {
    onSuccess: () => {
      cancelDelete();
    },
    onError: () => {
      showToast('error', 'Failed to delete the announcement. Please try again.');
    },
    onFinish: () => {
      isDeleting.value = false;
    },
  });
}

// Display server flash messages as toast (e.g., from create/update/destroy)
const lastFlashMessage = ref<string | null>(null);
onMounted(() => {
  const anyPage: any = page.props;
  const msg = (anyPage?.flash?.success as string | undefined) || '';
  if (msg && msg !== lastFlashMessage.value) {
    showToast('success', msg);
    lastFlashMessage.value = msg;
  }
});
watch(
  () => (page.props as any)?.flash?.success as string | undefined,
  (val) => {
    const msg = val || '';
    if (msg && msg !== lastFlashMessage.value) {
      showToast('success', msg);
      lastFlashMessage.value = msg;
    }
  }
);
</script>

<template>
  <Head title="Announcements" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="mx-auto w-full max-w-7xl px-4 py-4 sm:py-6 space-y-5">
      <!-- Toast -->
      <Toast v-model="toast.show" :type="toast.type" :message="toast.message" :duration="3500" />

      <section class="rounded-2xl border border-neutral-200/80 bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-950 sm:p-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
          <div class="space-y-2">
            <div class="inline-flex items-center gap-2 rounded-full bg-neutral-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-neutral-600 dark:bg-neutral-900 dark:text-neutral-300">
              Announcements
              <span class="h-1 w-1 rounded-full bg-amber-500"></span>
            </div>
            <div>
              <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">Company Updates</h1>
              <p class="text-sm text-neutral-500 dark:text-neutral-400">Search, review, and manage the latest announcements.</p>
            </div>
          </div>
          <div class="flex flex-col gap-2 sm:items-end">
            <Link
              href="/announcements/create"
              class="inline-flex items-center justify-center gap-2 rounded-lg bg-neutral-900 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-neutral-800 dark:bg-white dark:text-neutral-900 dark:hover:bg-neutral-200"
            >
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              New Announcement
            </Link>
            <div class="flex flex-wrap items-center gap-2 text-xs text-neutral-500 dark:text-neutral-400">
              <span class="rounded-full bg-neutral-100 px-3 py-1 font-semibold text-neutral-700 dark:bg-neutral-800 dark:text-neutral-200">
                Total: {{ announcements.data.length }}
              </span>
            </div>
          </div>
        </div>

        <div class="mt-4 grid gap-3 sm:grid-cols-[1fr_auto] sm:items-center">
          <div class="relative">
            <input
              v-model="search"
              type="text"
              placeholder="Search announcements..."
              class="w-full rounded-lg border border-neutral-200 bg-neutral-50 px-3 py-2 pl-9 text-sm text-neutral-800 outline-none ring-2 ring-transparent transition focus:border-neutral-400 focus:ring-neutral-200 dark:border-neutral-800 dark:bg-neutral-900 dark:text-white dark:focus:border-neutral-600"
            />
            <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400">⌕</span>
          </div>
          <div class="flex flex-wrap items-center gap-3 text-sm text-neutral-600 dark:text-neutral-300">
            <label class="text-xs text-neutral-500">Per page</label>
            <select
              v-model.number="perPage"
              class="rounded-md border border-neutral-200 bg-white px-3 py-2 text-sm dark:border-neutral-800 dark:bg-neutral-900 dark:text-white"
            >
              <option :value="10">10</option>
              <option :value="25">25</option>
              <option :value="50">50</option>
              <option :value="100">100</option>
            </select>
          </div>
        </div>

        <div class="mt-4 space-y-3">
          <!-- Mobile cards -->
          <div class="space-y-3 sm:hidden">
            <div v-if="announcements.data.length === 0" class="rounded-xl border border-dashed border-neutral-200 bg-neutral-50 p-4 text-sm text-neutral-500 dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-300">
              No announcements yet. Create your first announcement.
            </div>
            <div
              v-else
              v-for="item in announcements.data"
              :key="item.id"
              class="rounded-xl border border-neutral-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-neutral-800 dark:bg-neutral-950"
            >
              <div class="flex items-start justify-between gap-2">
                <div class="min-w-0">
                  <div class="truncate text-base font-semibold text-neutral-900 dark:text-white">{{ item.title }}</div>
                  <div class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">{{ new Date(item.created_at || '').toLocaleDateString() }}</div>
                </div>
                <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800 dark:bg-amber-900/40 dark:text-amber-200">Live</span>
              </div>
              <div class="mt-3 flex items-center justify-end gap-3 text-sm">
                <Link :href="`/announcements/${item.id}`" class="text-neutral-700 hover:underline dark:text-neutral-300">View</Link>
                <Link :href="`/announcements/${item.id}/edit`" class="text-neutral-700 hover:underline dark:text-neutral-300">Edit</Link>
                <button @click="requestDelete(item.id, item.title)" class="text-red-600 hover:underline">Delete</button>
              </div>
            </div>
          </div>

          <!-- Desktop table -->
          <div class="hidden overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-800 dark:bg-neutral-950 sm:block">
            <div class="overflow-x-auto">
              <table class="w-full divide-y divide-neutral-200 text-sm dark:divide-neutral-800">
                <thead class="bg-neutral-50 text-neutral-600 dark:bg-neutral-900 dark:text-neutral-300">
                  <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">Title</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">Created</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide">Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">
                  <tr v-if="announcements.data.length === 0">
                    <td colspan="3" class="px-4 py-4 text-center text-sm text-neutral-500 dark:text-neutral-300">No announcements yet.</td>
                  </tr>
                  <tr v-else v-for="item in announcements.data" :key="item.id" class="hover:bg-neutral-50 dark:hover:bg-neutral-900/60">
                    <td class="px-4 py-3 text-sm font-medium text-neutral-900 dark:text-white">
                      {{ item.title }}
                    </td>
                    <td class="px-4 py-3 text-sm text-neutral-700 dark:text-neutral-300">{{ new Date(item.created_at || '').toLocaleString() }}</td>
                    <td class="px-4 py-3 text-right">
                      <div class="flex items-center justify-end gap-3 text-sm">
                        <Link :href="`/announcements/${item.id}`" class="text-neutral-700 hover:underline dark:text-neutral-300">View</Link>
                        <Link :href="`/announcements/${item.id}/edit`" class="text-neutral-700 hover:underline dark:text-neutral-300">Edit</Link>
                        <button @click="requestDelete(item.id, item.title)" class="text-red-600 hover:underline">Delete</button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div v-if="announcements.links?.length" class="flex flex-col gap-2 border-t border-neutral-200 p-3 text-sm text-neutral-600 dark:border-neutral-800 dark:text-neutral-300 sm:flex-row sm:items-center sm:justify-between">
              <div class="flex items-center gap-2">
                <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                <span>Showing latest announcements</span>
              </div>
              <div class="flex flex-wrap items-center gap-2">
                <template v-for="(link, idx) in announcements.links" :key="idx">
                  <span v-if="!link.url" class="rounded-md px-3 py-1.5 text-sm text-neutral-400" v-html="link.label" />
                  <Link v-else :href="link.url" class="rounded-md px-3 py-1.5 text-sm transition" :class="link.active ? 'bg-neutral-900 text-white dark:bg-white dark:text-neutral-900' : 'hover:bg-neutral-100 dark:hover:bg-neutral-800'" v-html="link.label" />
                </template>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>

    <!-- Delete Confirmation Modal -->
    <Dialog v-model:open="confirmOpen">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Delete announcement</DialogTitle>
          <DialogDescription>
            This action cannot be undone. This will permanently delete
            <span class="font-medium">{{ deletingTitle }}</span>.
          </DialogDescription>
        </DialogHeader>
        <DialogFooter>
          <button @click="cancelDelete" type="button" class="rounded-md border px-4 py-2 text-sm">Cancel</button>
          <button @click="confirmDelete" :disabled="isDeleting" class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white disabled:opacity-60">Delete</button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template>
