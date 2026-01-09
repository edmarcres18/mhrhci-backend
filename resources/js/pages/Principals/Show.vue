<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface Principal {
  id: number;
  name: string;
  description?: string | null;
  logo?: string | null;
  logo_url?: string | null;
  created_at?: string | null;
  updated_at?: string | null;
  is_featured?: boolean;
}

const props = defineProps<{ principal: Principal }>();

const breadcrumbs = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Principals', href: '/principals' },
  { title: props.principal.name, href: `/principals/${props.principal.id}` },
];

const hasDescription = computed(() => !!props.principal.description);
const logoUrl = computed(() => props.principal.logo_url ?? undefined);
const isLogoModalOpen = ref(false);
const openLogoModal = () => {
  if (logoUrl.value) {
    isLogoModalOpen.value = true;
  }
};
const closeLogoModal = () => {
  isLogoModalOpen.value = false;
};
</script>

<template>
  <Head :title="props.principal.name" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="mx-auto w-full max-w-5xl space-y-4 p-4 sm:space-y-6 sm:p-6">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="space-y-2">
          <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold text-neutral-900 dark:text-white">{{ props.principal.name }}</h1>
            <span
              class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold"
              :class="props.principal.is_featured ? 'bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-200' : 'bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-300'"
            >
              {{ props.principal.is_featured ? 'Featured' : 'Not featured' }}
            </span>
          </div>
          <p class="text-sm text-neutral-500 dark:text-neutral-400">
            Created {{ props.principal.created_at || '—' }} • Updated {{ props.principal.updated_at || '—' }}
          </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
          <Link
            href="/principals"
            class="inline-flex items-center justify-center rounded-md border border-neutral-300 px-4 py-2 text-sm font-medium text-neutral-700 shadow-sm transition hover:-translate-y-0.5 hover:bg-neutral-50 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-200 dark:hover:bg-neutral-800"
          >
            Back
          </Link>
          <Link
            :href="`/principals/${props.principal.id}/edit`"
            class="inline-flex items-center justify-center rounded-md bg-neutral-900 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-neutral-800 dark:bg-white dark:text-black dark:hover:bg-neutral-200"
          >
            Edit
          </Link>
        </div>
      </div>

      <div class="grid gap-4 sm:grid-cols-[1fr,1.3fr] sm:gap-6">
        <div class="rounded-2xl border border-neutral-200 bg-gradient-to-b from-neutral-50 to-white p-5 shadow-sm dark:border-neutral-800 dark:from-neutral-900 dark:to-neutral-950">
          <div class="mb-3 text-sm font-semibold text-neutral-600 dark:text-neutral-300">Brand</div>
          <div class="flex flex-col items-center justify-center gap-3">
            <button
              type="button"
              class="group relative w-36 rounded-xl border border-dashed border-neutral-300 bg-white p-3 shadow-inner transition focus:outline-none focus:ring-2 focus:ring-neutral-400 dark:border-neutral-700 dark:bg-neutral-900"
              :disabled="!logoUrl"
              @click="openLogoModal"
            >
              <div class="aspect-square overflow-hidden rounded-lg bg-neutral-50 dark:bg-neutral-800">
                <img
                  v-if="logoUrl"
                  :src="logoUrl"
                  alt="Logo"
                  class="mx-auto max-h-full max-w-full object-contain transition duration-200 group-hover:scale-[1.02]"
                />
                <div v-else class="flex h-full w-full items-center justify-center text-xs font-medium text-neutral-500 dark:text-neutral-400">
                  No Logo
                </div>
              </div>
              <div
                v-if="logoUrl"
                class="pointer-events-none absolute inset-0 flex items-end justify-center rounded-xl bg-gradient-to-t from-black/10 to-transparent opacity-0 transition group-hover:opacity-100"
              >
                <span class="mb-2 inline-flex items-center gap-1 rounded-full bg-white/90 px-2 py-1 text-[11px] font-semibold text-neutral-700 shadow-sm backdrop-blur dark:bg-neutral-800/90 dark:text-neutral-200">
                  View full size
                </span>
              </div>
            </button>
            <p class="text-xs text-neutral-500 dark:text-neutral-400">Uploads are displayed in a square card for consistency.</p>
          </div>
        </div>

        <div class="rounded-2xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-800 dark:bg-neutral-950">
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">Principal Details</h2>
            <span class="text-xs uppercase tracking-wide text-neutral-400">Overview</span>
          </div>
          <dl class="mt-4 grid gap-4 sm:grid-cols-2">
            <div class="space-y-1 rounded-lg bg-neutral-50 p-3 dark:bg-neutral-900">
              <dt class="text-xs font-semibold uppercase tracking-wide text-neutral-500 dark:text-neutral-400">Name</dt>
              <dd class="text-base text-neutral-900 dark:text-neutral-50">{{ props.principal.name }}</dd>
            </div>
            <div class="space-y-1 rounded-lg bg-neutral-50 p-3 dark:bg-neutral-900">
              <dt class="text-xs font-semibold uppercase tracking-wide text-neutral-500 dark:text-neutral-400">Status</dt>
              <dd class="flex items-center gap-2 text-sm text-neutral-800 dark:text-neutral-100">
                <span
                  class="h-2 w-2 rounded-full"
                  :class="props.principal.is_featured ? 'bg-green-500' : 'bg-neutral-400'"
                />
                {{ props.principal.is_featured ? 'Featured' : 'Standard' }}
              </dd>
            </div>
            <div class="space-y-1 rounded-lg bg-neutral-50 p-3 dark:bg-neutral-900 sm:col-span-2">
              <dt class="text-xs font-semibold uppercase tracking-wide text-neutral-500 dark:text-neutral-400">Description</dt>
              <dd class="text-sm leading-relaxed text-neutral-700 dark:text-neutral-200">
                <span v-if="hasDescription">{{ props.principal.description }}</span>
                <span v-else class="text-neutral-400 dark:text-neutral-500">No description provided.</span>
              </dd>
            </div>
          </dl>
        </div>
      </div>
    </div>
    <div
      v-if="isLogoModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm"
      @click.self="closeLogoModal"
    >
      <div class="relative max-h-[90vh] w-full max-w-5xl overflow-hidden rounded-2xl bg-white/95 p-4 shadow-2xl dark:bg-neutral-900/95">
        <button
          type="button"
          class="absolute right-3 top-3 inline-flex h-9 w-9 items-center justify-center rounded-full bg-neutral-100 text-neutral-700 shadow hover:bg-neutral-200 focus:outline-none focus:ring-2 focus:ring-neutral-400 dark:bg-neutral-800 dark:text-neutral-200 dark:hover:bg-neutral-700"
          @click="closeLogoModal"
          aria-label="Close"
        >
          ✕
        </button>
        <div class="flex items-center justify-center overflow-auto">
          <img
            v-if="logoUrl"
            :src="logoUrl"
            alt="Logo full size"
            class="h-auto w-auto max-h-[80vh] max-w-full object-contain"
          />
        </div>
      </div>
    </div>
  </AppLayout>
</template>
