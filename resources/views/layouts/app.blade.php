<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Travel Orders Management')</title>
    @vite('resources/css/app.css')

    <style>
        /* Optional: smooth font rendering */
        body { -webkit-font-smoothing: antialiased; }
    </style>
</head>

<body class="bg-gray-50 text-gray-900 min-h-screen flex flex-col">
    {{-- Navigation Bar --}}
    <nav class="bg-blue-900 text-white shadow-sm">
        <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
            <a href="{{ route('travel_order.index') }}" class="font-semibold text-lg tracking-wide">
                DOST-SDN Travel Orders
            </a>
            <div class="flex gap-6 text-sm">
                <a href="{{ route('travel_order.index') }}" class="hover:underline">Home</a>
                <a href="{{ route('travel_order.create') }}" class="hover:underline">New Travel Order</a>
            </div>
        </div>
    </nav>

    {{-- Page Content --}}
    <main class="grow max-w-7xl mx-auto w-full px-6 py-10">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="bg-gray-200 text-gray-600 text-xs text-center py-4 mt-auto">
        © {{ date('Y') }} Department of Science and Technology - Surigao del Norte
    </footer>

    @stack('scripts')
</body>
</html>
