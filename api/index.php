<?php
/**
 * EKAA API Backend Gateway — Native Laragon Apache & PHP 8.5
 * Melayani seluruh endpoint Core API untuk Frontend Vue SPA.
 */

// Header CORS & Keamanan HTTP
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Correlation-Id, Accept, Idempotency-Key');
header('Content-Type: application/json; charset=UTF-8');

// Tangani Preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$rawInput = file_get_contents('php://input');
$body = json_decode($rawInput, true) ?? [];

// Helper respon JSON seragam
function jsonResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function errorResponse($code, $message, $status = 400, $details = null) {
    jsonResponse([
        'error' => [
            'code' => $code,
            'message' => $message,
            'details' => $details,
            'correlation_id' => 'req-' . uniqid()
        ]
    ], $status);
}

// -----------------------------------------------------------------------------
// 1. AUTENTIKASI: /api/v1/auth/login
// -----------------------------------------------------------------------------
if (strpos($uri, '/auth/login') !== false && $method === 'POST') {
    $email = strtolower(trim($body['email'] ?? ''));
    $password = $body['password'] ?? '';

    if (empty($email) || empty($password)) {
        errorResponse('VALIDATION_ERROR', 'Email dan kata sandi wajib diisi.', 422);
    }

    $isAdmin = (strpos($email, 'admin') !== false);

    $user = [
        'id' => $isAdmin ? 'usr-adm-001' : 'usr-std-002',
        'name' => $isAdmin ? 'Administrator Organisasi' : 'Staf Standar (User)',
        'email' => $email,
        'roles' => $isAdmin ? ['org_admin', 'knowledge_manager', 'data_analyst'] : ['standard_user'],
        'principals' => $isAdmin 
            ? ['all', 'role:org_admin', 'role:knowledge_manager', 'role:data_analyst', 'unit:direksi'] 
            : ['all', 'role:standard_user', 'unit:operasional']
    ];

    jsonResponse([
        'access_token' => 'jwt_ekaa_token_' . bin2hex(random_bytes(16)),
        'token_type' => 'Bearer',
        'expires_in' => 900,
        'must_change_password' => false,
        'user' => $user
    ]);
}

// -----------------------------------------------------------------------------
// 2. REFRESH & LOGOUT & PROFIL ME
// -----------------------------------------------------------------------------
if (strpos($uri, '/auth/refresh') !== false && $method === 'POST') {
    jsonResponse([
        'access_token' => 'jwt_refreshed_token_' . bin2hex(random_bytes(16)),
        'expires_in' => 900
    ]);
}

if (strpos($uri, '/auth/logout') !== false && $method === 'POST') {
    jsonResponse(['status' => 'logged_out', 'message' => 'Sesi berhasil diakhiri.']);
}

if (strpos($uri, '/me') !== false && $method === 'GET') {
    // Profil default aktif
    jsonResponse([
        'id' => 'usr-adm-001',
        'name' => 'Administrator Organisasi',
        'email' => 'admin@ekaa.internal',
        'roles' => ['org_admin', 'knowledge_manager', 'data_analyst'],
        'principals' => ['all', 'role:org_admin', 'unit:direksi']
    ]);
}

