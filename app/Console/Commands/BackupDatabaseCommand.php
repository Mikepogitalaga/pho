<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Services\BackupService;
use Illuminate\Console\Command;
use Throwable;

class BackupDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup 
                            {--prefix=scheduled : Prefix for the backup filename}
                            {--retention=14 : Number of days to retain old backups (0 to disable)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a safety backup of the application database and purge old backups';

    /**
     * Execute the console command.
     */
    public function handle(BackupService $backupService): int
    {
        $this->info('Starting database backup...');

        $prefix = $this->option('prefix') ?: 'scheduled';
        $retention = (int) $this->option('retention');

        try {
            $result = $backupService->createBackup($prefix);
            $sizeFormatted = $backupService->formatBytes($result['size']);

            $this->info("✓ Backup created successfully: {$result['filename']} ({$sizeFormatted})");

            // Audit log
            try {
                AuditLog::create([
                    'user_id' => null,
                    'user_name' => 'System Scheduler',
                    'action' => 'backup_scheduled',
                    'module' => 'Database Backup',
                    'record_id' => null,
                    'record_label' => $result['filename'],
                    'changes' => [
                        'type' => $prefix,
                        'size' => $sizeFormatted,
                    ],
                    'ip_address' => '127.0.0.1',
                ]);
            } catch (Throwable) {
                // Continue if audit fails
            }

            // Cleanup old backups if retention is enabled
            if ($retention > 0) {
                $this->info("Checking for backups older than {$retention} days...");
                $deleted = $backupService->cleanupOldBackups($retention);
                if ($deleted > 0) {
                    $this->info("✓ Purged {$deleted} expired backup file(s).");
                } else {
                    $this->info("No expired backups found.");
                }
            }

            return Command::SUCCESS;
        } catch (Throwable $e) {
            $this->error("✗ Backup failed: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
