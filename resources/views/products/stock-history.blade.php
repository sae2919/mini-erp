@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">

    <h2 class="text-xl font-bold mb-4">
        Stock History - {{ $product->name }}
    </h2>

    <table class="min-w-full border border-gray-200 rounded-lg overflow-hidden">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-2 text-left">Date</th>
                <th class="p-2 text-left">Type</th>
                <th class="p-2 text-left">Qty</th>
                <th class="p-2 text-left">Reference</th>
            </tr>
        </thead>

        <tbody>
            @forelse($logs as $log)
            <tr class="border-t">
                <td class="p-2">{{ $log->created_at->format('d M Y H:i') }}</td>
                <td class="p-2">
                    @if($log->type === 'in')
                        <span class="text-green-600 font-semibold">IN</span>
                    @elseif($log->type === 'out')
                        <span class="text-red-600 font-semibold">OUT</span>
                    @else
                        ADJUST
                    @endif
                </td>
                <td class="p-2">{{ $log->quantity }}</td>
                <td class="p-2">{{ $log->reference_type ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center p-4 text-gray-500">
                    No stock movements found
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

</div>
@endsection