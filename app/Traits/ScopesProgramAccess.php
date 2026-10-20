<?php

namespace App\Traits;

use App\Models\Program;

trait ScopesProgramAccess
{
    /**
     * Program names the signed-in user may view, or an empty array when the
     * account is unrestricted (administrators and accounts with no program).
     */
    protected function viewableProgramNames(): array
    {
        $user = auth()->user();

        if (! $user || $user->isAdmin() || ! $user->program_id) {
            return [];
        }

        return Program::whereIn('id', $user->all_programs)
            ->pluck('name')
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Whether the current request is from a program user, whose access must be
     * restricted to their own program's records.
     */
    protected function isProgramScopedViewer(): bool
    {
        return $this->viewableProgramNames() !== [];
    }

    /**
     * Narrow a query to the programs the current user may view. Columns holding
     * a program name differ per model, so the caller passes the column.
     */
    protected function scopeToViewablePrograms($query, string $column)
    {
        $programNames = $this->viewableProgramNames();

        if ($programNames !== []) {
            $query->whereIn($column, $programNames);
        }

        return $query;
    }

    /**
     * Reject a single record that belongs to another program.
     */
    protected function authorizeProgramRecord($record, string $column): void
    {
        $programNames = $this->viewableProgramNames();

        if ($programNames === []) {
            return;
        }

        abort_unless(
            in_array($record->{$column}, $programNames, true),
            403,
            'Your account does not have access to this record.'
        );
    }
}
