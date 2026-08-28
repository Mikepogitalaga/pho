@extends('layouts.app')

@section('title', 'Facilities Management')
@section('pageHeading', 'Facilities Management')
@section('pageSubheading', 'Manage facilities by category — Hospitals, RHU\'s, NLAs/Other Agencies, and PHO Clinic.')

@section('content')
    <section class="section-card" aria-label="Facilities overview">
        <div class="section-header compact">
            <div>
                <h3 class="section-card-title">All Facilities</h3>
                <p class="page-description">{{ $facilities->count() }} total facility(ies)</p>
            </div>
            <button type="button" class="btn btn-primary" onclick="openAddModal()">+ Add New Facility</button>
        </div>

        @if($facilities->isEmpty())
            <div class="empty-state" role="status">
                <strong>No facilities added yet.</strong>
                <div>Click "Add New Facility" to get started.</div>
            </div>
        @else
            @foreach($categories as $category)
                @php $items = $groupedFacilities->get($category, collect()); @endphp
                @if($items->isNotEmpty())
                    <div style="margin-bottom: 1.5rem;">
                        <h4 style="color: var(--text); font-size: 0.95rem; font-weight: 700; margin-bottom: 0.75rem; padding-bottom: 0.4rem; border-bottom: 2px solid var(--border);">{{ $category }} <span style="color: var(--text-muted); font-weight: 400;">({{ $items->count() }})</span></h4>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Address</th>
                                        <th class="col-hide-md">Contact Person</th>
                                        <th class="col-hide-md">Phone</th>
                                        <th style="width:160px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                     @foreach($items as $facility)
                                         <tr>
                                              <td class="mobile-card-header" style="font-weight:600;">
                                                  <span style="color:var(--text);">{{ $facility->name }}</span>
                                                  <span style="font-size:0.75rem;color:var(--text-muted);">{{ $facility->category }}</span>
                                              </td>
                                             <td data-label="Address">{{ $facility->address ?? '—' }}</td>
                                              <td data-label="Contact Person" class="col-hide-md">{{ $facility->contact_person ?? '—' }}</td>
                                              <td data-label="Phone" class="col-hide-md">{{ $facility->phone_number ?? '—' }}</td>
                                              <td class="mobile-card-actions">
                                                  <button type="button" class="btn btn-secondary" style="min-height:2rem;padding:0.3rem 0.7rem;font-size:0.8rem;" onclick='openEditModal(@json($facility))'>Edit</button>
                                                  <form method="POST" action="{{ route('facilities.destroy', $facility) }}" style="display:inline" onsubmit="return confirm('Delete this facility?');">
                                                      @csrf
                                                      @method('DELETE')
                                                      <button type="submit" class="btn btn-danger" style="min-height:2rem;padding:0.3rem 0.7rem;font-size:0.8rem;">Delete</button>
                                                  </form>
                                              </td>
                                         </tr>
                                     @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            @endforeach
        @endif
    </section>

     {{-- ===== ADD / EDIT MODAL ===== --}}
     <div id="facilityModal" style="display:none;position:fixed;inset:0;z-index:1000;background:rgba(0,0,0,0.5);align-items:center;justify-content:center;">
         <div style="background:var(--surface);border-radius:1rem;width:min(500px,92vw);max-height:90vh;overflow-y:auto;box-shadow:var(--shadow);">
             <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
                 <h3 id="modalTitle" style="margin:0;font-size:1.1rem;font-weight:700;color:var(--text);">Add New Facility</h3>
                 <button type="button" onclick="closeModal()" style="background:none;border:none;font-size:1.5rem;cursor:pointer;color:var(--text-muted);line-height:1;">&times;</button>
             </div>
             <form id="facilityForm" method="POST" action="{{ route('facilities.store') }}" style="padding:1.5rem;">
                 @csrf
                 <input type="hidden" id="modalMethod" name="_method" value="POST">
                 <input type="hidden" id="modalId" name="" value="">
                 <div class="form-group" style="margin-bottom:1rem;">
                     <label for="modalName">Facility Name <span style="color:var(--danger);">*</span></label>
                     <input type="text" id="modalName" name="name" required placeholder="Enter facility name" style="width:100%;">
                 </div>
                 <div class="form-group" style="margin-bottom:1rem;">
                     <label for="modalCategory">Category <span style="color:var(--danger);">*</span></label>
                     <select id="modalCategory" name="category" required style="width:100%;">
                         @foreach(\App\Models\Facility::categories() as $cat)
                             <option value="{{ $cat }}">{{ $cat }}</option>
                         @endforeach
                     </select>
                 </div>
                 <div class="form-group" style="margin-bottom:1rem;">
                     <label for="modalAddress">Address</label>
                     <input type="text" id="modalAddress" name="address" placeholder="Enter address" style="width:100%;">
                 </div>
                 <div class="form-group" style="margin-bottom:1rem;">
                     <label for="modalContact">Contact Person</label>
                     <input type="text" id="modalContact" name="contact_person" placeholder="Enter contact person" style="width:100%;">
                 </div>
                 <div class="form-group" style="margin-bottom:1.25rem;">
                     <label for="modalPhone">Phone Number</label>
                     <input type="text" id="modalPhone" name="phone_number" placeholder="Enter phone number" style="width:100%;">
                 </div>
                 <div style="display:flex;gap:0.75rem;justify-content:flex-end;">
                     <button type="button" class="btn btn-ghost" onclick="closeModal()">Cancel</button>
                     <button type="submit" class="btn btn-primary" id="modalSubmitBtn">Add Facility</button>
                 </div>
             </form>
         </div>
     </div>
@endsection

@push('scripts')
<script>
     const modal = document.getElementById('facilityModal');
     const form = document.getElementById('facilityForm');
     const title = document.getElementById('modalTitle');
     const methodInput = document.getElementById('modalMethod');
     const submitBtn = document.getElementById('modalSubmitBtn');

     function openAddModal() {
         title.textContent = 'Add New Facility';
         form.action = '{{ route('facilities.store') }}';
         methodInput.value = 'POST';
         document.getElementById('modalName').value = '';
         document.getElementById('modalCategory').value = 'Hospitals';
         document.getElementById('modalAddress').value = '';
         document.getElementById('modalContact').value = '';
         document.getElementById('modalPhone').value = '';
         submitBtn.textContent = 'Add Facility';
         modal.style.display = 'flex';
     }

     function openEditModal(facility) {
         title.textContent = 'Edit Facility';
         form.action = '{{ route('facilities.update', ['facility' => '__ID__']) }}'.replace('__ID__', facility.id);
         methodInput.value = 'PUT';
         document.getElementById('modalName').value = facility.name;
         document.getElementById('modalCategory').value = facility.category;
         document.getElementById('modalAddress').value = facility.address || '';
         document.getElementById('modalContact').value = facility.contact_person || '';
         document.getElementById('modalPhone').value = facility.phone_number || '';
         submitBtn.textContent = 'Save Changes';
         modal.style.display = 'flex';
     }

     function closeModal() {
         modal.style.display = 'none';
     }

     modal.addEventListener('click', function(e) {
         if (e.target === modal) closeModal();
     });
</script>
@endpush
