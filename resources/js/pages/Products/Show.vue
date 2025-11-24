<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { reactive, ref, onMounted, watch, onBeforeUnmount, computed } from 'vue';
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogFooter,
} from '@/components/ui/dialog';
import Toast from './Toast.vue';

interface Product {
  id: number;
  name: string;
  description?: string | null;
  images?: string[] | null;
  features?: string[] | null;
  is_featured?: boolean | null;
  created_at?: string | null;
}

const props = defineProps<{ product: Product }>();
const page = usePage();
const canDeleteProducts = computed(() => (page.props.auth as any)?.canDeleteProducts ?? false);

const breadcrumbs = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Products', href: '/products' },
  { title: props.product.name, href: `/products/${props.product.id}` },
];

function imageUrl(path?: string) {
  if (!path) return undefined;
  return `/storage/${path}`;
}

// Image gallery state
const selectedImageIndex = ref(0);
const isImageModalOpen = ref(false);

const selectedImage = computed(() => {
  if (props.product.images && props.product.images.length > 0) {
    return imageUrl(props.product.images[selectedImageIndex.value]);
  }
  return undefined;
});

function selectImage(index: number) {
  selectedImageIndex.value = index;
}

function openImageModal(index: number) {
  selectedImageIndex.value = index;
  isImageModalOpen.value = true;
}

function closeImageModal() {
  isImageModalOpen.value = false;
}

function nextImage() {
  if (props.product.images && selectedImageIndex.value < props.product.images.length - 1) {
    selectedImageIndex.value++;
  }
}

function prevImage() {
  if (selectedImageIndex.value > 0) {
    selectedImageIndex.value--;
  }
}

function formatDate(dateString?: string | null) {
  if (!dateString) return 'N/A';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
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
  router.delete(`/products/${props.product.id}` , {
    onSuccess: () => {
      // Rely on server flash on redirect; avoid duplicate local toast
      cancelDelete();
    },
    onError: () => {
      showToast('error', 'Failed to delete the product. Please try again.');
    },
    onFinish: () => {
      isDeleting.value = false;
    },
  });
}

