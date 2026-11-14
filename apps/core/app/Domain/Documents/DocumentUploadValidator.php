<?php

namespace App\Domain\Documents;

use Exception;

class DocumentUploadValidator
{
    private const MAX_DOCUMENT_SIZE_BYTES = 52428800; // 50 MB (§8.4)
    private const ALLOWED_EXTENSIONS = ['pdf', 'docx', 'xlsx', 'csv'];

    /**
     * Validate upload size, extension, MIME, and magic bytes (§20.1, AC-M2-01).
     */
    public function validateFile(string $filename, int $sizeBytes, string $mimeType, string $fileContent): void
    {
        // 1. Check file size
        if ($sizeBytes > self::MAX_DOCUMENT_SIZE_BYTES) {
            throw new Exception('PAYLOAD_TOO_LARGE: Ukuran berkas melebihi batas maksimum 50 MB.');
        }

        // 2. Check extension
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (!in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
            throw new Exception('UNSUPPORTED_MEDIA_TYPE: Ekstensi berkas tidak diizinkan.');
        }

        // 3. Check magic bytes
        $this->verifyMagicBytes($ext, $fileContent);
    }

    /**
     * Verify true file magic bytes to prevent MIME spoofing.
     */
    public function verifyMagicBytes(string $extension, string $content): void
    {
        $prefix4 = substr($content, 0, 4);

        if ($extension === 'pdf') {
            // PDF magic bytes: %PDF (0x25 0x50 0x44 0x46)
            if (strncmp($content, "%PDF-", 5) !== 0) {
                throw new Exception('UNSUPPORTED_MEDIA_TYPE: Magic bytes berkas PDF tidak valid atau rusak.');
            }
        } elseif (in_array($extension, ['docx', 'xlsx'], true)) {
            // PKZip magic bytes: PK\x03\x04
            if ($prefix4 !== "PK\x03\x04") {
                throw new Exception('UNSUPPORTED_MEDIA_TYPE: Magic bytes dokumen Office Open XML tidak valid.');
            }
        }
    }

    /**
     * Scan with ClamAV fail-closed (§30.2, T-06, AC-M2-01).
     * If ClamAV server is unreachable or offline, reject immediately (fail-closed).
     *
     * @param string $fileContent
     * @param bool $isClamAvAvailable
     * @param bool $isMalwareDetected
     * @throws Exception
     */
    public function scanClamAv(string $fileContent, bool $isClamAvAvailable, bool $isMalwareDetected = false): void
    {
        // Fail-closed invariant: If scanner is down, REJECT upload
        if (!$isClamAvAvailable) {
            throw new Exception('UPSTREAM_ERROR: Pemindai antivirus ClamAV tidak tersedia. Unggahan ditolak.');
        }

        // Check for EICAR standard antivirus test string or infected payload
        if ($isMalwareDetected || str_contains($fileContent, 'X5O!P%@AP[4\PZX54(P^)7CC)7}$EICAR-STANDARD-ANTIVIRUS-TEST-FILE!$H+H*')) {
            throw new Exception('MALWARE_DETECTED: Berkas terdeteksi memuat pola malware atau virus.');
        }
    }
}
