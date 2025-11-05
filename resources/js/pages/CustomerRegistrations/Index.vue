<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { onMounted, reactive, ref, computed } from 'vue';
import axios from 'axios';
import Toast from './Toast.vue';

interface RegistrationItem {
  id: number;
  entry_number: string;
  name: string;
  hospital: string;
  address: string;
  position: string;
  contact_number: string;
  email: string;
  products_interest?: string | null;
  created_at?: string | null;
  updated_at?: string | null;
}

const breadcrumbs = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Customer Registrations', href: '/customer-registrations' },
];

const items = ref<RegistrationItem[]>([]);
const loading = ref<boolean>(false);
const search = ref<string>('');
const page = usePage();
const canDelete = computed<boolean>(() => {
  const auth: any = page.props.auth || {};
  return Boolean(auth?.canDeleteCustomerRegistrations);
});

type ToastType = 'success' | 'error' | 'info' | 'warning';
const toast = reactive<{ show: boolean; type: ToastType; message: string }>({ show: false, type: 'info', message: '' });

function showToast(type: ToastType, message: string) {
  toast.show = true;
  toast.type = type;
  toast.message = message;
}

async function fetchItems(limit?: number) {
  loading.value = true;
  try {
    const { data } = await axios.get('/api/v1/customer-registrations', { params: { limit } });
    if (data?.success) {
      items.value = data.data as RegistrationItem[];
    } else {
      showToast('error', data?.message || 'Failed to load registrations');
    }
  } catch (err: any) {
    showToast('error', err?.response?.data?.message || 'Failed to load registrations');
  } finally {
    loading.value = false;
  }
}

function filteredItems() {
  const q = (search.value || '').toLowerCase().trim();
  if (!q) return items.value;
  return items.value.filter((i) =>
    [i.name, i.hospital, i.email, i.contact_number, i.position, i.entry_number]
      .filter(Boolean)
      .some((v) => String(v).toLowerCase().includes(q))
  );
}

async function deleteItem(id: number) {
  if (!canDelete.value) {
    showToast('error', 'You do not have permission to delete registrations');
    return;
  }
  const ok = window.confirm('Delete this registration? This cannot be undone.');
  if (!ok) return;
  try {
    const { data } = await axios.delete(`/api/v1/customer-registrations/${id}`);
    if (data?.success) {
      showToast('success', 'Registration deleted successfully');
      items.value = items.value.filter((i) => i.id !== id);
    } else {
      showToast('error', data?.message || 'Failed to delete registration');
    }
  } catch (err: any) {
    showToast('error', err?.response?.data?.message || 'Failed to delete registration');
  }
}

function formatDate(iso?: string | null) {
  if (!iso) return '';
  const d = new Date(iso);
  return d.toLocaleString();
}

onMounted(() => {
  fetchItems();
});
</script>

<template>
  <Head title="Customer Registrations" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="mx-auto w-full max-w-7xl p-4">
      <Toast v-model="toast.show" :type="toast.type" :message="toast.message" :duration="3500" />

      <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="text-xl font-semibold">Customer Registrations</h1>
        <Link
          href="/customer-registrations/create"
          class="inline-flex w-full items-center justify-center rounded-lg bg-black px-4 py-2 text-sm font-medium text-white hover:bg-neutral-800 dark:bg-white dark:text-black dark:hover:bg-neutral-200 sm:w-auto"
        >
          <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          New Registration
        </Link>
      </div>

      <div class="mb-3">
        <input
          v-model="search"
          type="text"
          placeholder="Search by name, hospital, email, contact..."
          class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-black dark:bg-neutral-900 dark:text-white"
        />
      </div>

      <div class="overflow-x-auto rounded-lg border">
        <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-800">
          <thead class="bg-neutral-50 dark:bg-neutral-900">
            <tr>
              <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider">Entry #</th>
              <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider">Name</th>
              <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider">Hospital</th>
              <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider">Position</th>
              <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider">Contact</th>
              <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider">Email</th>
              <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider">Created</th>
              <th class="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">
            <tr v-if="loading">
              <td colspan="8" class="px-3 py-4 text-center text-sm">Loading...</td>
            </tr>
            <tr v-else-if="filteredItems().length === 0">
              <td colspan="8" class="px-3 py-4 text-center text-sm">No registrations found.</td>
            </tr>
            <tr v-for="item in filteredItems()" :key="item.id" class="hover:bg-neutral-50 dark:hover:bg-neutral-900">
              <td class="px-3 py-2 text-sm">{{ item.entry_number }}</td>
              <td class="px-3 py-2 text-sm">
                <Link :href="`/customer-registrations/${item.id}`" class="text-black hover:underline dark:text-white">
                  {{ item.name }}
                </Link>
              </td>
              <td class="px-3 py-2 text-sm">{{ item.hospital }}</td>
              <td class="px-3 py-2 text-sm">{{ item.position }}</td>
              <td class="px-3 py-2 text-sm">{{ item.contact_number }}</td>
              <td class="px-3 py-2 text-sm">{{ item.email }}</td>
              <td class="px-3 py-2 text-sm">{{ formatDate(item.created_at) }}</td>
              <td class="px-3 py-2 text-right">
                <button
                  v-if="canDelete"
                  type="button"
                  class="inline-flex items-center rounded-md border border-red-300 px-3 py-1 text-xs font-medium text-red-700 hover:bg-red-50 dark:border-red-700 dark:text-red-300 dark:hover:bg-red-900/30"
                  @click="deleteItem(item.id)"
                >
                  Delete
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>