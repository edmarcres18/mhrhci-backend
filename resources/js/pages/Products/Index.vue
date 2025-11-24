<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import type { InertiaForm } from '@inertiajs/vue3';
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

interface Product {
  id: number;
  name: string;
  product_type?: string;
  product_type_label?: string;
  description?: string | null;
  images?: string[] | null;
  features?: string[] | null;
  is_featured?: boolean;
  created_at?: string | null;
}

interface ProductType {
  value: string;
  label: string;
}

interface Pagination<T> {
  data: T[];
  links: { url: string | null; label: string; active: boolean }[];
}

const page = usePage();
const products = computed(() => (page.props.products as Pagination<Product>) ?? { data: [], links: [] });
const initialFilters = computed(() => (page.props as any)?.filters ?? { search: '', product_type: '', perPage: 10 });
const productTypes = computed(() => (page.props as any)?.productTypes ?? []);
const canDeleteProducts = computed(() => (page.props.auth as any)?.canDeleteProducts ?? false);
const search = ref<string>(initialFilters.value.search || '');
const productType = ref<string>(initialFilters.value.product_type || '');
const perPage = ref<number>(Number(initialFilters.value.perPage) || 10);
let searchTimer: number | undefined;

function applyFilters() {
  router.get('/products', { 
    search: search.value || undefined, 
    product_type: productType.value || undefined,
    perPage: perPage.value 
  }, { preserveState: true, preserveScroll: true, replace: true });
}

watch(
  () => search.value,
  (val) => {
    if (searchTimer) window.clearTimeout(searchTimer);
    searchTimer = window.setTimeout(() => {
      applyFilters();
    }, 350);
  }
);

watch(
  () => productType.value,
  (val) => {
    applyFilters();
  }
);

watch(
  () => perPage.value,
  (val) => {
    applyFilters();
  }
);

const breadcrumbs = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Products', href: '/products' },
];

function imageUrl(path?: string) {
  if (!path) return undefined;
  return `/storage/${path}`;
}