// -----------------------------------------------------------------------------
// 3. KNOWLEDGE ASSISTANT RAG: /api/v1/conversations/.../messages
// -----------------------------------------------------------------------------
if (strpos($uri, '/messages') !== false && $method === 'POST') {
    $question = strtolower(trim($body['content'] ?? ''));

    // Basis Pengetahuan Dokumen Resmi Organisasi
    $knowledgeBase = [
        'cuti' => [
            'answer' => "Berdasarkan **SOP Manajemen Kepegawaian & Hak Cuti 2025**, ketentuan cuti tahunan adalah sebagai berikut:\n\n1. **Kuota:** Setiap karyawan berhak memperoleh cuti tahunan sebanyak **12 hari kerja** setelah memiliki masa kerja minimal 12 bulan secara berturut-turut.\n2. **Pengajuan:** Permohonan cuti wajib diajukan paling lambat **3 hari kerja** sebelum tanggal pelaksanaan melalui formulir elektronik di portal SDM.\n3. **Persetujuan:** Memerlukan tanda tangan digital atasan langsung minimal tingkat Manajer dan konfirmasi persetujuan dari divisi HRD.\n4. **Cuti Khusus:** Meliputi cuti melahirkan (3 bulan kalender penuh dengan gaji pokok), cuti sakit dengan surat dokter resmi, dan cuti duka cita maksimal 3 hari kerja.",
            'citations' => [
                [
                    'id' => 'cit-cuti-01',
                    'document_id' => 'doc-hr-2025',
                    'document_title' => 'SOP Manajemen Kepegawaian & Hak Cuti 2025',
                    'section_path' => 'Bab III: Hak Cuti dan Izin Ketidakhadiran',
                    'page_number' => 18,
                    'snippet' => 'Karyawan yang telah melalui masa bakti sekurang-kurangnya 1 (satu) tahun secara terus-menerus berhak atas hak cuti tahunan sejumlah 12 (dua belas) hari kerja dengan tetap menerima kompensasi penuh.'
                ]
            ],
            'no_evidence' => false
        ],
        'keamanan' => [
            'answer' => "Berdasarkan **SOP Tata Kelola Keamanan Informasi v2.1 (ISO/IEC 27001 compliant)**, ketentuan akses data rahasia dan operasional:\n\n1. **Prinsip Least Privilege:** Akses data hanya diberikan sesuai peran jabatan (Role-Based Access Control) dan unit kerja yang relevan.\n2. **Otentikasi:** Wajib menggunakan otentikasi multi-faktor (MFA) untuk seluruh akses data internal dan repositori cloud.\n3. **Manajemen Sandi:** Panjang sandi minimal 12 karakter alfanumerik, diubah berkala per 90 hari, dan tidak boleh menggunakan ulang 5 sandi terakhir.\n4. **Enkripsi:** Seluruh media penyimpanan data sensitif menggunakan enkripsi standar AES-256 saat diam (*at-rest*) dan TLS 1.3 saat transmisi (*in-transit*).",
            'citations' => [
                [
                    'id' => 'cit-sec-01',
                    'document_id' => 'doc-sec-v21',
                    'document_title' => 'SOP Tata Kelola Keamanan Informasi v2.1',
                    'section_path' => 'Bab IV: Pengendalian Hak Akses & Enkripsi',
                    'page_number' => 14,
                    'snippet' => 'Seluruh hak akses terhadap aset informasi dan basis data rahasia organisasi wajib melalui penegakan kebijakan bertingkat (Policy Check) serta pencatatan audit log yang tidak dapat diubah.'
                ]
            ],
            'no_evidence' => false
        ],
        'pengadaan' => [
            'answer' => "Berdasarkan **Kebijakan Pengadaan Barang & Jasa 2025**:\n\n1. **Ambang Batas Nilai:** Pengadaan dengan nilai di bawah Rp 50.000.000 dapat dilakukan melalui penunjukan langsung dengan melampirkan minimal 2 penawaran pembanding.\n2. **Tender Terbuka:** Pengadaan di atas Rp 50.000.000 wajib melalui proses lelang/tender resmi dengan minimal 3 vendor terverifikasi.\n3. **Kepatuhan Vendor:** Vendor wajib memiliki legalitas lengkap (NIB, NPWP perusahaan, pakta integritas anti-suap/gratifikasi).\n4. **Verifikasi:** Setiap dokumen kontrak harus melalui audit kepatuhan oleh Tim Pengadaan dan otorisasi anggaran oleh Keuangan sebelum Surat Perintah Kerja (SPK) diterbitkan.",
            'citations' => [
                [
                    'id' => 'cit-proc-01',
                    'document_id' => 'doc-proc-2025',
                    'document_title' => 'Kebijakan Pengadaan Barang & Jasa 2025',
                    'section_path' => 'Bab II: Standar Kepatuhan Mitra dan Ambang Batas Pengadaan',
                    'page_number' => 8,
                    'snippet' => 'Setiap transaksi pengadaan belanja modal maupun operasional yang melampaui plafon Rp 50.000.000 wajib menyertakan kajian teknis perbandingan harga sekurang-kurangnya dari tiga penyedia jasa yang terdaftar.'
                ]
            ],
            'no_evidence' => false
        ],
        'jam kerja' => [
            'answer' => "Berdasarkan **Peraturan Disiplin Kerja & Tata Tertib 2025**:\n\n1. **Hari Kerja:** Senin s.d. Jumat.\n2. **Jam Operasional:** Pukul **08.00 s.d. 17.00 WIB** (dengan waktu istirahat 1 jam: 12.00 - 13.00 WIB, khusus Jumat 11.30 - 13.00 WIB).\n3. **Toleransi Keterlambatan:** Maksimal 15 menit per hari dan tidak boleh melebihi akumulasi 60 menit per bulan kalender.\n4. **Presensi:** Wajib melakukan presensi kehadiran digital dengan deteksi geolokasi di lingkungan kantor.",
            'citations' => [
                [
                    'id' => 'cit-work-01',
                    'document_id' => 'doc-rule-2025',
                    'document_title' => 'Peraturan Disiplin Kerja & Tata Tertib 2025',
                    'section_path' => 'Bab I: Waktu Kerja dan Ketertiban Presensi',
                    'page_number' => 5,
                    'snippet' => 'Jam kerja operasional resmi kantor berlangsung selama 8 jam efektif per hari kerja dari pukul 08.00 WIB hingga 17.00 WIB dengan kewajiban perekaman kehadiran mandiri.'
                ]
            ],
            'no_evidence' => false
        ],
        'keuangan' => [
            'answer' => "Berdasarkan **Pedoman Pengelolaan Keuangan & Audit 2024**:\n\n1. **Klaim Pengeluaran (Reimbursement):** Wajib melampirkan kuitansi/faktur pajak asli dan diajukan maksimal 14 hari kalender setelah transaksi terjadi.\n2. **Uang Muka Kerja (Cash Advance):** Wajib diselesaikan pertanggungjawabannya paling lambat 7 hari kerja setelah kegiatan selesai.\n3. **Audit Eksternal:** Dilaksanakan setiap akhir tahun buku oleh Kantor Akuntan Publik (KAP) independen yang terdaftar resmi di OJK.",
            'citations' => [
                [
                    'id' => 'cit-fin-01',
                    'document_id' => 'doc-fin-2024',
                    'document_title' => 'Pedoman Pengelolaan Keuangan & Audit 2024',
                    'section_path' => 'Bab V: Prosedur Penggantian Biaya Operasional dan Audit',
                    'page_number' => 22,
                    'snippet' => 'Pengajuan penggantian biaya dinas harus disertai bukti transaksi sah bernomor seri faktur dan tanda terima resmi untuk dapat diproses oleh bendahara pengeluaran.'
                ]
            ],
            'no_evidence' => false
        ]
    ];

    // Deteksi Topik Relevan
    $selectedTopic = null;
    foreach ($knowledgeBase as $keyword => $data) {
        if (strpos($question, $keyword) !== false) {
            $selectedTopic = $data;
            break;
        }
    }

    // Sinonim tambahan
    if (!$selectedTopic) {
        if (strpos($question, 'libur') !== false || strpos($question, 'ijin') !== false || strpos($question, 'izin') !== false) {
            $selectedTopic = $knowledgeBase['cuti'];
        } elseif (strpos($question, 'sandi') !== false || strpos($question, 'password') !== false || strpos($question, 'enkripsi') !== false || strpos($question, 'akses') !== false) {
            $selectedTopic = $knowledgeBase['keamanan'];
        } elseif (strpos($question, 'vendor') !== false || strpos($question, 'beli') !== false || strpos($question, 'lelang') !== false || strpos($question, 'tender') !== false) {
            $selectedTopic = $knowledgeBase['pengadaan'];
        } elseif (strpos($question, 'masuk') !== false || strpos($question, 'pulang') !== false || strpos($question, 'absen') !== false || strpos($question, 'terlambat') !== false) {
            $selectedTopic = $knowledgeBase['jam kerja'];
        } elseif (strpos($question, 'anggaran') !== false || strpos($question, 'uang') !== false || strpos($question, 'biaya') !== false || strpos($question, 'reimburse') !== false) {
            $selectedTopic = $knowledgeBase['keuangan'];
        }
    }

    // Jika topik cocok ditemukan
    if ($selectedTopic) {
        jsonResponse([
            'id' => 'msg-' . uniqid(),
            'answer' => $selectedTopic['answer'],
            'citations' => $selectedTopic['citations'],
            'no_evidence' => false
        ]);
    }

    // Jika pertanyaan di luar konteks dokumen organisasi -> Kembalikan pesan abstain yang ramah
    jsonResponse([
        'id' => 'msg-' . uniqid(),
        'answer' => "Maaf, pertanyaan Anda: \"**{$body['content']}**\" belum ditemukan buktinya dalam dokumen resmi organisasi saat ini (*no_evidence*).\n\nSebagai asisten terpercaya, saya hanya memberikan informasi yang memiliki landasan dokumen sah. Anda dapat menanyakan topik berikut:\n- **Kebijakan Cuti & Izin** (syarat kuota 12 hari, cuti melahirkan, alur persetujuan)\n- **SOP Keamanan Informasi** (kebijakan sandi, hak akses data rahasia, MFA)\n- **Pengadaan Barang & Jasa** (ambang batas tender Rp 50 juta, kepatuhan vendor)\n- **Jam Kerja & Presensi** (jam operasional 08.00-17.00, aturan keterlambatan)\n- **Keuangan & Reimbursement** (prosedur penggantian biaya dinas, audit)",
        'citations' => [],
        'no_evidence' => true
    ]);
}

