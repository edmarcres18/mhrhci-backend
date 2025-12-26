<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, reactive, ref, watch, onMounted, onBeforeUnmount } from 'vue';
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogFooter,
} from '@/components/ui/dialog';

type ToastType = 'success' | 'error';
type Principal = {
  id: number;
  name: string;
  description?: string | null;
  logo?: string | null;
  is_featured?: boolean;
  created_at?: string | null;
};

type Pagination<T> = {
  data: T[];
  links: { url: string | null; label: string; active: boolean }[];
};

const page = usePage();
const principals = computed(() => (page.props.principals as Pagination<Principal>) ?? { data: [], links: [] });
const initialFilters = computed(() => (page.props as any)?.filters ?? { search: '', perPage: 10 });
const search = ref<string>(initialFilters.value.search || '');
const perPage = ref<number>(Number(initialFilters.value.perPage) || 10);
let searchTimer: number | undefined;

const breadcrumbs = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Principals', href: '/principals' },
];

// Delete confirmation
const confirmOpen = ref(false);
const deletingId = ref<number | null>(null);
const deletingName = ref<string>('');
const isDeleting = ref(false);

function requestDelete(id: number, name: string) {
  deletingId.value = id;
  deletingName.value = name;
  confirmOpen.value = true;
}

function cancelDelete() {
  confirmOpen.value = false;
  deletingId.value = null;
  deletingName.value = '';
}

function confirmDelete() {
  if (!deletingId.value) return;
  isDeleting.value = true;
  router.delete(`/principals/${deletingId.value}`, {
    onSuccess: () => cancelDelete(),
    onError: () => showToast('error', 'Failed to delete principal.'),
    onFinish: () => {
      isDeleting.value = false;
    },
  });
}

// Toast
const toast = reactive<{ show: boolean; type: ToastType; message: string }>({
  show: false,
  type: 'success',
  message: '',
});
let toastTimer: number | undefined;
function showToast(type: ToastType, message: string, duration = 3200) {
  toast.show = true;
  toast.type = type;
  toast.message = message;
  if (toastTimer) window.clearTimeout(toastTimer);
  toastTimer = window.setTimeout(() => (toast.show = false), duration);
}
onBeforeUnmount(() => {
  if (toastTimer) window.clearTimeout(toastTimer);
});

// Flash success to toast
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

watch(
  () => search.value,
  (val) => {
    if (searchTimer) window.clearTimeout(searchTimer);
    searchTimer = window.setTimeout(() => {
      router.get('/principals', { search: val || undefined, perPage: perPage.value }, { preserveState: true, preserveScroll: true, replace: true });
    }, 350);
  }
);

watch(
  () => perPage.value,
  (val) => {
    router.get('/principals', { search: search.value || undefined, perPage: val }, { preserveState: true, preserveScroll: true, replace: true });
  }
);

function formatDate(value?: string | null) {
  if (!value) return '—';
  return new Date(value).toLocaleString();
}
</script>

