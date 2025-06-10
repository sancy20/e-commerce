@extends('layouts.admin')

@section('title', 'Vendor Payouts & History')

@section('content')
    <h1 class="text-2xl font-bold mb-6">Vendor Payouts & History</h1>

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
    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Outstanding Payouts Section --}}
    <div class="bg-white shadow-md rounded-lg p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">Vendors Outstanding Payouts</h2>
        <table class="min-w-full divide-y divide-gray-200 mb-4">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vendor</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tier</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Commission Rate</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Outstanding Amount</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($vendors as $vendor)
                    @php
                        $outstanding = $vendor->getOutstandingPayoutAmount();
                    @endphp
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $vendor->name }} ({{ $vendor->email }})</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $vendor->vendor_tier }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $vendor->commission_rate * 100 }}%</td>
                        <td class="px-6 py-4 whitespace-nowrap font-bold ${{ $outstanding > 0 ? 'text-red-600' : 'text-green-600' }}">${{ number_format($outstanding, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if ($outstanding > 0)
                                <form action="{{ route('admin.vendor-payouts.store') }}" method="POST" class="inline-block" onsubmit="return confirm('Record a payout of ${{ number_format($outstanding, 2) }} for {{ $vendor->name }}?');">
                                    @csrf
                                    <input type="hidden" name="vendor_id" value="{{ $vendor->id }}">
                                    <input type="hidden" name="amount" value="{{ number_format($outstanding, 2, '.', '') }}"> {{-- Ensure format for decimal --}}
                                    <input type="hidden" name="payment_method" value="Bank Transfer"> {{-- Default payment method --}}
                                    <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-2 rounded text-sm">Record Payout</button>
                                </form>
                            @else
                                <span class="text-gray-500 text-sm">No Payout Due</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">No approved vendors found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Recent Payout History Section --}}
    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-4">Recent Payout History</h2>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vendor</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($recentPayouts as $payout)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $payout->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $payout->vendor->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">${{ number_format($payout->amount, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $payout->payment_method ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ ucfirst($payout->status) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $payout->paid_at->format('M d, Y H:i A') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <form action="{{ route('admin.vendor-payouts.destroy', $payout->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this payout record? (Use with extreme caution)');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">No recent payout history.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection