<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\ReceivingController;
use App\Http\Controllers\ReleaseController;
use App\Models\Program;
use App\Models\Receiving;
use App\Models\Release;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;

app('view')->share('errors', new ViewErrorBag());

$fail = 0;
function check(string $name, bool $cond): void
{
    global $fail;
    echo ($cond ? 'PASS  ' : 'FAIL  ') . $name . "\n";
    if (! $cond) $fail++;
}

// ---- compile + lint all touched views and classes ----
$compiler = new Illuminate\View\Compilers\BladeCompiler(new Illuminate\Filesystem\Filesystem(), sys_get_temp_dir());
foreach (['layouts/app', 'receivings/index', 'receivings/view', 'releases/index', 'releases/view'] as $view) {
    $out = $compiler->compileString(file_get_contents(__DIR__ . '/resources/views/' . $view . '.blade.php'));
    file_put_contents(__DIR__ . '/storage/app/v_' . basename($view) . '.php', $out);
    exec('php -l ' . escapeshellarg(__DIR__ . '/storage/app/v_' . basename($view) . '.php') . ' 2>&1', $lint, $code);
    check("view {$view} compiles + lints", $code === 0);
}

foreach ([App\Traits\ScopesProgramAccess::class, App\Http\Middleware\EnsureProgramUser::class, ReceivingController::class, ReleaseController::class] as $class) {
    $short = (new ReflectionClass($class))->getShortName();
    $exists = (new ReflectionClass($class))->isTrait() ? trait_exists($class) : class_exists($class);
    check("class {$short} loads", $exists);
}

$pu = User::whereNotNull('program_id')->first();
$programNames = Program::whereIn('id', $pu->all_programs)->pluck('name')->unique()->values()->toArray();
echo "\nprogram user id={$pu->id}  viewable programs=" . json_encode($programNames) . "\n";

// button hrefs resolve from route(), so assert on the URL tail
$btn = function (string $html): array {
    preg_match_all('/<a href="([^"]+)"[^>]*class="btn[^"]*"[^>]*>([^<]*)<\/a>/', $html, $m, PREG_SET_ORDER);
    return array_map(fn ($s) => [trim($s[2]), $s[1]], $m);
};

Auth::setUser($pu);

// ---- receivings / releases index, as a program user ----
$recHtml = (new ReceivingController())->index(new Request())->render();
$recBtns = $btn($recHtml);
$recLabels = array_column($recBtns, 0);
check('receivings index: no New Receiving', ! in_array('New Receiving', $recLabels, true));
check('receivings index: keeps Download Excel', in_array('Download Excel', $recLabels, true));
check('receivings index: keeps Print', in_array('Print', $recLabels, true));
check('receivings index: View links only', ! in_array('Edit', $recLabels, true));

$relHtml = (new ReleaseController())->index(new Request())->render();
$relBtns = $btn($relHtml);
$relLabels = array_column($relBtns, 0);
check('releases index: no New Release Slip', ! in_array('New Release Slip', $relLabels, true));
check('releases index: keeps Download Excel', in_array('Download Excel', $relLabels, true));
check('releases index: keeps Print', in_array('Print', $relLabels, true));

// ---- admin sees the create buttons ----
$admin = User::first();
$admin->program_id = null;
Auth::setUser($admin);
$adminRec = $btn((new ReceivingController())->index(new Request())->render());
check('admin receivings index: keeps New Receiving', in_array('New Receiving', array_column($adminRec, 0), true));
$adminRel = $btn((new ReleaseController())->index(new Request())->render());
check('admin releases index: keeps New Release Slip', in_array('New Release Slip', array_column($adminRel, 0), true));

// ---- record-level gate: own program renders, foreign is rejected ----
Auth::setUser($pu);
$own = Receiving::whereIn('stock_keeping_unit', $programNames)->first();
$foreign = Receiving::whereNotIn('stock_keeping_unit', $programNames)->first();

$rendered = null;
if ($own) {
    $rendered = $btn((new ReceivingController())->view($own)->render());
    check('own receiving detail renders', true);
    check('own receiving hides Edit', ! in_array('Edit', array_column($rendered, 0), true));
    check('own receiving keeps Print', in_array('Print', array_column($rendered, 0), true));
    check('own receiving keeps Back link', in_array('Back to Receivings', array_column($rendered, 0), true));
} else {
    echo "SKIP  no receiving in own program\n";
}

if ($foreign) {
    $blocked = false;
    try {
        (new ReceivingController())->view($foreign)->render();
    } catch (\Throwable $e) {
        $blocked = str_contains($e->getMessage(), 'does not have access to this record');
    }
    check('foreign receiving detail is rejected (403)', $blocked);
} else {
    echo "SKIP  no foreign receiving exists\n";
}

