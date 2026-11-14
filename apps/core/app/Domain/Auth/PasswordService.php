<?php

namespace App\Domain\Auth;

use Exception;

class PasswordService
{
    private const MIN_PASSWORD_LENGTH = 12;

    /**
     * Hash password using adaptive standard password_hash (Argon2id or bcrypt).
     */
    public function hashPassword(string $password): string
    {
        $this->validateStrength($password);
        $algo = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT;
        return password_hash($password, $algo);
    }

    /**
     * Verify password hash against plain input.
     */
    public function verifyPassword(string $plainPassword, string $passwordHash): bool
    {
        return password_verify($plainPassword, $passwordHash);
    }

    /**
     * Validate password complexity requirements.
     */
    public function validateStrength(string $password): void
    {
        if (strlen($password) < self::MIN_PASSWORD_LENGTH) {
            throw new Exception('Kata sandi harus memiliki minimal ' . self::MIN_PASSWORD_LENGTH . ' karakter.');
        }
        if (!preg_match('/[A-Z]/', $password)) {
            throw new Exception('Kata sandi harus memuat minimal satu huruf kapital.');
        }
        if (!preg_match('/[a-z]/', $password)) {
            throw new Exception('Kata sandi harus memuat minimal satu huruf kecil.');
        }
        if (!preg_match('/[0-9]/', $password)) {
            throw new Exception('Kata sandi harus memuat minimal satu angka.');
        }
    }

    /**
     * Generate secure random temporary password (FR-034, AC-M1-09).
     */
    public function generateTemporaryPassword(): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        $pass = '';
        $max = strlen($chars) - 1;
        for ($i = 0; $i < 16; $i++) {
            $pass .= $chars[random_int(0, $max)];
        }
        return $pass;
    }
}