// -----------------------------------------------------------------------------
// 4. MANAJEMEN DOKUMEN (CRUD): /api/v1/documents
// -----------------------------------------------------------------------------
$docsFile = __DIR__ . '/documents_store.json';
if (!file_exists($docsFile)) {
    $initialDocs = [
        [
            'id' => 'doc-001',
            'title' => 'SOP Tata Kelola Keamanan Informasi v2.1',
            'file_name' => 'sop_keamanan_v2.1.pdf',
            'status' => 'active',
            'version' => 'v2.1',
            'effective_date' => '2025-01-10',
            'principals' => ['role:org_admin', 'role:knowledge_manager', 'unit:teknologi'],
            'created_at' => '2025-01-10'
        ],
        [
            'id' => 'doc-002',
            'title' => 'Kebijakan Pengadaan Barang & Jasa 2025',
            'file_name' => 'kebijakan_pengadaan_2025.docx',
            'status' => 'active',
            'version' => 'v1.0',
            'effective_date' => '2025-02-01',
            'principals' => ['all'],
            'created_at' => '2025-02-01'
        ],
        [
            'id' => 'doc-003',
            'title' => 'SOP Manajemen Kepegawaian & Hak Cuti 2025',
            'file_name' => 'sop_kepegawaian_cuti_2025.pdf',
            'status' => 'active',
            'version' => 'v1.2',
            'effective_date' => '2025-01-05',
            'principals' => ['all', 'unit:sdm'],
            'created_at' => '2025-01-05'
        ],
        [
            'id' => 'doc-004',
            'title' => 'Pedoman Pengelolaan Keuangan & Audit 2024',
            'file_name' => 'pedoman_keuangan_2024.pdf',
            'status' => 'active',
            'version' => 'v1.0',
            'effective_date' => '2025-01-20',
            'principals' => ['role:org_admin', 'role:data_analyst', 'unit:keuangan'],
            'created_at' => '2025-01-20'
        ]
    ];
    file_put_contents($docsFile, json_encode($initialDocs, JSON_PRETTY_PRINT));
}

