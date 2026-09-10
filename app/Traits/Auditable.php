<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            self::writeAudit($model, 'created', []);
        });

        static::updated(function ($model) {
            $skip    = ['updated_at', 'created_at'];
            $changes = [];
            foreach ($model->getDirty() as $field => $newValue) {
                if (in_array($field, $skip, true)) continue;
                $changes[$field] = [
                    'old' => $model->getOriginal($field),
                    'new' => $newValue,
                ];
            }
            if (!empty($changes)) {
                self::writeAudit($model, 'updated', $changes);
            }
        });

        static::deleted(function ($model) {
            self::writeAudit($model, 'deleted', []);
        });
    }

    private static function writeAudit($model, string $action, array $changes): void
    {
        try {
            $user = Auth::user();
            AuditLog::create([
                'user_id'      => $user?->id,
                'user_name'    => $user?->name ?? 'System',
                'action'       => $action,
                'module'       => class_basename($model),
                'record_id'    => $model->getKey(),
                'record_label' => self::resolveLabel($model),
                'changes'      => empty($changes) ? null : $changes,
                'ip_address'   => Request::ip(),
            ]);
        } catch (\Throwable) {
            // Never break the main transaction due to audit failure
        }
    }

    private static function resolveLabel($model): string
    {
        return $model->receiving_number
            ?? $model->release_number
            ?? $model->pas_number
            ?? $model->item_code
            ?? $model->name
            ?? $model->company_name
            ?? ((string) $model->getKey());
    }
}
