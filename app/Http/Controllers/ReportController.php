<?php

namespace App\Http\Controllers;

use App\Exports\LiquidationExport;
use App\Exports\MasterInventoryExport;
use App\Models\Item;
use App\Models\Release;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function liquidation(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);

        if ($perPage <= 0) {
            $perPage = PHP_INT_MAX;
        }

        $releases = $this->liquidationQuery($request)->paginate($perPage)->withQueryString();

        $totalQuantity = 0;
        $totalCost = 0;

        foreach ($releases as $release) {
            foreach ($release->items as $item) {
                $totalQuantity += (int) $item->quantity_released;
                $totalCost += ((float) $item->unit_cost ?? 0) * (int) $item->quantity_released;
            }
        }

        $categoriesWithLiquidations = \App\Models\ReleaseItem::whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('reports.liquidation', compact('releases', 'totalQuantity', 'totalCost', 'categoriesWithLiquidations'));
    }

    public function export(Request $request)
    {
        $query = $this->liquidationQuery($request);

        if ($releaseId = $request->query('release')) {
            $query->where('releases.id', $releaseId);
        }

        $releases = $query->get();

        $ptrFilter = trim((string) $request->input('ptr_number', ''));
        $releaseId = $request->query('release');
        $category = trim((string) $request->input('category', ''));

        if ($ptrFilter !== '') {
            $fileName = 'Liquidation_Report_' . preg_replace('/[^A-Za-z0-9\-]/', '_', $ptrFilter) . '.xlsx';
        } elseif ($releaseId) {
            $release = Release::find($releaseId);
            $ptr = $release ? ($release->ptr_itr_ris_no ?? $release->release_number ?? 'liquidation') : 'liquidation';
            $fileName = 'Liquidation_Report_' . preg_replace('/[^A-Za-z0-9\-]/', '_', (string) $ptr) . '.xlsx';
        } elseif ($category !== '') {
            $fileName = 'Liquidation_Report_' . preg_replace('/[^A-Za-z0-9\-]/', '_', $category) . '.xlsx';
        } else {
            $fileName = 'Liquidation_Report_' . now()->format('Y-m-d-His') . '.xlsx';
        }

        return Excel::download(new LiquidationExport($releases, $category !== '' ? $category : null), $fileName);
    }

    public function masterFile(Request $request)
    {
        [$rows, $summary] = $this->masterInventoryRows($request);
        $category = trim((string) $request->query('category', ''));
        $month = (int) $request->query('month', 0);
        $month = $month >= 1 && $month <= 12 ? $month : 0;
        $year = (int) $request->query('year', 0);
        $year = $year >= 2000 && $year <= 2099 ? $year : (int) date('Y');

        return view('reports.master-file', compact('rows', 'summary', 'category', 'month', 'year'));
    }

    public function masterFileExport(Request $request)
    {
        [$rows, $summary] = $this->masterInventoryRows($request);

        return Excel::download(
            new MasterInventoryExport($rows, $summary),
            'Master_Inventory_File_' . now()->format('Y-m-d-His') . '.xlsx'
        );
    }

    private function masterInventoryRows(Request $request): array
    {
        $category = trim((string) $request->query('category', ''));
        $month = (int) $request->query('month', 0);
        $month = $month >= 1 && $month <= 12 ? $month : 0;
        $year = (int) $request->query('year', 0);
        $year = $year >= 2000 && $year <= 2099 ? $year : (int) date('Y');
        $items = Item::with(['receivingItems.receiving.supplier', 'releaseItems' => function ($q) {
                $q->whereHas('release', fn ($r) => $r->whereIn('status', ['Released', 'Released through pass']));
            }, 'releaseItems.release'])
            ->where(function ($query) {
                $query->whereHas('receivingItems')
                    ->orWhereHas('releaseItems.release', function ($releaseQuery) {
                        $releaseQuery->whereIn('status', ['Released', 'Released through pass']);
                    });
            })
            ->orderBy('name')
            ->get();

        $rows = $items->groupBy(function (Item $item) {
            return strtolower(trim((string) $item->name));
        })->map(function ($itemGroup) use ($category, $month, $year) {
            $item = $itemGroup->first();
            $allReceivingItems = $itemGroup->flatMap(fn (Item $groupItem) => $groupItem->receivingItems)
                ->filter(fn ($line) => $this->masterLineMatches($line, 'receiving', $item, $category, 0, $year));
            $allReleaseItems = $itemGroup->flatMap(fn (Item $groupItem) => $groupItem->releaseItems)
                ->filter(fn ($line) => $this->masterLineMatches($line, 'release', $item, $category, 0, $year));
            $receivingItems = $allReceivingItems->filter(fn ($line) => $this->masterLineMatches($line, 'receiving', $item, '', $month, $year));
            $releaseItems = $allReleaseItems->filter(fn ($line) => $this->masterLineMatches($line, 'release', $item, '', $month, $year));
            $purchases = $receivingItems->sum('quantity_received');
            $purchaseCost = $receivingItems->sum(fn ($line) => (float) ($line->unit_cost ?? 0) * (int) $line->quantity_received);
            $disposals = $releaseItems->sum('quantity_released');
            $expired = $receivingItems->filter(fn ($line) => $line->expiry_date && $line->expiry_date->isPast())->sum('quantity_received');
            $currentStock = $itemGroup->sum(fn (Item $groupItem) => (int) $groupItem->quantity_on_hand);
            $receivingAfterPeriod = $this->masterLinesAfterMonth($allReceivingItems, 'receiving', $month, $year);
            $releaseAfterPeriod = $this->masterLinesAfterMonth($allReleaseItems, 'release', $month, $year);
            $expiredAfterPeriod = $allReceivingItems->filter(fn ($line) => $line->expiry_date
                && $line->expiry_date->isPast()
                && $this->dateIsAfterMonth($line->expiry_date, $month, $year)
            )->sum('quantity_received');
            $available = $currentStock - $this->masterLinesAfterMonth($allReceivingItems, 'receiving', $month, $year)->sum('quantity_received') + $this->masterLinesAfterMonth($allReleaseItems, 'release', $month, $year)->sum('quantity_released') + $expiredAfterPeriod;
            if ($month === 0) {
                $available = $currentStock + $allReleaseItems->sum('quantity_released') + $allReceivingItems->filter(fn ($line) => $line->expiry_date && $line->expiry_date->isPast())->sum('quantity_received');
                $beginning = $available - (int) $purchases;
            } else {
                $beginning = $available - (int) $purchases + (int) $disposals + (int) $expired;
            }
            $available = $beginning + (int) $purchases;
            $ending = $available - (int) $disposals - (int) $expired;
            $averageCost = $purchases > 0 ? $purchaseCost / $purchases : (float) ($item->unit_cost ?? 0);
            $productCodes = $allReceivingItems->pluck('item_code')->filter()->unique()->values();
            $purchaseSegments = ['GSO' => 0, 'ACP' => 0, 'DOH' => 0];
            $purchaseSegmentCosts = ['GSO' => 0, 'ACP' => 0, 'DOH' => 0];
            foreach ($receivingItems as $line) {
                $supplierType = strtoupper(trim((string) ($line->receiving?->supplier?->supplier_type ?? '')));
                $segment = array_key_exists($supplierType, $purchaseSegments) ? $supplierType : 'GSO';
                $purchaseSegments[$segment] += (int) $line->quantity_received;
                $purchaseSegmentCosts[$segment] += (float) ($line->unit_cost ?? 0) * (int) $line->quantity_received;
            }
            $disposalSegments = ['Implementing' => 0, 'Hospitals' => 0, 'RHU' => 0, 'NLA' => 0];
            $disposalCost = 0;
            foreach ($releaseItems as $line) {
                $segment = trim((string) ($line->release?->facility_category ?? '')) ?: 'Implementing';
                $segment = $segment === 'PHO Clinic' ? 'Implementing' : $segment;
                if (array_key_exists($segment, $disposalSegments)) {
                    $disposalSegments[$segment] += (int) $line->quantity_released;
                }
                $disposalCost += (float) ($line->unit_cost ?? 0) * (int) $line->quantity_released;
            }
            $beginningCost = (float) $beginning * $averageCost;
            $availableCost = (float) $available * $averageCost;
            $availableAverageCost = $available > 0 ? $availableCost / $available : ($averageCost > 0 ? $averageCost : 0);
            $expiredCost = (float) $expired * $averageCost;
            $adjustedEnding = $ending;

            return [
                'code' => $productCodes->implode(', '), 'name' => $item->name, 'category' => $item->category,
                'unit' => $item->unit ?: $item->display_unit,
                'unit_cost' => (float) ($item->unit_cost ?? $averageCost), 'beginning_qty' => $beginning, 'beginning_cost' => $beginningCost,
                'purchase_gso_qty' => $purchaseSegments['GSO'], 'purchase_gso_cost' => $purchaseSegments['GSO'] ? $purchaseSegmentCosts['GSO'] / $purchaseSegments['GSO'] : 0,
                'purchase_acp_qty' => $purchaseSegments['ACP'], 'purchase_acp_cost' => $purchaseSegments['ACP'] ? $purchaseSegmentCosts['ACP'] / $purchaseSegments['ACP'] : 0,
                'purchase_doh_qty' => $purchaseSegments['DOH'], 'purchase_doh_cost' => $purchaseSegments['DOH'] ? $purchaseSegmentCosts['DOH'] / $purchaseSegments['DOH'] : 0,
                'available_qty' => $available, 'available_cost' => $availableCost, 'available_average_cost' => $availableAverageCost,
                'disposal_implementing' => $disposalSegments['Implementing'], 'disposal_hospitals' => $disposalSegments['Hospitals'], 'disposal_rhu' => $disposalSegments['RHU'], 'disposal_nla' => $disposalSegments['NLA'], 'disposal_cost' => $disposalCost,
                'expired_cost' => $expiredCost, 'ending_qty' => $ending, 'ending_cost' => (float) $ending * $averageCost,
                'average_cost' => $averageCost, 'check_balance' => (float) $ending * $averageCost,
                'additional_count' => 0, 'adjusted_ending' => $adjustedEnding, 'adjusted_amount' => $adjustedEnding * $averageCost,
                '_include' => $receivingItems->isNotEmpty() || $releaseItems->isNotEmpty(),
            ];
        })->filter(fn (array $row) => $row['_include'])->map(function (array $row) {
            unset($row['_include']);
            return $row;
        })->values()->all();

        $summary = [
            'beginning' => array_sum(array_column($rows, 'beginning_qty')), 'purchases' => array_sum(array_column($rows, 'purchase_gso_qty')) + array_sum(array_column($rows, 'purchase_acp_qty')) + array_sum(array_column($rows, 'purchase_doh_qty')),
            'available' => array_sum(array_column($rows, 'available_qty')), 'disposals' => array_sum(array_column($rows, 'disposal_implementing')) + array_sum(array_column($rows, 'disposal_hospitals')) + array_sum(array_column($rows, 'disposal_rhu')) + array_sum(array_column($rows, 'disposal_nla')),
            'expired' => array_sum(array_column($rows, 'expired_cost')), 'ending' => array_sum(array_column($rows, 'ending_qty')),
            'adjusted_ending' => array_sum(array_column($rows, 'adjusted_ending')), 'total_cost' => array_sum(array_column($rows, 'adjusted_amount')),
            'average_cost' => count($rows) ? array_sum(array_column($rows, 'average_cost')) / count($rows) : 0,
            'check_balance' => array_sum(array_column($rows, 'check_balance')),
        ];

        return [$rows, $summary];
    }

    private function masterLineMatches($line, string $type, Item $item, string $category, int $month, int $year): bool
    {
        if ($type === 'release' && !in_array($line->release?->status, ['Released', 'Released through pass'], true)) {
            return false;
        }

        $categoryMatches = $category === ''
            || strcasecmp((string) $item->category, $category) === 0
            || strcasecmp((string) $line->category, $category) === 0;

        if (!$categoryMatches) {
            return false;
        }

        if ($month === 0) {
            return true;
        }

        $date = $type === 'receiving' ? $line->receiving?->date_received : $line->release?->date_released;

        if (!$date instanceof \DateTimeInterface) {
            return false;
        }

        return (int) $date->format('Y') === $year && (int) $date->format('m') === $month;
    }

    private function masterLinesAfterMonth($lines, string $type, int $month, int $year)
    {
        if ($month === 0) {
            return collect();
        }

        return $lines->filter(function ($line) use ($type, $month, $year) {
            $date = $type === 'receiving' ? $line->receiving?->date_received : $line->release?->date_released;

            if (!$date instanceof \DateTimeInterface) {
                return false;
            }

            return (int) $date->format('Y') === $year && (int) $date->format('m') > $month;
        });
    }

    private function dateIsAfterMonth($date, int $month, int $year): bool
    {
        if (!$date instanceof \DateTimeInterface) {
            return false;
        }

        return $month > 0 && (int) $date->format('Y') === $year && (int) $date->format('m') > $month;
    }

    private function liquidationQuery(Request $request)
    {
        $query = Release::query()
            ->with('items.item')
            ->whereIn('status', ['Released', 'Released through pass'])
            ->latest('date_released');

        $ptrNumber = trim((string) $request->input('ptr_number', ''));
        $facility = trim((string) $request->input('facility', ''));
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $itemDescription = trim((string) $request->input('item_description', ''));
        $category = trim((string) $request->input('category', ''));

        if ($ptrNumber !== '') {
            $query->where(function ($q) use ($ptrNumber) {
                $q->where('ptr_itr_ris_no', 'like', '%' . $ptrNumber . '%')
                  ->orWhere('release_number', 'like', '%' . $ptrNumber . '%');
            });
        }

        if ($facility !== '') {
            $query->where('facility_name', 'like', '%' . $facility . '%');
        }

        if ($startDate) {
            $query->whereDate('date_released', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('date_released', '<=', $endDate);
        }

        if ($itemDescription !== '') {
            $query->whereHas('items', function ($q) use ($itemDescription) {
                $q->where('item_description', 'like', '%' . $itemDescription . '%');
            });
        }

        if ($category !== '') {
            $query->whereHas('items', function ($q) use ($category) {
                $q->where('category', $category);
            });
        }

        return $query;
    }
}
