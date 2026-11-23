<?php

namespace App\Domain\Sync;

use InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Folder Scanner (AC-M7-01, §20, §21).
 *
 * Scans local or mounted filesystem directories, computes content hashes,
 * and detects changes (new, modified, deleted, unchanged) relative to
 * previous synchronization manifests.
 */
class FolderScanner
{
    public const ALLOWED_EXTENSIONS = ['pdf', 'docx', 'xlsx', 'csv'];

    /**
     * Scan directory and compare with existing manifest state.
     *
     * @param string $directoryPath Absolute path to root folder being synced
     * @param array<string, string> $previousManifest Map of [relative_path => sha256_hash]
     * @return array{
     *   scanned_count: int,
     *   new: array<string, array{path: string, hash: string, size: int}>,
     *   modified: array<string, array{path: string, hash: string, old_hash: string, size: int}>,
     *   deleted: array<string, array{path: string, old_hash: string}>,
     *   unchanged: array<string, array{path: string, hash: string}>,
     *   current_manifest: array<string, string>
     * }
     */
    public function scan(string $directoryPath, array $previousManifest = []): array
    {
        if (!is_dir($directoryPath)) {
            throw new InvalidArgumentException("Direktori tidak ditemukan: {$directoryPath}");
        }

        $currentFiles = [];
        $currentManifest = [];
        $scannedCount = 0;

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directoryPath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        $realRoot = realpath($directoryPath);

        foreach ($iterator as $item) {
            if (!$item->isFile()) {
                continue;
            }

            $fileName = $item->getFilename();

            // Ignore hidden files and Office temp files (e.g. ~$document.docx)
            if (str_starts_with($fileName, '.') || str_starts_with($fileName, '~$')) {
                continue;
            }

            $ext = strtolower($item->getExtension());
            if (!in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
                continue;
            }

            $filePath = $item->getRealPath();
            $relativePath = ltrim(substr($filePath, strlen($realRoot)), DIRECTORY_SEPARATOR);
            // Normalize path separators to forward slash for cross-platform manifest
            $normalizedPath = str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);

            $hash = hash_file('sha256', $filePath);
            $size = $item->getSize();
            $scannedCount++;

            $currentFiles[$normalizedPath] = [
                'path' => $filePath,
                'relative_path' => $normalizedPath,
                'hash' => $hash,
                'size' => $size,
            ];
            $currentManifest[$normalizedPath] = $hash;
        }

        $new = [];
        $modified = [];
        $unchanged = [];
        $deleted = [];

        // 1. Detect new, modified, and unchanged files
        foreach ($currentFiles as $relPath => $fileMeta) {
            $hash = $fileMeta['hash'];
            if (!isset($previousManifest[$relPath])) {
                $new[$relPath] = $fileMeta;
            } elseif ($previousManifest[$relPath] !== $hash) {
                $modified[$relPath] = array_merge($fileMeta, [
                    'old_hash' => $previousManifest[$relPath],
                ]);
            } else {
                $unchanged[$relPath] = $fileMeta;
            }
        }

        // 2. Detect deleted files (in previous manifest, missing on disk)
        foreach ($previousManifest as $relPath => $oldHash) {
            if (!isset($currentFiles[$relPath])) {
                $deleted[$relPath] = [
                    'relative_path' => $relPath,
                    'old_hash' => $oldHash,
                ];
            }
        }

        return [
            'scanned_count' => $scannedCount,
            'new' => $new,
            'modified' => $modified,
            'deleted' => $deleted,
            'unchanged' => $unchanged,
            'current_manifest' => $currentManifest,
        ];
    }
}
