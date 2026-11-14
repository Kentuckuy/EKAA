<?php

namespace App\Domain\Documents;

use Exception;

class DocumentPolicy
{
    /**
     * Authorize document access against user principals (§9.2, AC-M2-03, AC-M2-07).
     *
     * @param array $userPrincipals Array of principals ['all', 'user:xyz', 'role:abc', 'unit:123', ...]
     * @param array $docPermissions Array of permission records from core.document_permissions
     * @param string $docStatus Status ('draft', 'active', 'revoked')
     * @param string $userId
     * @param string $docOwnerId
     * @return bool
     * @throws Exception Returns 404 NOT FOUND (not 403) if unauthorized!
     */
    public function authorizeView(
        array $userPrincipals,
        array $docPermissions,
        string $docStatus,
        string $userId,
        string $docOwnerId
    ): bool {
        // Owner always has access
        if ($userId === $docOwnerId) {
            return true;
        }

        // If document is revoked or draft, standard users cannot view
        if ($docStatus === 'revoked') {
            throw new Exception('NOT_FOUND: Dokumen tidak ditemukan.'); // 404 masking
        }

        // Check if any permission matches user principals
        $hasMatch = false;
        foreach ($docPermissions as $perm) {
            $type = $perm['principal_type'];
            $id = $perm['principal_id'] ?? null;

            if ($type === 'all' && in_array('all', $userPrincipals, true)) {
                $hasMatch = true;
                break;
            }
            if ($type === 'user' && in_array('user:' . $id, $userPrincipals, true)) {
                $hasMatch = true;
                break;
            }
            if ($type === 'role' && in_array('role:' . $id, $userPrincipals, true)) {
                $hasMatch = true;
                break;
            }
            if ($type === 'unit' && in_array('unit:' . $id, $userPrincipals, true)) {
                $hasMatch = true;
                break;
            }
        }

        if (!$hasMatch) {
            // Mask with 404 to prevent resource existence enumeration (AC-M2-03)
            throw new Exception('NOT_FOUND: Dokumen tidak ditemukan.');
        }

        return true;
    }
}
