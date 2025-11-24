<script setup lang="ts">
import { ref, reactive, onMounted, computed } from 'vue';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardContent, CardFooter } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';

type ToastType = 'success' | 'error';

interface HeroBackground {
  id: number;
  image_path: string;
  url: string;
  created_at?: string | null;
  updated_at?: string | null;
}

const MAX_IMAGES = 5;

const images = ref<HeroBackground[]>([]);
const isLoading = ref(false);
const isUploading = ref(false);
const fileInputRef = ref<HTMLInputElement | null>(null);
type SelectedFile = { file: File; preview: string; width?: number; height?: number; valid: boolean; reason?: string };
const isDragging = ref(false);
const selected = ref<SelectedFile[]>([]);
const selectedValidCount = computed(() => selected.value.filter((s) => s.valid).length);
const remaining = () => Math.max(0, MAX_IMAGES - images.value.length - selectedValidCount.value);
const previewIndex = ref(0);
function nextPreview() {
  if (images.value.length === 0) return;
  previewIndex.value = (previewIndex.value + 1) % images.value.length;
}
function prevPreview() {
  if (images.value.length === 0) return;
  previewIndex.value = (previewIndex.value - 1 + images.value.length) % images.value.length;
}

function getCookie(name: string): string {
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);
  if (parts.length === 2) return decodeURIComponent(parts.pop()!.split(';').shift()!);
  return '';
}

async function ensureCsrf(): Promise<void> {
  try {
    await fetch('/sanctum/csrf-cookie', { credentials: 'include' });
  } catch {}
}

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

async function fetchImages() {
  try {
    isLoading.value = true;
    const res = await fetch('/api/hero-backgrounds');
    const json = await res.json();
    if (json.success) {
      images.value = json.data ?? [];
    } else {
      showToast('error', json.message || 'Failed to load images');
    }
  } catch {
    showToast('error', 'Network error while loading images');
  } finally {
    isLoading.value = false;
  }
}

async function uploadSelected() {
  const files = selected.value.filter((s) => s.valid).map((s) => s.file);
  if (files.length === 0) {
    showToast('error', 'Please select images to upload');
    return;
  }

  if (files.length > MAX_IMAGES) {
    showToast('error', `You can upload up to ${MAX_IMAGES} images at a time`);
    return;
  }

  if (images.value.length + files.length > MAX_IMAGES) {
    showToast('error', 'Upload limit exceeded: total must be 5 or fewer');
    return;
  }

  const formData = new FormData();
  for (const f of files) {
    formData.append('images[]', f);
  }

  try {
    isUploading.value = true;
    await ensureCsrf();
    const token = getCookie('XSRF-TOKEN');
    const res = await fetch('/api/hero-backgrounds', {
      method: 'POST',
      body: formData,
      credentials: 'include',
      headers: {
        'Accept': 'application/json',
        'X-XSRF-TOKEN': token,
      },
    });
    const json = await res.json();
    if (json.success) {
      images.value = json.data ?? [];
      clearSelected();
      showToast('success', 'Images uploaded successfully');
    } else {
      const msg = json.message || 'Upload failed';
      showToast('error', msg);
    }
  } catch {
    showToast('error', 'Network error during upload');
  } finally {
    isUploading.value = false;
  }
}

const confirmDeleteOpen = ref(false);
const deletingId = ref<number | null>(null);
const isDeleting = ref(false);

function requestDelete(id: number) {
  deletingId.value = id;
  confirmDeleteOpen.value = true;
}

function cancelDelete() {
  confirmDeleteOpen.value = false;
  deletingId.value = null;
}

async function confirmDelete() {
  if (deletingId.value === null) return;
  try {
    isDeleting.value = true;
    await ensureCsrf();
    const token = getCookie('XSRF-TOKEN');
    const res = await fetch(`/api/hero-backgrounds/${deletingId.value}`, {
      method: 'DELETE',
      credentials: 'include',
      headers: {
        'Accept': 'application/json',
        'X-XSRF-TOKEN': token,
      },
    });
    const json = await res.json();
    if (json.success) {
      images.value = json.data ?? [];
      showToast('success', 'Image deleted successfully');
      cancelDelete();
    } else {
      showToast('error', json.message || 'Delete failed');
    }
  } catch {
    showToast('error', 'Network error during delete');
  } finally {
    isDeleting.value = false;
  }
}

