<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
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

const props = defineProps<{ id: number }>();

const breadcrumbs = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Customer Registrations', href: '/customer-registrations' },
  { title: 'Details', href: `/customer-registrations/${props.id}` },
];

const item = ref<RegistrationItem | null>(null);
const loading = ref<boolean>(false);
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

async function fetchItem() {
  loading.value = true;
  try {
    const { data } = await axios.get(`/api/v1/customer-registrations/${props.id}`);
    if (data?.success) {
      item.value = data.data as RegistrationItem;
    } else {
      showToast('error', data?.message || 'Failed to load registration');
    }
  } catch (err: any) {
    showToast('error', err?.response?.data?.message || 'Failed to load registration');
  } finally {
    loading.value = false;
  }
}

async function deleteItem() {
  if (!canDelete.value) {
    showToast('error', 'You do not have permission to delete registrations');
    return;
  }
  const ok = window.confirm('Delete this registration? This cannot be undone.');
  if (!ok) return;
  try {
    const { data } = await axios.delete(`/api/v1/customer-registrations/${props.id}`);
    if (data?.success) {
      showToast('success', 'Registration deleted successfully');
      router.visit('/customer-registrations');
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

onMounted(() => fetchItem());
</script>

<template>
  <Head title="Registration Details" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="mx-auto w-full max-w-3xl p-4">
      <Toast v-model="toast.show" :type="toast.type" :message="toast.message" :duration="3500" />

      <div class="mb-4 flex items-center justify-between">
        <h1 class="text-xl font-semibold">Registration Details</h1>
        <div class="flex items-center gap-3">
          <Link href="/customer-registrations" class="text-sm text-neutral-600 hover:underline dark:text-neutral-300">Back</Link>
          <button
            v-if="canDelete"
            type="button"
            class="rounded-lg border border-red-300 px-3 py-1 text-sm font-medium text-red-700 hover:bg-red-50 dark:border-red-700 dark:text-red-300 dark:hover:bg-red-900/30"
            @click="deleteItem"
          >
            Delete
          </button>
        </div>
      </div>

      <div v-if="loading" class="rounded-lg border p-4 text-sm">Loading...</div>
      <div v-else-if="!item" class="rounded-lg border p-4 text-sm">Registration not found.</div>
      <div v-else class="rounded-lg border p-4">
        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div>
            <dt class="text-xs font-semibold uppercase text-neutral-500">Entry Number</dt>
            <dd class="text-sm">{{ item.entry_number }}</dd>
          </div>
          <div>
            <dt class="text-xs font-semibold uppercase text-neutral-500">Name</dt>
            <dd class="text-sm">{{ item.name }}</dd>
          </div>
          <div>
            <dt class="text-xs font-semibold uppercase text-neutral-500">Hospital</dt>
            <dd class="text-sm">{{ item.hospital }}</dd>
          </div>
          <div>
            <dt class="text-xs font-semibold uppercase text-neutral-500">Position</dt>
            <dd class="text-sm">{{ item.position }}</dd>
          </div>
          <div class="sm:col-span-2">
            <dt class="text-xs font-semibold uppercase text-neutral-500">Address</dt>
            <dd class="text-sm">{{ item.address }}</dd>
          </div>
          <div>
            <dt class="text-xs font-semibold uppercase text-neutral-500">Contact Number</dt>
            <dd class="text-sm">{{ item.contact_number }}</dd>
          </div>
          <div>
            <dt class="text-xs font-semibold uppercase text-neutral-500">Email</dt>
            <dd class="text-sm">{{ item.email }}</dd>
          </div>
          <div class="sm:col-span-2">
            <dt class="text-xs font-semibold uppercase text-neutral-500">Products Interest / Remarks</dt>
            <dd class="text-sm">{{ item.products_interest || '—' }}</dd>
          </div>
          <div>
            <dt class="text-xs font-semibold uppercase text-neutral-500">Created</dt>
            <dd class="text-sm">{{ formatDate(item.created_at) }}</dd>
          </div>
          <div>
            <dt class="text-xs font-semibold uppercase text-neutral-500">Updated</dt>
            <dd class="text-sm">{{ formatDate(item.updated_at) }}</dd>
          </div>
        </dl>
      </div>
    </div>
  </AppLayout>
</template>