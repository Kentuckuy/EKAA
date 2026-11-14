<?php

namespace App\Domain\Conversations;

use App\Domain\Documents\DocumentPolicy;
use Exception;

class CitationVerifier
{
    private DocumentPolicy $documentPolicy;

    public function __construct(DocumentPolicy $documentPolicy)
    {
        $this->documentPolicy = $documentPolicy;
    }

    /**
     * Layer-2 Citation Verification in Laravel Core (§18.2, §22.3, AC-M4-05).
     * Verifies Policy for every document_id cited by AI response.
     * Any citation failing Policy is stripped and logged as an anomaly.
     * If all citations are stripped, the answer status becomes 'no_evidence'.
     *
     * @param array $userPrincipals
     * @param string $userId
     * @param array $citations
     * @param array $documentsDb Lookup map [document_id => ['permissions' => [...], 'status' => 'active', 'owner_id' => '...']]
     * @return array ['citations' => array, 'status' => string, 'anomalies' => array]
     */
    public function verifyCitations(
        array $userPrincipals,
        string $userId,
        array $citations,
        array $documentsDb
    ): array {
        $allowedCitations = [];
        $anomalies = [];

        foreach ($citations as $cit) {
            $docId = $cit['document_id'];
            $docRecord = $documentsDb[$docId] ?? null;

            if (!$docRecord) {
                $anomalies[] = [
                    'document_id' => $docId,
                    'reason' => 'DOCUMENT_DOES_NOT_EXIST',
                ];
                continue;
            }

            try {
                $this->documentPolicy->authorizeView(
                    $userPrincipals,
                    $docRecord['permissions'] ?? [],
                    $docRecord['status'] ?? 'draft',
                    $userId,
                    $docRecord['owner_id'] ?? ''
                );
                $allowedCitations[] = $cit;
            } catch (Exception $e) {
                // Security anomaly: Citation failed Policy verification!
                $anomalies[] = [
                    'document_id' => $docId,
                    'reason' => 'POLICY_AUTHORIZATION_FAILED',
                    'error' => $e->getMessage(),
                ];
            }
        }

        // If all citations failed, status becomes no_evidence
        $finalStatus = count($allowedCitations) > 0 ? 'answered' : 'no_evidence';

        return [
            'citations' => $allowedCitations,
            'status' => $finalStatus,
            'anomalies' => $anomalies,
        ];
    }
}