onMounted(() => {
  fetchImages();
});

async function measureImage(file: File): Promise<{ width: number; height: number }> {
  return new Promise((resolve, reject) => {
    const url = URL.createObjectURL(file);
    const img = new Image();
    img.onload = () => {
      const w = img.naturalWidth || img.width;
      const h = img.naturalHeight || img.height;
      URL.revokeObjectURL(url);
      resolve({ width: w, height: h });
    };
    img.onerror = () => {
      URL.revokeObjectURL(url);
      reject(new Error('Failed to read image'));
    };
    img.src = url;
  });
}

async function validateAndAddFiles(files: File[]) {
  for (const file of files) {
    const typeOk = ['image/jpeg', 'image/png', 'image/webp'].includes(file.type);
    const sizeOk = file.size <= 4 * 1024 * 1024;
    let dimsOk = false;
    let w = 0;
    let h = 0;
    try {
      const dims = await measureImage(file);
      w = dims.width;
      h = dims.height;
      dimsOk = w === 1920 && h >= 800 && h <= 810;
    } catch {}
    const valid = typeOk && sizeOk && dimsOk;
    const reason = !typeOk
      ? 'Invalid type'
      : !sizeOk
      ? 'Too large'
      : !dimsOk
      ? 'Invalid dimensions'
      : '';
    if (images.value.length + selectedValidCount.value + (valid ? 1 : 0) > MAX_IMAGES) {
      showToast('error', 'Selection exceeds the remaining limit');
      continue;
    }
    const preview = URL.createObjectURL(file);
    selected.value.push({ file, preview, width: w, height: h, valid, reason });
  }
}

function removeSelected(index: number) {
  const item = selected.value[index];
  if (item) URL.revokeObjectURL(item.preview);
  selected.value.splice(index, 1);
}

function clearSelected() {
  for (const item of selected.value) URL.revokeObjectURL(item.preview);
  selected.value = [];
}

function onDragOver() {
  isDragging.value = true;
}

function onDragLeave() {
  isDragging.value = false;
}

function onDrop(e: DragEvent) {
  isDragging.value = false;
  const dt = e.dataTransfer;
  const files = dt ? Array.from(dt.files || []) : [];
  if (files.length > 0) validateAndAddFiles(files);
}

function onFileInputChange(e: Event) {
  const target = e.target as HTMLInputElement;
  const files = target.files ? Array.from(target.files) : [];
  if (files.length > 0) validateAndAddFiles(files);
  target.value = '';
}
</script>

