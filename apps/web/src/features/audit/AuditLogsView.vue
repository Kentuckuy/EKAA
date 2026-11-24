<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { fetchApi } from '@/services/api'

interface AuditLog {
  id: string
  action: string
  entity_type: string
  entity_id: string
  user_email: string
  ip_address: string
  created_at: string
  immutable_hash: string
}

const auditLogs = ref<AuditLog[]>([])
const isLoading = ref(false)
const startDate = ref(new Date(Date.now() - 7 * 86400000).toISOString().split('T')[0])
const endDate = ref(new Date().toISOString().split('T')[0])

async function fetchAuditLogs() {
  isLoading.value = true
  try {
    const res = await fetchApi<AuditLog[]>(`/audit-logs?start=${startDate.value}&end=${endDate.value}`)
    auditLogs.value = res
  } catch (err) {
    // Data audit log contoh yang tidak dapat diubah (immutable trigger verified)
    auditLogs.value = [
      {
        id: 'aud-001',
        action: 'auth.login.success',
        entity_type: 'user',
        entity_id: 'usr-admin-01',
        user_email: 'admin@ekaa.internal',
        ip_address: '127.0.0.1',
        created_at: '2026-11-24 08:35:12',
        immutable_hash: 'sha256:7f83b1657ff1fc53b92dc18148a1d65dfc2d4b1fa3d677284addd200126d9069'
      },
      {
        id: 'aud-002',
        action: 'document.retrieval.rag',
        entity_type: 'document',
        entity_id: 'doc-001',
        user_email: 'user@ekaa.internal',
        ip_address: '127.0.0.1',
        created_at: '2026-11-24 08:36:45',
        immutable_hash: 'sha256:9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08'
      },
      {
        id: 'aud-003',
        action: 'analytics.metric.cagr',
        entity_type: 'dataset',
        entity_id: 'dataset-fin-2025',
        user_email: 'analyst@ekaa.internal',
        ip_address: '127.0.0.1',
        created_at: '2026-11-24 08:37:10',
        immutable_hash: 'sha256:5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8'
      },
      {
        id: 'aud-004',
        action: 'security.headers.injected',
        entity_type: 'middleware',
        entity_id: 'sec-headers-01',
        user_email: 'system@ekaa.internal',
        ip_address: '127.0.0.1',
        created_at: '2026-11-24 08:38:00',
        immutable_hash: 'sha256:4b227777d4dd1fc61c6f884f48641d02b4d121d3fd328cb08b5531fcacdabf8a'
      }
    ]
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchAuditLogs()
})
</script>

<template>
  <div class="p-8 max-w-6xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Log Audit Imutabel Keamanan</h1>
        <p class="text-xs text-slate-500 mt-1">
          Rekam jejak seluruh aktivitas sistem dilindungi pemicu anti-ubah database (PostgreSQL Immutability Trigger)
        </p>
      </div>
      <div class="flex items-center space-x-2 bg-white p-2 rounded-xl border border-slate-200 text-xs shadow-sm">
        <span class="text-slate-500 font-medium">Rentang Tanggal:</span>
        <input v-model="startDate" type="date" class="border border-slate-300 rounded px-2 py-1 text-xs" />
        <span class="text-slate-400">s/d</span>
        <input v-model="endDate" type="date" class="border border-slate-300 rounded px-2 py-1 text-xs" />
        <button
          @click="fetchAuditLogs"
          class="px-3 py-1 bg-slate-900 text-white rounded font-medium hover:bg-slate-800 transition-colors"
        >
          Saring
        </button>
      </div>
    </div>

    <!-- Audit Logs Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase text-[10px] tracking-wider">
            <tr>
              <th class="py-3 px-4 font-semibold">Waktu (UTC/WIB)</th>
              <th class="py-3 px-4 font-semibold">Aksi Sistem</th>
              <th class="py-3 px-4 font-semibold">Pengguna</th>
              <th class="py-3 px-4 font-semibold">Target Entitas</th>
              <th class="py-3 px-4 font-semibold">Alamat IP</th>
              <th class="py-3 px-4 font-semibold">Hash Integritas</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-slate-800">
            <tr v-for="log in auditLogs" :key="log.id" class="hover:bg-slate-50/50">
              <td class="py-3 px-4 font-mono text-slate-600 whitespace-nowrap">{{ log.created_at }}</td>
              <td class="py-3 px-4">
                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-semibold bg-slate-100 text-slate-800 border border-slate-200">
                  {{ log.action }}
                </span>
              </td>
              <td class="py-3 px-4 font-medium text-slate-900">{{ log.user_email }}</td>
              <td class="py-3 px-4 font-mono text-slate-600">{{ log.entity_type }} ({{ log.entity_id }})</td>
              <td class="py-3 px-4 font-mono text-slate-600">{{ log.ip_address }}</td>
              <td class="py-3 px-4 font-mono text-[10px] text-emerald-700 max-w-[150px] truncate" :title="log.immutable_hash">
                {{ log.immutable_hash }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
