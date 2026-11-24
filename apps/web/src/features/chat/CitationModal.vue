<script setup lang="ts">
export interface Citation {
  id: string
  document_id: string
  document_title: string
  version_id?: string
  section_path: string
  page_number: number
  snippet: string
  score?: number
}

defineProps<{
  citation: Citation | null
  isOpen: boolean
}>()

const emit = defineEmits<{
  (e: 'close'): void
}>()
</script>

<template>
  <div v-if="isOpen && citation" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="emit('close')"></div>

    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
      <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-xl border border-slate-200">
        <!-- Header -->
        <div class="bg-slate-900 px-6 py-4 flex items-center justify-between text-white">
          <div class="flex items-center space-x-2">
            <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-emerald-600 text-xs font-bold">
              <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
              </svg>
            </span>
            <div>
              <h3 class="text-sm font-bold text-white">Sitasi Sumber Dokumen Terverifikasi</h3>
              <p class="text-[11px] text-slate-400">Verifikasi Kebijakan Hak Akses Lolos (Policy Check Passed)</p>
            </div>
          </div>
          <button @click="emit('close')" class="text-slate-400 hover:text-white p-1 rounded-md transition-colors">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Body -->
        <div class="px-6 py-5 space-y-4">
          <!-- Metadata Grid -->
          <div class="grid grid-cols-2 gap-3 text-xs">
            <div class="bg-slate-50 p-3 rounded-lg border border-slate-200">
              <span class="text-slate-400 font-medium block">Nama Dokumen</span>
              <span class="font-semibold text-slate-800 break-words">{{ citation.document_title }}</span>
            </div>
            <div class="bg-slate-50 p-3 rounded-lg border border-slate-200">
              <span class="text-slate-400 font-medium block">Halaman & Bagian</span>
              <span class="font-semibold text-slate-800">
                Hal. {{ citation.page_number }} <span class="text-slate-400">({{ citation.section_path || 'Bab Utama' }})</span>
              </span>
            </div>
          </div>

          <!-- Snippet Content -->
          <div>
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Kutipan Asli Dokumen:</label>
            <div class="p-4 rounded-xl bg-amber-50/70 border border-amber-200/80 text-slate-800 text-xs leading-relaxed font-mono whitespace-pre-wrap max-h-60 overflow-y-auto">
              {{ citation.snippet }}
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="bg-slate-50 px-6 py-3 border-t border-slate-200 flex justify-end">
          <button
            type="button"
            @click="emit('close')"
            class="px-4 py-2 rounded-lg bg-white border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition-colors shadow-sm"
          >
            Tutup
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
