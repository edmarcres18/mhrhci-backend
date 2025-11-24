<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import HeroBackgroundManager from '@/components/HeroBackgroundManager.vue';

const breadcrumbs = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Hero Backgrounds', href: '/hero-backgrounds' },
];
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="px-4 py-6">
      <h1 class="text-lg font-semibold">Hero Backgrounds</h1>
      <p class="text-sm text-gray-500">Manage homepage hero section background images.</p>
      <div class="mt-4">
        <HeroBackgroundManager />
      </div>
    </div>
  </AppLayout>
  
</template>

