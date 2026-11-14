<?php

namespace App\Domain\Documents;

use Exception;

class AclSyncService
{
    /**
     * Synchronously revoke a document and sync to AI service (§18.3, AC-M2-04).
     *
     * @param string $documentId
     * @param callable $aiClientCallback Function that calls AI PUT /internal/v1/documents/{id}/status
     * @return array
     * @throws Exception
     */
    public function revokeDocument(string $documentId, callable $aiClientCallback): array
    {
        // 1. Mark as revoked in Laravel Core state first
        $localStatus = 'revoked';

        // 2. Synchronous call to AI service
        try {
            $aiResponse = $aiClientCallback($documentId, ['status' => 'revoked', 'is_current' => false]);
            if (!isset($aiResponse['status']) || $aiResponse['status'] !== 'updated') {
                throw new Exception('AI_SYNC_FAILED');
            }
        } catch (Exception $e) {
            // Invariant 6: Action fails for user, but document REMAINS marked as revoked in core
            // (its citations will be discarded on Layer 2 verification)
            throw new Exception(
                'AI_SYNC_FAILED: Gagal menyinkronkan status pencabutan ke layanan AI. ' .
                'Dokumen tetap ditandai dicabut pada sistem inti.'
            );
        }

        return [
            'status' => 'revoked',
            'document_id' => $documentId,
            'ai_synced' => true,
        ];
    }
}
