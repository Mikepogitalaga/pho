<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\ReceivingController;
use App\Http\Controllers\ReleaseController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;

app('view')->share('errors', new ViewErrorBag());

$pu = User::whereNotNull('program_id')->first();
Auth::setUser($pu);

$html = (new ReceivingController())->index(new Request())->render();
echo "=== receivings index: action buttons ===\n";
preg_match_all('/<a href="([^"]+)"[^>]*class="btn[^"]*"[^>]*>([^<]*)<\/a>/', $html, $m, PREG_SET_ORDER);
foreach ($m as $set) {
    echo "  href=" . $set[1] . "  label=" . trim($set[2]) . "\n";
}

echo "\n=== releases index: action buttons ===\n";
$html2 = (new ReleaseController())->index(new Request())->render();
preg_match_all('/<a href="([^"]+)"[^>]*class="btn[^"]*"[^>]*>([^<]*)<\/a>/', $html2, $m2, PREG_SET_ORDER);
foreach ($m2 as $set) {
    echo "  href=" . $set[1] . "  label=" . trim($set[2]) . "\n";
}

echo "\n=== receivings view: action buttons ===\n";
$rec = App\Models\Receiving::first();
$html3 = (new ReceivingController())->view($rec)->render();
preg_match_all('/<a href="([^"]+)"[^>]*class="btn[^"]*"[^>]*>([^<]*)<\/a>/', $html3, $m3, PREG_SET_ORDER);
foreach ($m3 as $set) {
    echo "  href=" . $set[1] . "  label=" . trim($set[2]) . "\n";
}

echo "\n=== releases view: action buttons ===\n";
$rel = App\Models\Release::first();
$html4 = (new ReleaseController())->view($rel)->render();
preg_match_all('/<a href="([^"]+)"[^>]*class="btn[^"]*"[^>]*>([^<]*)<\/a>/', $html4, $m4, PREG_SET_ORDER);
foreach ($m4 as $set) {
    echo "  href=" . $set[1] . "  label=" . trim($set[2]) . "\n";
}

echo "\n=== layout sidebar links ===\n";
$u = User::first();
$u->program_id = $pu->program_id;
Auth::setUser($u);
$layout = view('layouts.app')->render();
preg_match_all('/<a href="([^"]+)" class="sidebar-link[^"]*"/', $layout, $m5, PREG_SET_ORDER);
foreach ($m5 as $set) {
    echo "  " . $set[1] . "\n";
}
