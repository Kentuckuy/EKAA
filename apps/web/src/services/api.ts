/**
 * EKAA Core API HTTP Client
 * Mematuhi Invarian §27.3: Access token disimpan HANYA di memori.
 * Refresh token berputar melalui HttpOnly cookie otomatis.
 * Dilengkapi graceful mock fallback untuk mode pengembangan saat backend offline.
 */

import { getMockResponse } from './mock'

export interface UniformErrorResponse {
  error: {
    code: string
    message: string
    details?: Record<string, any>
    correlation_id?: string
  }
}

export class ApiError extends Error {
  code: string
  details?: Record<string, any>
  status: number
  correlationId?: string

  constructor(status: number, code: string, message: string, details?: Record<string, any>, correlationId?: string) {
    super(message)
    this.name = 'ApiError'
    this.status = status
    this.code = code
    this.details = details
    this.correlationId = correlationId
  }
}

let inMemoryAccessToken: string | null = null
let isRefreshing = false
let refreshSubscribers: ((token: string) => void)[] = []

export function setAccessToken(token: string | null) {
  inMemoryAccessToken = token
}

export function getAccessToken(): string | null {
  return inMemoryAccessToken
}

function onRefreshed(token: string) {
  refreshSubscribers.forEach((callback) => callback(token))
  refreshSubscribers = []
}

function generateCorrelationId(): string {
  return 'req-' + Math.random().toString(36).substring(2, 10) + '-' + Date.now().toString(36)
}

export async function fetchApi<T = any>(endpoint: string, options: RequestInit = {}): Promise<T> {
  const correlationId = generateCorrelationId()
  const headers = new Headers(options.headers || {})

  if (!headers.has('Accept')) {
    headers.set('Accept', 'application/json')
  }

  headers.set('X-Correlation-Id', correlationId)

  if (inMemoryAccessToken && !headers.has('Authorization')) {
    headers.set('Authorization', `Bearer ${inMemoryAccessToken}`)
  }

  // Handle json payload automatically if object given
  if (options.body && typeof options.body === 'object' && !(options.body instanceof FormData)) {
    headers.set('Content-Type', 'application/json')
    options.body = JSON.stringify(options.body)
  }

  options.headers = headers
  options.credentials = 'include' // Kirim cookie HttpOnly untuk refresh token

  const url = endpoint.startsWith('http') ? endpoint : `/api/v1${endpoint.startsWith('/') ? '' : '/'}${endpoint}`

  let response: Response
  try {
    response = await fetch(url, options)
  } catch (networkError) {
    // Backend offline / jaringan terputus -> gunakan mock otomatis di dev
    console.warn(`[EKAA DEV] Backend port 8000 offline, menggunakan mock data untuk: ${endpoint}`)
    return getMockResponse(endpoint, options) as T
  }

  // Jika proxy Vite mengembalikan 502/503 (backend port 8000 belum menyala)
  if (response.status === 502 || response.status === 503) {
    console.warn(`[EKAA DEV] Proxy gateway mendeteksi backend belum siap (${response.status}), beralih ke mock fallback: ${endpoint}`)
    return getMockResponse(endpoint, options) as T
  }

  // Tangani 401 Unauthorized dan putar refresh token jika token kadaluarsa
  if (response.status === 401 && !endpoint.includes('/auth/login') && !endpoint.includes('/auth/refresh')) {
    if (!isRefreshing) {
      isRefreshing = true
      try {
        const refreshResponse = await fetch('/api/v1/auth/refresh', {
          method: 'POST',
          credentials: 'include',
          headers: {
            Accept: 'application/json',
            'X-Correlation-Id': generateCorrelationId()
          }
        })

        if (refreshResponse.ok) {
          const refreshData = await refreshResponse.json()
          setAccessToken(refreshData.access_token)
          isRefreshing = false
          onRefreshed(refreshData.access_token)
        } else {
          isRefreshing = false
          setAccessToken(null)
          window.dispatchEvent(new CustomEvent('auth:expired'))
          throw new ApiError(401, 'AUTH_EXPIRED', 'Sesi telah berakhir, silakan masuk kembali.')
        }
      } catch (err) {
        isRefreshing = false
        setAccessToken(null)
        window.dispatchEvent(new CustomEvent('auth:expired'))
        throw err
      }
    }

    return new Promise((resolve, reject) => {
      refreshSubscribers.push(async (newToken: string) => {
        try {
          const retryHeaders = new Headers(options.headers)
          retryHeaders.set('Authorization', `Bearer ${newToken}`)
          const retryRes = await fetch(url, { ...options, headers: retryHeaders })
          if (!retryRes.ok) {
            const errJson = await retryRes.json().catch(() => null)
            reject(new ApiError(retryRes.status, errJson?.error?.code || 'ERROR', errJson?.error?.message || 'Gagal memproses ulang permintaan'))
          } else {
            resolve(await retryRes.json())
          }
        } catch (retryErr) {
          reject(retryErr)
        }
      })
    })
  }

  if (!response.ok) {
    let errorData: UniformErrorResponse | null = null
    try {
      errorData = await response.json()
    } catch {
      // bukan JSON
    }

    const code = errorData?.error?.code || `HTTP_${response.status}`
    const message = errorData?.error?.message || response.statusText || 'Terjadi kesalahan sistem'
    const details = errorData?.error?.details
    throw new ApiError(response.status, code, message, details, correlationId)
  }

  // Handle empty 204 or responses with no content
  if (response.status === 204) {
    return {} as T
  }

  return await response.json()
}