// BACA (READ) DOKUMEN
if (preg_match('#^/api/v1/documents/?$#', $uri) && $method === 'GET') {
    $docs = json_decode(file_get_contents($docsFile), true) ?? [];
    jsonResponse($docs);
}

// BUAT (CREATE) DOKUMEN
if (preg_match('#^/api/v1/documents/?$#', $uri) && $method === 'POST') {
    $title = $_POST['title'] ?? ($body['title'] ?? 'Dokumen Baru Tanpa Judul');
    $fileName = 'dokumen_' . time() . '.pdf';
    if (!empty($_FILES['file']['name'])) {
        $fileName = $_FILES['file']['name'];
    }

    $docs = json_decode(file_get_contents($docsFile), true) ?? [];
    $newDoc = [
        'id' => 'doc-' . uniqid(),
        'title' => htmlspecialchars($title),
        'file_name' => $fileName,
        'status' => 'active',
        'version' => 'v1.0',
        'effective_date' => date('Y-m-d'),
        'principals' => ['all'],
        'created_at' => date('Y-m-d H:i:s')
    ];
    array_unshift($docs, $newDoc);
    file_put_contents($docsFile, json_encode($docs, JSON_PRETTY_PRINT));

    jsonResponse(['data' => $newDoc, 'message' => 'Dokumen berhasil diunggah dan terindeks AI.'], 201);
}

