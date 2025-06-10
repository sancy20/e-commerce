@extends('layouts.vendor')

@section('title', 'Request Tier Upgrade')

@section('content')
    <div class="container mx-auto px-4 py-8 max-w-2xl">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">Request Tier Upgrade</h1>

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

            <p class="text-lg text-gray-700 mb-4">
                Your current vendor tier is: <span class="font-bold text-blue-600">{{ $user->vendor_tier }}</span>.
            </p>

            {{-- Conditional Content --}}
            @if ($isHighestTier)
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                    <strong class="font-bold">Congratulations!</strong>
                    <span class="block sm:inline">You are already at the highest vendor tier (Diamond). No further upgrades are available.</span>
                </div>
                <a href="{{ route('vendor.dashboard') }}" class="mt-4 inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Go to Dashboard</a>
            @elseif ($hasPendingRequest)
                <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-6" role="alert">
                    <strong class="font-bold">Request Pending!</strong>
                    <span class="block sm:inline">Your upgrade request to the {{ $user->requested_vendor_tier }} tier is currently under review. You will be notified of its status shortly.</span>
                </div>
                <p class="text-md text-gray-600 mb-4">
                    Requested on: {{ $user->upgrade_requested_at->format('M d, Y H:i A') }}
                </p>
                <a href="{{ route('vendor.dashboard') }}" class="mt-4 inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Go to Dashboard</a>
            @elseif (empty($availableTiers))
                <div class="bg-yellow-100 border border-yellow-400 text-yellow-800 px-4 py-3 rounded relative mb-6" role="alert">
                    <strong class="font-bold">No Upgrades Available!</strong>
                    <span class="block sm:inline">Based on your current tier, there are no higher tiers to request at this time.</span>
                </div>
                <a href="{{ route('vendor.dashboard') }}" class="mt-4 inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Go to Dashboard</a>
            @else
                {{-- Show the actual form --}}
                <p class="text-md text-gray-600 mb-6">
                    Request to upgrade to a higher tier to unlock more features and benefits.
                </p>

                <form action="{{ route('vendor.upgrade_request.submit') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label for="requested_tier" class="block text-gray-700 text-sm font-bold mb-2">Request Tier:</label>
                        <select name="requested_tier" id="requested_tier" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('requested_tier') border-red-500 @enderror" required>
                            <option value="">-- Select a Tier --</option>
                            @foreach ($availableTiers as $tierKey => $tierValue)
                                <option value="{{ $tierKey }}" {{ old('requested_tier') == $tierKey ? 'selected' : '' }}>{{ $tierValue }}</option>
                            @endforeach
                        </select>
                        @error('requested_tier')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label for="reason" class="block text-gray-700 text-sm font-bold mb-2">Reason for Upgrade (Optional):</label>
                        <textarea name="reason" id="reason" rows="4" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('reason') border-red-500 @enderror">{{ old('reason') }}</textarea>
                        @error('reason')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg focus:outline-none focus:shadow-outline text-xl">
                        Submit Upgrade Request
                    </button>
                </form>
            @endif
            {{-- End Conditional Content --}}

        </div>
    </div>
@endsection