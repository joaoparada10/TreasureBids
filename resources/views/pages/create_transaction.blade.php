@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Create Transaction</h1>
    <form action="{{ route('transactions.store', $auction->id) }}" method="POST">
        @csrf
        <p><strong>{{ $highestBid->auction->title }} </p>
        <p><strong>Winner:</strong> {{ $highestBid->bidder->username }}</p>
        <p><strong>Price:</strong> €{{ $highestBid->value }}</p>
        <button type="submit" class="btn btn-primary">Confirm and Send to Buyer</button>
    </form>
</div>
@endsection