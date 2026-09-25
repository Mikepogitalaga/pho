<?php

namespace App\Traits;

use Illuminate\Support\Collection;

trait ComputesCodeAvailability
{
    public function codeAvailability(): array
    {
        if (! $this->relationLoaded('receivingItems')) {
            return [];
        }

        $codes = [];

        foreach ($this->receivingItems as $position => $receivingItem) {
            $codes[] = [
                'key'      => (string) ($receivingItem->id ?? $position),
                'code'     => trim((string) ($receivingItem->item_code ?? '')),
                'lot'      => trim((string) ($receivingItem->lot_number ?? '')),
                'received' => (int) ($receivingItem->quantity_received ?? 0),
                'expiry'   => $receivingItem->expiry_date ?? null,
                'deducted' => 0,
            ];
        }

        if ($codes === []) {
            return [];
        }

        $order = $this->firstExpiryFirstOutOrder($codes);

        $claim = function (int $index, int $quantity) use (&$codes): int {
            $remaining = max(0, $codes[$index]['received'] - $codes[$index]['deducted']);
            $taken = min($quantity, $remaining);

            $codes[$index]['deducted'] += $taken;

            return $quantity - $taken;
        };

        $find = function (callable $matches) use ($codes, $order): ?int {
            foreach ($order as $index) {
                if ($matches($codes[$index])) {
                    return $index;
                }
            }

            return null;
        };

        $unattributed = [];

        foreach ($this->activeReleaseItems() as $releaseItem) {
            $quantity = (int) ($releaseItem->quantity_released ?? 0);

            if ($quantity <= 0) {
                continue;
            }

            $itemCode = trim((string) ($releaseItem->item_code ?? ''));
            $lotNumber = trim((string) ($releaseItem->lot_number ?? ''));

            $index = $itemCode !== ''
                ? $find(fn (array $code): bool => $code['code'] !== '' && strcasecmp($code['code'], $itemCode) === 0)
                : null;

            if ($index === null && $lotNumber !== '') {
                $index = $find(fn (array $code): bool => $code['lot'] !== '' && strcasecmp($code['lot'], $lotNumber) === 0);
            }

            if ($index === null && $itemCode === '' && $lotNumber === '') {
                $index = $find(fn (array $code): bool => $code['lot'] === '');
            }

            if ($index === null) {
                $unattributed[] = $quantity;

                continue;
            }

            $leftover = $claim($index, $quantity);

            if ($leftover > 0) {
                $unattributed[] = $leftover;
            }
        }

        foreach ($unattributed as $quantity) {
            foreach ($order as $index) {
                if ($quantity <= 0) {
                    break;
                }

                $quantity = $claim($index, $quantity);
            }
        }

        $availability = [];

        foreach ($codes as $code) {
            $availability[$code['key']] = max(0, $code['received'] - $code['deducted']);
        }

        return $availability;
    }

    public function attachCodeAvailability(): static
    {
        if (! $this->relationLoaded('receivingItems')) {
            return $this;
        }

        $availability = $this->codeAvailability();

        foreach ($this->receivingItems->values() as $position => $receivingItem) {
            $key = (string) ($receivingItem->id ?? $position);

            $receivingItem->available_quantity = $availability[$key] ?? 0;
        }

        return $this;
    }

    protected function activeReleaseItems(): Collection
    {
        if (! $this->relationLoaded('releaseItems')) {
            return collect();
        }

        return $this->releaseItems
            ->filter(fn ($releaseItem) => ! in_array($releaseItem->release?->status ?? '', ['Canceled', 'Returned'], true))
            ->values();
    }

    protected function firstExpiryFirstOutOrder(array $codes): array
    {
        $order = array_keys($codes);

        usort($order, function (int $a, int $b) use ($codes): int {
            $expiryA = $codes[$a]['expiry'] ?? null;
            $expiryB = $codes[$b]['expiry'] ?? null;

            if ($expiryA && $expiryB && $expiryA != $expiryB) {
                return $expiryA < $expiryB ? -1 : 1;
            }

            if ($expiryA && ! $expiryB) {
                return -1;
            }

            if (! $expiryA && $expiryB) {
                return 1;
            }

            return strnatcasecmp($codes[$a]['key'], $codes[$b]['key']);
        });

        return $order;
    }
}
