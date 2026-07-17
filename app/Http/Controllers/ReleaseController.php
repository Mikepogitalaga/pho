<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Release;
use App\Models\ReleaseItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Throwable;

class ReleaseController extends Controller
{
    public function updateStatus(Request $request, Release $release, string $status)
    {
        $allowed = ['released-through-pass', 'released', 'canceled', 'returned', 'unreleased'];
        if (!in_array($status, $allowed, true)) {
            return redirect()->back()->with('error', 'Invalid status.');
        }

        $previousStatus = $release->status;

        $newStatus = match ($status) {
            'released-through-pass' => 'Released through pass',
            'released' => 'Released',
            'canceled' => 'Canceled',
            'returned' => 'Returned',
            default => 'Unreleased',
        };

        $release->status = $newStatus;

        // For released/released-through-pass we capture receiving metadata.
        // These fields already exist on the DB as `received_by` and we reuse `date_released` as receiving date.
        if (in_array($newStatus, ['Released', 'Released through pass'], true)) {
            $release->received_by = $request->input('received_by', $release->received_by);
            $release->date_released = $request->input('date_released', $release->date_released);

            // Also persist PTR/ITR/RIS No. if provided.
            $release->ptr_itr_ris_no = $request->input('ptr_itr_ris_no', $release->ptr_itr_ris_no);
        }

        // Inventory adjustments:
        // - When a release is initially created, item quantity_on_hand is decremented.
        // - If user marks a release as canceled or returned, we restore quantities.
        if (in_array($newStatus, ['Canceled', 'Returned'], true) && !in_array($previousStatus, ['Canceled', 'Returned'], true)) {
            foreach ($release->items as $releaseItem) {
                $item = Item::find($releaseItem->item_id);
                if ($item) {
                    $item->increment('quantity_on_hand', (int) $releaseItem->quantity_released);
                }
            }
        }

        $release->save();

        return redirect()->route('releases.view', $release)->with('success', 'Release status updated.');
    }

    public function index(Request $request)
    {
        $query = Release::query()->latest('date_released');

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('release_number', 'like', '%' . $search . '%')
                    ->orWhere('pas_number', 'like', '%' . $search . '%')
                    ->orWhere('pho_code', 'like', '%' . $search . '%')
                    ->orWhere('facility_name', 'like', '%' . $search . '%')
                    ->orWhere('ptr_itr_ris_no', 'like', '%' . $search . '%')
                    ->orWhere('status', 'like', '%' . $search . '%');
            });
        }

        $facility = trim((string) $request->input('facility', ''));
        if ($facility !== '') {
            $query->where('facility_name', 'like', '%' . $facility . '%');
        }

        $phoCode = trim((string) $request->input('pho_code', ''));
        if ($phoCode !== '') {
            $query->where('pho_code', 'like', '%' . $phoCode . '%');
        }

        $pasNumber = trim((string) $request->input('pas_number', ''));
        if ($pasNumber !== '') {
            $query->where('pas_number', 'like', '%' . $pasNumber . '%');
        }

        $status = $request->input('status');
        if (!empty($status)) {
            $newStatus = match ($status) {
                'released-through-pass' => 'Released through pass',
                'released' => 'Released',
                'canceled' => 'Canceled',
                'returned' => 'Returned',
                'unreleased' => 'Unreleased',
                default => $status,
            };

            $query->where('status', $newStatus);
        }

        $releases = $query->paginate(15);

        return view('releases.index', compact('releases'));
    }

    public function view(Release $release)
    {
        // Eager-load released items relation (defined on the Release model)
        $release->load('items');

        return view('releases.view', compact('release'));
    }

    public function update(Request $request, Release $release)
    {
        $request->validate([
            'pas_number' => 'nullable|string|max:255',
            'health_program_coordinator' => 'nullable|string|max:255',
            'ptr_itr_ris_no' => 'nullable|string|max:255',
            'pho_code' => 'nullable|string|max:255',
            'source_docs_ptr_po_no' => 'nullable|string|max:255',
            'facility_name' => 'nullable|string|max:255',
            'received_by' => 'required|string|max:255',
            'date_released' => 'required|date',
            'status' => 'required|string|in:Unreleased,Released,Released through pass,Canceled,Returned',
            'notes' => 'nullable|string',
        ]);

        $release->update($request->only([
            'pas_number',
            'health_program_coordinator',
            'ptr_itr_ris_no',
            'pho_code',
            'source_docs_ptr_po_no',
            'facility_name',
            'received_by',
            'date_released',
            'status',
            'notes',
        ]));

        return redirect()->route('releases.view', $release)->with('success', 'Release details updated successfully.');
    }

    public function create()
    {
        $items = Item::orderBy('name')->get();

        return view('releases.create', compact('items'));
    }

    public function store(Request $request)
    {
        // Status is set automatically after saving.
        $request->validate([
            'pas_number' => 'nullable|string|max:255',
            'health_program_coordinator' => 'nullable|string|max:255',
            'ptr_itr_ris_no' => 'nullable|string|max:255',
            'pho_code' => 'nullable|string|max:255',
            'source_docs_ptr_po_no' => 'nullable|string|max:255',
            'facility_name' => 'nullable|string|max:255',
            'received_by' => 'nullable|string|max:255',
            'date_released' => 'required|date',
            'status' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.item_description' => 'nullable|string|max:1000',
            'items.*.quantity_released' => 'required|integer|min:1',
            'items.*.uom' => 'nullable|string|max:255',
            'items.*.unit_cost' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $release = Release::create([
                    'release_number' => 'REL-' . strtoupper(Str::random(8)),
                    'pas_number' => $request->input('pas_number'),
                    // After saving a new release slip, it starts as "Unreleased".
                    // Status will be updated later via dedicated actions.
                    'status' => 'Unreleased',
                    'health_program_coordinator' => $request->input('health_program_coordinator'),
                    'ptr_itr_ris_no' => $request->input('ptr_itr_ris_no'),
                    'pho_code' => $request->input('pho_code'),
                    'source_docs_ptr_po_no' => $request->input('source_docs_ptr_po_no'),
                    'facility_name' => $request->input('facility_name'),
                    'received_by' => $request->input('received_by'),
                    'date_released' => $request->input('date_released'),
                    'notes' => $request->input('notes'),
                ]);

                foreach ($request->input('items') as $itemData) {
                    $item = Item::find($itemData['item_id']);

                    if ($item && $item->quantity_on_hand < (int) $itemData['quantity_released']) {
                        $available = (int) $item->quantity_on_hand;
                        $requested = (int) $itemData['quantity_released'];

                        throw new \Exception(
                            "Not enough stock for item {$item->name}. Available: {$available}, Requested: {$requested}."
                        );
                    }

                    ReleaseItem::create([
                        'release_id' => $release->id,
                        'item_id' => $itemData['item_id'],
                        'item_description' => $itemData['item_description'] ?? $item->name,
                        'quantity_released' => $itemData['quantity_released'],
                        'uom' => $itemData['uom'] ?? $item->unit,
                        'unit_cost' => $itemData['unit_cost'] ?? null,
                    ]);

                    $item->decrement('quantity_on_hand', (int) $itemData['quantity_released']);
                }
            });

            return redirect()->route('releases.index')->with('success', 'Release slip saved and inventory updated.');
        } catch (Throwable $e) {
            // Show as flash notification (layouts/app.blade.php reads session('error')).
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}

