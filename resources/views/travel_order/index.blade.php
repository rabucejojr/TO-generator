@extends('layouts.app')

@section('title', 'Travel Orders')

@section('content')
<div class="bg-white shadow rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-lg font-semibold text-gray-800">Travel Orders</h2>
        <a href="{{ route('travel_order.create') }}"
           class="bg-blue-800 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition">
           + New Travel Order
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 bg-green-100 border border-green-300 text-green-800 px-4 py-2 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="min-w-full border border-gray-200 text-sm text-left">
            <thead class="bg-gray-100 border-b">
                <tr>
                    <th class="py-2 px-3">No.</th>
                    <th class="py-2 px-3">Name</th>
                    <th class="py-2 px-3">Destination</th>
                    <th class="py-2 px-3">Dates</th>
                    <th class="py-2 px-3">Purpose</th>
                    <th class="py-2 px-3 text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($travelOrders as $order)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-2 px-3">{{ $order->travel_order_no }}</td>
                        <td class="py-2 px-3">
                            @php
                                $travelers = $order->name;

                                // Handle if $order->name is JSON string
                                if (is_string($travelers)) {
                                    $decoded = json_decode($travelers, true);
                                    $travelers = is_array($decoded) ? $decoded : [];
                                }

                                // Normalize data (handles arrays of strings or arrays of objects)
                                $names = collect($travelers)->map(function ($item) {
                                    if (is_array($item) && isset($item['name'])) {
                                        return $item['name']; // array format: ['name' => 'Juan Dela Cruz']
                                    } elseif (is_string($item)) {
                                        return $item; // simple string format
                                    }
                                    return null;
                                })->filter()->implode(', ');
                            @endphp

                            {{ $names ?: '—' }}
                        </td>

                        <td class="py-2 px-3">{{ $order->destination }}</td>
                        <td class="py-2 px-3">{{ $order->inclusive_dates }}</td>
                        <td class="py-2 px-3">{{ Str::limit($order->purpose, 40) }}</td>
                        <td class="py-2 px-3 flex gap-2 justify-center">
                            <a href="{{ route('travel_order.edit', $order) }}"
                               class="text-yellow-600 hover:text-yellow-700 font-medium">Edit</a>
                            <a href="{{ route('travel_order.preview', $order) }}" target="_blank"
                               class="text-blue-700 hover:text-blue-800 font-medium">PDF</a>
                            <form action="{{ route('travel_order.destroy', $order) }}" method="POST" onsubmit="return confirm('Delete this record?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="text-red-600 hover:text-red-700 font-medium">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-3 px-4 text-center text-gray-500">
                            No travel orders found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $travelOrders->links() }}
    </div>
</div>
@endsection
