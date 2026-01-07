<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue';
import Toast from './Toast.vue';

const MAX_TITLE_LENGTH = 255;
const MAX_DESCRIPTION_LENGTH = 5000;

const breadcrumbs = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Announcements', href: '/announcements' },
  { title: 'Create', href: '/announcements/create' },
];

const form = useForm({
  title: '',
  description: '',
});

const page = usePage();

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

// Watch for flash messages
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

const isSubmitting = computed(() => form.processing);

function submit() {
  if (!form.title.trim()) {
    showToast('error', 'Title is required.');
    return;
  }

  form.post('/announcements', {
    onSuccess: () => {
      form.reset('title', 'description');
      showToast('success', 'Announcement created successfully!');
    },
    onError: () => {
      showToast('error', 'Failed to create announcement. Please check the form.');
    },
  });
}

const remainingTitleChars = computed(() => MAX_TITLE_LENGTH - form.title.length);
const remainingDescriptionChars = computed(() => MAX_DESCRIPTION_LENGTH - form.description.length);
</script>

<template>
  <Head title="Create Announcement" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="mx-auto w-full max-w-3xl p-4">
      <!-- Toast -->
      <Toast v-model="toast.show" :type="toast.type" :message="toast.message" :duration="3500" />

      <div class="mb-4 flex items-center justify-between">
        <h1 class="text-xl font-semibold">Create Announcement</h1>
        <Link href="/announcements" class="text-sm text-primary hover:underline">Back to list</Link>
      </div>

      <form @submit.prevent="submit" class="space-y-6 rounded-xl border border-sidebar-border/70 bg-white p-6 shadow-sm dark:border-sidebar-border dark:bg-neutral-950 sm:p-8">
        <!-- Title -->
        <div class="space-y-2">
          <div class="flex items-center justify-between">
            <label class="block text-sm font-semibold text-neutral-700 dark:text-neutral-200">
              Title <span class="text-red-500">*</span>
            </label>
            <span class="text-xs text-neutral-500" :class="{ 'text-red-500': remainingTitleChars < 0 }">
              {{ remainingTitleChars }} / {{ MAX_TITLE_LENGTH }}
            </span>
          </div>
          <input
            v-model="form.title"
            type="text"
            :maxlength="MAX_TITLE_LENGTH"
            placeholder="e.g., Scheduled Maintenance Notice"
            class="w-full rounded-lg border border-neutral-300 bg-white px-4 py-3 text-sm outline-none transition-all focus:border-black focus:ring-2 focus:ring-black/10 dark:border-neutral-700 dark:bg-neutral-900 dark:text-white dark:focus:border-white dark:focus:ring-white/10"
            :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500/10': form.errors.title }"
          />
          <div v-if="form.errors.title" class="flex items-center gap-1 text-sm text-red-600">
            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <span>{{ form.errors.title }}</span>
          </div>
        </div>

        <!-- Description -->
        <div class="space-y-2">
          <div class="flex items-center justify-between">
            <label class="block text-sm font-semibold text-neutral-700 dark:text-neutral-200">Description</label>
            <span class="text-xs text-neutral-500" :class="{ 'text-red-500': remainingDescriptionChars < 0 }">
              {{ remainingDescriptionChars }} / {{ MAX_DESCRIPTION_LENGTH }}
            </span>
          </div>
          <textarea
            v-model="form.description"
            rows="8"
            :maxlength="MAX_DESCRIPTION_LENGTH"
            placeholder="Details about the announcement..."
            class="w-full resize-none rounded-lg border border-neutral-300 bg-white px-4 py-3 text-sm outline-none transition-all focus:border-black focus:ring-2 focus:ring-black/10 dark:border-neutral-700 dark:bg-neutral-900 dark:text-white dark:focus:border-white dark:focus:ring-white/10"
            :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500/10': form.errors.description }"
          ></textarea>
          <div v-if="form.errors.description" class="flex items-center gap-1 text-sm text-red-600">
            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <span>{{ form.errors.description }}</span>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex flex-col-reverse gap-3 border-t border-neutral-200 pt-6 dark:border-neutral-800 sm:flex-row sm:justify-between sm:items-center">
          <Link
            href="/announcements"
            class="flex items-center justify-center gap-2 rounded-lg border border-neutral-300 bg-white px-6 py-3 text-sm font-medium text-neutral-700 transition-all hover:bg-neutral-50 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300 dark:hover:bg-neutral-800"
          >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Cancel
          </Link>
          <button
            :disabled="isSubmitting"
            type="submit"
            class="flex items-center justify-center gap-2 rounded-lg bg-black px-8 py-3 text-sm font-semibold text-white shadow-lg transition-all hover:bg-neutral-800 hover:shadow-xl active:scale-95 disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:bg-black dark:bg-white dark:text-black dark:hover:bg-neutral-200 dark:disabled:hover:bg-white"
          >
            <svg v-if="isSubmitting" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Create
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
