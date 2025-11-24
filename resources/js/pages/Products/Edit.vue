<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, onMounted, reactive, watch, onBeforeUnmount } from 'vue';
import Toast from './Toast.vue';

const MAX_NAME_LENGTH = 100;
const MAX_DESCRIPTION_LENGTH = 1000;

interface ProductType {
  value: string;
  label: string;
}

interface Product {
  id: number;
  name: string;
  product_type: string;
  description?: string | null;
  images?: string[] | null;
  features?: string[] | null;
  is_featured?: boolean | null;
}

const props = defineProps<{ 
  product: Product;
  productTypes: ProductType[];
}>();

const breadcrumbs = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Products', href: '/products' },
  { title: 'Edit', href: `/products/${props.product.id}/edit` },
];

const form = useForm({
  name: props.product.name ?? '',
  product_type: props.product.product_type ?? 'medical_supplies',
  description: props.product.description ?? '',
  features: [...(props.product.features ?? [])] as string[],
  images: [] as File[],
  keepExistingImages: true as boolean,
  is_featured: (props.product as any).is_featured ?? false,
});

const newFeature = ref('');
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

function addFeature() {
  const val = newFeature.value.trim();
  if (!val) return;
  form.features.push(val);
  newFeature.value = '';
}
function removeFeature(i: number) {
  form.features.splice(i, 1);
}

function onFilesChange(e: Event) {
  const input = e.target as HTMLInputElement;
  if (!input.files) return;
  handleFiles(Array.from(input.files));
  input.value = '';
}

