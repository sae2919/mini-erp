@foreach($requests as $r)
<tr>
    <td>{{ $r->seller->name }}</td>
    <td>{{ $r->product->name }}</td>
    <td>{{ $r->quantity }}</td>
    <td>{{ $r->status }}</td>

    <td>
        <form method="POST" action="{{ route('stock-requests.approve',$r->id) }}">
            @csrf
            <button>Approve</button>
        </form>

        <form method="POST" action="{{ route('stock-requests.reject',$r->id) }}">
            @csrf
            <button>Reject</button>
        </form>
    </td>
</tr>
@endforeach