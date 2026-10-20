<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Symfony\Component\Process\Process;

class BackupService
{
    protected string $storagePath;

    public function __construct()
    {
        $this->storagePath = storage_path('app/backups');
        if (!File::exists($this->storagePath)) {
            File::makeDirectory($this->storagePath, 0755, true);
        }
    }

    /**
     * Get the absolute storage path for backups.
     */
    public function getStoragePath(): string
    {
        return $this->storagePath;
    }

    /**
     * Resolve the mysqldump binary path.
     */
    public function getMysqldumpBinary(): string
    {
        // 1. Explicitly configured path in env
        $configured = env('MYSQL_DUMP_PATH');
        if ($configured && File::exists($configured)) {
            return $configured;
        }

        // 2. Windows XAMPP standard path
        $xamppPath = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';
        if (File::exists($xamppPath)) {
            return $xamppPath;
        }

        // 3. Fallback to system PATH
        return 'mysqldump';
    }

    /**
     * Resolve the mysql binary path.
     */
    public function getMysqlBinary(): string
    {
        // 1. Explicitly configured path in env
        $configured = env('MYSQL_PATH');
        if ($configured && File::exists($configured)) {
            return $configured;
        }

        // 2. Windows XAMPP standard path
        $xamppPath = 'C:\\xampp\\mysql\\bin\\mysql.exe';
        if (File::exists($xamppPath)) {
            return $xamppPath;
        }

        // 3. Fallback to system PATH
        return 'mysql';
    }

    /**
     * Validate that a resolved path is safely contained within the backup directory.
     */
    protected function assertPathInBackupDir(string $resolvedPath, string $originalFilename): void
    {
        $realBackup = realpath($this->storagePath);
        $realTarget = realpath($resolvedPath);

        if ($realBackup === false || $realTarget === false || str_starts_with($realTarget, $realBackup) !== true) {
            throw new RuntimeException("Invalid backup path requested: {$originalFilename}");
        }
    }

    /**
     * Create a database backup.
     *
     * @param string|null $prefix Optional prefix for filename (e.g. 'pre-restore-snapshot')
     * @return array{filename: string, full_path: string, size: int}
     */
    public function createBackup(?string $prefix = null): array
    {
        $dbConfig = config('database.connections.mysql');
        $database = $dbConfig['database'] ?? env('DB_DATABASE', 'pho-supply_inventory');
        $username = $dbConfig['username'] ?? env('DB_USERNAME', 'root');
        $password = $dbConfig['password'] ?? env('DB_PASSWORD', '');
        $host = $dbConfig['host'] ?? env('DB_HOST', '127.0.0.1');
        $port = $dbConfig['port'] ?? env('DB_PORT', '3306');

        $prefix = $prefix ? rtrim($prefix, '-_') . '-' : 'backup-';
        $filename = $prefix . date('Y-m-d_H-i-s') . '.sql';
        $fullPath = $this->storagePath . DIRECTORY_SEPARATOR . $filename;

        $command = [
            $this->getMysqldumpBinary(),
            '--user=' . $username,
            '--host=' . $host,
            '--port=' . $port,
            '--result-file=' . $fullPath,
            '--single-transaction',
            '--quick',
            '--routines',
            '--triggers',
        ];

        if (!empty($password)) {
            $command[] = '--password=' . $password;
        }

        $command[] = $database;

        $process = new Process($command);
        $process->setTimeout(300);
        $process->run();

        if (!$process->isSuccessful() || !File::exists($fullPath) || File::size($fullPath) === 0) {
            $error = $process->getErrorOutput() ?: $process->getOutput();
            if (File::exists($fullPath)) {
                File::delete($fullPath);
            }
            throw new RuntimeException("Backup failed: " . ($error ?: "Unknown error while creating backup file."));
        }

        return [
            'filename' => $filename,
            'full_path' => $fullPath,
            'size' => File::size($fullPath),
        ];
    }

    /**
     * Restore database from a backup file in storage.
     *
     * @param string $filename
     * @param bool $createSafetySnapshot
     * @return array{snapshot: ?string, restored_file: string}
     */
    public function restoreBackup(string $filename, bool $createSafetySnapshot = true): array
    {
        $filename = basename($filename); // Prevent path traversal
        $filePath = $this->storagePath . DIRECTORY_SEPARATOR . $filename;
        $this->assertPathInBackupDir($filePath, $filename);

        if (!File::exists($filePath)) {
            throw new RuntimeException("Backup file [{$filename}] not found.");
        }

        $snapshotFile = null;
        if ($createSafetySnapshot) {
            $snapshot = $this->createBackup('pre-restore-snapshot');
            $snapshotFile = $snapshot['filename'];
        }

        $this->executeSqlFile($filePath);

        return [
            'snapshot' => $snapshotFile,
            'restored_file' => $filename,
        ];
    }

    /**
     * Restore database from an uploaded SQL file.
     *
     * @param UploadedFile $file
     * @param bool $createSafetySnapshot
     * @return array{snapshot: ?string, restored_file: string}
     */
    public function restoreFromUpload(UploadedFile $file, bool $createSafetySnapshot = true): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        if ($extension !== 'sql') {
            throw new RuntimeException('Only valid .sql backup files can be restored.');
        }

