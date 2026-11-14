<?php

namespace App\Policies;

use Exception;

class ConversationPolicy
{
    /**
     * Authorize viewing conversation history (AC-M4-06).
     * Conversation history can strictly only be viewed by its creator.
     */
    public function authorizeAccess(string $requestingUserId, string $conversationOwnerId): bool
    {
        if ($requestingUserId !== $conversationOwnerId) {
            throw new Exception('FORBIDDEN: Anda tidak dapat mengakses riwayat percakapan pengguna lain.');
        }
        return true;
    }
}
