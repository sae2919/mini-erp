@extends('layouts.app')

@section('content')
<div class="max-w-lg mx-auto bg-white p-6 rounded-lg shadow">

    <h2 class="text-lg font-bold mb-4">📦 Request Stock</h2>

    <form method="POST" action="{{ route('stock-requests.store') }}">
        @csrf

        <div class="mb-3">
            <label>Product</label>
            <select name="product_id" class="w-full border p-2 rounded" required>
                @foreach($products as $p)
                    <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->stock_quantity }} available)</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>Quantity</label>
            <input type="number" name="quantity" min="1" class="w-full border p-2 rounded" required>
        </div>

        <button class="bg-indigo-600 text-white px-4 py-2 rounded">Submit Request</button>
    </form>

</div>
@endsection