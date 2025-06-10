@extends('layouts.admin')

@section('title', 'Edit User')

@section('content')
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Edit User: {{ $user->name }}</h1>
        <a href="{{ route('admin.users.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Back to Users</a>
    </div>

    <div class="bg-white shadow-md rounded-lg p-6">
        <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Name:</label>
                <input type="text" name="name" id="name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('name') border-red-500 @enderror" value="{{ old('name', $user->name) }}" required>
                @error('name')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email:</label>
                <input type="email" name="email" id="email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('email') border-red-500 @enderror" value="{{ old('email', $user->email) }}" required>
                @error('email')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <label for="address" class="block text-gray-700 text-sm font-bold mb-2">Address:</label>
                <input type="text" name="address" id="address" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('address') border-red-500 @enderror" value="{{ old('address', $user->address) }}">
                @error('address')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <label for="phone" class="block text-gray-700 text-sm font-bold mb-2">Phone:</label>
                <input type="text" name="phone" id="phone" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('phone') border-red-500 @enderror" value="{{ old('phone', $user->phone) }}">
                @error('phone')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
            </div>

            {{-- NEW BUSINESS DETAILS DISPLAY/EDIT --}}
            <h3 class="text-xl font-semibold text-gray-800 mt-6 mb-4 border-t pt-4">Business Information (Vendor)</h3>
            <div class="mb-4">
                <label for="business_name" class="block text-gray-700 text-sm font-bold mb-2">Business Name:</label>
                <input type="text" name="business_name" id="business_name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('business_name') border-red-500 @enderror" value="{{ old('business_name', $user->business_name) }}">
                @error('business_name')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <label for="business_address" class="block text-gray-700 text-sm font-bold mb-2">Business Address:</label>
                <textarea name="business_address" id="business_address" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('business_address') border-red-500 @enderror">{{ old('business_address', $user->business_address) }}</textarea>
                @error('business_address')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <label for="business_description" class="block text-gray-700 text-sm font-bold mb-2">Business Description:</label>
                <textarea name="business_description" id="business_description" rows="5" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('business_description') border-red-500 @enderror">{{ old('business_description', $user->business_description) }}</textarea>
                @error('business_description')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>
            {{-- END NEW BUSINESS DETAILS --}}

            <div class="mb-4 flex items-center">
                <input type="checkbox" name="is_vendor" id="is_vendor" class="mr-2 leading-tight" {{ old('is_vendor', $user->isVendor()) ? 'checked' : '' }}>
                <label for="is_vendor" class="text-gray-700 text-sm font-bold">Is Vendor?</label>
            </div>

            <div class="mb-4 flex items-center">
                <input type="checkbox" name="is_admin" id="is_admin" class="mr-2 leading-tight" {{ old('is_admin', $user->is_admin) ? 'checked' : '' }}>
                <label for="is_admin" class="text-gray-700 text-sm font-bold">Is Administrator?</label>
            </div>

            <div class="mb-4">
                <label for="vendor_tier" class="block text-gray-700 text-sm font-bold mb-2">Vendor Tier:</label>
                <select name="vendor_tier" id="vendor_tier" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('vendor_tier') border-red-500 @enderror" {{ !$user->isVendor() ? 'disabled' : '' }}>
                    <option value="Silver" {{ old('vendor_tier', $user->vendor_tier) == 'Silver' ? 'selected' : '' }}>Silver</option>
                    <option value="Gold" {{ old('vendor_tier', $user->vendor_tier) == 'Gold' ? 'selected' : '' }}>Gold</option>
                    <option value="Diamond" {{ old('vendor_tier', $user->vendor_tier) == 'Diamond' ? 'selected' : '' }}>Diamond</option>
                </select>
                @error('vendor_tier')
                    <p class="text-red-500 text-xs italic">{{ $message }}</p>
                @enderror
            </div>

            {{-- ADD THIS NEW INPUT FOR COMMISSION RATE --}}
            <div class="mb-4">
                <label for="commission_rate" class="block text-gray-700 text-sm font-bold mb-2">Commission Rate (e.g., 0.05 for 5%):</label>
                <input type="number" name="commission_rate" id="commission_rate" step="0.0001" min="0" max="1" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('commission_rate') border-red-500 @enderror" value="{{ old('commission_rate', $user->commission_rate) }}" {{ !$user->isVendor() ? 'disabled' : '' }}>
                @error('commission_rate')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>
            {{-- END NEW INPUT --}}

            <div class="flex items-center justify-end">
                <button type="submit" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Update User</button>
            </div>
        </form>
    </div>
@endsection

{{-- Optional: Add JavaScript to enable/disable commission rate input based on Is Vendor checkbox --}}
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const isVendorCheckbox = document.getElementById('is_vendor');
        const vendorTierSelect = document.getElementById('vendor_tier');
        const commissionRateInput = document.getElementById('commission_rate'); // Get new input

        function toggleVendorFields() {
            vendorTierSelect.disabled = !isVendorCheckbox.checked;
            commissionRateInput.disabled = !isVendorCheckbox.checked; // Disable commission rate
        }

        // Initial state
        toggleVendorFields();

        // Listen for changes
        isVendorCheckbox.addEventListener('change', toggleVendorFields);
    });
</script>
@endpush