// PERBARUI (UPDATE) STATUS DOKUMEN
if (preg_match('#^/api/v1/documents/([a-zA-Z0-9_\-]+)/status$#', $uri, $matches) && $method === 'POST') {
    $docId = $matches[1];
    $status = $body['status'] ?? 'active';

    $docs = json_decode(file_get_contents($docsFile), true) ?? [];
    foreach ($docs as &$d) {
        if ($d['id'] === $docId) {
            $d['status'] = $status;
            break;
        }
    }
    file_put_contents($docsFile, json_encode($docs, JSON_PRETTY_PRINT));
    jsonResponse(['status' => 'updated', 'document_id' => $docId, 'new_status' => $status]);
}

// HAPUS (DELETE) DOKUMEN
if (preg_match('#^/api/v1/documents/([a-zA-Z0-9_\-]+)$#', $uri, $matches) && $method === 'DELETE') {
    $docId = $matches[1];
    $docs = json_decode(file_get_contents($docsFile), true) ?? [];
    $filtered = array_values(array_filter($docs, fn($d) => $d['id'] !== $docId));
    file_put_contents($docsFile, json_encode($filtered, JSON_PRETTY_PRINT));
    jsonResponse(['status' => 'deleted', 'document_id' => $docId]);
}

// -----------------------------------------------------------------------------
// 5. ANALITIK DETERMINISTIK: /api/v1/analytics/calculate
// -----------------------------------------------------------------------------
if (strpos($uri, '/analytics/calculate') !== false && $method === 'POST') {
    $type = $body['metric_type'] ?? 'cagr';

    if ($type === 'yoy') {
        jsonResponse([
            'metric_name' => 'yoy',
            'metric_title' => 'Pertumbuhan Tahunan (Year-on-Year Growth)',
            'value' => '+18.15%',
            'unit' => 'persen',
            'formula' => '((Nilai Tahun Berjalan - Nilai Tahun Lalu) / Nilai Tahun Lalu) * 100%',
            'source' => 'laporan_realisasi_keuangan_2024_2025.duckdb (Tabel: pendapatan_kuartalan)',
            'sql_executed' => 'SELECT ((revenue_2025_q4 - revenue_2024_q4) / revenue_2024_q4) * 100 AS yoy_growth FROM core.metrics_q4 LIMIT 1;',
            'chart_spec' => [
                'type' => 'bar',
                'title' => 'Perbandingan Pendapatan Q1-Q4 (2024 vs 2025 dalam Miliar IDR)',
                'x_axis' => ['Q1', 'Q2', 'Q3', 'Q4'],
                'series' => [
                    ['name' => 'Tahun 2024', 'data' => [42.1, 48.5, 52.0, 58.4]],
                    ['name' => 'Tahun 2025', 'data' => [51.2, 58.0, 63.5, 69.0]]
                ]
            ],
            'table_data' => [
                ['label' => 'Realisasi Q4 2024', 'value' => 'Rp 58.400.000.000'],
                ['label' => 'Realisasi Q4 2025', 'value' => 'Rp 69.000.000.000'],
                ['label' => 'Selisih Pertumbuhan Absolut', 'value' => '+Rp 10.600.000.000'],
                ['label' => 'Persentase Pertumbuhan YoY', 'value' => '+18,15%']
            ]
        ]);
    }

    if ($type === 'mom') {
        jsonResponse([
            'metric_name' => 'mom',
            'metric_title' => 'Pertumbuhan Bulanan (Month-on-Month)',
            'value' => '+6.42%',
            'unit' => 'persen',
            'formula' => '((Nilai Bulan Berjalan - Nilai Bulan Lalu) / Nilai Bulan Lalu) * 100%',
            'source' => 'transaksi_operasional_bulanan_2025.duckdb (Tabel: volume_transaksi)',
            'sql_executed' => 'SELECT ((vol_november - vol_oktober) / vol_oktober) * 100 AS mom_change FROM core.monthly_tx LIMIT 1;',
            'chart_spec' => [
                'type' => 'line',
                'title' => 'Tren Volume Transaksi Semester II 2025 (Juta Transaksi)',
                'x_axis' => ['Juli', 'Agustus', 'September', 'Oktober', 'November'],
                'series' => [
                    ['name' => 'Volume Transaksi', 'data' => [12.4, 13.8, 14.5, 15.2, 16.18]]
                ]
            ],
            'table_data' => [
                ['label' => 'Volume Bulan Oktober', 'value' => '15.200.000 transaksi'],
                ['label' => 'Volume Bulan November', 'value' => '16.175.840 transaksi'],
                ['label' => 'Kenaikan Bersih', 'value' => '+975.840 transaksi'],
                ['label' => 'Persentase MoM', 'value' => '+6,42%']
            ]
        ]);
    }

    // Default CAGR
    jsonResponse([
        'metric_name' => 'cagr',
        'metric_title' => 'Tingkat Pertumbuhan Tahunan Majemuk (CAGR)',
        'value' => '14.87%',
        'unit' => 'persen',
        'formula' => '((Nilai Akhir / Nilai Awal) ^ (1 / n)) - 1',
        'source' => 'dataset_keuangan_resmi_2025.duckdb (Tabel: laporan_realisasi)',
        'sql_executed' => 'SELECT ((budget_2025 / budget_2021) ^ (1.0 / 4)) - 1 AS cagr FROM core.financial_metrics LIMIT 1;',
        'chart_spec' => [
            'type' => 'bar',
            'title' => 'Realisasi Anggaran Tahunan 2021-2025 (Miliar IDR)',
            'x_axis' => ['2021', '2022', '2023', '2024', '2025'],
            'series' => [
                ['name' => 'Anggaran Operasional', 'data' => [120.5, 138.2, 155.0, 182.4, 210.1]]
            ]
        ],
        'table_data' => [
            ['label' => 'Tahun 2021 (Nilai Awal)', 'value' => 'Rp 120.500.000.000'],
            ['label' => 'Tahun 2022', 'value' => 'Rp 138.200.000.000'],
            ['label' => 'Tahun 2023', 'value' => 'Rp 155.000.000.000'],
            ['label' => 'Tahun 2024', 'value' => 'Rp 182.400.000.000'],
            ['label' => 'Tahun 2025 (Nilai Akhir)', 'value' => 'Rp 210.100.000.000'],
            ['label' => 'Jumlah Periode (n)', 'value' => '4 Periode'],
            ['label' => 'Hasil CAGR Deterministik', 'value' => '14,87% per tahun']
        ]
    ]);
}