// Delete confirmation modal state
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
  router.delete(`/products/${deletingId.value}` , {
    onSuccess: () => {
      // Server redirect to index carries flash.success; component will remount and display it via onMounted.
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
// Also react when Inertia updates props without a remount (same component visit)
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
  <Head title="Products" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="mx-auto w-full max-w-7xl p-4">
      <!-- Toast -->
      <Toast v-model="toast.show" :type="toast.type" :message="toast.message" :duration="3500" />

      <div class="mb-4 flex flex-col gap-3">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <h1 class="text-xl font-semibold">Products</h1>
          <Link
            href="/products/create"
            class="inline-flex w-full items-center justify-center rounded-lg bg-black px-4 py-2 text-sm font-medium text-white hover:bg-neutral-800 dark:bg-white dark:text-black dark:hover:bg-neutral-200 sm:w-auto"
          >
            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            New Product
          </Link>
        </div>
        
        <!-- Filters -->
        <div class="flex w-full flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <!-- Product Type Filter -->
            <div class="relative">
              <select
                v-model="productType"
                class="w-full appearance-none rounded-md border border-neutral-300 bg-white px-3 py-2 pr-8 text-sm outline-none transition-all focus:border-black focus:ring-2 focus:ring-black/10 dark:border-neutral-700 dark:bg-neutral-900 dark:text-white dark:focus:border-white dark:focus:ring-white/10 sm:w-auto"
              >
                <option value="">All Types</option>
                <option v-for="type in productTypes" :key="type.value" :value="type.value">
                  {{ type.label }}
                </option>
              </select>
              <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-neutral-500 dark:text-neutral-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </div>
            </div>
            
            <!-- Search Input -->
            <div class="relative flex-1 sm:w-64">
              <input
                v-model="search"
                type="text"
                placeholder="Search products..."
                class="w-full rounded-md border border-neutral-300 bg-white px-3 py-2 pl-3 pr-10 text-sm outline-none transition-all focus:border-black focus:ring-2 focus:ring-black/10 dark:border-neutral-700 dark:bg-neutral-900 dark:text-white dark:focus:border-white dark:focus:ring-white/10"
              />
              <svg class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
          </div>
          
          <!-- Per Page -->
          <div class="flex items-center gap-2">
            <label class="text-xs text-neutral-500 dark:text-neutral-400">Per page:</label>
            <select
              v-model.number="perPage"
              class="rounded-md border border-neutral-300 bg-white px-2 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900 dark:text-white"
            >
              <option :value="10">10</option>
              <option :value="25">25</option>
              <option :value="50">50</option>
              <option :value="100">100</option>
            </select>
          </div>
        </div>
      </div>

      <div v-if="products.data.length === 0" class="rounded-xl border border-sidebar-border/70 p-10 text-center dark:border-sidebar-border">
        <p class="text-sm text-neutral-600 dark:text-neutral-300">No products yet. Create your first product.</p>
      </div>

      <div v-else class="space-y-4">
        <!-- Mobile list (xs) -->
        <div class="space-y-3 sm:hidden">
          <div v-for="p in products.data" :key="p.id" class="rounded-xl border border-sidebar-border/70 p-3 dark:border-neutral-800">
            <div class="flex items-center gap-3">
              <div class="h-12 w-16 overflow-hidden rounded bg-neutral-100 dark:bg-neutral-800">
                <img v-if="p.images && p.images[0]" :src="imageUrl(p.images[0])" class="h-full w-full object-cover" />
              </div>
              <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2">
                  <div class="truncate font-medium">{{ p.name }}</div>
                  <span v-if="p.product_type_label" class="inline-flex shrink-0 items-center rounded-full px-2 py-0.5 text-xs font-medium" :class="p.product_type === 'medical_equipment' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'">{{ p.product_type_label }}</span>
                  <span v-if="p.is_featured" class="inline-flex shrink-0 items-center rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">Featured</span>
                </div>
                <div class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">{{ new Date(p.created_at || '').toLocaleDateString() }}</div>
              </div>
            </div>
            <div class="mt-3 flex items-center justify-end gap-3 text-sm">
              <Link :href="`/products/${p.id}`" class="hover:underline">View</Link>
              <Link :href="`/products/${p.id}/edit`" class="hover:underline">Edit</Link>
              <button v-if="canDeleteProducts" @click="requestDelete(p.id, p.name)" class="text-red-600 hover:underline">Delete</button>
            </div>
          </div>
          <!-- Mobile pagination -->
          <div v-if="products.links?.length" class="flex flex-wrap items-center justify-between gap-2 text-sm">
            <div class="text-neutral-500">Showing latest products</div>
            <div class="flex flex-wrap items-center gap-2">
              <template v-for="(link, idx) in products.links" :key="idx">
                <span v-if="!link.url" class="rounded-md px-3 py-1.5 text-sm text-neutral-400" v-html="link.label" />
                <Link v-else :href="link.url" class="rounded-md px-3 py-1.5 text-sm" :class="link.active ? 'bg-black text-white dark:bg-white dark:text-black' : 'hover:bg-neutral-100 dark:hover:bg-neutral-800'" v-html="link.label" />
              </template>
            </div>
          </div>
        </div>

        <!-- Table (sm and up) -->
        <div class="hidden overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border sm:block">
          <div class="relative w-full overflow-auto">
            <table class="w-full caption-bottom text-sm">
              <thead class="bg-neutral-50 text-left text-xs uppercase text-neutral-500 dark:bg-neutral-900/60 dark:text-neutral-400">
                <tr>
                  <th class="px-4 py-3 font-medium">Name</th>
                  <th class="px-4 py-3 font-medium">Type</th>
                  <th class="px-4 py-3 font-medium">Created</th>
                  <th class="px-4 py-3 text-right font-medium">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="p in products.data" :key="p.id" class="border-t border-sidebar-border/70 hover:bg-neutral-50 dark:border-neutral-800 dark:hover:bg-neutral-900/60">
                  <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                      <div class="h-9 w-12 overflow-hidden rounded bg-neutral-100 dark:bg-neutral-800">
                        <img v-if="p.images && p.images[0]" :src="imageUrl(p.images[0])" class="h-full w-full object-cover" />
                      </div>
                      <div>
                        <div class="font-medium">{{ p.name }}</div>
                        <div class="mt-1">
                          <span v-if="p.is_featured" class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">Featured</span>
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="px-4 py-3">
                    <span v-if="p.product_type_label" class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium" :class="p.product_type === 'medical_equipment' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'">{{ p.product_type_label }}</span>
                  </td>
                  <td class="px-4 py-3">{{ new Date(p.created_at || '').toLocaleString() }}</td>
                  <td class="px-4 py-3 text-right">
                    <div class="flex items-center justify-end gap-3">
                      <Link :href="`/products/${p.id}`" class="hover:underline">View</Link>
                      <Link :href="`/products/${p.id}/edit`" class="hover:underline">Edit</Link>
                      <button v-if="canDeleteProducts" @click="requestDelete(p.id, p.name)" class="text-red-600 hover:underline">Delete</button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-if="products.links?.length" class="flex items-center justify-between border-t border-sidebar-border/70 p-3 text-sm dark:border-neutral-800">
            <div class="text-neutral-500">Showing latest products</div>
            <div class="flex flex-wrap items-center gap-2">
              <template v-for="(link, idx) in products.links" :key="idx">
                <span v-if="!link.url" class="rounded-md px-3 py-1.5 text-sm text-neutral-400" v-html="link.label" />
                <Link v-else :href="link.url" class="rounded-md px-3 py-1.5 text-sm" :class="link.active ? 'bg-black text-white dark:bg-white dark:text-black' : 'hover:bg-neutral-100 dark:hover:bg-neutral-800'" v-html="link.label" />
              </template>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <Dialog v-model:open="confirmOpen">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Delete product</DialogTitle>
          <DialogDescription>
            This action cannot be undone. This will permanently delete
            <span class="font-medium">{{ deletingName }}</span> and remove its images.
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
