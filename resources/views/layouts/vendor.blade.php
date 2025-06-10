<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Vendor - @yield('title', config('app.name', 'Laravel'))</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="flex h-screen">
        <aside class="w-64 bg-gray-800 text-white flex-shrink-0">
            <div class="p-4 text-xl font-semibold border-b border-gray-700">
                Vendor Panel
            </div>
            <nav class="mt-4">
                <a href="{{ route('vendor.dashboard') }}" class="block px-4 py-2 hover:bg-gray-700">Dashboard</a>
                <a href="{{ route('vendor.products.index') }}" class="block px-4 py-2 hover:bg-gray-700">My Products</a>
                <a href="{{ route('vendor.orders.index') }}" class="block px-4 py-2 hover:bg-gray-700">My Orders</a>
                <a href="{{ route('vendor.reports.index') }}" class="block px-4 py-2 hover:bg-gray-700">My Reports</a>
                <a href="{{ route('vendor.reviews.index') }}" class="block px-4 py-2 hover:bg-gray-700">My Reviews</a>
                <a href="{{ route('vendor.upgrade_request.form') }}" class="block px-4 py-2 hover:bg-gray-700">Request Tier Upgrade</a>
                {{-- Add more vendor links here --}}
            </nav>
        </aside>

        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="flex justify-between items-center bg-white shadow p-4">
                <div class="text-xl font-semibold">@yield('title')</div>
                <div class="flex items-center space-x-4"> {{-- Added flexbox for spacing --}}
                    @auth
                        @include('partials.notification_bell')
                    @endauth
                    @auth
                        @include('partials.mail_icon')
                    @endauth
                    @auth
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <a href="{{ route('logout') }}"
                               onclick="event.preventDefault(); this.closest('form').submit();"
                               class="text-blue-600 hover:underline">
                                Logout ({{ Auth::user()->name }})
                            </a>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Login</a>
                    @endauth
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto p-6">
                <!-- @if (session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif
                @if (session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                @endif
                @if (session('info'))
                    <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline">{{ session('info') }}</span>
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
                @endif -->

                @yield('content')
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>