function handleFiles(files: File[]) {
  const existingCount = form.keepExistingImages ? (props.product.images?.length ?? 0) : 0;
  const allowed = Math.max(0, 5 - existingCount - form.images.length);
  const toAdd = files.slice(0, allowed);
  if (files.length > allowed) {
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
  // @ts-ignore - guard for SSR environments
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
  if (!form.name.trim()) {
    showToast('error', 'Product name is required.');
    return;
  }
  
  // Robust method spoofing: send POST with _method=PUT in the form body
  form
    .transform((data) => ({ ...data, is_featured: data.is_featured ? 1 : 0, _method: 'PUT' }))
    .post(`/products/${props.product.id}`, {
      forceFormData: true,
      preserveScroll: true,
      onSuccess: () => {
        showToast('success', 'Product updated successfully!');
      },
      onError: () => {
        showToast('error', 'Failed to update product. Please check the form.');
      },
    });
}

const remainingNameChars = computed(() => MAX_NAME_LENGTH - form.name.length);
const remainingDescChars = computed(() => MAX_DESCRIPTION_LENGTH - form.description.length);
const totalImageCount = computed(() => {
  const existing = form.keepExistingImages ? (props.product.images?.length ?? 0) : 0;
  return existing + form.images.length;
});

function imageUrl(path?: string) {
  if (!path) return undefined;
  return `/storage/${path}`;
}
</script>

<template>
  <Head :title="`Edit: ${props.product.name}`" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="mx-auto w-full max-w-3xl p-4">
      <!-- Toast -->
      <Toast v-model="toast.show" :type="toast.type" :message="toast.message" :duration="3500" />

      <div class="mb-4 flex items-center justify-between">
        <h1 class="text-xl font-semibold">Edit Product</h1>
        <Link href="/products" class="text-sm text-primary hover:underline">Back to list</Link>
      </div>

      <form @submit.prevent="submit" class="space-y-6 rounded-xl border border-sidebar-border/70 bg-white p-6 shadow-sm dark:border-sidebar-border dark:bg-neutral-950 sm:p-8">
        <!-- Product Name -->
        <div class="space-y-2">
          <div class="flex items-center justify-between">
            <label class="block text-sm font-semibold text-neutral-700 dark:text-neutral-200">
              Product Name <span class="text-red-500">*</span>
            </label>
            <span class="text-xs text-neutral-500" :class="{ 'text-red-500': remainingNameChars < 0 }">
              {{ remainingNameChars }} / {{ MAX_NAME_LENGTH }}
            </span>
          </div>
          <input 
            v-model="form.name" 
            type="text" 
            :maxlength="MAX_NAME_LENGTH"
            placeholder="e.g., Premium Medical Equipment"
            class="w-full rounded-lg border border-neutral-300 bg-white px-4 py-3 text-sm outline-none transition-all focus:border-black focus:ring-2 focus:ring-black/10 dark:border-neutral-700 dark:bg-neutral-900 dark:text-white dark:focus:border-white dark:focus:ring-white/10" 
            :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500/10': form.errors.name }"
          />
          <div v-if="form.errors.name" class="flex items-center gap-1 text-sm text-red-600">
            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <span>{{ form.errors.name }}</span>
          </div>
        </div>

        <!-- Product Type -->
        <div class="space-y-2">
          <label class="block text-sm font-semibold text-neutral-700 dark:text-neutral-200">
            Product Type <span class="text-red-500">*</span>
          </label>
          <div class="relative">
            <select 
              v-model="form.product_type"
              class="w-full appearance-none rounded-lg border border-neutral-300 bg-white px-4 py-3 pr-10 text-sm outline-none transition-all focus:border-black focus:ring-2 focus:ring-black/10 dark:border-neutral-700 dark:bg-neutral-900 dark:text-white dark:focus:border-white dark:focus:ring-white/10"
              :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500/10': form.errors.product_type }"
            >
              <option v-for="type in productTypes" :key="type.value" :value="type.value">
                {{ type.label }}
              </option>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-neutral-500 dark:text-neutral-400">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </div>
          </div>
          <div v-if="form.errors.product_type" class="flex items-center gap-1 text-sm text-red-600">
            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <span>{{ form.errors.product_type }}</span>
          </div>
        </div>

        <!-- Featured Toggle -->
        <div class="space-y-2">
          <label class="block text-sm font-semibold text-neutral-700 dark:text-neutral-200">Featured</label>
          <div class="flex items-center justify-between rounded-lg border border-neutral-200 p-3 dark:border-neutral-800">
            <span class="text-sm text-neutral-600 dark:text-neutral-300">Mark as featured product</span>
            <button type="button" @click="form.is_featured = !form.is_featured" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors"
              :class="form.is_featured ? 'bg-black dark:bg-white' : 'bg-neutral-300 dark:bg-neutral-700'">
              <span class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition-transform dark:bg-black" :class="form.is_featured ? 'translate-x-5' : 'translate-x-1'"></span>
            </button>
          </div>
          <div v-if="form.errors.is_featured" class="flex items-center gap-1 text-sm text-red-600">
            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <span>{{ form.errors.is_featured }}</span>
          </div>
        </div>

        <!-- Description -->
        <div class="space-y-2">
          <div class="flex items-center justify-between">
            <label class="block text-sm font-semibold text-neutral-700 dark:text-neutral-200">Description</label>
            <span class="text-xs text-neutral-500" :class="{ 'text-red-500': remainingDescChars < 0 }">
              {{ remainingDescChars }} / {{ MAX_DESCRIPTION_LENGTH }}
            </span>
          </div>
          <textarea 
            v-model="form.description" 
            rows="5" 
            :maxlength="MAX_DESCRIPTION_LENGTH"
            placeholder="Describe your product in detail... Include key features, specifications, and what makes it unique."
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

        <!-- Features -->
        <div class="space-y-3">
          <label class="block text-sm font-semibold text-neutral-700 dark:text-neutral-200">Key Features</label>
          <div class="flex flex-col gap-2 sm:flex-row">
            <input 
              v-model="newFeature" 
              type="text" 
              placeholder="e.g., Noise cancellation, 40-hour battery life"
              @keydown.enter.prevent="addFeature"
              class="flex-1 rounded-lg border border-neutral-300 bg-white px-4 py-3 text-sm outline-none transition-all focus:border-black focus:ring-2 focus:ring-black/10 dark:border-neutral-700 dark:bg-neutral-900 dark:text-white dark:focus:border-white dark:focus:ring-white/10" 
            />
            <button 
              type="button" 
              @click="addFeature" 
              class="flex items-center justify-center gap-2 rounded-lg bg-black px-4 py-3 text-sm font-medium text-white transition-all hover:bg-neutral-800 active:scale-95 dark:bg-white dark:text-black dark:hover:bg-neutral-200 sm:px-6"
            >
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              <span class="hidden sm:inline">Add</span>
            </button>
          </div>
          <div v-if="form.features.length > 0" class="flex flex-wrap gap-2">
            <div 
              v-for="(f, i) in form.features" 
              :key="i" 
              class="group flex items-center gap-2 rounded-full bg-gradient-to-r from-neutral-100 to-neutral-50 px-4 py-2 text-sm shadow-sm transition-all hover:shadow-md dark:from-neutral-800 dark:to-neutral-900"
            >
              <svg class="h-3.5 w-3.5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              <span class="font-medium text-neutral-700 dark:text-neutral-200">{{ f }}</span>
              <button 
                type="button" 
                @click="removeFeature(i)" 
                class="ml-1 text-neutral-400 transition-colors hover:text-red-600 dark:text-neutral-500 dark:hover:text-red-400"
                aria-label="Remove feature"
              >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>
          <p v-else class="text-sm text-neutral-500 dark:text-neutral-400">No features added yet. Add some key features to highlight.</p>
          <div v-if="form.errors.features" class="flex items-center gap-1 text-sm text-red-600">
            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <span>{{ form.errors.features }}</span>
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
          <div v-if="props.product.images?.length && form.keepExistingImages" class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
            <div 
              v-for="(img, i) in props.product.images" 
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
            href="/products" 
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
