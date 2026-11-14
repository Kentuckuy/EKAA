<?php

namespace App\Domain\Settings;

class SettingService
{
    /**
     * Filter and mask secret settings for API output.
     * Secrets are never shown again in plaintext (FR-004).
     */
    public function formatSettingForOutput(array $settingRow): array
    {
        $isSecret = (bool)($settingRow['is_secret'] ?? false);
        $value = $settingRow['value'];

        if ($isSecret) {
            $value = '********'; // Masked secret value
        }

        return [
            'key' => $settingRow['key'],
            'value' => $value,
            'is_secret' => $isSecret,
            'updated_at' => $settingRow['updated_at'] ?? null,
        ];
    }
}
