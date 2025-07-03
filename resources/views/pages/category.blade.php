@extends('layouts.app')
@section('content')

    <div class="container mt-5" style="margin-top: 1em !important;">
        <div class="card category-card" style="flex-direction: row">
            <div class="scroll-arrow" id="scroll-arrow-left">
                <i class="bi bi-arrow-left-circle"></i>
            </div>
            <div class="category-list" id="category-list">
                @foreach ($categories as $category)
                @include('partials.tag', ['category' => $category])
                @endforeach
            </div>
            <div class="scroll-arrow" id="scroll-arrow-right">
                <i class="bi bi-arrow-right-circle"></i>
            </div>
        </div>

        <div class="card current-category">
            Searching on: @include('partials.tag', ['category' => $myCategory])
        </div>

        @include('partials.auction_card_list', ['auctions' => $myCategory->auctions])
        
    </div>
    <script>auctionCardRedirect()</script>
    <script src="{{ asset('js/categoryList.js') }}"></script>
    <script>startAllCountdowns()</script>
    
    @endsection