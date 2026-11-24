<script setup lang="ts">
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { ApiError } from '@/services/api'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const email = ref('')
const password = ref('')
const errorMessage = ref('')
const isSubmitting = ref(false)

async function handleLogin() {
  if (!email.value || !password.value) {
    errorMessage.value = 'Harap isi email dan kata sandi.'
    return
  }

  errorMessage.value = ''
  isSubmitting.value = true

  try {
    await authStore.login(email.value, password.value)
    const redirect = (route.query.redirect as string) || '/'
    await router.replace(redirect)
  } catch (err: any) {
    if (err instanceof ApiError) {
      if (err.status === 429) {
        errorMessage.value = 'Terlalu banyak percobaan masuk (maksimal 5 kali/menit). Akun atau IP Anda dibatasi sementara untuk keamanan.'
      } else if (err.status === 401) {
        errorMessage.value = 'Email atau kata sandi tidak sesuai.'
      } else {
        errorMessage.value = err.message || 'Gagal masuk ke sistem.'
      }
    } else {
      errorMessage.value = 'Gagal terhubung ke server Laragon EKAA.'
    }
  } finally {
    isSubmitting.value = false
  }
}

function fillDemo(role: 'admin' | 'user') {
  errorMessage.value = ''
  if (role === 'admin') {
    email.value = 'admin@ekaa.internal'
    password.value = 'AdminPass123!'
  } else {
    email.value = 'user@ekaa.internal'
    password.value = 'UserPass123!'
  }
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-slate-900 px-4 sm:px-6 lg:px-8 relative overflow-hidden">
    <!-- Background subtle gradient -->
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-emerald-900/20 via-slate-900 to-slate-950 pointer-events-none"></div>

    <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-2xl shadow-2xl border border-slate-100 z-10">
      <!-- Header -->
      <div class="text-center">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-emerald-600 text-white font-extrabold text-2xl shadow-lg shadow-emerald-600/30 mb-4">
          E
        </div>
        <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">EKAA Platform</h2>
        <p class="mt-1 text-sm text-slate-500 font-medium">Enterprise Knowledge & Analytics Assistant</p>
      </div>

      <!-- Alert Pesan Error -->
      <div
        v-if="errorMessage"
        class="p-4 rounded-xl text-xs font-medium bg-rose-50 border border-rose-200 text-rose-700 flex items-start space-x-2.5"
      >
        <svg class="w-4 h-4 text-rose-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <span>{{ errorMessage }}</span>
      </div>

      <!-- Form Login -->
      <form class="mt-8 space-y-5" @submit.prevent="handleLogin">
        <div>
          <label for="email" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
            Alamat Email
          </label>
          <input
            id="email"
            v-model="email"
            type="email"
            autocomplete="email"
            required
            placeholder="nama@organisasi.id"
            class="block w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-slate-900 text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-shadow"
          />
        </div>

        <div>
          <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
            Kata Sandi
          </label>
          <input
            id="password"
            v-model="password"
            type="password"
            autocomplete="current-password"
            required
            placeholder="••••••••"
            class="block w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-slate-900 text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-shadow"
          />
        </div>

        <button
          type="submit"
          :disabled="isSubmitting"
          class="w-full flex justify-center items-center py-2.5 px-4 rounded-lg font-semibold text-sm text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 shadow-md shadow-emerald-600/20 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
        >
          <svg v-if="isSubmitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          {{ isSubmitting ? 'Memverifikasi...' : 'Masuk ke Platform' }}
        </button>
      </form>

      <!-- Demo Account Quick Select (Hanya Admin dan User Sesuai Arahan) -->
      <div class="pt-4 border-t border-slate-100">
        <p class="text-[11px] text-slate-500 font-medium mb-3 text-center">Pilih peran akun untuk masuk:</p>
        <div class="grid grid-cols-2 gap-2.5">
          <button
            type="button"
            @click="fillDemo('admin')"
            class="p-2.5 rounded-xl border border-slate-200 hover:border-emerald-500 hover:bg-emerald-50/50 text-left transition-all group"
          >
            <div class="flex items-center space-x-1.5">
              <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
              <span class="text-xs font-bold text-slate-800 group-hover:text-emerald-700">Akun Admin</span>
            </div>
            <p class="text-[10px] text-slate-500 mt-1">Akses Lengkap: Chat, CRUD Dokumen, Analitik Data, & Audit Log</p>
          </button>

          <button
            type="button"
            @click="fillDemo('user')"
            class="p-2.5 rounded-xl border border-slate-200 hover:border-emerald-500 hover:bg-emerald-50/50 text-left transition-all group"
          >
            <div class="flex items-center space-x-1.5">
              <span class="w-2 h-2 rounded-full bg-blue-500"></span>
              <span class="text-xs font-bold text-slate-800 group-hover:text-blue-700">Akun User</span>
            </div>
            <p class="text-[10px] text-slate-500 mt-1">Akses Khusus: Tanya-Jawab Dokumen Organisasi (Chat RAG)</p>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
