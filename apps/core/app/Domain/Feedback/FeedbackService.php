<?php

namespace App\Domain\Feedback;

use Exception;
use InvalidArgumentException;

/**
 * Feedback Service (AC-M6-03, AC-M6-04, §27).
 *
 * Captures user thumbs up / thumbs down evaluations on AI assistant messages,
 * enforcing structured negative reasons and strict user ownership.
 */
class FeedbackService
{
    public const VALID_RATINGS = ['positive', 'negative', 'thumbs_up', 'thumbs_down'];

    public const VALID_NEGATIVE_REASONS = [
        'inaccurate',      // Jawaban tidak akurat / salah fakta
        'incomplete',      // Jawaban kurang lengkap / terpotong
        'wrong_citation',  // Sitasi atau pasal rujukan salah / tidak relevan
        'unhelpful',       // Tidak menjawab substansi pertanyaan
        'other',           // Alasan lainnya (dengan catatan)
    ];

    /**
     * Submit or update feedback for an assistant response message.
     *
     * @param array $input [
     *   'conversation_id' => string,
     *   'message_id'      => string,
     *   'user_id'         => string,
     *   'rating'          => 'positive'|'negative'|'thumbs_up'|'thumbs_down',
     *   'reason'          => string|null (wajib jika negatif),
     *   'comment'         => string|null,
     *   'conversation_owner_id' => string,
     * ]
     * @return array Created/updated feedback record
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function recordFeedback(array $input): array
    {
        // 1. Verify user ownership of conversation (zero cross-access leakage)
        $userId = $input['user_id'] ?? null;
        $conversationOwnerId = $input['conversation_owner_id'] ?? null;

        if (!$userId || !$conversationOwnerId || $userId !== $conversationOwnerId) {
            // Mask unauthorized conversation with 404 to prevent enumeration
            throw new Exception('NOT_FOUND: Percakapan tidak ditemukan.');
        }

        // 2. Validate rating
        $rating = strtolower(trim((string)($input['rating'] ?? '')));
        if (!in_array($rating, self::VALID_RATINGS, true)) {
            throw new InvalidArgumentException(
                "Rating harus bernilai salah satu dari: " . implode(', ', self::VALID_RATINGS)
            );
        }

        $isNegative = in_array($rating, ['negative', 'thumbs_down'], true);
        $reason = $input['reason'] ?? null;

        // 3. Validate structured reason for negative feedback (AC-M6-04)
        if ($isNegative) {
            if (!$reason || !in_array($reason, self::VALID_NEGATIVE_REASONS, true)) {
                throw new InvalidArgumentException(
                    "Umpan balik negatif wajib menyertakan alasan terstruktur: " .
                    implode(', ', self::VALID_NEGATIVE_REASONS)
                );
            }
        } else {
            // Positive feedback does not require a reason
            $reason = null;
        }

        $comment = isset($input['comment']) ? trim((string)$input['comment']) : null;
        if ($comment !== null && strlen($comment) > 1000) {
            throw new InvalidArgumentException("Catatan komentar maksimal 1000 karakter.");
        }

        // Normalize rating to standard 'positive' / 'negative'
        $normalizedRating = $isNegative ? 'negative' : 'positive';

        // 4. Return canonical feedback payload
        return [
            'id'              => 'fb-' . bin2hex(random_bytes(8)),
            'conversation_id' => $input['conversation_id'],
            'message_id'      => $input['message_id'],
            'user_id'         => $userId,
            'rating'          => $normalizedRating,
            'reason'          => $reason,
            'comment'         => $comment,
            'status'          => 'recorded',
            'created_at'      => gmdate('Y-m-d\TH:i:s\Z'),
        ];
    }
}
