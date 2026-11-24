/**
 * Mock Fallback Provider untuk Mode Pengembangan
 * Aktif secara otomatis saat server backend (localhost:8000) belum dinyalakan,
 * sehingga pengguna tetap dapat menguji seluruh antarmuka web tanpa error.
 */

export function getMockResponse(endpoint: string, options: RequestInit = {}): any {
  const method = (options.method || 'GET').toUpperCase()

  // 1. Login
  if (endpoint.includes('/auth/login') && method === 'POST') {
    let email = 'user@ekaa.internal'
    try {
      if (typeof options.body === 'string') {
        const body = JSON.parse(options.body)
        email = body.email || email
      }
    } catch {}

    const isAdm = email.includes('admin')
    const isAnalyst = email.includes('analyst')

    const roles = isAdm ? ['org_admin'] : isAnalyst ? ['data_analyst'] : ['standard_user']
    const name = isAdm ? 'Administrator Organisasi' : isAnalyst ? 'Analis Data EKAA' : 'Staf Standar'

    return {
      access_token: 'mock-jwt-token-' + Date.now(),
      token_type: 'Bearer',
      expires_in: 900,
      must_change_password: false,
      user: {
        id: 'usr-' + (isAdm ? 'adm-01' : isAnalyst ? 'ana-01' : 'std-01'),
        name: name,
        email: email,
        roles: roles,
        principals: isAdm ? ['all', 'role:org_admin', 'unit:direksi'] : ['all', 'role:standard_user']
      }
    }
  }

  // 2. Refresh Token
  if (endpoint.includes('/auth/refresh') && method === 'POST') {
    return {
      access_token: 'mock-refreshed-jwt-' + Date.now(),
      expires_in: 900
    }
  }

  // 3. User Profile (/me)
  if (endpoint.includes('/me') && method === 'GET') {
    return {
      id: 'usr-current',
      name: 'Pengguna EKAA (Demo Mode)',
      email: 'user@ekaa.internal',
      roles: ['org_admin', 'knowledge_manager', 'data_analyst'],
      principals: ['all', 'role:org_admin', 'unit:teknologi']
    }
  }

  // 4. Dokumen
  if (endpoint.includes('/documents') && method === 'GET') {
    return [
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
  }

  // 5. Upload Dokumen
  if (endpoint.includes('/documents') && method === 'POST') {
    return {
      data: {
        id: 'doc-' + Date.now(),
        version_id: 'ver-' + Date.now(),
        status: 'pending'
      }
    }
  }

  // 6. Audit Logs
  if (endpoint.includes('/audit-logs')) {
    return [
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
      }
    ]
  }

  // Default fallback
  return { status: 'ok', mock: true }
}
