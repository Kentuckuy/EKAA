<?php

namespace App\Domain\Documents;

use Exception;

class AIClient
{
    private bool $isAiHealthy;

    public function __construct(bool $isAiHealthy = true)
    {
        $this->isAiHealthy = $isAiHealthy;
    }

    /**
     * Call AI internal API with graceful 503 fallback (§18.5, AC-M2-05).
     */
    public function dispatchAsk(array $payload): array
    {
        if (!$this->isAiHealthy) {
            throw new Exception(
                'AI_UNAVAILABLE: Layanan AI saat ini tidak tersedia. ' .
                'Fungsi non-AI sistem tetap beroperasi normal.'
            );
        }

        return [
            'status' => 'answered',
            'route' => 'document',
        ];
    }
}
