<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Travel Orders Management')</title>
    <link rel="icon" type="image/png" href="{{ asset('dost-logo.png') }}">
    @vite('resources/css/app.css')

    <style>
        /* Optional: smooth font rendering */
        body {
            -webkit-font-smoothing: antialiased;
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-900 min-h-screen flex flex-col">
    {{-- Navigation Bar --}}
    <nav class="bg-blue-900 text-white shadow-sm">
        <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
            {{-- Logo + Title --}}
            <a href="{{ route('travel_order.index') }}"
                class="flex items-center space-x-3 font-semibold text-lg tracking-wide">
                <img src="{{ asset('dost-logo.png') }}" alt="DOST Logo" class="h-10 w-10 object-contain">
                <span class="leading-tight">DOST-SDN Travel Orders</span>
            </a>

            {{-- Navigation Links --}}
            <div class="flex items-center gap-6 text-sm">
                <a href="{{ route('travel_order.index') }}" class="hover:underline hover:text-gray-200 transition">
                    Home
                </a>
                <a href="{{ route('travel_order.create') }}" class="hover:underline hover:text-gray-200 transition">
                    New Travel Order
                </a>
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
