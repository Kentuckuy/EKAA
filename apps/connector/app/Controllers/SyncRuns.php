<?php

namespace App\Controllers;

use App\Domain\Sync\FolderScanner;
use App\Domain\Sync\CoreHandoffClient;
use CodeIgniter\RESTful\ResourceController;
use Exception;

/**
 * Sync Runs Controller (AC-M7-02, AC-M7-03, AC-M7-04, AC-M7-06, AC-M7-07).
 *
 * Coordinates folder scanning, item-by-item Core handoff with fault isolation,
 * deletion synchronization, and rate-limiting.
 */
class SyncRuns extends ResourceController
{
    protected $format = 'json';

    private FolderScanner $scanner;
    private CoreHandoffClient $handoffClient;

    // Minimum seconds between sync runs for the same source (AC-M7-07)
    public const DEBOUNCE_INTERVAL_SECONDS = 60;

    public function __construct(
        ?FolderScanner $scanner = null,
        ?CoreHandoffClient $handoffClient = null
    ) {
        $this->scanner = $scanner ?? new FolderScanner();
        $this->handoffClient = $handoffClient ?? new CoreHandoffClient();
    }

    /**
     * Execute a synchronization run with complete fault isolation.
     *
     * @param array $params [
     *   'source_id'         => string,
     *   'folder_path'       => string,
     *   'last_synced_at'    => int|null (timestamp in seconds),
     *   'previous_manifest' => array<string, string>,
     *   'fail_file_paths'   => array<string> (for simulated testing of faulty files)
     * ]
     * @return array Sync run report
     */
    public function executeRun(array $params): array
    {
        $sourceId = $params['source_id'] ?? 'default-source';
        $folderPath = $params['folder_path'] ?? '';
        $lastSyncedAt = $params['last_synced_at'] ?? 0;
        $previousManifest = $params['previous_manifest'] ?? [];
        $failFiles = $params['fail_file_paths'] ?? [];

        $now = time();

        // 1. Rate Limiting / Debounce check (AC-M7-07)
        if ($lastSyncedAt > 0 && ($now - $lastSyncedAt) < self::DEBOUNCE_INTERVAL_SECONDS) {
            $retryAfter = self::DEBOUNCE_INTERVAL_SECONDS - ($now - $lastSyncedAt);
            return [
                'status'      => 'rate_limited',
                'code'        => 429,
                'message'     => "Sinkronisasi dibatasi. Harap tunggu {$retryAfter} detik sebelum menjalankan ulang.",
                'retry_after' => $retryAfter,
            ];
        }

        // 2. Scan directory
        $scanResult = $this->scanner->scan($folderPath, $previousManifest);

        $runId = 'run-' . bin2hex(random_bytes(8));
        $itemResults = [];
        $counts = [
            'created'   => 0,
            'updated'   => 0,
            'deleted'   => 0,
            'unchanged' => count($scanResult['unchanged']),
            'failed'    => 0,
        ];

        // 3. Process NEW files (Fault isolation: AC-M7-04)
        foreach ($scanResult['new'] as $relPath => $fileInfo) {
            try {
                if (in_array($relPath, $failFiles, true)) {
                    throw new Exception("Simulasi kegagalan I/O pada berkas: {$relPath}");
                }
                $this->handoffClient->dispatchItem([
                    'source_id'     => $sourceId,
                    'relative_path' => $relPath,
                    'sha256_hash'   => $fileInfo['hash'],
                    'action'        => 'create',
                ]);
                $itemResults[] = ['path' => $relPath, 'action' => 'create', 'status' => 'success'];
                $counts['created']++;
            } catch (Exception $e) {
                // Individual failure does NOT abort remaining files!
                $itemResults[] = [
                    'path'   => $relPath,
                    'action' => 'create',
                    'status' => 'failed',
                    'error'  => $e->getMessage(),
                ];
                $counts['failed']++;
            }
        }

        // 4. Process MODIFIED files
        foreach ($scanResult['modified'] as $relPath => $fileInfo) {
            try {
                if (in_array($relPath, $failFiles, true)) {
                    throw new Exception("Simulasi kegagalan transmisi pada berkas: {$relPath}");
                }
                $this->handoffClient->dispatchItem([
                    'source_id'     => $sourceId,
                    'relative_path' => $relPath,
                    'sha256_hash'   => $fileInfo['hash'],
                    'action'        => 'update',
                ]);
                $itemResults[] = ['path' => $relPath, 'action' => 'update', 'status' => 'success'];
                $counts['updated']++;
            } catch (Exception $e) {
                $itemResults[] = [
                    'path'   => $relPath,
                    'action' => 'update',
                    'status' => 'failed',
                    'error'  => $e->getMessage(),
                ];
                $counts['failed']++;
            }
        }

        // 5. Process DELETED files (Marked deleted in Core: AC-M7-03)
        foreach ($scanResult['deleted'] as $relPath => $fileInfo) {
            try {
                $this->handoffClient->dispatchItem([
                    'source_id'     => $sourceId,
                    'relative_path' => $relPath,
                    'sha256_hash'   => $fileInfo['old_hash'],
                    'action'        => 'delete',
                ]);
                $itemResults[] = ['path' => $relPath, 'action' => 'delete', 'status' => 'success'];
                $counts['deleted']++;
            } catch (Exception $e) {
                $itemResults[] = [
                    'path'   => $relPath,
                    'action' => 'delete',
                    'status' => 'failed',
                    'error'  => $e->getMessage(),
                ];
                $counts['failed']++;
            }
        }

        // 6. Record UNCHANGED files
        foreach ($scanResult['unchanged'] as $relPath => $fileInfo) {
            $itemResults[] = ['path' => $relPath, 'action' => 'none', 'status' => 'skipped'];
        }

        // 7. Assemble sync report (AC-M7-02, AC-M7-06)
        return [
            'status'           => 'completed',
            'run_id'           => $runId,
            'source_id'        => $sourceId,
            'started_at'       => gmdate('Y-m-d\TH:i:s\Z', $now),
            'completed_at'     => gmdate('Y-m-d\TH:i:s\Z'),
            'summary'          => [
                'total_scanned' => $scanResult['scanned_count'],
                'created'       => $counts['created'],
                'updated'       => $counts['updated'],
                'deleted'       => $counts['deleted'],
                'unchanged'     => $counts['unchanged'],
                'failed'        => $counts['failed'],
            ],
            'items'            => $itemResults,
            'updated_manifest' => $scanResult['current_manifest'],
        ];
    }
}