<template>
  <Head title="Principals" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="mx-auto w-full max-w-6xl p-4">
      <!-- Toast -->
      <transition name="fade">
        <div
          v-if="toast.show"
          class="mb-4 rounded-lg border px-4 py-3 text-sm shadow"
          :class="toast.type === 'success' ? 'border-green-200 bg-green-50 text-green-800' : 'border-red-200 bg-red-50 text-red-800'"
        >
          {{ toast.message }}
        </div>
      </transition>

      <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="text-xl font-semibold">Principals</h1>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-2">
          <div class="flex items-center gap-2">
            <label class="text-xs text-neutral-500">Per page</label>
            <select
              v-model.number="perPage"
              class="rounded-md border border-neutral-300 px-2 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white"
            >
              <option :value="10">10</option>
              <option :value="25">25</option>
              <option :value="50">50</option>
              <option :value="100">100</option>
            </select>
          </div>
          <div class="relative w-full sm:w-64">
            <input
              v-model="search"
              type="text"
              placeholder="Search principals..."
              class="w-full rounded-md border border-neutral-300 px-3 py-2 pr-10 text-sm outline-none focus:border-black focus:ring-2 focus:ring-black/10 dark:border-neutral-700 dark:bg-neutral-900 dark:text-white dark:focus:border-white dark:focus:ring-white/10"
            />
            <svg class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
          <Link
            href="/principals/create"
            class="inline-flex items-center justify-center rounded-lg bg-black px-4 py-2 text-sm font-medium text-white transition hover:bg-neutral-800 dark:bg-white dark:text-black dark:hover:bg-neutral-200"
          >
            New Principal
          </Link>
        </div>
      </div>

      <div v-if="principals.data.length === 0" class="rounded-xl border border-neutral-200 p-10 text-center text-sm text-neutral-600 dark:border-neutral-800 dark:text-neutral-300">
        No principals yet. Create your first principal.
      </div>

      <div v-else class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-800">
        <div class="relative w-full overflow-auto">
          <table class="w-full text-sm">
            <thead class="bg-neutral-50 text-left text-xs uppercase text-neutral-500 dark:bg-neutral-900/60 dark:text-neutral-400">
              <tr>
                <th class="px-4 py-3 font-medium">Name</th>
                <th class="px-4 py-3 font-medium">Description</th>
                <th class="px-4 py-3 font-medium">Logo</th>
                <th class="px-4 py-3 font-medium">Featured</th>
                <th class="px-4 py-3 font-medium">Created</th>
                <th class="px-4 py-3 text-right font-medium">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="p in principals.data" :key="p.id" class="border-t border-neutral-200 hover:bg-neutral-50 dark:border-neutral-800 dark:hover:bg-neutral-900/50">
                <td class="px-4 py-3 font-medium text-neutral-900 dark:text-neutral-100">
                  <div class="flex items-center gap-2">
                    <img v-if="p.logo" :src="p.logo" alt="Logo" class="h-8 w-8 rounded-full border border-neutral-200 object-cover dark:border-neutral-700" />
                    <span>{{ p.name }}</span>
                  </div>
                </td>
                <td class="px-4 py-3 text-neutral-600 dark:text-neutral-300">
                  <span class="line-clamp-2">{{ p.description || '—' }}</span>
                </td>
                <td class="px-4 py-3">
                  <span v-if="p.logo" class="text-xs text-neutral-500">Yes</span>
                  <span v-else class="text-xs text-neutral-400">None</span>
                </td>
                <td class="px-4 py-3">
                  <span
                    :class="p.is_featured ? 'text-xs font-semibold text-green-600' : 'text-xs text-neutral-400'"
                  >
                    {{ p.is_featured ? 'Yes' : 'No' }}
                  </span>
                </td>
                <td class="px-4 py-3 text-neutral-600 dark:text-neutral-300">
                  {{ formatDate(p.created_at) }}
                </td>
                <td class="px-4 py-3 text-right">
                  <div class="flex items-center justify-end gap-3">
                    <Link :href="`/principals/${p.id}/edit`" class="text-sm text-neutral-700 hover:underline dark:text-neutral-200">Edit</Link>
                    <button @click="requestDelete(p.id, p.name)" class="text-sm text-red-600 hover:underline">Delete</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-if="principals.links?.length" class="flex flex-wrap items-center justify-between border-t border-neutral-200 p-3 text-sm dark:border-neutral-800">
          <div class="text-neutral-500 dark:text-neutral-400">Showing principals</div>
          <div class="flex flex-wrap items-center gap-2">
            <template v-for="(link, idx) in principals.links" :key="idx">
              <span v-if="!link.url" class="rounded-md px-3 py-1.5 text-neutral-400" v-html="link.label" />
              <Link
                v-else
                :href="link.url"
                class="rounded-md px-3 py-1.5"
                :class="link.active ? 'bg-black text-white dark:bg-white dark:text-black' : 'hover:bg-neutral-100 dark:hover:bg-neutral-800'"
                v-html="link.label"
              />
            </template>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <Dialog v-model:open="confirmOpen">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Delete principal</DialogTitle>
          <DialogDescription>
            This action cannot be undone. This will permanently delete <span class="font-medium">{{ deletingName }}</span>.
          </DialogDescription>
        </DialogHeader>
        <DialogFooter>
          <button @click="cancelDelete" type="button" class="rounded-md border px-4 py-2 text-sm">Cancel</button>
          <button @click="confirmDelete" :disabled="isDeleting" class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white disabled:opacity-60">
            Delete
          </button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
