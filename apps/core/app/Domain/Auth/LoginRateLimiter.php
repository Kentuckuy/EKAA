<?php

namespace App\Domain\Auth;

class LoginRateLimiter
{
    private const MAX_ATTEMPTS = 5;
    private const DECAY_SECONDS = 60; // 1 minute window

    /**
     * In-memory or key-value rate check logic (AC-M1-02).
     */
    public function isLockedOut(int $currentAttempts): bool
    {
        return $currentAttempts >= self::MAX_ATTEMPTS;
    }

    public function getMaxAttempts(): int
    {
        return self::MAX_ATTEMPTS;
    }

    public function getDecaySeconds(): int
    {
        return self::DECAY_SECONDS;
    }
}
