<?php

namespace App\Domain\Identity;

class PrincipalResolver
{
    /**
     * Resolve user access principals (§16.1, DD-14, DEC-025, AC-M2-07).
     * Calculates:
     * - 'all'
     * - 'user:<user_id>'
     * - 'role:<role_code>' for each assigned role
     * - 'unit:<unit_id>' for user's assigned unit AND ALL parent/ancestor units recursively
     *
     * @param string $userId
     * @param bool $isActive
     * @param array $roleCodes Array of role codes ['standard_user', ...]
     * @param array $assignedUnitIds Array of unit UUIDs user directly belongs to
     * @param array $unitParentMap Lookup map [unit_id => parent_unit_id]
     * @return array List of string principals
     */
    public function resolve(
        string $userId,
        bool $isActive,
        array $roleCodes,
        array $assignedUnitIds,
        array $unitParentMap = []
    ): array {
        // If user is inactive, they possess ZERO access principals (AC-M1-05)
        if (!$isActive) {
            return [];
        }

        $principals = ['all'];
        $principals[] = 'user:' . $userId;

        // Add roles
        foreach ($roleCodes as $role) {
            $principals[] = 'role:' . $role;
        }

        // Expand units and all their parent/ancestor units recursively (DD-14)
        $visitedUnits = [];
        foreach ($assignedUnitIds as $unitId) {
            $current = $unitId;
            while (!empty($current) && !isset($visitedUnits[$current])) {
                $visitedUnits[$current] = true;
                $principals[] = 'unit:' . $current;

                // Move up to parent unit if present
                $current = $unitParentMap[$current] ?? null;
            }
        }

        return array_values(array_unique($principals));
    }
}
