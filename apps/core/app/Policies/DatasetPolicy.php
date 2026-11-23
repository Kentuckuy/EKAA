<?php

namespace App\Policies;

use Exception;

/**
 * Dataset Policy (AC-M5-06, §23, §24, §28.4).
 *
 * Enforces zero cross-access leakage for tabular datasets and analytics queries.
 * Unauthorized requests MUST receive 404 NOT FOUND (never 403) to prevent
 * resource enumeration attacks.
 */
class DatasetPolicy
{
    /**
     * Authorize viewing or querying a tabular dataset.
     *
     * @param array $userPrincipals Array of active principals for user e.g. ['all', 'user:uuid', 'role:analyst', 'unit:finance']
     * @param array $datasetAclPrincipals Array of principals authorized on this dataset e.g. ['user:uuid', 'unit:finance']
     * @param string $datasetStatus Status of dataset ('active', 'archived', 'revoked')
     * @param string $userId UUID of the requesting user
     * @param string $ownerId UUID of the dataset creator/owner
     * @return bool
     * @throws Exception Throws NOT_FOUND exception on any access denial to mask existence
     */
    public function authorizeAccess(
        array $userPrincipals,
        array $datasetAclPrincipals,
        string $datasetStatus,
        string $userId,
        string $ownerId
    ): bool {
        // Owner always has access if dataset is active
        if ($userId === $ownerId && $datasetStatus === 'active') {
            return true;
        }

        // If dataset is revoked or archived, standard users cannot access
        if ($datasetStatus !== 'active') {
            throw new Exception('NOT_FOUND: Dataset tidak ditemukan.');
        }

        // Intersection check between user principals and dataset ACL principals
        $intersection = array_intersect($userPrincipals, $datasetAclPrincipals);
        if (empty($intersection)) {
            // Mask with 404 NOT FOUND to eliminate information disclosure
            throw new Exception('NOT_FOUND: Dataset tidak ditemukan.');
        }

        return true;
    }

    /**
     * Authorize analytical querying execution.
     *
     * @param array $userPrincipals
     * @param array $dataset
     * @param string $userId
     * @return bool
     * @throws Exception
     */
    public function authorizeQuery(array $userPrincipals, array $dataset, string $userId): bool
    {
        return $this->authorizeAccess(
            $userPrincipals,
            $dataset['acl_principals'] ?? [],
            $dataset['status'] ?? 'active',
            $userId,
            $dataset['owner_id'] ?? ''
        );
    }
}
