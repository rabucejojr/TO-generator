@extends('layouts.app')

@section('title', 'Edit Travel Order')

@section('content')
<div class="bg-white shadow rounded-lg p-6 max-w-4xl mx-auto">
    <h2 class="text-lg font-semibold text-gray-800 mb-6">Edit Travel Order</h2>

    <form action="{{ route('travel_order.update', $travelOrder) }}" method="POST">
        @csrf
        @method('PUT')
        @include('travel_order._form', ['travelOrder' => $travelOrder])
    </form>
</div>
@endsection