<template>
  <div class="space-y-6">
    <Card>
      <CardHeader>
        <div class="flex items-center justify-between">
          <CardTitle class="text-base">Hero Preview</CardTitle>
          <div class="flex items-center gap-2">
            <Button variant="outline" size="sm" @click="prevPreview">Prev</Button>
            <Button variant="outline" size="sm" @click="nextPreview">Next</Button>
          </div>
        </div>
      </CardHeader>
      <CardContent>
        <div class="relative w-full h-48 sm:h-64 lg:h-72 rounded-md overflow-hidden">
          <img v-if="images.length > 0" :src="images[previewIndex]?.url || images[previewIndex]?.image_path" alt="Hero" class="absolute inset-0 w-full h-full object-cover" />
          <div class="absolute inset-0 bg-black/30" />
          <div class="absolute inset-x-6 bottom-6 text-white">
            <div class="text-lg sm:text-xl lg:text-2xl font-semibold">Homepage Hero Section</div>
            <div class="text-xs sm:text-sm opacity-90">Preview using current images</div>
          </div>
        </div>
      </CardContent>
    </Card>

    <Card>
      <CardHeader>
        <CardTitle class="text-base">Upload Images</CardTitle>
      </CardHeader>
      <CardContent>
        <div
          class="flex flex-col items-center justify-center gap-3 rounded-md border-2 border-dashed p-6 text-center transition-colors"
          :class="isDragging ? 'border-primary bg-primary/5' : 'border-muted'"
          @dragover.prevent="onDragOver"
          @dragleave.prevent="onDragLeave"
          @drop.prevent="onDrop"
        >
          <div class="flex gap-3">
            <Button variant="secondary" @click="fileInputRef?.click()">Browse</Button>
            <Button :disabled="isUploading || selectedValidCount === 0" :aria-busy="isUploading" @click="uploadSelected">
              {{ isUploading ? 'Uploading…' : `Upload ${selectedValidCount} file(s)` }}
            </Button>
            <Button variant="outline" :disabled="selected.length === 0" @click="clearSelected">Clear</Button>
          </div>
          <p class="text-xs text-muted-foreground">
            Drag & drop images here or click Browse. {{ remaining() }} image(s) remaining.
          </p>
          <input
            ref="fileInputRef"
            type="file"
            multiple
            accept="image/jpeg,image/png,image/webp"
            class="sr-only"
            :disabled="isUploading || images.length >= MAX_IMAGES"
            @change="onFileInputChange"
          />
        </div>
        <div class="mt-4 text-xs text-muted-foreground">
          Requirements: 1920×800–810 px; jpeg/png/webp; ≤ 4MB; max {{ MAX_IMAGES }} total.
        </div>
        <div v-if="selected.length > 0" class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <Card v-for="(s, i) in selected" :key="i" class="overflow-hidden">
            <CardContent class="p-0">
              <img :src="s.preview" alt="Selected" class="w-full h-32 object-cover" />
            </CardContent>
            <CardFooter class="flex items-center justify-between text-xs">
              <div>
                <span v-if="s.width && s.height">{{ s.width }}×{{ s.height }}</span>
                <span v-else>Unknown size</span>
                <span :class="s.valid ? 'text-green-600' : 'text-red-600'" class="ml-2">{{ s.valid ? 'Valid' : s.reason }}</span>
              </div>
              <Button variant="ghost" size="sm" @click="removeSelected(i)">Remove</Button>
            </CardFooter>
          </Card>
        </div>
      </CardContent>
    </Card>

    <div v-if="isLoading" class="text-sm text-gray-500">Loading images…</div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <Card v-for="item in images" :key="item.id" class="overflow-hidden">
        <CardHeader>
          <div class="flex items-center justify-between">
            <CardTitle class="text-sm">Image #{{ item.id }}</CardTitle>
            <Badge :variant="previewIndex === images.indexOf(item) ? 'secondary' : 'outline'">
              {{ previewIndex === images.indexOf(item) ? 'Active in Preview' : 'Applied' }}
            </Badge>
          </div>
        </CardHeader>
        <CardContent class="p-0">
          <img :src="item.url || item.image_path" alt="Hero Background" class="w-full h-40 object-cover" loading="lazy" />
        </CardContent>
        <CardFooter class="flex items-center justify-end gap-2">
          <Button variant="destructive" @click="requestDelete(item.id)">Delete</Button>
        </CardFooter>
      </Card>
    </div>

    <Dialog v-model:open="confirmDeleteOpen">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Delete Image</DialogTitle>
          <DialogDescription>Are you sure you want to delete this image?</DialogDescription>
        </DialogHeader>
        <DialogFooter>
          <Button variant="secondary" @click="cancelDelete">Cancel</Button>
          <Button variant="destructive" :disabled="isDeleting" :aria-busy="isDeleting" @click="confirmDelete">
            {{ isDeleting ? 'Deleting…' : 'Delete' }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <div v-if="toast.show" class="fixed bottom-4 right-4">
      <div
        :class="[
          'px-4 py-3 rounded-md shadow-md text-white',
          toast.type === 'success' ? 'bg-green-600' : 'bg-red-600',
        ]"
      >
        {{ toast.message }}
      </div>
    </div>
  </div>
  
</template>
