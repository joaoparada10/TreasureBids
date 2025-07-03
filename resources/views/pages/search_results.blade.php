@extends('layouts.app')

@section('title', 'Search Results')

@section('content')
<div class="container py-4">
    <h1 class="text-primary mb-4 text-center" style="margin-bottom: 2em">Search Results for <span style="color: var(--accent-color); font-style: italic">{{ $query }}</span></h1>

    <div class="filter card" style="background-color: var(--accent-color)">
        <form method="GET" class="filter-form" action="{{ route('search.results') }}">
            <input type="hidden" name="q" value="{{ request('q') }}">
            <div>
                <label for="min_price">Min Price:</label>
                <input type="number" class="text-box-filter" name="min_price" id="min_price" value="{{ request('min_price') }}">
            </div>
            <div>
                <label for="max_price">Max Price:</label>
                <input type="number" name="max_price" class="text-box-filter" id="max_price" value="{{ request('max_price') }}">
            </div>
            <div>
                <label for="status">Status:</label>
                <select name="status" class="text-box-filter" id="status">
                    <option value="">Any</option>
                    <option value="Active" {{ request('status') === 'Active' ? 'selected' : '' }}>Active</option>
                    <option value="Scheduled" {{ request('status') === 'Scheduled' ? 'selected' : '' }}>Scheduled</option>
                </select>
            </div>
            <button type="submit">Filter</button>
        </form>
    </div>

    

    @if ($auctions->isEmpty())
        <p class="text-muted text-center no-results">No results found for your search query. Try using different keywords.</p>
    @else
        <!-- Pagination at the top -->
        <div class="d-flex justify-content-center mt-4">
            {{ $auctions->appends(['q' => $query])->links() }}
        </div>

        <!-- Auction Cards -->
        <div class="auction-results">
            @include('partials.auction_card_list', ['auctions' => $auctions])
        </div>

        <!-- Pagination at the bottom -->
        <div class="d-flex justify-content-center mt-4">
            {{ $auctions->appends(['q' => $query])->links() }}
        </div>
    @endif
</div>
<script>auctionCardRedirect()</script>
<script>startAllCountdowns()</script>


@endsection
