<script setup lang="ts">
import { ref, computed } from 'vue';
import AdminLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import type { InertiaForm } from '@inertiajs/vue3';

const uploadForm = useForm({
  title: '',
  file: null as File | null,
  course_id: null as number | null,
}) as InertiaForm;

const previewUrl = ref<string | null>(null);
const uploading = ref(false);

function onFileChange(e: Event) {
  const input = e.target as HTMLInputElement;
  if (!input.files || !input.files[0]) {
    uploadForm.file = null;
    previewUrl.value = null;
    return;
  }
  const f = input.files[0];
  uploadForm.file = f;
  // create preview for images
  if (f.type.startsWith('image/')) {
    const reader = new FileReader();
    reader.onload = (ev) => (previewUrl.value = ev.target?.result as string);
    reader.readAsDataURL(f);
  } else {
    previewUrl.value = null;
  }
}

async function submit() {
  uploading.value = true;
  uploadForm.post(route('admin.notes.store'), {
    forceFormData: true,
    onFinish: () => (uploading.value = false),
  });
}

function confirmDelete(id: number) {
  if (!confirm('Delete this note?')) return;
  router.delete(route('admin.notes.destroy', id));
}
</script>

<template>
  <Head title="Notes" />

  <AdminLayout>
    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
          <div class="p-6 text-gray-900 dark:text-gray-100">

            <!-- Header -->
            <div class="mb-8">
              <h1 class="mb-2 text-3xl font-bold">University Notes</h1>
              <p class="text-gray-600 dark:text-gray-400">Upload notes (PDF / images). Files are stored on Firebase.</p>
            </div>

            <!-- Upload form -->
            <div class="mb-8">
              <form @submit.prevent="submit" class="space-y-4">
                <div>
                  <label class="block text-sm font-medium">Title</label>
                  <input v-model="uploadForm.title" type="text" required class="mt-1 block w-full rounded-md border px-3 py-2" />
                </div>

                <div>
                  <label class="block text-sm font-medium">Course ID (optional)</label>
                  <input v-model="uploadForm.course_id" type="number" class="mt-1 block w-full rounded-md border px-3 py-2" />
                </div>

                <div>
                  <label class="block text-sm font-medium">File (PDF or Image)</label>
                  <input type="file" accept="application/pdf,image/*" @change="onFileChange" class="mt-1 block w-full" />
                </div>

                <div v-if="previewUrl" class="mt-2">
                  <p class="text-sm">Preview:</p>
                  <img :src="previewUrl" class="mt-1 max-h-64 rounded border" />
                </div>

                <div>
                  <button type="submit" :disabled="uploading" class="inline-flex items-center rounded bg-blue-600 px-4 py-2 text-white">
                    <span v-if="!uploading">Upload</span>
                    <span v-else>Uploading…</span>
                  </button>
                </div>
              </form>
            </div>

            <!-- Notes list -->
            <div>
              <h2 class="mb-4 border-b pb-2 text-2xl font-semibold">Uploaded Notes</h2>

              <div v-if="$page.props.notes.data.length === 0">
                <p class="text-sm text-gray-500">No notes uploaded yet.</p>
              </div>

              <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3">
                <div v-for="note in $page.props.notes.data" :key="note.id" class="rounded border bg-white p-4 dark:bg-gray-900">
                  <div class="flex justify-between">
                    <h3 class="text-lg font-medium">{{ note.title }}</h3>
                    <button @click="confirmDelete(note.id)" class="text-red-600">Delete</button>
                  </div>

                  <div class="mt-3">
                    <div v-if="note.file_type === 'image'">
                      <img :src="note.file_url" class="max-h-40 w-full object-contain rounded" />
                    </div>
                    <div v-else>
                      <a :href="note.file_url" target="_blank" class="text-blue-600 underline">Open PDF</a>
                    </div>
                  </div>

                  <div class="mt-2 text-xs text-gray-500">
                    Uploaded: {{ new Date(note.created_at).toLocaleString() }}
                  </div>
                </div>
              </div>

              <!-- pagination -->
              <div class="mt-6">
                <Pagination :links="$page.props.notes.links" />
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
