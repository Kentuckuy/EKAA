<?php

namespace App\Domain\Identity;

use Exception;

class UserService
{
    /**
     * Enforce rule: The last active org_admin cannot be deactivated or have their admin role revoked (AC-M1-08).
     */
    public function assertCanDeactivateUser(string $targetUserId, array $targetUserRoles, int $totalActiveOrgAdmins): void
    {
        if (in_array('org_admin', $targetUserRoles, true) && $totalActiveOrgAdmins <= 1) {
            throw new Exception('Admin organisasi terakhir tidak dapat dinonaktifkan.');
        }
    }

    /**
     * Enforce rule: The org_admin role cannot be removed if it leaves zero org_admins.
     */
    public function assertCanRemoveRole(string $targetUserId, string $roleToRemove, int $totalActiveOrgAdmins): void
    {
        if ($roleToRemove === 'org_admin' && $totalActiveOrgAdmins <= 1) {
            throw new Exception('Peran admin organisasi terakhir tidak dapat dicabut.');
        }
    }

    /**
     * Check if assigning a parent unit would cause a circular dependency.
     */
    public function assertNoUnitCycle(string $unitId, ?string $newParentId, array $unitParentMap): void
    {
        if (empty($newParentId)) {
            return;
        }

        if ($unitId === $newParentId) {
            throw new Exception('Sebuah unit kerja tidak dapat menjadi induk bagi dirinya sendiri.');
        }

        $current = $newParentId;
        $visited = [$unitId => true];

        while (!empty($current)) {
            if (isset($visited[$current])) {
                throw new Exception('Terdeteksi dependensi siklus pada hierarki unit kerja.');
            }
            $visited[$current] = true;
            $current = $unitParentMap[$current] ?? null;
        }
    }
}
