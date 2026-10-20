<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('release_items', function (Blueprint $table) {
            $table->string('item_code')->nullable()->after('item_id');
        });

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

        $releaseItems = DB::table('release_items')
            ->whereNull('item_code')
            ->get(['id', 'item_id', 'lot_number']);

        foreach ($releaseItems as $releaseItem) {
            $lot = trim((string) $releaseItem->lot_number);

            if ($lot !== '' && ! empty($codesByLot[$releaseItem->item_id][$lot])) {
                $candidates = array_unique($codesByLot[$releaseItem->item_id][$lot]);
            } elseif (! empty($codesByItem[$releaseItem->item_id])) {
                $candidates = array_unique($codesByItem[$releaseItem->item_id]);
            } else {
                continue;
            }

            if (count($candidates) === 1) {
                DB::table('release_items')
                    ->where('id', $releaseItem->id)
                    ->update(['item_code' => reset($candidates)]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('release_items', function (Blueprint $table) {
            $table->dropColumn('item_code');
        });
    }
};
