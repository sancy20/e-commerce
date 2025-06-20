@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">My Profile</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Sidebar Navigation --}}
        <div class="md:col-span-1 bg-white rounded-lg shadow-md p-6">
            <nav class="space-y-2">
                <a href="{{ route('dashboard.index') }}" class="block px-4 py-2 text-lg text-gray-700 hover:bg-gray-100 rounded-md">Dashboard Overview</a>
                <a href="{{ route('dashboard.orders') }}" class="block px-4 py-2 text-lg text-gray-700 hover:bg-gray-100 rounded-md">My Orders</a>
                <a href="{{ route('dashboard.profile') }}" class="block px-4 py-2 text-lg font-medium text-blue-700 bg-blue-50 rounded-md">My Profile</a>
                <a href="{{ route('wishlist.index') }}" class="block px-4 py-2 text-lg text-gray-700 hover:bg-gray-100 rounded-md">My Wishlist</a>
                
                @if(auth()->user() && auth()->user()->isVendor())
                    <div class="border-t pt-2 mt-2">
                        <span class="block px-4 pt-2 text-xs font-semibold text-gray-500 uppercase">Vendor Panel</span>
                        <a href="{{ route('vendor.dashboard') }}" class="block px-4 py-2 text-lg text-gray-700 hover:bg-green-50 hover:text-green-700 rounded-md">
                            Go to Vendor Dashboard
                        </a>
                    </div>
                @endif
                
                <div class="border-t pt-2 mt-2">
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <button type="submit" class="block w-full text-left px-4 py-2 text-lg text-gray-700 hover:bg-red-50 hover:text-red-700 rounded-md">
                            Logout
                        </button>
                    </form>
                </div>
            </nav>
        </div>

        {{-- Profile Content --}}
        <div class="md:col-span-2 bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Update Profile Information</h2>

            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    {{ session('success') }}
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

            <form action="{{ route('dashboard.profile.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Name:</label>
                    <input type="text" name="name" id="name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('name') border-red-500 @enderror" value="{{ old('name', $user->name) }}" required>
                    @error('name')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email Address:</label>
                    <input type="email" name="email" id="email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('email') border-red-500 @enderror" value="{{ old('email', $user->email) }}" required>
                    @error('email')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Add address and phone fields if you added them to the users table --}}
                <div class="mb-4">
                    <label for="address" class="block text-gray-700 text-sm font-bold mb-2">Address:</label>
                    <input type="text" name="address" id="address" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('address') border-red-500 @enderror" value="{{ old('address', $user->address) }}">
                    @error('address')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="phone" class="block text-gray-700 text-sm font-bold mb-2">Phone:</label>
                    <input type="text" name="phone" id="phone" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('phone') border-red-500 @enderror" value="{{ old('phone', $user->phone) }}">
                    @error('phone')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>


                <div class="flex items-center justify-end">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Update Profile
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection