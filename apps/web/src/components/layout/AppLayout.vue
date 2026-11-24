<script setup lang="ts">
import { computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const user = computed(() => authStore.user)
const currentRouteName = computed(() => route.name)

async function handleLogout() {
  await authStore.logout()
  router.push({ name: 'login' })
}

function getRoleBadge(roles: string[] | undefined) {
  if (!roles || roles.length === 0) return 'Pengguna'
  if (roles.includes('org_admin')) return 'Org Admin'
  if (roles.includes('knowledge_manager')) return 'Knowledge Manager'
  if (roles.includes('data_analyst')) return 'Data Analyst'
  return 'Standard User'
}
</script>

<template>
  <div class="min-h-screen flex bg-slate-50 text-slate-900">
    <!-- Sidebar Navigasi -->
    <aside class="w-64 bg-slate-900 text-slate-200 flex flex-col border-r border-slate-800 select-none flex-shrink-0">
      <!-- Header Brand -->
      <div class="h-16 flex items-center px-6 border-b border-slate-800 space-x-3">
        <div class="w-8 h-8 rounded-lg bg-emerald-600 flex items-center justify-center font-bold text-white shadow-sm">
          E
        </div>
        <div>
          <h1 class="font-bold text-white tracking-wide text-sm">EKAA PLATFORM</h1>
          <p class="text-[10px] text-slate-400 font-medium">Enterprise Assistant</p>
        </div>
      </div>

      <!-- Menu Navigasi Utama -->
      <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
        <router-link
          to="/"
          class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors"
          :class="currentRouteName === 'chat' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-800/60'"
        >
          <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
          </svg>
          <span>Chat Knowledge (RAG)</span>
        </router-link>

        <router-link
          to="/analytics"
          class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors"
          :class="currentRouteName === 'analytics' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-800/60'"
        >
          <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
          </svg>
          <span>Analitik Deterministik</span>
        </router-link>

        <router-link
          to="/documents"
          class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors"
          :class="currentRouteName === 'documents' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-800/60'"
        >
          <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          <span>Dokumen & Izin</span>
        </router-link>

        <div v-if="authStore.isOrgAdmin" class="pt-4 pb-1">
          <p class="px-3 text-[10px] uppercase tracking-wider font-semibold text-slate-500">Administrasi</p>
        </div>

        <router-link
          v-if="authStore.isOrgAdmin"
          to="/audit"
          class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors"
          :class="currentRouteName === 'audit' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-400 hover:text-slate-100 hover:bg-slate-800/60'"
        >
          <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
          </svg>
          <span>Log Audit Imutabel</span>
        </router-link>
      </nav>

      <!-- Profil Pengguna & Keluar -->
      <div class="p-3 border-t border-slate-800 bg-slate-950/40">
        <div class="flex items-center justify-between p-2 rounded-lg bg-slate-900/80 border border-slate-800">
          <div class="flex items-center space-x-2.5 overflow-hidden">
            <div class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center text-xs font-bold text-slate-200 flex-shrink-0">
              {{ (user?.name || 'U').charAt(0).toUpperCase() }}
            </div>
            <div class="truncate">
              <p class="text-xs font-semibold text-slate-200 truncate">{{ user?.name || 'Pengguna' }}</p>
              <span class="inline-block px-1.5 py-0.5 rounded text-[9px] font-medium bg-emerald-950 text-emerald-400 border border-emerald-800">
                {{ getRoleBadge(user?.roles) }}
              </span>
            </div>
          </div>
          <button
            @click="handleLogout"
            title="Keluar"
            class="text-slate-400 hover:text-rose-400 p-1.5 rounded-md hover:bg-slate-800 transition-colors"
          >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
          </button>
        </div>
      </div>
    </aside>

    <!-- Konten Halaman -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
      <!-- Topbar Header -->
      <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6 z-10">
        <div class="flex items-center space-x-3">
          <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Modul:</span>
          <h2 class="text-base font-bold text-slate-800 capitalize">
            {{ currentRouteName === 'chat' ? 'Asisten Pengetahuan Dokumen (RAG)' : currentRouteName === 'analytics' ? 'Asisten Analitik Deterministik' : currentRouteName === 'documents' ? 'Penyimpanan & Izin Dokumen' : 'Audit Log Keamanan' }}
          </h2>
        </div>
        <div class="flex items-center space-x-3">
          <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
            Sistem Aktif
          </span>
        </div>
      </header>

      <!-- Body Slot -->
      <main class="flex-1 overflow-y-auto">
        <slot />
      </main>
    </div>
  </div>
</template>
