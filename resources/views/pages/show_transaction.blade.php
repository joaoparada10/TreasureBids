@extends('layouts.app')
<link href="{{ url('css/transaction.css') }}" rel="stylesheet">

@section('title', 'Transaction Details')

@section('content')
<div class="container mt-4">
    <h1 class="mb-4">Transaction Details</h1>

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5>Transaction ID: {{ $transaction->id }}</h5>
        </div>
        <div class="card-body">
            <p><strong>Auction Title:</strong> 
                <a href="{{ route('auction.show', $transaction->auction->id) }}" class="text-decoration-none">
                    {{ $transaction->auction->title }}
                </a>
            </p>
            <p><strong>Auction Owner:</strong> 
                <a href="{{ route('profile', $transaction->auction->owner->username) }}" class="text-decoration-none">
                    {{ $transaction->auction->owner->username }}
                </a>
            </p>
            <p><strong>Buyer:</strong> 
                <a href="{{ route('profile', $transaction->buyer->username) }}" class="text-decoration-none">
                    {{ $transaction->buyer->username }}
                </a>
            </p>
            <p><strong>Shipping Address:</strong> {{ $transaction->buyer->address }}</p>
            <p><strong>Price:</strong> €{{ number_format($transaction->price, 2) }}</p>
            <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($transaction->date)->format('Y-m-d H:i') }}</p>

        </div>
    </div>

    <div class="mt-4">
        @if (auth()->id() === $transaction->buyer_id)
            <h4>Rate the Auction</h4>
            @if (!$transaction->auction->rating)
                <form action="{{ route('ratings.store', $transaction->auction->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="rating_value" class="form-label">Rating</label>
                        <select name="rating_value" id="rating_value" class="form-select" required>
                            <option value="">-- Select Rating --</option>
                            @for ($i = 1; $i <= 5; $i++)
                                <option value="{{ $i }}">{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="comment" class="form-label">Comment</label>
                        <textarea name="comment" id="comment" rows="3" class="form-control"></textarea>
                    </div>
                    <button type="submit" class="btn btn-success">Submit Rating</button>
                </form>
            @else
                <p class="text-muted">You have already rated this auction.</p>
            @endif
        @endif
    </div>

    <div class="mt-4">
        <a href="{{ route('auction.show', $transaction->auction->id) }}" class="btn btn-secondary">Back to Auction</a>
        <button class="btn btn-primary" onclick="window.print()">Print Transaction</button>
    </div>
</div>
@endsection
