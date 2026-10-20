<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pas_items', function (Blueprint $table) {
            $table->string('item_code')->nullable()->after('item_id');
        });

        $this->repairRunawayProductCodes();
        $this->backfillItemCodes();
    }

    public function down(): void
    {
        Schema::table('pas_items', function (Blueprint $table) {
            $table->dropColumn('item_code');
        });
    }

    /**
     * Older builds built the PC code from every trailing digit of the last
     * code, so the year+month part got concatenated into the sequence and
     * grew on every save (e.g. "PC26102610261026100004"). Rebuild them as
     * PC + yy + mm + the trailing 4-digit sequence.
     */
    protected function repairRunawayProductCodes(): void
    {
        DB::table('pas_items')
            ->where('product_code', 'like', 'PC%')
            ->orderBy('id')
            ->get(['id', 'product_code'])
            ->each(function ($row) {
                $code = (string) $row->product_code;

                if (strlen($code) < 6) {
                    return;
                }

                $yearMonth = substr($code, 2, 4);
                $sequence = substr($code, -4);

                if (! ctype_digit($yearMonth) || ! ctype_digit($sequence)) {
                    return;
                }

                $normalized = 'PC' . $yearMonth . $sequence;

                if ($normalized !== $code) {
                    DB::table('pas_items')->where('id', $row->id)->update(['product_code' => $normalized]);
                }
            });
    }

    /**
     * Link the item code stored on the receiving items so existing PAS rows
     * show the code of the item they were created from. Rows without an
     * item link are matched through their description.
     */
    protected function backfillItemCodes(): void
    {
        $receivingItems = DB::table('receiving_items')
            ->whereNotNull('item_code')
            ->where('item_code', '<>', '')
            ->get(['item_id', 'item_code', 'lot_number']);

        $codesByItem = [];
        $codesByLot = [];

        foreach ($receivingItems as $receivingItem) {
            $codesByItem[$receivingItem->item_id][] = $receivingItem->item_code;

            $lot = trim((string) $receivingItem->lot_number);

            if ($lot !== '') {
                $codesByLot[$receivingItem->item_id][$lot][] = $receivingItem->item_code;
            }
        }

        if ($codesByItem === []) {
            return;
        }

        DB::table('pas_items')
            ->whereNull('item_code')
            ->orderBy('id')
            ->get(['id', 'item_id', 'item_description', 'lot_number'])
            ->each(function ($row) use ($codesByItem, $codesByLot) {
                $candidates = [];

                if ($row->item_id) {
                    $lot = trim((string) $row->lot_number);

                    if ($lot !== '' && ! empty($codesByLot[$row->item_id][$lot])) {
                        $candidates = $codesByLot[$row->item_id][$lot];
                    } elseif (! empty($codesByItem[$row->item_id])) {
                        $candidates = $codesByItem[$row->item_id];
                    }
                } else {
                    // Rows saved without an item link: match by description.
                    $item = DB::table('items')
                        ->whereRaw('LOWER(name) = ?', [Str::lower((string) $row->item_description)])
                        ->first();

                    if ($item && ! empty($codesByItem[$item->id])) {
                        $candidates = $codesByItem[$item->id];
                    }
                }

                $candidates = array_values(array_unique($candidates ?? []));

                if (count($candidates) !== 1) {
                    return;
                }

                DB::table('pas_items')->where('id', $row->id)->update(['item_code' => $candidates[0]]);
            });
    }
};
