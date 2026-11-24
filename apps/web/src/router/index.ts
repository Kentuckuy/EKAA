import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/login',
      name: 'login',
      component: () => import('@/features/auth/LoginView.vue'),
      meta: { guestOnly: true }
    },
    {
      path: '/',
      name: 'chat',
      component: () => import('@/features/chat/ChatView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/analytics',
      name: 'analytics',
      component: () => import('@/features/analytics/AnalyticsView.vue'),
      meta: { requiresAuth: true, adminOnly: true }
    },
    {
      path: '/documents',
      name: 'documents',
      component: () => import('@/features/documents/DocumentsView.vue'),
      meta: { requiresAuth: true, adminOnly: true }
    },
    {
      path: '/audit',
      name: 'audit',
      component: () => import('@/features/audit/AuditLogsView.vue'),
      meta: { requiresAuth: true, adminOnly: true }
    },
    {
      path: '/:pathMatch(.*)*',
      redirect: '/'
    }
  ]
})

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()

  // Coba periksa sesi hanya pada inisialisasi awal dan bukan saat menuju rute tamu (login)
  if (!authStore.hasCheckedInitialSession && !to.meta.guestOnly) {
    await authStore.checkSession()
  }

  // Jika membutuhkan autentikasi tetapi belum login, arahkan ke login
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return next({ name: 'login', query: { redirect: to.fullPath } })
  }

  // Jika rute hanya untuk tamu (login) tetapi sudah login, arahkan ke halaman utama
  if (to.meta.guestOnly && authStore.isAuthenticated) {
    return next({ name: 'chat' })
  }

  // Jika rute khusus admin dan pengguna bukan admin (hanya user biasa), kembalikan ke chat
  if (to.meta.adminOnly && !authStore.isOrgAdmin) {
    return next({ name: 'chat' })
  }

  next()
})

export default router
