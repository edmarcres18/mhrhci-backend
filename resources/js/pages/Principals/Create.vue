<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, reactive, ref } from 'vue';

type ToastType = 'success' | 'error';

const breadcrumbs = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Principals', href: '/principals' },
  { title: 'Create', href: '/principals/create' },
];

const form = useForm({
  name: '',
  description: '',
  logo: null as File | null,
});

const fileInputRef = ref<HTMLInputElement | null>(null);
const isSubmitting = computed(() => form.processing);

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

// Flash success
const page = usePage();
const lastFlashMessage = ref<string | null>(null);
onMounted(() => {
  const anyPage: any = page.props;
  const msg = (anyPage?.flash?.success as string | undefined) || '';
  if (msg && msg !== lastFlashMessage.value) {
    showToast('success', msg);
    lastFlashMessage.value = msg;
  }
});

function pickFile() {
  fileInputRef.value?.click();
}

function onFileChange(e: Event) {
  const input = e.target as HTMLInputElement;
  const file = input.files?.[0];
  if (file) {
    form.logo = file;
  }
}

function removeLogo() {
  form.logo = null;
  if (fileInputRef.value) fileInputRef.value.value = '';
}

function logoPreview(): string | undefined {
  if (!form.logo) return undefined;
  try {
    return URL.createObjectURL(form.logo);
  } catch {
    return undefined;
  }
}

function submit() {
  if (!form.name.trim()) {
    showToast('error', 'Name is required.');
    return;
  }

  const data = new FormData();
  data.append('name', form.name);
  if (form.description) data.append('description', form.description);
  if (form.logo) data.append('logo', form.logo);

  form.post('/principals', {
    forceFormData: true,
    onSuccess: () => {
      form.reset('name', 'description', 'logo');
      if (fileInputRef.value) fileInputRef.value.value = '';
      showToast('success', 'Principal created successfully.');
    },
    onError: () => {
      showToast('error', 'Failed to create principal. Please review the form.');
    },
  });
}
</script>

<template>
  <Head title="Create Principal" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="mx-auto w-full max-w-3xl p-4">
      <transition name="fade">
        <div
          v-if="toast.show"
          class="mb-4 rounded-lg border px-4 py-3 text-sm shadow"
          :class="toast.type === 'success' ? 'border-green-200 bg-green-50 text-green-800' : 'border-red-200 bg-red-50 text-red-800'"
        >
          {{ toast.message }}
        </div>
      </transition>

      <div class="mb-4 flex items-center justify-between">
        <h1 class="text-xl font-semibold">Create Principal</h1>
        <Link href="/principals" class="text-sm text-primary hover:underline">Back to list</Link>
      </div>

      <form @submit.prevent="submit" class="space-y-6 rounded-xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-800 dark:bg-neutral-950 sm:p-8">
        <!-- Name -->
        <div class="space-y-2">
          <label class="block text-sm font-semibold text-neutral-700 dark:text-neutral-200">
            Name <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.name"
            type="text"
            maxlength="255"
            placeholder="Principal name"
            class="w-full rounded-lg border border-neutral-300 bg-white px-4 py-3 text-sm outline-none focus:border-black focus:ring-2 focus:ring-black/10 dark:border-neutral-700 dark:bg-neutral-900 dark:text-white dark:focus:border-white dark:focus:ring-white/10"
            :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500/10': form.errors.name }"
          />
          <div v-if="form.errors.name" class="text-sm text-red-600">{{ form.errors.name }}</div>
        </div>

        <!-- Description -->
        <div class="space-y-2">
          <label class="block text-sm font-semibold text-neutral-700 dark:text-neutral-200">
            Description
          </label>
          <textarea
            v-model="form.description"
            rows="4"
            maxlength="1000"
            placeholder="Short description"
            class="w-full rounded-lg border border-neutral-300 bg-white px-4 py-3 text-sm outline-none focus:border-black focus:ring-2 focus:ring-black/10 dark:border-neutral-700 dark:bg-neutral-900 dark:text-white dark:focus:border-white dark:focus:ring-white/10"
            :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500/10': form.errors.description }"
          ></textarea>
          <div v-if="form.errors.description" class="text-sm text-red-600">{{ form.errors.description }}</div>
        </div>

        <!-- Logo -->
        <div class="space-y-2">
          <label class="block text-sm font-semibold text-neutral-700 dark:text-neutral-200">
            Logo
          </label>
          <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div
              class="flex h-28 w-full max-w-xs cursor-pointer items-center justify-center rounded-lg border-2 border-dashed border-neutral-300 bg-neutral-50 text-sm text-neutral-500 transition hover:border-black dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300"
              :class="{ 'border-red-500': form.errors.logo }"
              @click="pickFile"
            >
              <input ref="fileInputRef" type="file" accept="image/*" class="hidden" @change="onFileChange" />
              <span>{{ form.logo ? 'Change logo' : 'Click to upload logo' }}</span>
            </div>
            <div v-if="form.logo" class="flex items-center gap-3">
              <img :src="logoPreview()" alt="Logo preview" class="h-16 w-16 rounded-full border object-cover" />
              <button type="button" class="text-sm text-red-600 hover:underline" @click="removeLogo">Remove</button>
            </div>
          </div>
          <div v-if="form.errors.logo" class="text-sm text-red-600">{{ form.errors.logo }}</div>
        </div>

        <!-- Actions -->
        <div class="flex flex-col-reverse gap-3 border-t border-neutral-200 pt-6 dark:border-neutral-800 sm:flex-row sm:items-center sm:justify-between">
          <Link
            href="/principals"
            class="flex items-center justify-center gap-2 rounded-lg border border-neutral-300 bg-white px-6 py-3 text-sm font-medium text-neutral-700 transition hover:bg-neutral-50 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300 dark:hover:bg-neutral-800"
          >
            Cancel
          </Link>
          <button
            type="submit"
            :disabled="isSubmitting"
            class="flex items-center justify-center gap-2 rounded-lg bg-black px-8 py-3 text-sm font-semibold text-white shadow-lg transition hover:bg-neutral-800 active:scale-95 disabled:cursor-not-allowed disabled:opacity-60 dark:bg-white dark:text-black dark:hover:bg-neutral-200"
          >
            <svg v-if="isSubmitting" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            {{ isSubmitting ? 'Creating...' : 'Create Principal' }}
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.15s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
