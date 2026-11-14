<?php

namespace App\Domain\Audit;

use DateTimeImmutable;
use DateTimeZone;

class AuditRecorder
{
    /**
     * Prepare a structured audit log entry with PII masking (§30.4, FR-032).
     */
    public function formatEntry(
        ?string $actorId,
        string $actorType,
        string $action,
        string $resourceType,
        ?string $resourceId,
        string $outcome,
        ?string $ip,
        string $correlationId,
        array $details = []
    ): array {
        $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));

        return [
            'id' => bin2hex(random_bytes(16)), // UUIDv7 placeholder
            'occurred_at' => $now->format('Y-m-d\TH:i:s.u\Z'),
            'actor_id' => $actorId,
            'actor_type' => $actorType,
            'action' => $action,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'outcome' => $outcome,
            'ip' => $ip,
            'correlation_id' => $correlationId,
            'details' => $this->maskPII($details),
        ];
    }

    /**
     * Mask sensitive Personally Identifiable Information (NIK, email, phone numbers).
     */
    public function maskPII(array $data): array
    {
        $masked = [];
        foreach ($data as $key => $val) {
            if (is_array($val)) {
                $masked[$key] = $this->maskPII($val);
            } elseif (is_string($val)) {
                // Mask 16-digit Indonesian NIK: 320101XXXXXXXX01
                $val = preg_replace('/(\d{6})\d{8}(\d{2})/', '$1********$2', $val);
                // Mask Indonesian phone numbers (+628... / 08...)
                $val = preg_replace('/(\+?62|0)8(\d{2})\d{4,6}(\d{2})/', '$1 8$2-****-$3', $val);
                // Mask email prefix: fatur@... -> f***r@...
                $val = preg_replace_callback('/([a-zA-Z0-9_\-\.]{1,2})([a-zA-Z0-9_\-\.]+)(@.+)/', function ($m) {
                    return $m[1] . str_repeat('*', strlen($m[2])) . $m[3];
                }, $val);
                $masked[$key] = $val;
            } else {
                $masked[$key] = $val;
            }
        }
        return $masked;
    }
}
