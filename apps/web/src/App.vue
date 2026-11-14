<script setup lang="ts">
import { ref, onMounted } from 'vue'

const appTitle = ref('EKAA — Enterprise Knowledge & Analytics Assistant')
const apiStatus = ref('Memeriksa...')

onMounted(async () => {
  try {
    const res = await fetch('/api/v1/health')
    if (res.ok) {
      const data = await res.json()
      apiStatus.value = `Tersambung (${data.service})`
    } else {
      apiStatus.value = 'Offline'
    }
  } catch (err) {
    apiStatus.value = 'Belum terhubung ke backend'
  }
})
</script>

<template>
  <main class="min-h-screen flex flex-col items-center justify-center p-6 bg-slate-100">
    <div class="max-w-md w-full bg-white rounded-xl shadow-md p-8 border border-slate-200">
      <h1 class="text-xl font-bold text-slate-800 mb-2">{{ appTitle }}</h1>
      <p class="text-sm text-slate-600 mb-4">Single-tenant Knowledge & Analytics Platform</p>
      <div class="flex items-center space-x-2 text-xs text-slate-500 bg-slate-50 p-3 rounded-lg border border-slate-100">
        <span class="font-semibold">Status Gateway:</span>
        <span class="text-emerald-600 font-medium">{{ apiStatus }}</span>
      </div>
    </div>
  </main>
</template>
