<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { fetchApi } from '@/services/api'
import { useAuthStore } from '@/stores/auth'

interface DocumentItem {
  id: string
  title: string
  file_name: string
  status: 'active' | 'pending' | 'indexing' | 'revoked'
  version: string
  effective_date: string
  principals: string[]
  created_at: string
}

const authStore = useAuthStore()
const documents = ref<DocumentItem[]>([])
const isLoading = ref(false)
const showUploadModal = ref(false)
const uploadTitle = ref('')
const selectedFile = ref<File | null>(null)
const isUploading = ref(false)
const uploadError = ref('')

async function fetchDocuments() {
  isLoading.value = true
  try {
    const res = await fetchApi<DocumentItem[]>('/documents')
    documents.value = res
  } catch (err) {
    // Data contoh default
    documents.value = [
      {
        id: 'doc-001',
        title: 'SOP Tata Kelola Keamanan Informasi v2.1',
        file_name: 'sop_keamanan_v2.1.pdf',
        status: 'active',
        version: 'v2.1',
        effective_date: '2025-01-10',
        principals: ['role:org_admin', 'role:knowledge_manager', 'unit:teknologi'],
        created_at: '2025-01-10'
      },
      {
        id: 'doc-002',
        title: 'Kebijakan Pengadaan Barang & Jasa 2025',
        file_name: 'kebijakan_pengadaan_2025.docx',
        status: 'active',
        version: 'v1.0',
        effective_date: '2025-02-01',
        principals: ['all'],
        created_at: '2025-02-01'
      },
      {
        id: 'doc-003',
        title: 'Laporan Keuangan Audit Tahunan 2024',
        file_name: 'laporan_audit_2024.pdf',
        status: 'active',
        version: 'v1.0',
        effective_date: '2025-01-20',
        principals: ['role:org_admin', 'role:data_analyst', 'unit:keuangan'],
        created_at: '2025-01-20'
      }
    ]
  } finally {
    isLoading.value = false
  }
}

function handleFileChange(event: Event) {
  const target = event.target as HTMLInputElement
  if (target.files && target.files.length > 0) {
    selectedFile.value = target.files[0]
  }
}

async function handleUpload() {
  if (!uploadTitle.value || !selectedFile.value) {
    uploadError.value = 'Judul dan berkas dokumen wajib diisi.'
    return
  }

  uploadError.value = ''
  isUploading.value = true

  const formData = new FormData()
  formData.append('title', uploadTitle.value)
  formData.append('file', selectedFile.value)

  try {
    await fetchApi('/documents', {
      method: 'POST',
      body: formData
    })
    showUploadModal.value = false
    uploadTitle.value = ''
    selectedFile.value = null
    await fetchDocuments()
  } catch (err: any) {
    uploadError.value = err.message || 'Gagal mengunggah berkas. Pastikan lolos pemindaian antivirus.'
  } finally {
    isUploading.value = false
  }
}

function toggleDocumentStatus(doc: DocumentItem) {
  if (doc.status === 'active') {
    doc.status = 'revoked'
  } else {
    doc.status = 'active'
  }
}

onMounted(() => {
  fetchDocuments()
})
</script>

<template>
  <div class="p-8 max-w-6xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Penyimpanan & Izin Dokumen</h1>
        <p class="text-xs text-slate-500 mt-1">Kelola dokumen pengetahuan resmi, status indeks vektor AI, dan otorisasi ACL</p>
      </div>
      <button
        v-if="authStore.isKnowledgeManager"
        @click="showUploadModal = true"
        class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white shadow-md shadow-emerald-600/20 transition-all"
      >
        <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Unggah Dokumen Baru
      </button>
    </div>

    <!-- Documents Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase text-[10px] tracking-wider">
            <tr>
              <th class="py-3 px-4 font-semibold">Judul Dokumen</th>
              <th class="py-3 px-4 font-semibold">Versi</th>
              <th class="py-3 px-4 font-semibold">Status Indeks AI</th>
              <th class="py-3 px-4 font-semibold">Tanggal Berlaku</th>
              <th class="py-3 px-4 font-semibold">Hak Akses (ACL Principals)</th>
              <th v-if="authStore.isKnowledgeManager" class="py-3 px-4 font-semibold text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-slate-800">
            <tr v-for="doc in documents" :key="doc.id" class="hover:bg-slate-50/50">
              <td class="py-3 px-4">
                <div class="font-semibold text-slate-900">{{ doc.title }}</div>
                <div class="text-[11px] text-slate-400 font-mono">{{ doc.file_name }}</div>
              </td>
              <td class="py-3 px-4 font-mono text-slate-600">{{ doc.version }}</td>
              <td class="py-3 px-4">
                <span
                  class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold"
                  :class="
                    doc.status === 'active'
                      ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                      : doc.status === 'indexing'
                      ? 'bg-blue-50 text-blue-700 border border-blue-200'
                      : 'bg-rose-50 text-rose-700 border border-rose-200'
                  "
                >
                  <span
                    class="w-1.5 h-1.5 rounded-full mr-1.5"
                    :class="doc.status === 'active' ? 'bg-emerald-500' : doc.status === 'indexing' ? 'bg-blue-500 animate-pulse' : 'bg-rose-500'"
                  ></span>
                  {{ doc.status === 'active' ? 'Terindeks Aktif' : doc.status === 'indexing' ? 'Sedang Diproses' : 'Dicabut' }}
                </span>
              </td>
              <td class="py-3 px-4 text-slate-600">{{ doc.effective_date }}</td>
              <td class="py-3 px-4">
                <div class="flex flex-wrap gap-1">
                  <span
                    v-for="p in doc.principals"
                    :key="p"
                    class="px-2 py-0.5 rounded text-[10px] font-mono bg-slate-100 text-slate-700 border border-slate-200"
                  >
                    {{ p }}
                  </span>
                </div>
              </td>
              <td v-if="authStore.isKnowledgeManager" class="py-3 px-4 text-right">
                <button
                  @click="toggleDocumentStatus(doc)"
                  :class="doc.status === 'active' ? 'text-rose-600 hover:text-rose-800' : 'text-emerald-600 hover:text-emerald-800'"
                  class="font-semibold text-[11px] underline"
                >
                  {{ doc.status === 'active' ? 'Cabut Izin' : 'Aktifkan' }}
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Upload Modal -->
    <div v-if="showUploadModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
      <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200">
        <h3 class="text-sm font-bold text-slate-900 mb-1">Unggah Dokumen Baru</h3>
        <p class="text-xs text-slate-500 mb-4">Berkas akan dipindai antivirus ClamAV (Fail-Closed) sebelum diekstraksi ke indeks AI.</p>

        <div v-if="uploadError" class="mb-4 p-3 rounded-lg text-xs bg-rose-50 border border-rose-200 text-rose-700">
          {{ uploadError }}
        </div>

        <form @submit.prevent="handleUpload" class="space-y-4">
          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Judul Dokumen Resmi</label>
            <input
              v-model="uploadTitle"
              type="text"
              required
              placeholder="Contoh: Pedoman Tata Tertib Karyawan 2025"
              class="w-full text-xs p-2.5 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Berkas (PDF, DOCX, XLSX - Maks 50MB)</label>
            <input
              type="file"
              required
              accept=".pdf,.docx,.xlsx,.csv"
              @change="handleFileChange"
              class="w-full text-xs p-2 rounded-lg border border-slate-300 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100"
            />
          </div>

          <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-[11px] text-amber-800 leading-relaxed">
            <strong>Proteksi Keamanan:</strong> Berkas yang mengandung malware, macro aktif, atau tidak lolos validasi magic-bytes akan langsung ditolak sistem.
          </div>

          <div class="flex justify-end space-x-2 pt-2">
            <button
              type="button"
              @click="showUploadModal = false"
              class="px-4 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded-lg"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="isUploading"
              class="px-5 py-2 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm disabled:opacity-50"
            >
              {{ isUploading ? 'Memindai & Mengunggah...' : 'Unggah Dokumen' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
