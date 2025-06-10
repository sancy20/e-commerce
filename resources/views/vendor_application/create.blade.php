@extends('layouts.app')

@section('title', 'Become a Vendor')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-2xl">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Become a Vendor</h1>

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                {{ session('error') }}
            </div>
        @endif
        @if (session('info'))
            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4" role="alert">
                {{ session('info') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @auth
            @if (Auth::user()->isVendor() || Auth::user()->isPendingVendor())
                <p class="text-lg text-gray-600">
                    You have already applied or are an approved vendor.
                    @if (Auth::user()->isPendingVendor())
                        Your application is currently under review.
                    @endif
                </p>
                <a href="{{ route('dashboard.index') }}" class="mt-4 inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Go to Dashboard</a>
            @else
                <p class="text-lg text-gray-700 mb-6">
                    If you're interested in selling your products on our platform, please read our terms and conditions and fill out the details below.
                </p>

                <form action="{{ route('vendor_application.submit') }}" method="POST">
                    @csrf

                    {{-- NEW BUSINESS DETAILS FIELDS --}}
                    <div class="mb-4">
                        <label for="business_name" class="block text-gray-700 text-sm font-bold mb-2">Business Name:</label>
                        <input type="text" name="business_name" id="business_name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('business_name') border-red-500 @enderror" value="{{ old('business_name') }}" required>
                        @error('business_name')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="business_address" class="block text-gray-700 text-sm font-bold mb-2">Business Address:</label>
                        <textarea name="business_address" id="business_address" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('business_address') border-red-500 @enderror" required>{{ old('business_address') }}</textarea>
                        @error('business_address')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label for="business_description" class="block text-gray-700 text-sm font-bold mb-2">Describe Your Products/Business:</label>
                        <textarea name="business_description" id="business_description" rows="5" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('business_description') border-red-500 @enderror">{{ old('business_description') }}</textarea>
                        @error('business_description')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    {{-- END NEW BUSINESS DETAILS FIELDS --}}

                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Terms and Conditions:</label>
                        <div class="border border-gray-300 rounded-lg p-4 h-48 overflow-y-auto bg-gray-50 text-gray-800 text-sm">
                            <p class="font-semibold mb-2">Vendor Agreement Terms</p>
                            <p class="mb-2">1. All products must comply with platform policies.</p>
                            <p class="mb-2">2. Vendors are responsible for product quality and shipping as per agreed terms.</p>
                            <p class="mb-2">3. A commission rate of [X%] will be applied to all sales.</p>
                            <p class="mb-2">4. Payouts will be processed [weekly/monthly] after deducting commission.</p>
                            <p class="mb-2">5. The platform reserves the right to approve or reject applications at its sole discretion.</p>
                            <p>By checking the box below, you agree to all terms and conditions.</p>
                        </div>
                    </div>

                    <div class="mb-6 flex items-center">
                        <input type="checkbox" name="agreement" id="agreement" value="1" class="mr-2 leading-tight @error('agreement') border-red-500 @enderror" {{ old('agreement') ? 'checked' : '' }} required>
                        <label for="agreement" class="text-gray-700 text-sm font-bold">
                            I agree to the terms and conditions.
                        </label>
                        @error('agreement')
                            <p class="text-red-500 text-xs italic mt-1 ml-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg focus:outline-none focus:shadow-outline text-xl">
                        Submit Application
                    </button>
                </form>
            @endif
        @else
            <p class="text-lg text-gray-600">Please <a href="{{ route('login') }}" class="text-blue-500 hover:underline">log in</a> to apply to become a vendor.</p>
        @endauth
    </div>
</div>
@endsection