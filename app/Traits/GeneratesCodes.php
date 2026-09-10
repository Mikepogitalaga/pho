<?php

namespace App\Traits;

trait GeneratesCodes
{
    /**
     * Compute the next zero-padded sequence for a year/month scoped code.
     *
     * The sequence is the trailing digits of the code and resets to "0001"
     * at the start of every year because the year is already part of the
     * supplied $likePattern (e.g. "PAS-2026-08-%" or "PC2608%").
     */
    protected function nextYearSequence(string $modelClass, string $column, string $likePattern, int $pad = 4): string
    {
        $last = $modelClass::where($column, 'like', $likePattern)
            ->orderByRaw("LENGTH({$column}) DESC, {$column} DESC")
            ->value($column);

        $next = 1;
        if ($last && preg_match('/(\d+)$/', (string) $last, $matches)) {
            $next = (int) $matches[1] + 1;
        }

        return str_pad((string) $next, $pad, '0', STR_PAD_LEFT);
    }
}
