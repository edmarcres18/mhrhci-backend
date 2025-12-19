<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Principal {
  id: number;
  name: string;
  description?: string | null;
  logo?: string | null;
  logo_url?: string | null;
  created_at?: string | null;
  updated_at?: string | null;
}

const props = defineProps<{ principal: Principal }>();

const breadcrumbs = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Principals', href: '/principals' },
  { title: props.principal.name, href: `/principals/${props.principal.id}` },
];

const hasDescription = computed(() => !!props.principal.description);
</script>

<template>
  <Head :title="props.principal.name" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="mx-auto w-full max-w-4xl p-4 sm:p-6">
      <div class="mb-4 flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">{{ props.principal.name }}</h1>
          <p class="text-sm text-neutral-500 dark:text-neutral-400">
            Created {{ props.principal.created_at || '—' }} • Updated {{ props.principal.updated_at || '—' }}
          </p>
        </div>
        <div class="flex items-center gap-2">
          <Link
            href="/principals"
            class="rounded-md border border-neutral-300 px-4 py-2 text-sm text-neutral-700 hover:bg-neutral-50 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-200 dark:hover:bg-neutral-800"
          >
            Back
          </Link>
          <Link
            :href="`/principals/${props.principal.id}/edit`"
            class="rounded-md bg-black px-4 py-2 text-sm font-medium text-white hover:bg-neutral-800 dark:bg-white dark:text-black dark:hover:bg-neutral-200"
          >
            Edit
          </Link>
        </div>
      </div>

      <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-800 dark:bg-neutral-950">
        <div class="flex flex-col gap-6 sm:flex-row sm:items-start">
          <div class="flex-shrink-0">
            <div class="flex h-28 w-28 items-center justify-center rounded-full border border-dashed border-neutral-300 bg-neutral-50 dark:border-neutral-700 dark:bg-neutral-900">
              <img
                v-if="props.principal.logo_url"
                :src="props.principal.logo_url"
                alt="Logo"
                class="h-28 w-28 rounded-full object-cover"
              />
              <span v-else class="text-xs text-neutral-500">No Logo</span>
            </div>
          </div>
          <div class="flex-1 space-y-3">
            <div>
              <h2 class="text-sm font-semibold text-neutral-700 dark:text-neutral-200">Name</h2>
              <p class="text-base text-neutral-900 dark:text-neutral-50">{{ props.principal.name }}</p>
            </div>
            <div>
              <h2 class="text-sm font-semibold text-neutral-700 dark:text-neutral-200">Description</h2>
              <p class="text-neutral-700 dark:text-neutral-200">
                <span v-if="hasDescription">{{ props.principal.description }}</span>
                <span v-else class="text-neutral-400 dark:text-neutral-500">No description provided.</span>
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
