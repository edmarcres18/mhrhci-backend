<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, onMounted, reactive, watch, onBeforeUnmount } from 'vue';
import Toast from './Toast.vue';

const MAX_TITLE_LENGTH = 255;
const MAX_CONTENT_LENGTH = 10000;
const MAX_IMAGE_SIZE_MB = 10;

interface Blog {
  id: number;
  title: string;
  content?: string | null;
  images?: string[] | null;
}

const props = defineProps<{ blog: Blog }>();

const breadcrumbs = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Blogs', href: '/blogs' },
  { title: 'Edit', href: `/blogs/${props.blog.id}/edit` },
];

const form = useForm({
  title: props.blog.title ?? '',
  content: props.blog.content ?? '',
  images: [] as File[],
  keepExistingImages: true as boolean,
});

const isClient = ref(false);
const page = usePage();
const isDragging = ref(false);
const fileInputRef = ref<HTMLInputElement | null>(null);

onMounted(() => {
  isClient.value = true;
});

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

function onFilesChange(e: Event) {
  const input = e.target as HTMLInputElement;
  if (!input.files) return;
  handleFiles(Array.from(input.files));
  input.value = '';
}

function handleFiles(files: File[]) {
  const filtered = files.filter(f => f.size <= MAX_IMAGE_SIZE_MB * 1024 * 1024);
  const rejectedCount = files.length - filtered.length;
  const existingCount = form.keepExistingImages ? (props.blog.images?.length ?? 0) : 0;
  const allowed = Math.max(0, 5 - existingCount - form.images.length);
  const toAdd = filtered.slice(0, allowed);
  if (rejectedCount > 0) {
    showToast('error', `Some images exceed ${MAX_IMAGE_SIZE_MB}MB and were skipped.`);
  }
  if (filtered.length > allowed) {
    showToast('error', `You can only add ${allowed} more image(s). Maximum 5 images total.`);
  }
  form.images = [...form.images, ...toAdd];
}

function onDragOver(e: DragEvent) {
  e.preventDefault();
  isDragging.value = true;
}

function onDragLeave(e: DragEvent) {
  e.preventDefault();
  isDragging.value = false;
}

function onDrop(e: DragEvent) {
  e.preventDefault();
  isDragging.value = false;
  const files = Array.from(e.dataTransfer?.files || []).filter(f => f.type.startsWith('image/'));
  if (files.length > 0) {
    handleFiles(files);
  }
}

function triggerFileInput() {
  fileInputRef.value?.click();
}

function removeNewImage(i: number) {
  form.images.splice(i, 1);
}

function replaceAllImages() {
  form.keepExistingImages = false;
  form.images = [];
}

const isSubmitting = computed(() => form.processing);

function filePreview(file: File): string | undefined {
  if (!isClient.value) return undefined;
  // @ts-expect-error - guard for SSR environments
  const URLRef = typeof URL !== 'undefined' ? URL : undefined;
  if (!URLRef || typeof URLRef.createObjectURL !== 'function') return undefined;
  try {
    return URLRef.createObjectURL(file);
  } catch {
    return undefined;
  }
}

function submit() {
  // Validate before submit
  if (!form.title.trim()) {
    showToast('error', 'Title is required.');
    return;
  }
  
  // Robust method spoofing: send POST with _method=PUT in the form body
  form
    .transform((data) => ({ ...data, _method: 'PUT' }))
    .post(`/blogs/${props.blog.id}`, {
      forceFormData: true,
      preserveScroll: true,
      onSuccess: () => {
        showToast('success', 'Blog updated successfully!');
      },
      onError: () => {
        showToast('error', 'Failed to update blog. Please check the form.');
      },
    });
}

const remainingTitleChars = computed(() => MAX_TITLE_LENGTH - form.title.length);
const remainingContentChars = computed(() => MAX_CONTENT_LENGTH - form.content.length);
const totalImageCount = computed(() => {
  const existing = form.keepExistingImages ? (props.blog.images?.length ?? 0) : 0;
  return existing + form.images.length;
});

function imageUrl(path?: string) {
  if (!path) return undefined;
  return `/storage/${path}`;
}
</script>

