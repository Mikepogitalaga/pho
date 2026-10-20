<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Services\BackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class BackupController extends Controller
{
    public function __construct(
        protected BackupService $backupService
    ) {
        abort_unless(auth()->user()?->role === 'admin', 403);
    }

    /**
     * Display backup dashboard with list of backups.
     */
    public function index()
    {
        $backups = $this->backupService->getBackups();
        $totalStorage = $this->backupService->formatBytes($this->backupService->getTotalStorageSize());
        $latestBackup = $backups->first();

        return view('backups.index', compact('backups', 'totalStorage', 'latestBackup'));
    }

    /**
     * Create a manual database backup now.
     */
    public function store()
    {
        try {
            $result = $this->backupService->createBackup('manual');

            $this->logAudit('backup_created', $result['filename'], [
                'type' => 'manual',
                'size' => $this->backupService->formatBytes($result['size']),
            ]);

            return redirect()->route('backups.index')->with('success', "Database backup created successfully: {$result['filename']}");
        } catch (Throwable $e) {
            return redirect()->route('backups.index')->with('error', "Backup failed: " . $e->getMessage());
        }
    }

    /**
     * Securely download a backup file.
     */
    public function download(string $filename)
    {
        $filename = basename($filename);
        $filePath = $this->backupService->getStoragePath() . DIRECTORY_SEPARATOR . $filename;

        if (!file_exists($filePath)) {
            return redirect()->route('backups.index')->with('error', 'Backup file not found.');
        }

        $this->logAudit('backup_downloaded', $filename, [
            'size' => $this->backupService->formatBytes(filesize($filePath)),
        ]);

        return response()->download($filePath, $filename, [
            'Content-Type' => 'application/sql',
        ]);
    }

    /**
     * Restore database from an existing backup in storage with safety snapshot.
     */
    public function restore(Request $request, string $filename)
    {
        $request->validate([
            'confirmation' => ['required', 'string', 'in:RESTORE'],
        ], [
            'confirmation.in' => 'You must type "RESTORE" exactly to confirm database restoration.',
        ]);

        try {
            $result = $this->backupService->restoreBackup($filename, true);

            $this->logAudit('backup_restored', $filename, [
                'pre_restore_snapshot' => $result['snapshot'],
            ]);

            $message = "Database successfully restored from [{$filename}].";
            if ($result['snapshot']) {
                $message .= " A pre-restore safety snapshot was saved as [{$result['snapshot']}].";
            }

            return redirect()->route('backups.index')->with('success', $message);
        } catch (Throwable $e) {
            return redirect()->route('backups.index')->with('error', "Restore failed: " . $e->getMessage());
        }
    }

    /**
     * Upload an external SQL file and restore it with safety snapshot.
     */
    public function uploadRestore(Request $request)
    {
        $request->validate([
            'backup_file' => ['required', 'file', 'max:102400'], // max 100MB
            'upload_confirmation' => ['required', 'string', 'in:RESTORE'],
        ], [
            'backup_file.required' => 'Please select a .sql backup file to upload.',
            'upload_confirmation.in' => 'You must type "RESTORE" exactly to confirm database restoration.',
        ]);

        try {
            $uploadedFile = $request->file('backup_file');
            $result = $this->backupService->restoreFromUpload($uploadedFile, true);

            $this->logAudit('backup_upload_restored', $result['restored_file'], [
                'pre_restore_snapshot' => $result['snapshot'],
            ]);

            $message = "Database successfully restored from uploaded file [{$result['restored_file']}].";
            if ($result['snapshot']) {
                $message .= " A pre-restore safety snapshot was saved as [{$result['snapshot']}].";
            }

            return redirect()->route('backups.index')->with('success', $message);
        } catch (Throwable $e) {
            return redirect()->route('backups.index')->with('error', "Upload and restore failed: " . $e->getMessage());
        }
    }

    /**
     * Delete a backup file.
     */
    public function destroy(string $filename)
    {
        $filename = basename($filename);

        try {
            if ($this->backupService->deleteBackup($filename)) {
                $this->logAudit('backup_deleted', $filename);
                return redirect()->route('backups.index')->with('success', "Backup [{$filename}] was deleted.");
            }

            return redirect()->route('backups.index')->with('error', 'File could not be found or deleted.');
        } catch (Throwable $e) {
            return redirect()->route('backups.index')->with('error', "Deletion error: " . $e->getMessage());
        }
    }

    /**
     * Record backup operations to the system AuditLog.
     */
    protected function logAudit(string $action, string $recordLabel, ?array $changes = null): void
    {
        try {
            AuditLog::create([
                'user_id' => Auth::id(),
                'user_name' => Auth::user()?->name ?? 'Administrator',
                'action' => $action,
                'module' => 'Database Backup',
                'record_id' => null,
                'record_label' => $recordLabel,
                'changes' => $changes,
                'ip_address' => request()->ip(),
            ]);
        } catch (Throwable) {
            // Never break backup workflow due to audit logging error
        }
    }
}
