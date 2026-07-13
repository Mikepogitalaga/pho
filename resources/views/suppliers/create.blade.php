@extends('layouts.app')

@section('title', 'New Supplier')
@section('pageHeading', 'New Supplier')
@section('pageSubheading', 'Add a new supplier to the inventory system.')

@section('content')
    <section class="card">
        <form action="{{ route('suppliers.store') }}" method="POST" class="stack">

            @csrf

            <div class="form-grid-2">
                <div class="form-group">
                    <label>Company Name</label>
                    <input type="text" name="company_name" value="{{ old('company_name') }}" required>
                </div>
                <div class="form-group">
                    <label>Contact Person</label>
                    <input type="text" name="contact_person" value="{{ old('contact_person') }}">
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone_number" value="{{ old('phone_number') }}">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="{{ old('email') }}">
                </div>
            </div>

            <div class="form-group">
                <label>Address</label>
                <textarea name="address" rows="3">{{ old('address') }}</textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Supplier</button>
                <a href="{{ route('suppliers.index') }}" class="btn btn-ghost">Cancel</a>
            </div>
        </form>
    </section>
@endsection

