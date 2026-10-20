<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use App\Models\PasItem;

echo '--- pas_items columns ---' . PHP_EOL;
print_r(Schema::getColumnListing('pas_items'));

echo '--- items columns (item_code?) ---' . PHP_EOL;
$cols = Schema::getColumnListing('items');
echo (in_array('item_code', $cols) ? 'items.item_code EXISTS' : 'items.item_code MISSING') . PHP_EOL;

echo '--- stored pas_items rows ---' . PHP_EOL;
foreach (PasItem::with('item')->get() as $pi) {
    echo 'pas_item=' . $pi->id
        . ' product_code=' . var_export($pi->product_code, true)
        . ' item_id=' . var_export($pi->item_id, true)
        . ' linked item code=' . var_export($pi->item?->item_code, true)
        . ' desc=' . $pi->item_description . PHP_EOL;
}
