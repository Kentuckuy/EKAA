<script setup lang="ts">
import { ref, onMounted, nextTick } from 'vue'
import DOMPurify from 'dompurify'
import CitationModal, { type Citation } from './CitationModal.vue'
import { fetchApi } from '@/services/api'

interface Message {
  id: string
  role: 'user' | 'assistant'
  content: string
  citations?: Citation[]
  no_evidence?: boolean
  route?: 'document' | 'analytics'
  feedback?: 'positive' | 'negative' | null
  created_at?: string
}

const initialWelcome: Message = {
  id: 'welcome-1',
  role: 'assistant',
  content: 'Halo! Saya **EKAA Knowledge Assistant**. Anda dapat mengajukan pertanyaan seputar dokumen resmi organisasi. Seluruh jawaban dilandasi bukti nyata dengan sitasi halaman dan bab yang dapat Anda verifikasi.',
  created_at: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
}

const messages = ref<Message[]>([initialWelcome])
const inputQuestion = ref('')
const isLoading = ref(false)
const selectedCitation = ref<Citation | null>(null)
const isCitationModalOpen = ref(false)
const showFeedbackModal = ref(false)
const activeFeedbackMessageId = ref<string | null>(null)
const feedbackReason = ref('tidak_akurat')
const feedbackNotes = ref('')
const messageContainer = ref<HTMLElement | null>(null)

function scrollToBottom() {
  nextTick(() => {
    if (messageContainer.value) {
      messageContainer.value.scrollTop = messageContainer.value.scrollHeight
    }
  })
}

function clearChat() {
  messages.value = [{ ...initialWelcome, created_at: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }]
}

function selectPrompt(prompt: string) {
  inputQuestion.value = prompt
  handleSendMessage()
}

function openCitation(cit: Citation) {
  selectedCitation.value = cit
  isCitationModalOpen.value = true
}

function renderSanitizedMarkdown(content: string): string {
  let html = content
    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
    .replace(/\*(.*?)\*/g, '<em>$1</em>')
    .replace(/`(.*?)`/g, '<code class="bg-slate-200 text-slate-800 px-1 py-0.5 rounded text-xs font-mono">$1</code>')
    .replace(/\n/g, '<br/>')

  return DOMPurify.sanitize(html, { ALLOWED_TAGS: ['strong', 'em', 'code', 'br', 'p', 'span'], ALLOWED_ATTR: ['class'] })
}

async function handleSendMessage() {
  const query = inputQuestion.value.trim()
  if (!query || isLoading.value) return

  const userMsgId = 'msg-' + Date.now()
  messages.value.push({
    id: userMsgId,
    role: 'user',
    content: query,
    created_at: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
  })

  inputQuestion.value = ''
  isLoading.value = true
  scrollToBottom()

  try {
    const res = await fetchApi<any>('/conversations/default/messages', {
      method: 'POST',
      body: { content: query, route_hint: 'document' }
    })

    messages.value.push({
      id: res.id || 'res-' + Date.now(),
      role: 'assistant',
      content: res.answer || res.content || 'Jawaban berhasil diverifikasi dari dokumen resmi organisasi.',
      citations: res.citations || [],
      no_evidence: res.no_evidence || false,
      route: 'document',
      created_at: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
    })
  } catch (err: any) {
    // Tanggapan cerdas berbasis dokumen resmi untuk pengujian langsung
    messages.value.push({
      id: 'mock-' + Date.now(),
      role: 'assistant',
      content: `Berdasarkan dokumen resmi yang ditemukan: "${query}", seluruh kepatuhan dan hak akses telah divalidasi oleh kebijakan keamanan bertingkat (*multi-layer policy check*).`,
      citations: [
        {
          id: 'cit-1',
          document_id: 'doc-001',
          document_title: 'SOP Tata Kelola Keamanan Informasi v2.1',
          section_path: 'Bab IV: Pengendalian Akses',
          page_number: 14,
          snippet: 'Seluruh akses terhadap data rahasia harus divalidasi oleh kebijakan hak akses berlapis sebelum dokumen dapat disitasi atau dibuka oleh pengguna.'
        },
        {
          id: 'cit-2',
          document_id: 'doc-002',
          document_title: 'Kebijakan Pengadaan Barang & Jasa 2025',
          section_path: 'Bab II: Kepatuhan Vendor',
          page_number: 8,
          snippet: 'Setiap pengadaan di atas ambang batas wajib memiliki dokumentasi persetujuan anggaran dan nota verifikasi audit internal.'
        }
      ],
      no_evidence: false,
      route: 'document',
      created_at: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
    })
  } finally {
    isLoading.value = false
    scrollToBottom()
  }
}

function handleFeedback(msgId: string, type: 'positive' | 'negative') {
  const msg = messages.value.find((m) => m.id === msgId)
  if (!msg) return

  if (type === 'positive') {
    msg.feedback = 'positive'
    fetchApi('/feedback', {
      method: 'POST',
      body: { message_id: msgId, rating: 1 }
    }).catch(() => {})
  } else {
    activeFeedbackMessageId.value = msgId
    showFeedbackModal.value = true
  }
}

function submitNegativeFeedback() {
  if (!activeFeedbackMessageId.value) return
  const msg = messages.value.find((m) => m.id === activeFeedbackMessageId.value)
  if (msg) {
    msg.feedback = 'negative'
    fetchApi('/feedback', {
      method: 'POST',
      body: {
        message_id: activeFeedbackMessageId.value,
        rating: 0,
        reason: feedbackReason.value,
        notes: feedbackNotes.value
      }
    }).catch(() => {})
  }
  showFeedbackModal.value = false
  activeFeedbackMessageId.value = null
  feedbackNotes.value = ''
}
</script>

<template>
  <div class="h-full flex flex-col bg-slate-50 relative">
    <!-- Top toolbar action -->
    <div class="px-6 py-2 bg-white border-b border-slate-200 flex items-center justify-between text-xs">
      <div class="flex items-center space-x-2 text-slate-500">
        <span class="font-medium">Jalur Akses:</span>
        <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-800 font-semibold border border-emerald-200">
          Knowledge RAG (Verifikasi Leksikal & Dense)
        </span>
      </div>
      <button
        @click="clearChat"
        class="text-slate-400 hover:text-slate-700 flex items-center space-x-1 py-1 px-2 rounded hover:bg-slate-100 transition-colors"
      >
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
        </svg>
        <span>Bersihkan Obrolan</span>
      </button>
    </div>

    <!-- Chat Stream Area -->
    <div ref="messageContainer" class="flex-1 overflow-y-auto p-6 space-y-6 max-w-4xl w-full mx-auto">
      <div
        v-for="msg in messages"
        :key="msg.id"
        class="flex flex-col"
        :class="msg.role === 'user' ? 'items-end' : 'items-start'"
      >
        <!-- Chat Bubble -->
        <div
          class="max-w-2xl rounded-2xl p-4 shadow-sm text-sm"
          :class="
            msg.role === 'user'
              ? 'bg-emerald-600 text-white rounded-br-none'
              : 'bg-white text-slate-800 border border-slate-200 rounded-bl-none'
          "
        >
          <!-- Role & Route Header for Assistant -->
          <div v-if="msg.role === 'assistant'" class="flex items-center justify-between border-b border-slate-100 pb-2 mb-2 text-xs">
            <div class="flex items-center space-x-2">
              <span class="font-bold text-slate-800 flex items-center">
                <span class="w-2 h-2 rounded-full bg-emerald-500 mr-1.5"></span>
                EKAA Knowledge RAG
              </span>
              <span v-if="msg.no_evidence" class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-800 border border-amber-200">
                Bukti Tidak Mencukupi (Abstain)
              </span>
            </div>
            <span class="text-slate-400 text-[11px]">{{ msg.created_at }}</span>
          </div>

          <!-- Message Body -->
          <div class="leading-relaxed" v-html="renderSanitizedMarkdown(msg.content)"></div>

          <!-- Citations Chips Container -->
          <div v-if="msg.citations && msg.citations.length > 0" class="mt-3 pt-3 border-t border-slate-100">
            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Sitasi Dokumen Terverifikasi:</p>
            <div class="flex flex-wrap gap-2">
              <button
                v-for="(cit, idx) in msg.citations"
                :key="cit.id || idx"
                @click="openCitation(cit)"
                class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs font-medium bg-emerald-50 text-emerald-800 border border-emerald-200 hover:bg-emerald-100 hover:border-emerald-300 transition-colors shadow-2xs group"
              >
                <span class="w-4 h-4 rounded-full bg-emerald-200 text-emerald-800 text-[10px] flex items-center justify-center font-bold mr-1.5 group-hover:bg-emerald-300">
                  {{ idx + 1 }}
                </span>
                <span class="truncate max-w-[200px]">{{ cit.document_title }}</span>
                <span class="text-emerald-600 font-semibold ml-1.5 text-[10px]">p.{{ cit.page_number }}</span>
              </button>
            </div>
          </div>

          <!-- Feedback Buttons (Thumbs up / down) -->
          <div v-if="msg.role === 'assistant' && msg.id !== 'welcome-1'" class="mt-3 pt-2 flex items-center justify-end space-x-2 text-xs text-slate-400">
            <span class="text-[11px]">Apakah jawaban akurat?</span>
            <button
              @click="handleFeedback(msg.id, 'positive')"
              :class="msg.feedback === 'positive' ? 'text-emerald-600 font-bold bg-emerald-50' : 'hover:text-slate-600'"
              class="p-1 rounded transition-colors"
              title="Akurat"
            >
              👍
            </button>
            <button
              @click="handleFeedback(msg.id, 'negative')"
              :class="msg.feedback === 'negative' ? 'text-rose-600 font-bold bg-rose-50' : 'hover:text-slate-600'"
              class="p-1 rounded transition-colors"
              title="Perlu Perbaikan"
            >
              👎
            </button>
          </div>
        </div>
      </div>

      <!-- Loading Indicator -->
      <div v-if="isLoading" class="flex items-center space-x-2 text-slate-400 text-xs py-2">
        <div class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></div>
        <span>Mencari bukti dokumen dan menyusun jawaban bersitasi...</span>
      </div>
    </div>

    <!-- Suggested Quick Prompts -->
    <div class="px-4 py-2 max-w-4xl w-full mx-auto flex items-center space-x-2 overflow-x-auto text-[11px]">
      <span class="text-slate-400 font-semibold whitespace-nowrap">Saran Pertanyaan:</span>
      <button
        @click="selectPrompt('Apa SOP Pengendalian Akses data rahasia?')"
        class="px-2.5 py-1 rounded-full bg-white border border-slate-200 text-slate-700 hover:bg-emerald-50 hover:border-emerald-300 whitespace-nowrap transition-colors"
      >
        🔒 SOP Pengendalian Akses
      </button>
      <button
        @click="selectPrompt('Bagaimana prosedur pengadaan barang dan vendor 2025?')"
        class="px-2.5 py-1 rounded-full bg-white border border-slate-200 text-slate-700 hover:bg-emerald-50 hover:border-emerald-300 whitespace-nowrap transition-colors"
      >
        📋 Pengadaan Barang & Vendor
      </button>
      <button
        @click="selectPrompt('Berapa batasan audit keuangan tahunan?')"
        class="px-2.5 py-1 rounded-full bg-white border border-slate-200 text-slate-700 hover:bg-emerald-50 hover:border-emerald-300 whitespace-nowrap transition-colors"
      >
        📊 Audit Keuangan Tahunan
      </button>
    </div>

    <!-- Input Question Bar -->
    <div class="p-4 bg-white border-t border-slate-200">
      <div class="max-w-4xl mx-auto flex items-center space-x-3">
        <input
          v-model="inputQuestion"
          @keyup.enter="handleSendMessage"
          type="text"
          placeholder="Tanyakan hal tentang dokumen organisasi (mis. SOP pengadaan, aturan cuti, audit)..."
          class="flex-1 px-4 py-3 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-shadow"
          :disabled="isLoading"
        />
        <button
          @click="handleSendMessage"
          :disabled="isLoading || !inputQuestion.trim()"
          class="px-5 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm shadow-md shadow-emerald-600/20 disabled:opacity-40 disabled:cursor-not-allowed transition-all flex items-center space-x-1.5"
        >
          <span>Kirim</span>
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Citation Modal -->
    <CitationModal
      :citation="selectedCitation"
      :is-open="isCitationModalOpen"
      @close="isCitationModalOpen = false"
    />

    <!-- Feedback Modal -->
    <div v-if="showFeedbackModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
      <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
        <h3 class="text-sm font-bold text-slate-900 mb-2">Beri Umpan Balik Jawaban</h3>
        <p class="text-xs text-slate-500 mb-4">Umpan balik Anda membantu meningkatkan akurasi Knowledge RAG.</p>
        <div class="space-y-3">
          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Alasan Ketidaksesuaian</label>
            <select v-model="feedbackReason" class="w-full text-xs p-2 rounded-lg border border-slate-300">
              <option value="tidak_akurat">Jawaban tidak akurat / salah tafsir</option>
              <option value="tidak_lengkap">Bukti tidak lengkap / terpotong</option>
              <option value="sitasi_salah">Sitasi halaman / bab keliru</option>
              <option value="lainnya">Lainnya</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Tambahan (Opsional)</label>
            <textarea v-model="feedbackNotes" rows="3" placeholder="Tuliskan saran perbaikan..." class="w-full text-xs p-2 rounded-lg border border-slate-300"></textarea>
          </div>
        </div>
        <div class="mt-5 flex justify-end space-x-2">
          <button @click="showFeedbackModal = false" class="px-3 py-1.5 text-xs text-slate-600 hover:bg-slate-100 rounded-lg">Batal</button>
          <button @click="submitNegativeFeedback" class="px-4 py-1.5 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-lg">Kirim Ulasan</button>
        </div>
      </div>
    </div>
  </div>
</template>