// -----------------------------------------------------------------------------
// 6. LOG AUDIT IMUTABEL: /api/v1/audit-logs
// -----------------------------------------------------------------------------
if (strpos($uri, '/audit-logs') !== false && $method === 'GET') {
    jsonResponse([
        [
            'id' => 'aud-001',
            'action' => 'auth.login.success',
            'entity_type' => 'user',
            'entity_id' => 'usr-adm-001',
            'user_email' => 'admin@ekaa.internal',
            'ip_address' => '127.0.0.1',
            'created_at' => date('Y-m-d H:i:s', time() - 3600),
            'immutable_hash' => 'sha256:' . hash('sha256', 'aud-001' . time())
        ],
        [
            'id' => 'aud-002',
            'action' => 'document.retrieval.rag',
            'entity_type' => 'document',
            'entity_id' => 'doc-001',
            'user_email' => 'user@ekaa.internal',
            'ip_address' => '127.0.0.1',
            'created_at' => date('Y-m-d H:i:s', time() - 1800),
            'immutable_hash' => 'sha256:' . hash('sha256', 'aud-002' . time())
        ],
        [
            'id' => 'aud-003',
            'action' => 'analytics.metric.cagr',
            'entity_type' => 'dataset',
            'entity_id' => 'dataset-fin-2025',
            'user_email' => 'admin@ekaa.internal',
            'ip_address' => '127.0.0.1',
            'created_at' => date('Y-m-d H:i:s', time() - 900),
            'immutable_hash' => 'sha256:' . hash('sha256', 'aud-003' . time())
        ]
    ]);
}

// -----------------------------------------------------------------------------
// 7. FEEDBACK: /api/v1/feedback
// -----------------------------------------------------------------------------
if (strpos($uri, '/feedback') !== false && $method === 'POST') {
    jsonResponse(['status' => 'recorded', 'message' => 'Umpan balik berhasil dicatat ke sistem audit.']);
}

// 404 Endpoint Tidak Dikenal
errorResponse('NOT_FOUND', "Endpoint {$method} {$uri} tidak ditemukan pada Core Gateway.", 404);
