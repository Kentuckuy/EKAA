<?php

namespace App\Domain\Auth;

use DateTimeImmutable;
use DateTimeZone;
use Exception;

class TokenService
{
    private const ACCESS_TOKEN_TTL_SECONDS = 900; // 15 minutes (DEC-031)
    private const REFRESH_TOKEN_TTL_SECONDS = 604800; // 7 days

    /**
     * Generate an opaque access token and a rotating refresh token.
     */
    public function createTokenPair(string $userId, ?string $userAgent = null): array
    {
        $accessToken = bin2hex(random_bytes(32));
        $rawRefreshToken = bin2hex(random_bytes(48));
        $refreshTokenHash = hash('sha256', $rawRefreshToken);

        $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $accessExpiresAt = $now->modify('+' . self::ACCESS_TOKEN_TTL_SECONDS . ' seconds');
        $refreshExpiresAt = $now->modify('+' . self::REFRESH_TOKEN_TTL_SECONDS . ' seconds');

        return [
            'access_token' => $accessToken,
            'access_token_hash' => hash('sha256', $accessToken),
            'access_expires_at' => $accessExpiresAt,
            'refresh_token' => $rawRefreshToken,
            'refresh_token_hash' => $refreshTokenHash,
            'refresh_expires_at' => $refreshExpiresAt,
            'user_id' => $userId,
        ];
    }

    /**
     * Handle refresh token rotation and reuse detection (AC-M1-03).
     * If a revoked/replaced token is presented, REVOKES all active tokens for this user.
     *
     * @param string $rawRefreshToken
     * @param array $existingTokenRecord Record from core.refresh_tokens
     * @return array
     * @throws Exception
     */
    public function rotateRefreshToken(string $rawRefreshToken, ?array $existingTokenRecord): array
    {
        if (!$existingTokenRecord) {
            throw new Exception('TOKEN_NOT_FOUND');
        }

        $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $expiresAt = new DateTimeImmutable($existingTokenRecord['expires_at'], new DateTimeZone('UTC'));

        // Check if token was already revoked or replaced -> REUSE DETECTED!
        if (!empty($existingTokenRecord['revoked_at']) || !empty($existingTokenRecord['replaced_by'])) {
            // Signal to revoke all tokens for this user
            throw new Exception('REUSE_DETECTED');
        }

        // Check if expired
        if ($expiresAt < $now) {
            throw new Exception('TOKEN_EXPIRED');
        }

        // Issue new token pair
        $newPair = $this->createTokenPair($existingTokenRecord['user_id']);
        $newPair['replaced_token_id'] = $existingTokenRecord['id'];

        return $newPair;
    }
}