// Toast state (shared component)
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
  <Head :title="props.product.name" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="mx-auto w-full max-w-7xl p-4 sm:p-6">
      <Toast v-model="toast.show" :type="toast.type" :message="toast.message" :duration="3500" />

      <!-- Header -->
      <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="flex-1">
          <div class="flex items-center gap-2">
            <h1 class="text-2xl font-bold text-neutral-900 dark:text-white sm:text-3xl">{{ props.product.name }}</h1>
            <span v-if="props.product.is_featured" class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">Featured</span>
          </div>
          <p class="mt-2 flex items-center gap-1 text-sm text-neutral-500 dark:text-neutral-400">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            Created {{ formatDate(props.product.created_at) }}
          </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
          <Link 
            href="/products" 
            class="flex items-center gap-2 rounded-lg border border-neutral-300 bg-white px-4 py-2.5 text-sm font-medium text-neutral-700 transition-all hover:bg-neutral-50 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300 dark:hover:bg-neutral-800"
          >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back
          </Link>
          <Link 
            :href="`/products/${props.product.id}/edit`" 
            class="flex items-center gap-2 rounded-lg border border-neutral-300 bg-white px-4 py-2.5 text-sm font-medium text-neutral-700 transition-all hover:bg-neutral-50 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300 dark:hover:bg-neutral-800"
          >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Edit
          </Link>
          <button 
            v-if="canDeleteProducts"
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

      <!-- Content Grid -->
      <div class="grid gap-6 lg:grid-cols-3">
        <!-- Left Column -->
        <div class="space-y-6 lg:col-span-2">
          <!-- Image Gallery -->
          <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-800 dark:bg-neutral-950">
            <div v-if="props.product.images?.length" class="space-y-4 p-4">
              <!-- Main Image -->
              <div 
                class="group relative aspect-video cursor-zoom-in overflow-hidden rounded-lg bg-neutral-100 dark:bg-neutral-900"
                @click="openImageModal(selectedImageIndex)"
              >
                <img 
                  :src="selectedImage" 
                  :alt="props.product.name"
                  class="h-full w-full object-contain transition-transform duration-300 group-hover:scale-105" 
                />
                <div class="absolute inset-0 flex items-center justify-center bg-black/0 opacity-0 transition-all group-hover:bg-black/20 group-hover:opacity-100">
                  <div class="rounded-full bg-white/90 p-3 dark:bg-neutral-900/90">
                    <svg class="h-6 w-6 text-neutral-900 dark:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                    </svg>
                  </div>
                </div>
                <div class="absolute left-4 top-4 rounded-full bg-black/70 px-3 py-1 text-xs font-semibold text-white backdrop-blur-sm">
                  {{ selectedImageIndex + 1 }} / {{ props.product.images.length }}
                </div>
              </div>
              
              <!-- Thumbnails -->
              <div v-if="props.product.images.length > 1" class="grid grid-cols-4 gap-2 sm:grid-cols-5 md:grid-cols-6">
                <button
                  v-for="(img, i) in props.product.images"
                  :key="i"
                  @click="selectImage(i)"
                  class="group relative aspect-square overflow-hidden rounded-md border-2 transition-all hover:scale-105"
                  :class="[
                    selectedImageIndex === i 
                      ? 'border-black shadow-lg dark:border-white' 
                      : 'border-neutral-200 dark:border-neutral-800'
                  ]"
                >
                  <img 
                    :src="imageUrl(img)" 
                    :alt="`${props.product.name} - Image ${i + 1}`"
                    class="h-full w-full object-cover" 
                  />
                  <div 
                    v-if="selectedImageIndex === i" 
                    class="absolute inset-0 bg-black/20 dark:bg-white/20"
                  ></div>
                </button>
              </div>
            </div>
            
            <!-- Empty State -->
            <div v-else class="flex flex-col items-center justify-center p-16 text-center">
              <div class="rounded-full bg-neutral-100 p-6 dark:bg-neutral-800">
                <svg class="h-16 w-16 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>
              <h3 class="mt-4 text-lg font-semibold text-neutral-900 dark:text-white">No images available</h3>
              <p class="mt-1 text-sm text-neutral-500">This product doesn't have any images yet.</p>
            </div>
          </div>

          <!-- Description -->
          <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-800 dark:bg-neutral-950">
            <div class="mb-4 flex items-center gap-2">
              <svg class="h-5 w-5 text-neutral-700 dark:text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
              </svg>
              <h2 class="text-lg font-bold text-neutral-900 dark:text-white">Description</h2>
            </div>
            <div v-if="props.product.description" class="prose prose-sm max-w-none dark:prose-invert">
              <p class="whitespace-pre-line leading-relaxed text-neutral-700 dark:text-neutral-300">
                {{ props.product.description }}
              </p>
            </div>
            <div v-else class="rounded-lg border-2 border-dashed border-neutral-200 p-8 text-center dark:border-neutral-800">
              <svg class="mx-auto h-12 w-12 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              <p class="mt-2 text-sm text-neutral-500">No description provided.</p>
            </div>
          </div>
        </div>

        <!-- Right Column -->
        <div class="space-y-6">
          <!-- Features -->
          <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-800 dark:bg-neutral-950">
            <div class="mb-4 flex items-center gap-2">
              <svg class="h-5 w-5 text-neutral-700 dark:text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <h2 class="text-lg font-bold text-neutral-900 dark:text-white">Key Features</h2>
            </div>
            <div v-if="props.product.features?.length" class="space-y-2">
              <div 
                v-for="(f, i) in props.product.features" 
                :key="i" 
                class="flex items-start gap-3 rounded-lg bg-neutral-50 p-3 transition-colors hover:bg-neutral-100 dark:bg-neutral-900 dark:hover:bg-neutral-800"
              >
                <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span class="text-sm font-medium text-neutral-700 dark:text-neutral-200">{{ f }}</span>
              </div>
            </div>
            <div v-else class="rounded-lg border-2 border-dashed border-neutral-200 p-6 text-center dark:border-neutral-800">
              <svg class="mx-auto h-10 w-10 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
              <p class="mt-2 text-sm text-neutral-500">No features listed.</p>
            </div>
          </div>

          <!-- Product Info -->
          <div class="rounded-xl border border-neutral-200 bg-gradient-to-br from-neutral-50 to-white p-6 shadow-sm dark:border-neutral-800 dark:from-neutral-950 dark:to-neutral-900">
            <div class="mb-4 flex items-center gap-2">
              <svg class="h-5 w-5 text-neutral-700 dark:text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <h2 class="text-lg font-bold text-neutral-900 dark:text-white">Product Info</h2>
            </div>
            <div class="space-y-3">
              <div class="flex items-center justify-between rounded-lg border border-neutral-200 bg-white p-3 dark:border-neutral-800 dark:bg-neutral-950">
                <span class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Product ID</span>
                <span class="font-mono text-sm font-semibold text-neutral-900 dark:text-white">#{{ props.product.id }}</span>
              </div>
              <div class="rounded-lg border border-neutral-200 bg-white p-3 dark:border-neutral-800 dark:bg-neutral-950">
                <div class="mb-1 text-sm font-medium text-neutral-500 dark:text-neutral-400">Created</div>
                <div class="text-sm font-semibold text-neutral-900 dark:text-white">{{ formatDate(props.product.created_at) }}</div>
              </div>
              <div class="flex items-center justify-between rounded-lg border border-neutral-200 bg-white p-3 dark:border-neutral-800 dark:bg-neutral-950">
                <span class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Images</span>
                <span class="rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-bold text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                  {{ props.product.images?.length || 0 }}
                </span>
              </div>
              <div class="flex items-center justify-between rounded-lg border border-neutral-200 bg-white p-3 dark:border-neutral-800 dark:bg-neutral-950">
                <span class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Features</span>
                <span class="rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-bold text-green-800 dark:bg-green-900 dark:text-green-200">
                  {{ props.product.features?.length || 0 }}
                </span>
              </div>
              <div class="flex items-center justify-between rounded-lg border border-neutral-200 bg-white p-3 dark:border-neutral-800 dark:bg-neutral-950">
                <span class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Featured</span>
                <span :class="props.product.is_featured ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' : 'bg-neutral-200 text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300'" class="rounded-full px-2.5 py-0.5 text-xs font-bold">
                  {{ props.product.is_featured ? 'Yes' : 'No' }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Modal -->
    <Dialog v-model:open="confirmOpen">
      <DialogContent class="sm:max-w-md">
        <DialogHeader>
          <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/20">
            <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
          </div>
          <DialogTitle class="text-center text-xl">Delete Product?</DialogTitle>
          <DialogDescription class="text-center">
            This action cannot be undone. This will permanently delete
            <span class="font-semibold text-neutral-900 dark:text-white">{{ props.product.name }}</span> and remove all its images.
          </DialogDescription>
        </DialogHeader>
        <DialogFooter class="flex-col-reverse gap-2 sm:flex-row sm:justify-center">
          <button 
            @click="cancelDelete" 
            type="button" 
            class="flex w-full items-center justify-center gap-2 rounded-lg border border-neutral-300 bg-white px-6 py-2.5 text-sm font-medium text-neutral-700 transition-all hover:bg-neutral-50 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300 dark:hover:bg-neutral-800 sm:w-auto"
          >
            Cancel
          </button>
          <button 
            @click="confirmDelete" 
            :disabled="isDeleting" 
            class="flex w-full items-center justify-center gap-2 rounded-lg bg-red-600 px-6 py-2.5 text-sm font-semibold text-white transition-all hover:bg-red-700 active:scale-95 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto"
          >
            <svg v-if="isDeleting" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>{{ isDeleting ? 'Deleting...' : 'Delete Product' }}</span>
          </button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Image Lightbox -->
    <Dialog v-model:open="isImageModalOpen">
      <DialogContent class="max-w-5xl border-0 bg-black/95 p-0">
        <button 
          @click="closeImageModal" 
          class="absolute right-4 top-4 z-10 flex h-10 w-10 items-center justify-center rounded-full bg-white/20 text-white backdrop-blur-sm transition-all hover:bg-white/30"
        >
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
        
        <button 
          v-if="selectedImageIndex > 0"
          @click="prevImage" 
          class="absolute left-4 top-1/2 z-10 flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full bg-white/20 text-white backdrop-blur-sm transition-all hover:bg-white/30"
        >
          <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </button>
        
        <button 
          v-if="props.product.images && selectedImageIndex < props.product.images.length - 1"
          @click="nextImage" 
          class="absolute right-4 top-1/2 z-10 flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full bg-white/20 text-white backdrop-blur-sm transition-all hover:bg-white/30"
        >
          <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </button>
        
        <div class="relative aspect-video w-full">
          <img :src="selectedImage" :alt="props.product.name" class="h-full w-full object-contain" />
          <div class="absolute bottom-4 left-1/2 -translate-x-1/2 rounded-full bg-white/20 px-4 py-2 text-sm font-semibold text-white backdrop-blur-sm">
            {{ selectedImageIndex + 1 }} / {{ props.product.images?.length || 0 }}
          </div>
        </div>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template>
