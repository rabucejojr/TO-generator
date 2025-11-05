@extends('layouts.app')

@section('title', 'Create Travel Order')

@section('content')
<div class="bg-white shadow rounded-lg p-6 max-w-4xl mx-auto">
    <h2 class="text-lg font-semibold text-gray-800 mb-6">New Travel Order</h2>

    <form action="{{ route('travel_order.store') }}" method="POST">
        @include('travel_order._form')
    </form>
</div>
@endsection