$ownRel = Release::whereIn('health_program_coordinator', $programNames)->first();
if ($ownRel) {
    $rbtns = $btn((new ReleaseController())->view($ownRel)->render());
    check('own release detail renders', true);
    check('own release hides Edit', ! in_array('✏️ Edit', array_column($rbtns, 0), true));
    check('own release hides Download Excel', ! in_array('Download Excel', array_column($rbtns, 0), true));
    check('own release keeps Print PTR', in_array('Print PTR', array_column($rbtns, 0), true));
} else {
    echo "SKIP  no release in own program\n";
}

$foreignRel = Release::whereNotIn('health_program_coordinator', $programNames)->whereNotNull('health_program_coordinator')->first();
if ($foreignRel) {
    $blocked = false;
    try {
        (new ReleaseController())->view($foreignRel)->render();
    } catch (\Throwable $e) {
        $blocked = str_contains($e->getMessage(), 'does not have access to this record');
    }
    check('foreign release detail is rejected (403)', $blocked);
} else {
    echo "SKIP  no foreign release exists\n";
}

// ---- release detail template with a program-owned record (no DB row needed) ----
if ($ownRel === null) {
    Auth::setUser($pu);
    $synthetic = new Release([
        'release_number' => 'SYN-0001',
        'health_program_coordinator' => $programNames[0],
        'ptr_itr_ris_no' => 'PTR-SYN-1',
        'pas_number' => 'PAS-SYN-1',
        'status' => 'Released',
    ]);
    $synthetic->forceFill(['id' => 999001]);
    $synthetic->exists = true;
    $synthetic->setRelation('items', new \Illuminate\Database\Eloquent\Collection());
    $html4 = view('releases.view', ['release' => $synthetic, 'programUser' => true])->render();
    $btns4 = $btn($html4);
    $labels4 = array_column($btns4, 0);
    check('synthetic own release hides Edit', ! in_array('✏️ Edit', $labels4, true));
    check('synthetic own release hides Download Excel', ! in_array('Download Excel', $labels4, true));
    check('synthetic own release keeps Print PTR', in_array('Print PTR', $labels4, true));
    check('synthetic own release keeps Back', in_array('Back to Releases', $labels4, true));
} else {
    echo "SKIP  synthetic release check (real row existed)\n";
}

// release detail for an admin keeps Edit + Download Excel
Auth::setUser($admin);
$syntheticAdmin = new Release([
    'release_number' => 'SYN-0002',
    'health_program_coordinator' => 'Other Program',
    'ptr_itr_ris_no' => 'PTR-SYN-2',
    'status' => 'Released',
]);
$syntheticAdmin->setRelation('items', new \Illuminate\Database\Eloquent\Collection());
$btns5 = $btn(view('releases.view', ['release' => $syntheticAdmin, 'programUser' => false])->render());
$labels5 = array_column($btns5, 0);
check('admin release keeps Edit', in_array('✏️ Edit', $labels5, true));
check('admin release keeps Download Excel', in_array('Download Excel', $labels5, true));

// ---- sidebar exposure (rendered as the real program user) ----
Auth::setUser($pu);
$layout = view('layouts.app')->render();
$links = [];
preg_match_all('/<a href="([^"]+)" class="sidebar-link[^"]*"/', $layout, $m, PREG_SET_ORDER);
foreach ($m as $s) { $links[] = $s[1]; }
echo "\nprogram-user sidebar links:\n";
foreach ($links as $l) { echo "  {$l}\n"; }

check('sidebar has receivings', (bool) preg_grep('#/receivings$#', $links));
check('sidebar has releases', (bool) preg_grep('#/releases$#', $links));
check('sidebar shows items', (bool) preg_grep('#/items$#', $links));
check('sidebar shows pas', (bool) preg_grep('#/pas$#', $links));
check('sidebar hides suppliers', ! (bool) preg_grep('#/suppliers#', $links));
check('sidebar hides program management', ! (bool) preg_grep('#program-management#', $links));

// Admin keeps every link (and exactly one Receivings entry).
$admin = User::first();
$admin->program_id = null;
Auth::setUser($admin);
$layout2 = view('layouts.app')->render();
$links2 = [];
preg_match_all('/<a href="([^"]+)" class="sidebar-link[^"]*"/', $layout2, $m2, PREG_SET_ORDER);
foreach ($m2 as $s) { $links2[] = $s[1]; }
echo "\nadmin sidebar links:\n";
foreach ($links2 as $l) { echo "  {$l}\n"; }

check('admin sidebar has receivings exactly once', count(preg_grep('#/receivings$#', $links2)) === 1);
check('admin sidebar has releases', (bool) preg_grep('#/releases$#', $links2));
check('admin sidebar keeps suppliers', (bool) preg_grep('#/suppliers#', $links2));

echo "\n" . ($fail === 0 ? "ALL CHECKS PASSED\n" : "{$fail} CHECKS FAILED\n");
exit($fail ? 1 : 0);
