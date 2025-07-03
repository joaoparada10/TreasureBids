<div class="auction-list">
    @foreach($auctions as $auction)
        @include('partials.auction_card', ['auction' => $auction])
    @endforeach
</div>