<template>
  <Head :title="`Edit: ${props.blog.title}`" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="mx-auto w-full max-w-3xl p-4">
      <!-- Toast -->
      <Toast v-model="toast.show" :type="toast.type" :message="toast.message" :duration="3500" />

      <div class="mb-4 flex items-center justify-between">
        <h1 class="text-xl font-semibold">Edit Blog</h1>
        <Link href="/blogs" class="text-sm text-primary hover:underline">Back to list</Link>
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
            placeholder="e.g., 10 Tips for Better Productivity"
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

        <!-- Content -->
        <div class="space-y-2">
          <div class="flex items-center justify-between">
            <label class="block text-sm font-semibold text-neutral-700 dark:text-neutral-200">Content</label>
            <span class="text-xs text-neutral-500" :class="{ 'text-red-500': remainingContentChars < 0 }">
              {{ remainingContentChars }} / {{ MAX_CONTENT_LENGTH }}
            </span>
          </div>
          <textarea 
            v-model="form.content" 
            rows="10" 
            :maxlength="MAX_CONTENT_LENGTH"
            placeholder="Write your blog content here... Share your thoughts, ideas, and insights."
            class="w-full resize-none rounded-lg border border-neutral-300 bg-white px-4 py-3 text-sm outline-none transition-all focus:border-black focus:ring-2 focus:ring-black/10 dark:border-neutral-700 dark:bg-neutral-900 dark:text-white dark:focus:border-white dark:focus:ring-white/10"
            :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500/10': form.errors.content }"
          ></textarea>
          <div v-if="form.errors.content" class="flex items-center gap-1 text-sm text-red-600">
            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <span>{{ form.errors.content }}</span>
          </div>
        </div>

        <!-- Existing Images -->
        <div class="space-y-3">
          <div class="flex items-center justify-between">
            <label class="block text-sm font-semibold text-neutral-700 dark:text-neutral-200">Current Images</label>
            <button 
              type="button" 
              @click="replaceAllImages" 
              class="flex items-center gap-1 text-sm font-medium text-red-600 transition-colors hover:text-red-700 dark:text-red-400 dark:hover:text-red-500"
            >
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
              Replace all
            </button>
          </div>
          <div v-if="props.blog.images?.length && form.keepExistingImages" class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
            <div 
              v-for="(img, i) in props.blog.images" 
              :key="i" 
              class="group relative aspect-square overflow-hidden rounded-lg border border-neutral-200 shadow-sm dark:border-neutral-800"
            >
              <img :src="imageUrl(img)" class="h-full w-full object-cover" />
              <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent"></div>
              <div class="absolute bottom-2 left-2 rounded-full bg-green-500 px-2 py-0.5 text-xs font-semibold text-white shadow-lg">
                Current {{ i + 1 }}
              </div>
            </div>
          </div>
          <div v-else class="rounded-lg border-2 border-dashed border-red-200 bg-red-50 p-4 text-center dark:border-red-900 dark:bg-red-950/20">
            <p class="text-sm font-medium text-red-600 dark:text-red-400">⚠️ All existing images will be removed upon saving.</p>
          </div>
        </div>

        <!-- Add New Images -->
        <div class="space-y-3">
          <div class="flex items-center justify-between">
            <label class="block text-sm font-semibold text-neutral-700 dark:text-neutral-200">Add New Images</label>
            <span class="text-xs text-neutral-500">{{ totalImageCount }} / 5 images total</span>
          </div>
          
          <!-- Drag & Drop Zone -->
          <div 
            @dragover="onDragOver"
            @dragleave="onDragLeave"
            @drop="onDrop"
            @click="triggerFileInput"
            class="cursor-pointer rounded-lg border-2 border-dashed border-neutral-300 bg-neutral-50 p-8 text-center transition-all hover:border-black hover:bg-neutral-100 dark:border-neutral-700 dark:bg-neutral-900/50 dark:hover:border-white dark:hover:bg-neutral-900"
            :class="{
              'border-black bg-neutral-100 dark:border-white dark:bg-neutral-900': isDragging,
              'border-red-500 dark:border-red-500': form.errors.images
            }"
          >
            <input 
              ref="fileInputRef"
              type="file" 
              multiple 
              accept="image/*" 
              @change="onFilesChange" 
              class="hidden"
            />
            <div class="flex flex-col items-center gap-3">
              <div class="rounded-full bg-neutral-200 p-3 dark:bg-neutral-800">
                <svg class="h-8 w-8 text-neutral-600 dark:text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
              </div>
              <div>
                <p class="text-sm font-medium text-neutral-700 dark:text-neutral-200">
                  <span class="text-black dark:text-white">Click to upload</span> or drag and drop
                </p>
                <p class="mt-1 text-xs text-neutral-500">PNG, JPG, GIF up to 10MB (Max 5 images total)</p>
              </div>
            </div>
          </div>
          
          <!-- New Image Preview Grid -->
          <div v-if="isClient && form.images.length > 0" class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
            <div 
              v-for="(file, i) in form.images" 
              :key="i" 
              class="group relative aspect-square overflow-hidden rounded-lg border border-neutral-200 shadow-sm transition-all hover:shadow-md dark:border-neutral-800"
            >
              <img :src="filePreview(file)" class="h-full w-full object-cover" />
              <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 transition-opacity group-hover:opacity-100"></div>
              <button 
                type="button" 
                @click="removeNewImage(i)" 
                class="absolute right-2 top-2 flex h-7 w-7 items-center justify-center rounded-full bg-red-500 text-white opacity-0 shadow-lg transition-all hover:bg-red-600 group-hover:opacity-100"
                aria-label="Remove image"
              >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
              <div class="absolute bottom-2 left-2 rounded-full bg-blue-500 px-2 py-0.5 text-xs font-semibold text-white opacity-0 shadow-lg group-hover:opacity-100">
                New {{ i + 1 }}
              </div>
            </div>
          </div>
          
          <div v-if="form.errors.images" class="flex items-center gap-1 text-sm text-red-600">
            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <span>{{ form.errors.images }}</span>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col-reverse gap-3 border-t border-neutral-200 pt-6 dark:border-neutral-800 sm:flex-row sm:justify-between sm:items-center">
          <Link 
            href="/blogs" 
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
            <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            {{ isSubmitting ? 'Saving...' : 'Save Changes' }}
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
