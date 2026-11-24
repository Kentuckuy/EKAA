import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { fetchApi, setAccessToken, getAccessToken } from '@/services/api'

export interface UserProfile {
  id: string
  name: string
  email: string
  roles: string[]
  principals?: string[]
}

export const useAuthStore = defineStore('auth', () => {
  const user = ref<UserProfile | null>(null)
  const isAuthenticated = computed(() => !!user.value && !!getAccessToken())
  const isLoading = ref(false)
  const mustChangePassword = ref(false)
  const hasCheckedInitialSession = ref(false)
  const isExplicitlyLoggedOut = ref(false)

  const roles = computed(() => user.value?.roles || [])
  const isOrgAdmin = computed(() => roles.value.includes('org_admin'))
  const isKnowledgeManager = computed(() => roles.value.includes('knowledge_manager') || isOrgAdmin.value)
  const isDataAnalyst = computed(() => roles.value.includes('data_analyst') || isOrgAdmin.value)
  const isStandardUser = computed(() => roles.value.includes('standard_user'))

  async function login(email: string, password: string) {
    isLoading.value = true
    isExplicitlyLoggedOut.value = false
    hasCheckedInitialSession.value = true

    try {
      const res = await fetchApi<{
        access_token: string
        token_type: string
        expires_in: number
        must_change_password?: boolean
        user: UserProfile
      }>('/auth/login', {
        method: 'POST',
        body: { email, password }
      })

      setAccessToken(res.access_token)
      user.value = res.user
      mustChangePassword.value = !!res.must_change_password

      // Ambil data profil & principals lengkap
      await fetchMe()
      return res
    } finally {
      isLoading.value = false
    }
  }

  async function fetchMe() {
    try {
      const res = await fetchApi<UserProfile>('/me')
      user.value = res
      return res
    } catch (err) {
      user.value = null
      setAccessToken(null)
      throw err
    }
  }

  async function checkSession() {
    if (isExplicitlyLoggedOut.value || hasCheckedInitialSession.value) {
      return false
    }
    hasCheckedInitialSession.value = true

    try {
      // Coba refresh token diam-diam lewat cookie
      const res = await fetchApi<{ access_token: string }>('/auth/refresh', {
        method: 'POST'
      })
      if (!res || !res.access_token) {
        throw new Error('Sesi tidak valid')
      }
      setAccessToken(res.access_token)
      await fetchMe()
      return true
    } catch {
      user.value = null
      setAccessToken(null)
      return false
    }
  }

  async function logout() {
    isExplicitlyLoggedOut.value = true
    hasCheckedInitialSession.value = true

    try {
      if (getAccessToken()) {
        await fetchApi('/auth/logout', { method: 'POST' })
      }
    } catch {
      // Lanjutkan logout lokal
    } finally {
      user.value = null
      setAccessToken(null)
      mustChangePassword.value = false
    }
  }

  return {
    user,
    isAuthenticated,
    isLoading,
    mustChangePassword,
    hasCheckedInitialSession,
    isExplicitlyLoggedOut,
    roles,
    isOrgAdmin,
    isKnowledgeManager,
    isDataAnalyst,
    isStandardUser,
    login,
    fetchMe,
    checkSession,
    logout
  }
})
