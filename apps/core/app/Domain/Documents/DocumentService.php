<?php

namespace App\Domain\Documents;

use Exception;

class DocumentService
{
    /**
     * Enforce rule: A document without at least one permission rule CANNOT be activated (AC-M2-07).
     */
    public function assertCanActivate(array $permissionRules): void
    {
        if (empty($permissionRules)) {
            throw new Exception('VALIDATION_FAILED: Dokumen tanpa aturan izin tidak dapat diaktifkan.');
        }
    }

    /**
     * Generate storage key with randomized UUID path (§20.1).
     */
    public function generateStorageKey(string $documentId, int $versionNo, string $extension): string
    {
        return sprintf('documents/%s/v%d_%s.%s', $documentId, $versionNo, bin2hex(random_bytes(8)), $extension);
    }

    /**
     * Check idempotency: If idempotency key already processed, return existing result without duplicate (AC-M2-02).
     */
    public function handleIdempotency(string $idempotencyKey, array $idempotencyStore): ?array
    {
        if (isset($idempotencyStore[$idempotencyKey])) {
            return $idempotencyStore[$idempotencyKey];
        }
        return null;
    }
}
