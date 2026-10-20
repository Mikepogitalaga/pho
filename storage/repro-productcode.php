<?php

$item = App\Models\Item::find(134);
$code = 'AD-26-09-0002';

$ri = App\Models\ReceivingItem::where('item_id', $item->id)
    ->where('item_code', $code)
    ->with('receiving.supplier')
    ->firstOrFail();

$product = $item->load('nextExpiryItem', 'releaseItems.release');
$product->item_code = $ri->item_code;

$released = $product->releaseItems
    ->filter(fn ($r) => ! in_array($r->release->status ?? '', ['Canceled', 'Returned'], true))
    ->sum('quantity_released');
$received = $product->receivingItems->sum('quantity_received');

echo 'CURRENT -> totalReleased=' . $released
    . ' totalReceived=' . $received
    . ' totalStock=' . $product->quantity_on_hand
    . ' deductionPct=' . ($received > 0 ? round(($released / $received) * 100) : 0)
    . ' historyRows=' . $product->releaseItems->count()
    . PHP_EOL;

foreach ($product->releaseItems as $rli) {
    echo '  row release=' . ($rli->release->release_number ?? '?')
        . ' item_code=' . var_export($rli->item_code, true)
        . ' lot=' . var_export($rli->lot_number, true)
        . ' qty=' . $rli->quantity_released
        . ' status=' . ($rli->release->status ?? '?')
        . PHP_EOL;
}

$product->load('receivingItems');
$product->attachCodeAvailability();

foreach ($product->receivingItems as $r) {
    echo '  recv id=' . $r->id . ' code=' . $r->item_code . ' lot=' . var_export($r->lot_number, true)
        . ' received=' . $r->quantity_received . ' available=' . ($r->available_quantity ?? 'n/a') . PHP_EOL;
}
