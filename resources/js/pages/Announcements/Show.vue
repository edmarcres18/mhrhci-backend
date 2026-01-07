<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue';
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
  updated_at?: string | null;
}

const props = defineProps<{ announcement: Announcement }>();
const page = usePage();
const canDelete = computed(() => (page.props.auth as any)?.canDeleteAnnouncements ?? true);

const breadcrumbs = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Announcements', href: '/announcements' },
  { title: props.announcement.title, href: `/announcements/${props.announcement.id}` },
];

function formatDate(dateString?: string | null) {
  if (!dateString) return 'N/A';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
}

// Delete confirmation modal state
const confirmOpen = ref(false);
const isDeleting = ref(false);

function requestDelete() {
  confirmOpen.value = true;
}
function cancelDelete() {
  confirmOpen.value = false;
}
function confirmDelete() {
  isDeleting.value = true;
  router.delete(`/announcements/${props.announcement.id}`, {
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

// Toast state
type ToastType = 'success' | 'error';
const toast = reactive<{ show: boolean; type: ToastType; message: string }>({
  show: false,
  type: 'success',
  message: '',
});
let toastTimer: number | undefined;
function showToast(type: ToastType, message: string, duration = 3500) {
  toast.show = true;
  toast.type = type;
  toast.message = message;
  if (toastTimer) window.clearTimeout(toastTimer);
  toastTimer = window.setTimeout(() => (toast.show = false), duration);
}
onBeforeUnmount(() => {
  if (toastTimer) window.clearTimeout(toastTimer);
});
onMounted(() => {
  const anyPage: any = page.props;
  const ok = anyPage?.flash?.success as string | undefined;
  const err = anyPage?.flash?.error as string | undefined;
  if (ok) showToast('success', ok);
  if (err) showToast('error', err);
});
watch(
  () => (page.props as any)?.flash,
  (val: any) => {
    if (val?.success) showToast('success', val.success);
    if (val?.error) showToast('error', val.error);
  },
  { deep: true }
);
</script>

<template>
  <Head :title="props.announcement.title" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="mx-auto w-full max-w-5xl p-4 sm:p-6">
      <Toast v-model="toast.show" :type="toast.type" :message="toast.message" :duration="3500" />

      <!-- Header -->
      <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="flex-1">
          <h1 class="text-2xl font-bold text-neutral-900 dark:text-white sm:text-3xl">{{ props.announcement.title }}</h1>
          <p class="mt-2 flex items-center gap-1 text-sm text-neutral-500 dark:text-neutral-400">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            Created {{ formatDate(props.announcement.created_at) }}
          </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
          <Link
            href="/announcements"
            class="flex items-center gap-2 rounded-lg border border-neutral-300 bg-white px-4 py-2.5 text-sm font-medium text-neutral-700 transition-all hover:bg-neutral-50 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300 dark:hover:bg-neutral-800"
          >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back
          </Link>
          <Link
            :href="`/announcements/${props.announcement.id}/edit`"
            class="flex items-center gap-2 rounded-lg border border-neutral-300 bg-white px-4 py-2.5 text-sm font-medium text-neutral-700 transition-all hover:bg-neutral-50 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300 dark:hover:bg-neutral-800"
          >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Edit
          </Link>
          <button
            v-if="canDelete"
            @click="requestDelete"
            class="flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2.5 text-sm font-medium text-white transition-all hover:bg-red-700 active:scale-95"
          >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            Delete
          </button>
        </div>
      </div>

      <!-- Content -->
      <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-800 dark:bg-neutral-950">
        <div class="mb-4 flex items-center gap-2">
          <svg class="h-5 w-5 text-neutral-700 dark:text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
          </svg>
          <h2 class="text-lg font-bold text-neutral-900 dark:text-white">Description</h2>
        </div>
        <div v-if="props.announcement.description" class="prose prose-sm max-w-none dark:prose-invert">
          <p class="whitespace-pre-line leading-relaxed text-neutral-700 dark:text-neutral-300">
            {{ props.announcement.description }}
          </p>
        </div>
        <div v-else class="rounded-lg border-2 border-dashed border-neutral-200 p-8 text-center dark:border-neutral-800">
          <svg class="mx-auto h-12 w-12 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          <p class="mt-2 text-sm text-neutral-500">No description provided.</p>
        </div>
      </div>

      <!-- Meta -->
      <div class="mt-6 grid gap-4 sm:grid-cols-2">
        <div class="rounded-xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-950">
          <div class="text-xs font-semibold uppercase tracking-wide text-neutral-500 dark:text-neutral-400">Announcement ID</div>
          <div class="mt-1 font-mono text-sm font-semibold text-neutral-900 dark:text-white">#{{ props.announcement.id }}</div>
        </div>
        <div class="rounded-xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-950">
          <div class="text-xs font-semibold uppercase tracking-wide text-neutral-500 dark:text-neutral-400">Updated</div>
          <div class="mt-1 text-sm font-semibold text-neutral-900 dark:text-white">{{ formatDate(props.announcement.updated_at) }}</div>
        </div>
      </div>
    </div>

    <!-- Delete Modal -->
    <Dialog v-model:open="confirmOpen">
      <DialogContent class="sm:max-w-md">
        <DialogHeader>
          <DialogTitle>Delete announcement</DialogTitle>
          <DialogDescription>
            This action cannot be undone. This will permanently delete this announcement.
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
