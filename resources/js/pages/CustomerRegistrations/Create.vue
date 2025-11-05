<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import axios from 'axios';
import Toast from './Toast.vue';

interface FormState {
  entry_number: string;
  name: string;
  hospital: string;
  address: string;
  position: string;
  contact_number: string;
  email: string;
  products_interest?: string | null;
}

const breadcrumbs = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Customer Registrations', href: '/customer-registrations' },
  { title: 'Create', href: '/customer-registrations/create' },
];

const form = reactive<FormState>({
  entry_number: '',
  name: '',
  hospital: '',
  address: '',
  position: '',
  contact_number: '',
  email: '',
  products_interest: '',
});

const errors = reactive<Record<string, string>>({});
const submitting = ref<boolean>(false);

type ToastType = 'success' | 'error' | 'info' | 'warning';
const toast = reactive<{ show: boolean; type: ToastType; message: string }>({ show: false, type: 'info', message: '' });
function showToast(type: ToastType, message: string) {
  toast.show = true;
  toast.type = type;
  toast.message = message;
}

function clearErrors() {
  Object.keys(errors).forEach((k) => (errors[k] = ''));
}

async function submit() {
  clearErrors();
  submitting.value = true;
  try {
    const payload = { ...form };
    const { data } = await axios.post('/api/v1/customer-registrations', payload);
    if (data?.success) {
      showToast('success', 'Registration saved successfully');
      const id = data?.data?.id as number | undefined;
      if (id) {
        // Navigate to show page
        router.visit(`/customer-registrations/${id}`);
      } else {
        // Reset form if ID not returned
        Object.assign(form, {
          entry_number: '',
          name: '',
          hospital: '',
          address: '',
          position: '',
          contact_number: '',
          email: '',
          products_interest: '',
        });
      }
    } else {
      showToast('error', data?.message || 'Failed to save registration');
    }
  } catch (err: any) {
    if (err?.response?.status === 422) {
      const serverErrors = err.response.data?.errors || {};
      for (const k in serverErrors) {
        errors[k] = Array.isArray(serverErrors[k]) ? serverErrors[k][0] : String(serverErrors[k]);
      }
      showToast('error', 'Please fix validation errors');
    } else {
      showToast('error', err?.response?.data?.message || 'Failed to save registration');
    }
  } finally {
    submitting.value = false;
  }
}
</script>

<template>
  <Head title="New Registration" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="mx-auto w-full max-w-3xl p-4">
      <Toast v-model="toast.show" :type="toast.type" :message="toast.message" :duration="3500" />

      <div class="mb-4 flex items-center justify-between">
        <h1 class="text-xl font-semibold">New Registration</h1>
        <Link href="/customer-registrations" class="text-sm text-neutral-600 hover:underline dark:text-neutral-300">Back to list</Link>
      </div>

      <form @submit.prevent="submit" class="space-y-4">
        <!-- Entry Number -->
        <div>
          <label class="mb-1 block text-sm font-medium">Entry Number</label>
          <input
            v-model="form.entry_number"
            type="text"
            placeholder="e.g. 12345"
            class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-black dark:bg-neutral-900 dark:text-white"
          />
          <p v-if="errors.entry_number" class="mt-1 text-xs text-red-600">{{ errors.entry_number }}</p>
        </div>

        <!-- Name -->
        <div>
          <label class="mb-1 block text-sm font-medium">Name</label>
          <input v-model="form.name" type="text" class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-black dark:bg-neutral-900 dark:text-white" />
          <p v-if="errors.name" class="mt-1 text-xs text-red-600">{{ errors.name }}</p>
        </div>

        <!-- Hospital -->
        <div>
          <label class="mb-1 block text-sm font-medium">Hospital</label>
          <input v-model="form.hospital" type="text" class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-black dark:bg-neutral-900 dark:text-white" />
          <p v-if="errors.hospital" class="mt-1 text-xs text-red-600">{{ errors.hospital }}</p>
        </div>

        <!-- Address -->
        <div>
          <label class="mb-1 block text-sm font-medium">Address</label>
          <textarea v-model="form.address" rows="3" class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-black dark:bg-neutral-900 dark:text-white" />
          <p v-if="errors.address" class="mt-1 text-xs text-red-600">{{ errors.address }}</p>
        </div>

        <!-- Position -->
        <div>
          <label class="mb-1 block text-sm font-medium">Position</label>
          <input v-model="form.position" type="text" class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-black dark:bg-neutral-900 dark:text-white" />
          <p v-if="errors.position" class="mt-1 text-xs text-red-600">{{ errors.position }}</p>
        </div>

        <!-- Contact Number -->
        <div>
          <label class="mb-1 block text-sm font-medium">Contact Number</label>
          <input v-model="form.contact_number" type="tel" placeholder="e.g. 09xxxxxxxxx" class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-black dark:bg-neutral-900 dark:text-white" />
          <p v-if="errors.contact_number" class="mt-1 text-xs text-red-600">{{ errors.contact_number }}</p>
        </div>

        <!-- Email -->
        <div>
          <label class="mb-1 block text-sm font-medium">Email</label>
          <input v-model="form.email" type="email" class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-black dark:bg-neutral-900 dark:text-white" />
          <p v-if="errors.email" class="mt-1 text-xs text-red-600">{{ errors.email }}</p>
        </div>

        <!-- Products Interest / Remarks -->
        <div>
          <label class="mb-1 block text-sm font-medium">Products Interest / Remarks</label>
          <textarea v-model="form.products_interest" rows="3" class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-black dark:bg-neutral-900 dark:text-white" />
          <p v-if="errors.products_interest || errors.remarks" class="mt-1 text-xs text-red-600">{{ errors.products_interest || errors.remarks }}</p>
        </div>

        <div class="flex items-center justify-end gap-3">
          <Link href="/customer-registrations" class="rounded-lg px-4 py-2 text-sm text-neutral-700 hover:bg-neutral-100 dark:text-neutral-300 dark:hover:bg-neutral-900/30">Cancel</Link>
          <button type="submit" :disabled="submitting" class="rounded-lg bg-black px-4 py-2 text-sm font-medium text-white hover:bg-neutral-800 disabled:cursor-not-allowed disabled:opacity-70 dark:bg-white dark:text-black dark:hover:bg-neutral-200">
            {{ submitting ? 'Saving...' : 'Save' }}
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>