        $mimeType = strtolower($file->getMimeType() ?? '');
        $allowedMimes = ['text/plain', 'application/sql', 'application/octet-stream'];
        if (!in_array($mimeType, $allowedMimes, true)) {
            throw new RuntimeException('Invalid file type. Only plain text SQL files are allowed.');
        }

        if ($file->getSize() === 0) {
            throw new RuntimeException('The uploaded file is empty.');
        }

        // Store file with safe name
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeName = 'upload-' . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $originalName) . '-' . date('Y-m-d_H-i-s') . '.sql';
        $file->move($this->storagePath, $safeName);

        $savedPath = $this->storagePath . DIRECTORY_SEPARATOR . $safeName;

        return $this->restoreBackup($safeName, $createSafetySnapshot);
    }

    /**
     * Execute SQL file into current MySQL database.
     */
    protected function executeSqlFile(string $filePath): void
    {
        $dbConfig = config('database.connections.mysql');
        $database = $dbConfig['database'] ?? env('DB_DATABASE', 'pho-supply_inventory');
        $username = $dbConfig['username'] ?? env('DB_USERNAME', 'root');
        $password = $dbConfig['password'] ?? env('DB_PASSWORD', '');
        $host = $dbConfig['host'] ?? env('DB_HOST', '127.0.0.1');
        $port = $dbConfig['port'] ?? env('DB_PORT', '3306');

        // Using standard forward slashes for MySQL source path
        $normalizedPath = str_replace('\\', '/', $filePath);

        $command = [
            $this->getMysqlBinary(),
            '--user=' . $username,
            '--host=' . $host,
            '--port=' . $port,
            '--default-character-set=utf8mb4',
            '-e',
            "source {$normalizedPath}",
        ];

        if (!empty($password)) {
            $command[] = '--password=' . $password;
        }

        $command[] = $database;

        $process = new Process($command);
        $process->setTimeout(600); // 10 minutes max for large imports
        $process->run();

        if (!$process->isSuccessful()) {
            $error = $process->getErrorOutput() ?: $process->getOutput();
            throw new RuntimeException("Database restore failed: " . ($error ?: "Unknown error during SQL import."));
        }
    }

    /**
     * Get collection of all available backups.
     *
     * @return Collection
     */
    public function getBackups(): Collection
    {
        if (!File::exists($this->storagePath)) {
            return collect();
        }

        $files = File::files($this->storagePath);

        return collect($files)
            ->filter(fn($file) => $file->getExtension() === 'sql')
            ->map(function ($file) {
                $filename = $file->getFilename();
                $isSnapshot = str_starts_with($filename, 'pre-restore-snapshot');
                $isUpload = str_starts_with($filename, 'upload-');
                $isScheduled = str_starts_with($filename, 'scheduled-');

                $type = 'manual';
                if ($isSnapshot) {
                    $type = 'snapshot';
                } elseif ($isUpload) {
                    $type = 'upload';
                } elseif ($isScheduled) {
                    $type = 'scheduled';
                }

                return [
                    'filename' => $filename,
                    'path' => $file->getPathname(),
                    'size' => $file->getSize(),
                    'size_formatted' => $this->formatBytes($file->getSize()),
                    'created_at' => $file->getMTime(),
                    'date_formatted' => date('M d, Y h:i A', $file->getMTime()),
                    'type' => $type,
                    'is_snapshot' => $isSnapshot,
                ];
            })
            ->sortByDesc('created_at')
            ->values();
    }

    /**
     * Delete a backup file.
     */
    public function deleteBackup(string $filename): bool
    {
        $filename = basename($filename);
        $fullPath = $this->storagePath . DIRECTORY_SEPARATOR . $filename;
        $this->assertPathInBackupDir($fullPath, $filename);

        if (File::exists($fullPath)) {
            return File::delete($fullPath);
        }

        return false;
    }

    /**
     * Purge old backups using type-aware retention rules.
     *
     * - manual: retained until manually deleted
     * - scheduled: retained for $scheduledRetention days
     * - snapshot: retained for $snapshotRetention days
     * - upload: retained until manually deleted
     */
    public function cleanupOldBackups(
        int $scheduledRetention = 14,
        int $snapshotRetention = 14
    ): int {
        $cutoffScheduled = time() - ($scheduledRetention * 86400);
        $cutoffSnapshot = time() - ($snapshotRetention * 86400);
        $deleted = 0;

        foreach ($this->getBackups() as $backup) {
            $filename = $backup['filename'];
            $createdAt = $backup['created_at'];
            $type = $backup['type'];

            $shouldDelete = match ($type) {
                'scheduled' => $createdAt < $cutoffScheduled,
                'snapshot' => $createdAt < $cutoffSnapshot,
                default => false,
            };

            if ($shouldDelete && $this->deleteBackup($filename)) {
                $deleted++;
            }
        }

        return $deleted;
    }

    /**
     * Calculate total disk storage used by backups.
     */
    public function getTotalStorageSize(): int
    {
        return (int) $this->getBackups()->sum('size');
    }

    /**
     * Format bytes to human readable format.
     */
    public function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
