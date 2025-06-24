@extends('layouts.admin')
@section('title', 'Pending Attribute Requests')
@section('content')
    <h1 class="text-2xl font-bold mb-4">Pending Attribute Requests</h1>
    <div class="bg-white shadow-md rounded-lg p-6">
        @if($pendingValues->isEmpty())
            <p>There are no pending requests.</p>
        @else
            <table class="min-w-full">
                <!-- Table headers -->
                <tbody>
                    @foreach($pendingValues as $value)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">{{ $value->value }}</td>
                            <td class="px-6 py-4">{{ $value->attribute->name }}</td>
                            <td class="px-6 py-4">{{ $value->requester->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 flex space-x-2">
                                <form action="{{ route('admin.attributes.requests.approve', $value->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="bg-green-500 text-white px-3 py-1 rounded-md text-sm">Approve</button>
                                </form>
                                <form action="{{ route('admin.attributes.requests.destroy', $value->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to reject this request?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-red-500 text-white px-3 py-1 rounded-md text-sm">Reject</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection